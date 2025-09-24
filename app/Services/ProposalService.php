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

    public function getLecturerActiveProposals(Lecturer $lecturer): Collection
    {
        // Chỉ lấy đề tài đã được chấp nhận (đang tham gia)
        return Proposal::with(['lecturer.user', 'student.user', 'invitations'])
            ->where(function($query) use ($lecturer) {
                // Điều kiện 1: Đề tài do giảng viên tạo VÀ có ít nhất 1 invitation được chấp nhận
                $query->where('lecturer_id', $lecturer->id)
                    ->whereHas('invitations', function($invitationQuery) {
                        $invitationQuery->where('status', 'accepted');
                    })
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

    // Invitation-related operations are handled by InvitationService

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

    public function getActiveProposalsCount(Lecturer $lecturer): int
    {
        return Proposal::where('lecturer_id', $lecturer->id)
            ->where('status', 'active')
            ->count();
    }

    public function getStudentProposals(Student $student): Collection
    {
        return Proposal::where('student_id', $student->id)->get();
    }

    // Available lecturers handled by LecturerService

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

    /**
     * Update proposal and notify related users
     * 
     * @param Proposal $proposal
     * @param array $data
     * @return Proposal
     */
    public function updateProposalAndNotify(Proposal $proposal, array $data): Proposal
    {
        DB::beginTransaction();
        try {
            $proposal->update($data);

            // Notify students who have accepted invitations for this proposal
            $acceptedInvitations = $proposal->invitations()
                ->where('status', 'accepted')
                ->with('student.user')
                ->get();

            foreach ($acceptedInvitations as $invitation) {
                // Here you can add notification logic
                // For example using Laravel's notification system:
                // $invitation->student->user->notify(new ProposalUpdatedNotification($proposal));
            }

            DB::commit();
            return $proposal;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
} 