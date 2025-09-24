<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\Student;
use App\Models\Lecturer;
use App\Models\User;
use App\Models\Proposal;
use App\Contracts\InvitationServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class InvitationService implements InvitationServiceInterface
{
    private const VALID_STATUSES = [
        'pending',
        'accepted',
        'rejected',
        'expired',
        'withdrawn'
    ];

    private const ACTION_SYNONYMS = [
        'accept' => 'accepted',
        'reject' => 'rejected',
        'withdraw' => 'withdrawn',
    ];

    public function getInvitations(User $user): Collection
    {
        if ($user->role === 'lecturer') {
            $lecturerId = optional($user->lecturer)->id;
            if (!$lecturerId) {
                return collect();
            }
            return Invitation::with(['student.user', 'proposal'])
                ->where('lecturer_id', $lecturerId)
                ->orderBy('created_at', 'desc')
                ->get();
        } elseif ($user->role === 'student') {
            $studentId = optional($user->student)->id;
            if (!$studentId) {
                return collect();
            }
            return Invitation::with(['lecturer.user', 'proposal'])
                ->where('student_id', $studentId)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return Invitation::with(['student.user', 'lecturer.user', 'proposal'])->get();
    }

    public function getStudentInvitations(Student $student): Collection
    {
        return Invitation::with(['lecturer.user', 'proposal'])
            ->where('student_id', $student->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function createInvitation(array $data): Invitation
    {
        $data['status'] = $data['status'] ?? 'pending';
        
        if (!in_array($data['status'], self::VALID_STATUSES)) {
            throw new \InvalidArgumentException('Invalid status value');
        }

        return Invitation::create($data);
    }

    public function lecturerCanAcceptMore(int $lecturerId): bool
    {
        $lecturer = Lecturer::findOrFail($lecturerId);
        $accepted = Invitation::where('lecturer_id', $lecturerId)
            ->where('status', 'accepted')->count();
        return $accepted < (int) $lecturer->max_students;
    }

    public function proposalHasCapacity(int $proposalId): bool
    {
        $proposal = Proposal::findOrFail($proposalId);
        $accepted = Invitation::where('proposal_id', $proposalId)
            ->where('status', 'accepted')->count();
        return $accepted < (int) optional($proposal->lecturer)->max_students;
    }

    public function processInvitation(int $id, string $action): Invitation
    {
        $normalized = self::ACTION_SYNONYMS[$action] ?? $action;
        if (!in_array($normalized, self::VALID_STATUSES)) {
            throw new \InvalidArgumentException('Invalid status value');
        }

        return DB::transaction(function () use ($id, $normalized) {
            $invitation = Invitation::with(['lecturer', 'proposal'])->findOrFail($id);

            if ($normalized === 'accepted') {
                if (!$this->lecturerCanAcceptMore($invitation->lecturer_id)) {
                    throw new \RuntimeException('Lecturer is at capacity');
                }
                if (!$this->proposalHasCapacity($invitation->proposal_id)) {
                    throw new \RuntimeException('Proposal has no capacity');
                }
            }

            $invitation->update([
                'status' => $normalized,
                'processed_at' => now(),
            ]);

            return $invitation;
        });
    }

    public function withdrawInvitation(int $id): void
    {
        DB::transaction(function () use ($id) {
            $invitation = Invitation::findOrFail($id);
            if ($invitation->status !== 'pending') {
                throw new \InvalidArgumentException('Only pending invitations can be withdrawn');
            }
            if (now()->diffInHours($invitation->created_at) >= 24) {
                throw new \InvalidArgumentException('You can only withdraw within 24 hours');
            }
            $invitation->update([
                'status' => 'withdrawn',
                'processed_at' => now(),
            ]);
        });
    }

    public function canSendInvitation(Student $student, Lecturer $lecturer): bool
    {
        // Check if student already has a pending or accepted invitation with this lecturer
        $existingInvitation = Invitation::where('student_id', $student->id)
            ->where('lecturer_id', $lecturer->id)
            ->whereIn('status', ['pending', 'accepted'])
            ->exists();

        if ($existingInvitation) {
            return false;
        }

        // Check if lecturer has reached their maximum student limit
        $acceptedInvitations = $this->getActiveStudentsCount($lecturer);
        return $acceptedInvitations < $lecturer->max_students;
    }

    public function autoProcessExpiredInvitations(): void
    {
        Invitation::where('status', 'pending')
            ->where('created_at', '<=', now()->subDays(7))
            ->update(['status' => 'expired']);
    }

    public function findExistingInvitation(int $studentId, int $proposalId, ?int $lecturerId = null): ?Invitation
    {
        $query = Invitation::where([
            'student_id' => $studentId,
            'proposal_id' => $proposalId,
        ]);

        if (!is_null($lecturerId)) {
            $query->where('lecturer_id', $lecturerId);
        }

        return $query->first();
    }

    public function deleteInvitation(int $id, User $actor): bool
    {
        $invitation = Invitation::findOrFail($id);

        // Pending invitations cannot be deleted
        if ($invitation->status === 'pending') {
            throw new \InvalidArgumentException('Cannot delete pending invitations');
        }

        // Permission: student who sent it or lecturer who received it, or admin
        if ($actor->role === 'student') {
            if (optional($actor->student)->id !== $invitation->student_id) {
                throw new \InvalidArgumentException('Not allowed to delete this invitation');
            }
        } elseif ($actor->role === 'lecturer') {
            if (optional($actor->lecturer)->id !== $invitation->lecturer_id) {
                throw new \InvalidArgumentException('Not allowed to delete this invitation');
            }
        } elseif ($actor->role !== 'admin') {
            throw new \InvalidArgumentException('Not allowed to delete this invitation');
        }

        return (bool) $invitation->delete();
    }

    private function getActiveStudentsCount(Lecturer $lecturer): int
    {
        return Invitation::where('lecturer_id', $lecturer->id)
            ->where('status', 'accepted')
            ->count();
    }
} 