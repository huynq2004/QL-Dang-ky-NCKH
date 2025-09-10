<?php

namespace App\Traits;

use App\Models\Proposal;
use Illuminate\Support\Facades\Auth;
use App\Facades\ProposalFacade;
use App\Facades\InvitationFacade;
use App\Facades\LecturerFacade;

trait HasCommonData
{
    protected function getCommonData()
    {
        $user = Auth::user();
        $data = [
            'studentProposals' => collect(),
            'proposals' => collect(),
            'lecturers' => collect(),
            'invitations' => collect()
        ];
        
        if ($user->role === 'student') {
            $data['proposals'] = ProposalFacade::getProposals();
            $data['studentProposals'] = ProposalFacade::getStudentProposals($user->student);
            $data['lecturers'] = LecturerFacade::getAvailableLecturers();
            $data['invitations'] = InvitationFacade::getStudentInvitations($user->student);
        } elseif ($user->role === 'lecturer') {
            $data['proposals'] = ProposalFacade::getLecturerProposals($user->lecturer);
        } else {
            $data['proposals'] = Proposal::with('lecturer.user')->get();
        }

        return $data;
    }
} 