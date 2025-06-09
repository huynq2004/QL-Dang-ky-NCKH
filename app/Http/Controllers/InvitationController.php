<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Facades\ProposalFacade;
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
                ? ProposalFacade::getStudentInvitations($user->student)
                : Invitation::where('lecturer_id', $user->lecturer->id)->get(),
            'lecturers' => $user->role === 'student' ? ProposalFacade::getAvailableLecturers() : collect(),
            'lecturerProposals' => $user->role === 'lecturer' ? Proposal::where('lecturer_id', $user->lecturer->id)->get() : collect()
        ];

        return view('proposals.index', $data);
    }

    public function accept(Invitation $invitation)
    {
        $user = Auth::user();
        
        if ($user->role !== 'lecturer' || $invitation->lecturer_id !== $user->lecturer->id) {
            abort(403);
        }

        // Update invitation status
        $invitation->update(['status' => 'accepted']);

        // Update proposal status if this is a new topic request
        if ($invitation->proposal && $invitation->proposal->status === 'draft') {
            $invitation->proposal->update(['status' => 'active']);
        }

        return redirect()->back()->with('success', 'Request accepted successfully.');
    }

    public function reject(Invitation $invitation)
    {
        $user = Auth::user();
        
        if ($user->role !== 'lecturer' || $invitation->lecturer_id !== $user->lecturer->id) {
            abort(403);
        }

        $invitation->update(['status' => 'rejected']);

        return redirect()->back()->with('success', 'Request rejected successfully.');
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
        $existingInvitation = Invitation::where([
            'student_id' => $user->student->id,
            'lecturer_id' => $validated['lecturer_id'],
            'proposal_id' => $validated['proposal_id']
        ])->first();

        if ($existingInvitation) {
            return redirect()->back()->with('error', 'You have already sent a request for this research topic.');
        }

        // Create new invitation
        Invitation::create([
            'student_id' => $user->student->id,
            'lecturer_id' => $validated['lecturer_id'],
            'proposal_id' => $validated['proposal_id'],
            'message' => $validated['message'],
            'status' => 'pending'
        ]);

        return redirect()->back()->with('success', 'Request sent successfully.');
    }
} 