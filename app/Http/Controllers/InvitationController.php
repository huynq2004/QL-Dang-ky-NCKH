<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Facades\ProposalFacade;

class InvitationController extends Controller
{
    public function myInvitations()
    {
        $user = Auth::user();
        
        if ($user->role !== 'student') {
            abort(403);
        }

        $data = [
            'activeTab' => 'invitations',
            'proposals' => ProposalFacade::getProposals(),
            'studentProposals' => ProposalFacade::getStudentProposals($user->student),
            'invitations' => ProposalFacade::getStudentInvitations($user->student),
            'lecturers' => ProposalFacade::getAvailableLecturers()
        ];

        return view('proposals.index', $data);
    }

    public function accept(Invitation $invitation)
    {
        $user = Auth::user();
        
        if ($user->role !== 'lecturer' || $invitation->lecturer_id !== $user->lecturer->id) {
            abort(403);
        }

        $invitation->update(['status' => 'accepted']);

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
} 