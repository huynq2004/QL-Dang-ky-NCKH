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

    /**
     * Xử lý lời mời với action chung (hỗ trợ kiểm thử hộp đen)
     * 
     * @param Request $request
     * @param Invitation $invitation
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function processInvitation(Request $request, Invitation $invitation)
    {
        $validated = $request->validate([
            'action' => 'required|in:accept,reject,withdraw'
        ]);

        $action = $validated['action'];
        $user = Auth::user();

        try {
            switch ($action) {
                case 'accept':
                    if ($user->role !== 'lecturer' || $invitation->lecturer_id !== $user->lecturer->id) {
                        if ($user->role !== 'admin') {
                            return response()->json([
                                'success' => false,
                                'message' => 'Không có quyền xử lý lời mời này'
                            ], 403);
                        }
                    }
                    InvitationFacade::processInvitation($invitation->id, 'accept');
                    $message = 'Chấp nhận lời mời thành công';
                    break;

                case 'reject':
                    if ($user->role !== 'lecturer' || $invitation->lecturer_id !== $user->lecturer->id) {
                        if ($user->role !== 'admin') {
                            return response()->json([
                                'success' => false,
                                'message' => 'Không có quyền xử lý lời mời này'
                            ], 403);
                        }
                    }
                    InvitationFacade::processInvitation($invitation->id, 'reject');
                    $message = 'Từ chối lời mời thành công';
                    break;

                case 'withdraw':
                    if ($user->role !== 'student' || $invitation->student_id !== $user->student->id) {
                        if ($user->role !== 'admin') {
                            return response()->json([
                                'success' => false,
                                'message' => 'Không có quyền thu hồi lời mời này'
                            ], 403);
                        }
                    }
                    
                    // Kiểm tra thời gian thu hồi (24 giờ)
                    if ($invitation->created_at->diffInHours(now()) >= 24) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Không thể thu hồi: quá 24 giờ kể từ khi gửi'
                        ], 422);
                    }
                    
                    InvitationFacade::withdrawInvitation($invitation->id);
                    $message = 'Thu hồi lời mời thành công';
                    break;
            }

            // Trả về JSON response cho API testing
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message
                ], 200);
            }

            // Trả về redirect cho web interface
            return redirect()->back()->with('success', $message);

        } catch (\Throwable $e) {
            $errorMessage = $e->getMessage();
            
            // Xử lý các lỗi cụ thể
            if (str_contains($errorMessage, 'Lecturer is at capacity') || str_contains($errorMessage, 'Proposal has no capacity')) {
                $errorMessage = 'Không thể chấp nhận lời mời: Đề tài/giảng viên đã đạt số lượng tối đa.';
            } elseif (str_contains($errorMessage, 'Only pending invitations')) {
                $errorMessage = 'Chỉ có thể xử lý lời mời ở trạng thái đang chờ';
            } elseif (str_contains($errorMessage, 'You can only withdraw within 24 hours')) {
                $errorMessage = 'Không thể thu hồi: quá 24 giờ kể từ khi gửi';
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 422);
            }

            return redirect()->back()->with('error', $errorMessage);
        }
    }
} 