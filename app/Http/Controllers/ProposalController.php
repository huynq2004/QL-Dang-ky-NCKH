<?php

namespace App\Http\Controllers;

use App\Models\Proposal;
use App\Models\Invitation;
use App\Models\Lecturer;
use Illuminate\Http\Request;
use App\Contracts\ProposalServiceInterface;
use Illuminate\Support\Facades\Auth;
use App\Facades\ProposalFacade;
use App\Facades\InvitationFacade;
use App\Facades\LecturerFacade;
use App\Traits\HasCommonData;

class ProposalController extends Controller
{
    use HasCommonData;

    protected $proposalService;

    public function __construct(ProposalServiceInterface $proposalService)
    {
        $this->proposalService = $proposalService;
        $this->middleware('auth');
    }

    protected function checkStudentRole()
    {
        if (Auth::user()->role !== 'student') {
            abort(403, 'Chỉ sinh viên mới được thực hiện thao tác này.');
        }
    }

    public function index()
    {
        $user = Auth::user();
        $data = [
            'activeTab' => 'available',
            'proposals' => ProposalFacade::getProposals(),
            'studentProposals' => collect(),
            'invitations' => collect(),
            'lecturers' => collect(),
            'lecturerProposals' => collect()
        ];

        if ($user->role === 'student') {
            $data['studentProposals'] = ProposalFacade::getStudentProposals($user->student);
            $data['invitations'] = InvitationFacade::getStudentInvitations($user->student);
            $data['lecturers'] = LecturerFacade::getAvailableLecturers();
        } elseif ($user->role === 'lecturer') {
            $data['invitations'] = InvitationFacade::getInvitations($user);
            $data['lecturerProposals'] = ProposalFacade::getLecturerProposals($user->lecturer);
        }

        return view('proposals.index', $data);
    }

    public function myTopics()
    {
        $user = Auth::user();
        $data = [
            'activeTab' => 'my-topics',
            'proposals' => collect(),
            'studentProposals' => collect(),
            'invitations' => collect(),
            'lecturers' => collect(),
            'lecturerProposals' => collect()
        ];

        if ($user->role === 'student') {
            $data['studentProposals'] = ProposalFacade::getStudentProposals($user->student);
            $data['invitations'] = InvitationFacade::getStudentInvitations($user->student);
            $data['lecturers'] = LecturerFacade::getAvailableLecturers();
        } elseif ($user->role === 'lecturer') {
            $data['invitations'] = InvitationFacade::getInvitations($user);
            $data['lecturerProposals'] = ProposalFacade::getLecturerActiveProposals($user->lecturer);
        }

        return view('proposals.index', $data);
    }

    public function findSupervisor(\Illuminate\Http\Request $request)
    {
        $user = Auth::user();
        if ($user->role !== 'student') {
            abort(403);
        }

        $data = [
            'activeTab' => 'lecturers',
            'proposals' => ProposalFacade::getProposals(),
            'studentProposals' => ProposalFacade::getStudentProposals($user->student),
            'invitations' => InvitationFacade::getStudentInvitations($user->student),
            'lecturers' => LecturerFacade::searchAvailableLecturersBy(
                $request->input('by', 'name'),
                $request->input('q')
            )
        ];

        return view('proposals.index', $data);
    }

    public function show(Proposal $proposal)
    {
        $proposal->load(['lecturer.user', 'student.user']);
        $user = Auth::user();
        
        $canRequest = false;
        $existingRequest = null;
        
        if ($user->role === 'student') {
            $existingRequest = InvitationFacade::findExistingInvitation(
                $user->student->id,
                $proposal->id
            );
            
            $canRequest = !$existingRequest && $proposal->status === 'active';
        }

        return view('proposals.show', compact('proposal', 'canRequest', 'existingRequest'));
    }

    public function create()
    {
        $user = Auth::user();
        if ($user->role !== 'student' && $user->role !== 'lecturer') {
            abort(403, 'Chỉ sinh viên và giảng viên mới được tạo đề tài.');
        }
        return view('proposals.create');
    }

