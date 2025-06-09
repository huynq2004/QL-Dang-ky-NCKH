<?php

namespace App\Services;

use App\Models\Proposal;
use App\Models\Invitation;
use App\Mail\InvitationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ProposalService
{
    public function getAllProposals()
    {
        return Proposal::with(['student', 'lecturer'])->latest()->get();
    }

    public function createProposal(array $data)
    {
        $proposal = Proposal::create($data);
        
        // Create invitation for the lecturer
        $invitation = Invitation::create([
            'student_id' => $data['student_id'],
            'lecturer_id' => $data['lecturer_id'],
            'proposal_id' => $proposal->id,
            'status' => 'pending',
            'email' => $proposal->lecturer->email,
            'token' => Str::random(32)
        ]);

        // Send invitation email
        Mail::to($invitation->email)->send(new InvitationMail($invitation));

        return $proposal;
    }
} 