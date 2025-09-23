<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\Student;
use App\Models\Lecturer;
use App\Models\User;
use App\Contracts\InvitationServiceInterface;
use Illuminate\Database\Eloquent\Collection;

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

    public function processInvitation(int $id, string $action): Invitation
    {
        $normalized = self::ACTION_SYNONYMS[$action] ?? $action;
        if (!in_array($normalized, self::VALID_STATUSES)) {
            throw new \InvalidArgumentException('Invalid status value');
        }

        $invitation = Invitation::findOrFail($id);
        
        // Check student limit when accepting
        if ($normalized === 'accepted') {
            $lecturer = $invitation->lecturer;
            $acceptedCount = $this->getActiveStudentsCount($lecturer);
            if ($acceptedCount >= $lecturer->max_students) {
                throw new \InvalidArgumentException('Lecturer has reached maximum student limit');
            }
        }
        
        $invitation->update(['status' => $normalized]);
        
        if ($normalized === 'accepted' && $invitation->proposal) {
            $invitation->proposal->update(['status' => 'active']);
        }

        return $invitation;
    }

    public function withdrawInvitation(int $id): void
    {
        $invitation = Invitation::findOrFail($id);
        
        // Chỉ cho phép thu hồi nếu trạng thái là pending
        if ($invitation->status !== 'pending') {
            throw new \InvalidArgumentException('Only pending invitations can be withdrawn');
        }
        
        $invitation->update(['status' => 'withdrawn']);
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

    private function getActiveStudentsCount(Lecturer $lecturer): int
    {
        return Invitation::where('lecturer_id', $lecturer->id)
            ->where('status', 'accepted')
            ->count();
    }
} 