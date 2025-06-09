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
            'proposals' => ProposalFacade::getProposals(),
            'studentProposals' => $user->role === 'student' ? ProposalFacade::getStudentProposals($user->student) : collect(),
            'invitations' => $user->role === 'student' ? ProposalFacade::getStudentInvitations($user->student) : collect(),
            'lecturers' => $user->role === 'student' ? ProposalFacade::getAvailableLecturers() : collect()
        ];

        return view('proposals.index', $data);
    }

    public function myTopics()
    {
        $user = Auth::user();
        if ($user->role !== 'student') {
            abort(403);
        }

        $data = [
            'activeTab' => 'my-topics',
            'proposals' => ProposalFacade::getProposals(),
            'studentProposals' => ProposalFacade::getStudentProposals($user->student),
            'invitations' => ProposalFacade::getStudentInvitations($user->student),
            'lecturers' => ProposalFacade::getAvailableLecturers()
        ];

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
            'proposals' => ProposalFacade::getProposals(),
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
        $request->validate([
            'title' => 'required|string|max:255',
            'field' => 'required|string|max:255',
            'description' => 'nullable|string',
            'lecturer_id' => 'required|exists:lecturers,id'
        ]);

        $user = Auth::user();
        
        $proposal = new Proposal();
        $proposal->title = $request->title;
        $proposal->field = $request->field;
        $proposal->description = $request->description;
        $proposal->lecturer_id = $request->lecturer_id;
        
        if ($user->role === 'student') {
            $proposal->student_id = $user->student->id;
            $proposal->status = 'pending';
        } else {
            $proposal->status = 'active';
        }
        
        $proposal->save();

        if ($user->role === 'student') {
            // Create invitation
            Invitation::create([
                'student_id' => $user->student->id,
                'lecturer_id' => $request->lecturer_id,
                'proposal_id' => $proposal->id,
                'status' => 'pending'
            ]);
        }

        return redirect()->route('my-topics')->with('success', 'Research topic created successfully.');
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
            'description' => 'nullable|string'
        ]);

        ProposalFacade::updateProposal($proposal, $validated);

        return redirect()->route('proposals.index')->with('success', 'Proposal updated successfully.');
    }

    public function destroy(Proposal $proposal)
    {
        $user = Auth::user();
        
        if ($user->role !== 'lecturer' || $proposal->lecturer_id !== $user->lecturer->id) {
            return redirect()->back()->with('error', 'You are not authorized to delete this proposal.');
        }

        ProposalFacade::deleteProposal($proposal);

        return redirect()->route('proposals.index')->with('success', 'Proposal deleted successfully.');
    }

    public function invitations()
    {
        $user = Auth::user();
        if ($user->role === 'student') {
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
                $invitation->proposal->status = 'approved';
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

        return redirect()->route('proposals.index')->with('success', 'Invitation sent successfully.');
    }

    public function sendInvitation(Proposal $proposal)
    {
        $user = auth()->user();
        
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

        return redirect()->back()->with('success', 'Invitation sent successfully.');
    }

    public function withdrawInvitation(Invitation $invitation)
    {
        $user = auth()->user();
        
        if ($user->role !== 'student' || $invitation->student_id !== $user->student->id) {
            abort(403);
        }

        if ($invitation->status !== 'pending') {
            return redirect()->back()->with('error', 'You can only withdraw pending invitations.');
        }

        $invitation->delete();

        return redirect()->back()->with('success', 'Invitation withdrawn successfully.');
    }

    public function requestToJoin(Proposal $proposal)
    {
        $user = Auth::user();
        
        if ($user->role !== 'student') {
            abort(403);
        }

        // Check if request already exists
        $existingRequest = Invitation::where([
            'student_id' => $user->student->id,
            'proposal_id' => $proposal->id
        ])->exists();

        if ($existingRequest) {
            return redirect()->back()->with('error', 'You have already requested to join this research topic.');
        }

        Invitation::create([
            'student_id' => $user->student->id,
            'lecturer_id' => $proposal->lecturer_id,
            'proposal_id' => $proposal->id,
            'status' => 'pending'
        ]);

        return redirect()->back()->with('success', 'Request sent successfully.');
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