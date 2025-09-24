<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Facades\ProposalFacade;
use App\Facades\InvitationFacade;
use App\Facades\LecturerFacade;
use App\Models\Proposal;

class InvitationController extends Controller
{
    public function myInvitations()
    {
        $user = Auth::user();
        
        $data = [
            'activeTab' => 'invitations',
            'proposals' => ProposalFacade::getProposals(),
            'studentProposals' => $user->role === 'student' ? ProposalFacade::getStudentProposals($user->student) : collect(),
            'invitations' => $user->role === 'student' 
                ? InvitationFacade::getStudentInvitations($user->student)
                : InvitationFacade::getInvitations($user),
            'lecturers' => $user->role === 'student' ? LecturerFacade::getAvailableLecturers() : collect(),
            'lecturerProposals' => $user->role === 'lecturer' ? ProposalFacade::getLecturerProposals($user->lecturer) : collect()
        ];

        return view('proposals.index', $data);
    }

    public function accept(Invitation $invitation)
    {
        $user = Auth::user();
        
        if ($user->role !== 'lecturer' || $invitation->lecturer_id !== $user->lecturer->id) {
            abort(403);
        }

        try {
            InvitationFacade::processInvitation($invitation->id, 'accept');
            return redirect()->back()->with('success', 'Đã chấp nhận yêu cầu thành công.');
        } catch (\Throwable $e) {
            $message = $e->getMessage();
            if (str_contains($message, 'Lecturer is at capacity') || str_contains($message, 'Proposal has no capacity')) {
                $message = 'Không thể chấp nhận lời mời: Đề tài/giảng viên đã đạt số lượng tối đa.';
            } else {
                $message = 'Đã xảy ra lỗi. ' . $message;
            }
            return redirect()->back()->with('error', $message);
        }
    }

    public function reject(Invitation $invitation)
    {
        $user = Auth::user();
        
        if ($user->role !== 'lecturer' || $invitation->lecturer_id !== $user->lecturer->id) {
            abort(403);
        }

        try {
            InvitationFacade::processInvitation($invitation->id, 'reject');
            return redirect()->back()->with('success', 'Đã từ chối yêu cầu thành công.');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role !== 'student') {
            abort(403);
        }

        $validated = $request->validate([
            'lecturer_id' => 'required|exists:lecturers,id',
            'proposal_id' => 'required|exists:proposals,id',
            'message' => 'nullable|string|max:500'
        ]);

        // Check if an invitation already exists
        $existingInvitation = InvitationFacade::findExistingInvitation(
            $user->student->id,
            $validated['proposal_id'],
            $validated['lecturer_id']
        );

        if ($existingInvitation) {
            return redirect()->back()->with('error', 'Bạn đã gửi yêu cầu cho đề tài này trước đó.');
        }

        // Create new invitation
        InvitationFacade::createInvitation([
            'student_id' => $user->student->id,
            'lecturer_id' => $validated['lecturer_id'],
            'proposal_id' => $validated['proposal_id'],
            'message' => $validated['message'] ?? null,
            'status' => 'pending'
        ]);

        return redirect()->back()->with('success', 'Yêu cầu đã được gửi thành công.');
    }

    public function destroy(Invitation $invitation)
    {
        $user = Auth::user();

        try {
            InvitationFacade::deleteInvitation($invitation->id, $user);
            return redirect()->back()->with('success', 'Đã xoá lời mời khỏi hệ thống.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', 'Không thể xoá lời mời: ' . $e->getMessage());
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->with('error', 'Đã xảy ra lỗi khi xoá lời mời.');
        }
    }
} 