    public function edit(Proposal $proposal)
    {
        $this->checkStudentRole();

        if ($proposal->student_id !== Auth::user()->student->id) {
            return redirect()->route('proposals.index')
                ->with('error', 'Bạn không có quyền sửa đề tài này.');
        }

        if ($proposal->status === 'approved') {
            return redirect()->route('proposals.index')
                ->with('error', 'Bạn không thể sửa đề tài đã được phê duyệt.');
        }

        return view('proposals.edit', compact('proposal'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Common validation rules
        $rules = [
            'title' => 'required|string|max:255',
            'field' => 'required|string|max:255',
            'description' => 'nullable|string',
        ];

        // Add role-specific validation rules
        if ($user->role === 'student') {
            $rules['lecturer_id'] = 'required|exists:lecturers,id';
            $rules['message'] = 'nullable|string|max:500';
        }

        $request->validate($rules);

        $data = $request->only(['title', 'field', 'description']);
        
        if ($user->role === 'student') {
            $data['student_id'] = $user->student->id;
            $data['lecturer_id'] = $request->lecturer_id;
            $data['status'] = 'draft';
            
            $proposal = ProposalFacade::submitProposalWithInvitation($data);
        } else {
            $lecturerId = optional($user->lecturer)->id;
            if (!$lecturerId) {
                return redirect()->back()->with('error', 'Không tìm thấy hồ sơ giảng viên. Vui lòng liên hệ quản trị viên.');
            }
            $data['lecturer_id'] = $lecturerId;
            $data['status'] = 'active';
            
            $proposal = ProposalFacade::createProposal($data);
        }

        return redirect()->route('dashboard')
            ->with('success', 'Đề tài nghiên cứu đã được tạo thành công.');
    }

    public function update(Request $request, Proposal $proposal)
    {
        $user = Auth::user();
        
        if ($user->role !== 'lecturer' || $proposal->lecturer_id !== $user->lecturer->id) {
            return redirect()->back()->with('error', 'You are not authorized to edit this proposal.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'field' => 'required|string|max:100',
            'description' => 'nullable|string',
            'status' => 'required|in:draft,active,completed,cancelled'
        ]);

        ProposalFacade::updateProposalAndNotify($proposal, $validated);

        return redirect()->route('dashboard')->with('success', 'Cập nhật đề tài thành công.');
    }

    public function destroy(Proposal $proposal)
    {
        $user = Auth::user();
        
        if ($user->role !== 'admin') {
            return redirect()->back()->with('error', 'Chỉ quản trị viên mới có quyền xoá đề tài.');
        }

        ProposalFacade::deleteProposal($proposal);

        return redirect()->route('dashboard')->with('success', 'Xoá đề tài thành công.');
    }

    public function invitations()
    {
        $user = Auth::user();
        $invitations = InvitationFacade::getInvitations($user);
        return view('proposals.invitations', compact('invitations'));
    }

    public function processInvitation(Request $request, Invitation $invitation)
    {
        $user = Auth::user();
        $action = $request->input('action');

        // Kiểm tra quyền xử lý invitation
        if ($user->role === 'student' && $invitation->student_id !== $user->student->id) {
            return back()->with('error', 'Bạn không có quyền xử lý lời mời này.');
        }
        if ($user->role === 'lecturer' && $invitation->lecturer_id !== $user->lecturer->id) {
            return back()->with('error', 'Bạn không có quyền xử lý lời mời này.');
        }

        // Kiểm tra action hợp lệ
        if (!in_array($action, ['accept', 'reject', 'withdraw'])) {
            return back()->with('error', 'Thao tác không hợp lệ');
        }

        // Student chỉ có thể withdraw trong vòng 24h
        if ($user->role === 'student' && $action === 'withdraw') {
            $hours = $invitation->created_at->diffInHours(now());
            if ($hours > 24) {
                return back()->with('error', 'Bạn chỉ có thể thu hồi lời mời trong vòng 24 giờ kể từ khi gửi.');
            }
        }

        InvitationFacade::processInvitation($invitation->id, $action);
        return back()->with('success', 'Xử lý lời mời thành công.');
    }

    public function invite(Proposal $proposal)
    {
        $user = Auth::user();
        
        if ($user->role !== 'student') {
            return redirect()->back()->with('error', 'Chỉ sinh viên mới có thể gửi lời mời.');
        }

        if (!InvitationFacade::canSendInvitation($user->student, $proposal->lecturer)) {
            return redirect()->back()->with('error', 'Hiện bạn không thể gửi lời mời.');
        }

        InvitationFacade::createInvitation([
            'student_id' => $user->student->id,
            'lecturer_id' => $proposal->lecturer_id,
            'proposal_id' => $proposal->id,
            'status' => 'pending'
        ]);

        return redirect()->route('dashboard')->with('success', 'Đã gửi lời mời thành công.');
    }

    public function sendInvitation(Proposal $proposal)
    {
        $user = Auth::user();
        
        if ($user->role !== 'student') {
            abort(403);
        }

        // Check if invitation already exists
        $existingInvitation = InvitationFacade::findExistingInvitation(
            $user->student->id,
            $proposal->id,
            $proposal->lecturer_id
        );

        if ($existingInvitation !== null) {
            return redirect()->back()->with('error', 'Bạn đã gửi lời mời cho đề tài này trước đó.');
        }

        InvitationFacade::createInvitation([
            'student_id' => $user->student->id,
            'lecturer_id' => $proposal->lecturer_id,
            'proposal_id' => $proposal->id,
            'status' => 'pending'
        ]);

        return redirect()->route('dashboard')->with('success', 'Đã gửi lời mời thành công.');
    }

    public function withdrawInvitation(Invitation $invitation)
    {
        $user = Auth::user();
        
        if ($user->role !== 'student' || $invitation->student_id !== $user->student->id) {
            abort(403);
        }

        if ($invitation->status !== 'pending') {
            return redirect()->back()->with('error', 'Chỉ có thể thu hồi các lời mời đang chờ xử lý.');
        }

        InvitationFacade::withdrawInvitation($invitation->id);

        return redirect()->route('dashboard')->with('success', 'Đã thu hồi lời mời thành công.');
    }

    public function requestToJoin(Proposal $proposal)
    {
        $user = Auth::user();
        
        if ($user->role !== 'student') {
            return redirect()->back()->with('error', 'Chỉ sinh viên mới có thể yêu cầu tham gia đề tài.');
        }

        // Pre-check capacity via service (avoid hard-coded limit)
        if (!InvitationFacade::proposalHasCapacity($proposal->id)) {
            return redirect()->back()->with('error', 'Đề tài đã đạt số lượng sinh viên tham gia tối đa.');
        }

        // Check if student already has a request for this proposal
        $existingRequest = InvitationFacade::findExistingInvitation(
            $user->student->id,
            $proposal->id
        );

        if ($existingRequest) {
            return redirect()->back()->with('error', 'Bạn đã gửi yêu cầu cho đề tài này trước đó.');
        }

        // Create new invitation
        InvitationFacade::createInvitation([
            'student_id' => $user->student->id,
            'lecturer_id' => $proposal->lecturer_id,
            'proposal_id' => $proposal->id,
            'status' => 'pending'
        ]);

        return redirect()->back()->with('success', 'Yêu cầu đã được gửi. Vui lòng chờ phản hồi từ giảng viên hướng dẫn.');
    }

    public function withdrawRequest(Invitation $invitation)
    {
        $user = Auth::user();
        
        if ($user->role !== 'student' || $invitation->student_id !== $user->student->id) {
            abort(403);
        }

        if ($invitation->status !== 'pending') {
            return redirect()->back()->with('error', 'Chỉ có thể thu hồi các yêu cầu đang chờ xử lý.');
        }

        InvitationFacade::withdrawInvitation($invitation->id);

        return redirect()->back()->with('success', 'Đã thu hồi yêu cầu thành công.');
    }
} 