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

        InvitationFacade::processInvitation($invitation->id, 'accept');

        return redirect()->back()->with('success', 'Request accepted successfully.');
    }

    public function reject(Invitation $invitation)
    {
        $user = Auth::user();
        
        if ($user->role !== 'lecturer' || $invitation->lecturer_id !== $user->lecturer->id) {
            abort(403);
        }

        InvitationFacade::processInvitation($invitation->id, 'reject');

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
        $existingInvitation = InvitationFacade::findExistingInvitation(
            $user->student->id,
            $validated['proposal_id'],
            $validated['lecturer_id']
        );

        if ($existingInvitation) {
            return redirect()->back()->with('error', 'You have already sent a request for this research topic.');
        }

        // Create new invitation
        InvitationFacade::createInvitation([
            'student_id' => $user->student->id,
            'lecturer_id' => $validated['lecturer_id'],
            'proposal_id' => $validated['proposal_id'],
            'message' => $validated['message'] ?? null,
            'status' => 'pending'
        ]);

        return redirect()->back()->with('success', 'Request sent successfully.');
    }
} 