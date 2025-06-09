<?php

namespace App\Services;

use App\Models\Proposal;
use App\Models\Student;
use App\Models\Lecturer;
use App\Models\Invitation;
use App\Models\User;
use App\Contracts\ProposalServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class ProposalService implements ProposalServiceInterface
{
    public function getProposals(): Collection
    {
        $user = Auth::user();

        if ($user->role === 'lecturer') {
            return Proposal::with('lecturer.user')
                ->where('lecturer_id', $user->lecturer->id)
                ->get();
        } elseif ($user->role === 'student') {
            return Proposal::with('lecturer.user')
                ->where('status', 'active')
                ->get();
        }

        return Proposal::with('lecturer.user')->get();
    }

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

    public function getLecturerProposals(Lecturer $lecturer): Collection
    {
        return Proposal::where('lecturer_id', $lecturer->id)->get();
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

    public function createProposal(array $data): Proposal
    {
        return Proposal::create($data);
    }

    public function updateProposal(Proposal $proposal, array $data): Proposal
    {
        $proposal->update($data);
        return $proposal;
    }

    public function deleteProposal(Proposal $proposal): bool
    {
        return $proposal->delete();
    }

    public function processInvitation(int $id, string $action): Invitation
    {
        $invitation = Invitation::findOrFail($id);
        $invitation->status = $action;
        $invitation->save();
        return $invitation;
    }

    public function sendInvitation(Student $student, Lecturer $lecturer, ?int $proposalId = null): Invitation
    {
        return Invitation::create([
            'student_id' => $student->id,
            'lecturer_id' => $lecturer->id,
            'proposal_id' => $proposalId,
            'status' => 'pending'
        ]);
    }

    public function autoProcessExpiredInvitations(): void
    {
        Invitation::where('status', 'pending')
            ->where('created_at', '<=', now()->subDays(7))
            ->update(['status' => 'expired']);
    }

    public function withdrawInvitation(int $id): void
    {
        $invitation = Invitation::findOrFail($id);
        $invitation->status = 'withdrawn';
        $invitation->save();
    }

    public function getActiveProposalsCount(Lecturer $lecturer): int
    {
        return Proposal::where('lecturer_id', $lecturer->id)
            ->where('status', 'active')
            ->count();
    }

    public function getActiveStudentsCount(Lecturer $lecturer): int
    {
        return Invitation::where('lecturer_id', $lecturer->id)
            ->where('status', 'accepted')
            ->count();
    }

    public function createInvitation(array $data): Invitation
    {
        return Invitation::create($data);
    }

    public function getStudentProposals(Student $student): Collection
    {
        return Proposal::where('student_id', $student->id)->get();
    }

    public function getAvailableLecturers(): Collection
    {
        return Lecturer::with('user')
            ->where('status', 'active')
            ->get();
    }
} 