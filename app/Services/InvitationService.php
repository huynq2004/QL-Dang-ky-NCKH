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
        'expired'
    ];

    public function getInvitations(User $user): Collection
    {
        if ($user->role === 'lecturer') {
            return Invitation::with(['student.user', 'proposal'])
                ->where('lecturer_id', $user->lecturer->id)
                ->orderBy('created_at', 'desc')
                ->get();
        } elseif ($user->role === 'student') {
            return Invitation::with(['lecturer.user', 'proposal'])
                ->where('student_id', $user->student->id)
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
        if (!in_array($action, self::VALID_STATUSES)) {
            throw new \InvalidArgumentException('Invalid status value');
        }

        $invitation = Invitation::findOrFail($id);
        $invitation->update(['status' => $action]);
        
        if ($action === 'accepted' && $invitation->proposal) {
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
        
        $invitation->delete();
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
        return $acceptedInvitations < ($lecturer->max_students ?? 5);
    }

    public function autoProcessExpiredInvitations(): void
    {
        Invitation::where('status', 'pending')
            ->where('created_at', '<=', now()->subDays(7))
            ->update(['status' => 'expired']);
    }

    private function getActiveStudentsCount(Lecturer $lecturer): int
    {
        return Invitation::where('lecturer_id', $lecturer->id)
            ->where('status', 'accepted')
            ->count();
    }
} 