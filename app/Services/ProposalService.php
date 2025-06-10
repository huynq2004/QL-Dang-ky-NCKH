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
use App\Services\InvitationService;
use Illuminate\Support\Facades\DB;

class ProposalService implements ProposalServiceInterface
{
    protected $invitationService;

    public function __construct(InvitationService $invitationService)
    {
        $this->invitationService = $invitationService;
    }

    public function getProposals(): Collection
    {
        // Chỉ hiển thị đề tài active cho tất cả người dùng
        return Proposal::with(['lecturer.user', 'student.user'])
            ->where('status', 'active')
            ->get();
    }

    public function getLecturerProposals(Lecturer $lecturer): Collection
    {
        // Lấy tất cả đề tài liên quan đến giảng viên
        return Proposal::with(['lecturer.user', 'student.user', 'invitations'])
            ->where(function($query) use ($lecturer) {
                // Điều kiện 1: Đề tài do giảng viên tạo VÀ không có bất kỳ lời mời nào
                $query->where('lecturer_id', $lecturer->id)
                    ->whereDoesntHave('invitations')
                    // HOẶC
                    ->orWhere(function($subQuery) use ($lecturer) {
                        // Điều kiện 2: Đề tài mà giảng viên được mời VÀ đã chấp nhận
                        $subQuery->whereHas('invitations', function($invitationQuery) use ($lecturer) {
                            $invitationQuery->where([
                                'lecturer_id' => $lecturer->id,
                                'status' => 'accepted'
                            ]);
                        });
                    });
            })
            ->orderBy('created_at', 'desc')
            ->get();
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

    /**
     * Submit a proposal and create an invitation
     * 
     * @param array $data
     * @return mixed
     */
    public function submitProposalWithInvitation(array $data)
    {
        DB::beginTransaction();
        try {
            $proposal = Proposal::create($data);
            
            if (isset($data['student_id']) && isset($data['lecturer_id'])) {
                $this->invitationService->createInvitation([
                    'student_id' => $data['student_id'],
                    'lecturer_id' => $data['lecturer_id'],
                    'proposal_id' => $proposal->id,
                    'status' => 'pending'
                ]);
            }

            DB::commit();
            return $proposal;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
} 