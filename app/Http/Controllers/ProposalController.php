<?php

namespace App\Http\Controllers;

use App\Models\Proposal;
use App\Models\Invitation;
use App\Models\Lecturer;
use Illuminate\Http\Request;
use App\Contracts\ProposalServiceInterface;
use Illuminate\Support\Facades\Auth;
use App\Facades\ProposalFacade;
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
            abort(403, 'Only students can perform this action.');
        }
    }

    public function index()
    {
        $user = Auth::user();
        $data = [
            'activeTab' => 'available',
            'proposals' => Proposal::where('status', '!=', 'draft')->get(),
            'studentProposals' => collect(),
            'invitations' => collect(),
            'lecturers' => collect(),
            'lecturerProposals' => collect()
        ];

        if ($user->role === 'student') {
            $data['studentProposals'] = ProposalFacade::getStudentProposals($user->student);
            $data['invitations'] = ProposalFacade::getStudentInvitations($user->student);
            $data['lecturers'] = ProposalFacade::getAvailableLecturers();
        } elseif ($user->role === 'lecturer') {
            $data['invitations'] = Invitation::where('lecturer_id', $user->lecturer->id)->get();
            $data['lecturerProposals'] = Proposal::where('lecturer_id', $user->lecturer->id)->get();
        }
        // Admin can see all proposals without additional filtering

        return view('proposals.index', $data);
    }

    public function myTopics()
    {
        $user = Auth::user();
        $data = [
            'activeTab' => 'my-topics',
            'proposals' => Proposal::where('status', '!=', 'draft')->get(),
            'studentProposals' => collect(),
            'invitations' => collect(),
            'lecturers' => collect(),
            'lecturerProposals' => collect()
        ];

        if ($user->role === 'student') {
            $data['studentProposals'] = ProposalFacade::getStudentProposals($user->student);
            $data['invitations'] = ProposalFacade::getStudentInvitations($user->student);
            $data['lecturers'] = ProposalFacade::getAvailableLecturers();
        } elseif ($user->role === 'lecturer') {
            $data['invitations'] = Invitation::where('lecturer_id', $user->lecturer->id)->get();
            $data['lecturerProposals'] = Proposal::where('lecturer_id', $user->lecturer->id)->get();
        }
        // Admin can see all proposals without additional filtering

        return view('proposals.index', $data);
    }

    public function findSupervisor()
    {
        $user = Auth::user();
        if ($user->role !== 'student') {
            abort(403);
        }

        $data = [
            'activeTab' => 'lecturers',
            'proposals' => Proposal::where('status', '!=', 'draft')->get(),
            'studentProposals' => ProposalFacade::getStudentProposals($user->student),
            'invitations' => ProposalFacade::getStudentInvitations($user->student),
            'lecturers' => ProposalFacade::getAvailableLecturers()
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
            $existingRequest = Invitation::where([
                'student_id' => $user->student->id,
                'proposal_id' => $proposal->id
            ])->first();
            
            $canRequest = !$existingRequest && $proposal->status === 'active';
        }

        return view('proposals.show', compact('proposal', 'canRequest', 'existingRequest'));
    }

    public function create()
    {
        $this->checkStudentRole();
        return view('proposals.create');
    }

    public function edit(Proposal $proposal)
    {
        $this->checkStudentRole();

        if ($proposal->student_id !== Auth::user()->student->id) {
            return redirect()->route('proposals.index')
                ->with('error', 'You are not authorized to edit this proposal.');
        }

        if ($proposal->status === 'approved') {
            return redirect()->route('proposals.index')
                ->with('error', 'You cannot edit an approved proposal.');
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

        $proposal = new Proposal();
        $proposal->title = $request->title;
        $proposal->field = $request->field;
        $proposal->description = $request->description;
        
        if ($user->role === 'student') {
            $proposal->student_id = $user->student->id;
            $proposal->lecturer_id = $request->lecturer_id;
            $proposal->status = 'draft';
        } else {
            $proposal->lecturer_id = $user->lecturer->id;
            $proposal->status = 'active';
        }
        
        $proposal->save();

        if ($user->role === 'student') {
            // Create invitation for student's proposal
            Invitation::create([
                'student_id' => $user->student->id,
                'lecturer_id' => $request->lecturer_id,
                'proposal_id' => $proposal->id,
                'message' => $request->message,
                'status' => 'pending'
            ]);

            return redirect()->route('dashboard')
                ->with('success', 'Đề tài nghiên cứu đã được tạo và yêu cầu đã được gửi đến giảng viên. Vui lòng chờ phản hồi.');
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

        $this->proposalService->updateProposal($proposal, $validated);

        return redirect()->route('dashboard')->with('success', 'Research topic updated successfully.');
    }

    public function destroy(Proposal $proposal)
    {
        $user = Auth::user();
        
        if ($user->role !== 'lecturer' || $proposal->lecturer_id !== $user->lecturer->id) {
            return redirect()->back()->with('error', 'You are not authorized to delete this proposal.');
        }

        ProposalFacade::deleteProposal($proposal);

        return redirect()->route('dashboard')->with('success', 'Proposal deleted successfully.');
    }

    public function invitations()
    {
        $user = Auth::user();
        if ($user->role === 'admin') {
            $invitations = Invitation::all();
        } elseif ($user->role === 'student') {
            $invitations = Invitation::where('student_id', $user->student->id)->get();
        } else {
            $invitations = Invitation::where('lecturer_id', $user->lecturer->id)->get();
        }
        return view('proposals.invitations', compact('invitations'));
    }

    public function processInvitation(Request $request, Invitation $invitation)
    {
        $user = Auth::user();
        $action = $request->input('action');

        // Kiểm tra quyền xử lý invitation
        if ($user->role === 'student' && $invitation->student_id !== $user->student->id) {
            return back()->with('error', 'You are not authorized to process this invitation.');
        }
        if ($user->role === 'lecturer' && $invitation->lecturer_id !== $user->lecturer->id) {
            return back()->with('error', 'You are not authorized to process this invitation.');
        }

        // Kiểm tra action hợp lệ
        if (!in_array($action, ['accept', 'reject', 'withdraw'])) {
            return back()->with('error', 'Invalid action');
        }

        // Student chỉ có thể withdraw trong vòng 24h
        if ($user->role === 'student' && $action === 'withdraw') {
            $hours = $invitation->created_at->diffInHours(now());
            if ($hours > 24) {
                return back()->with('error', 'You can only withdraw an invitation within 24 hours of sending it.');
            }
        }

        // Xử lý action
        switch ($action) {
            case 'accept':
                $invitation->status = 'accepted';
                $invitation->proposal->status = 'active';
                
                // Create invitation for the student who created the proposal if they don't have one
                if ($invitation->proposal->student_id) {
                    $existingInvitation = Invitation::where([
                        'student_id' => $invitation->proposal->student_id,
                        'proposal_id' => $invitation->proposal->id
                    ])->first();

                    if (!$existingInvitation) {
                        Invitation::create([
                            'student_id' => $invitation->proposal->student_id,
                            'lecturer_id' => $invitation->proposal->lecturer_id,
                            'proposal_id' => $invitation->proposal->id,
                            'status' => 'accepted'
                        ]);
                    }
                }
                
                $invitation->proposal->save();
                break;
            case 'reject':
                $invitation->status = 'rejected';
                break;
            case 'withdraw':
                $invitation->status = 'withdrawn';
                break;
        }

        $invitation->save();
        return back()->with('success', 'Invitation ' . $action . 'ed successfully');
    }

    public function invite(Proposal $proposal)
    {
        $user = Auth::user();
        
        if ($user->role !== 'student') {
            return redirect()->back()->with('error', 'Only students can send invitations.');
        }

        if (!ProposalFacade::canSendInvitation($user->student, $proposal->lecturer)) {
            return redirect()->back()->with('error', 'You cannot send an invitation at this time.');
        }

        ProposalFacade::createInvitation([
            'student_id' => $user->student->id,
            'lecturer_id' => $proposal->lecturer_id,
            'proposal_id' => $proposal->id
        ]);

        return redirect()->route('dashboard')->with('success', 'Invitation sent successfully.');
    }

    public function sendInvitation(Proposal $proposal)
    {
        $user = Auth::user();
        
        if ($user->role !== 'student') {
            abort(403);
        }

        // Check if invitation already exists
        $existingInvitation = Invitation::where([
            'student_id' => $user->student->id,
            'lecturer_id' => $proposal->lecturer_id,
            'proposal_id' => $proposal->id,
        ])->exists();

        if ($existingInvitation) {
            return redirect()->back()->with('error', 'You have already sent an invitation for this proposal.');
        }

        Invitation::create([
            'student_id' => $user->student->id,
            'lecturer_id' => $proposal->lecturer_id,
            'proposal_id' => $proposal->id,
            'status' => 'pending'
        ]);

        return redirect()->route('dashboard')->with('success', 'Invitation sent successfully.');
    }

    public function withdrawInvitation(Invitation $invitation)
    {
        $user = Auth::user();
        
        if ($user->role !== 'student' || $invitation->student_id !== $user->student->id) {
            abort(403);
        }

        if ($invitation->status !== 'pending') {
            return redirect()->back()->with('error', 'You can only withdraw pending invitations.');
        }

        $invitation->delete();

        return redirect()->route('dashboard')->with('success', 'Invitation withdrawn successfully.');
    }

    public function requestToJoin(Proposal $proposal)
    {
        $user = Auth::user();
        
        if ($user->role !== 'student') {
            return redirect()->back()->with('error', 'Only students can request to join a research topic.');
        }

        // Check if the proposal already has 5 participating students
        $participatingStudentsCount = $proposal->invitations()->where('status', 'accepted')->count();
        if ($participatingStudentsCount >= 5) {
            return redirect()->back()->with('error', 'This research topic has reached the maximum number of participating students (5).');
        }

        // Check if student already has a request for this proposal
        $existingRequest = Invitation::where([
            'student_id' => $user->student->id,
            'proposal_id' => $proposal->id
        ])->first();

        if ($existingRequest) {
            return redirect()->back()->with('error', 'You have already sent a request for this research topic.');
        }

        // Create new invitation
        Invitation::create([
            'student_id' => $user->student->id,
            'lecturer_id' => $proposal->lecturer_id,
            'proposal_id' => $proposal->id,
            'status' => 'pending'
        ]);

        return redirect()->back()->with('success', 'Your request has been sent. Please wait for the supervisor\'s response.');
    }

    public function withdrawRequest(Invitation $invitation)
    {
        $user = Auth::user();
        
        if ($user->role !== 'student' || $invitation->student_id !== $user->student->id) {
            abort(403);
        }

        if ($invitation->status !== 'pending') {
            return redirect()->back()->with('error', 'You can only withdraw pending requests.');
        }

        $invitation->delete();

        return redirect()->back()->with('success', 'Request withdrawn successfully.');
    }
} 