<?php

namespace App\Services;

use App\Models\Proposal;
use App\Models\Invitation;
use App\Mail\InvitationMail;
use App\Mail\InvitationProcessedMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Contracts\ProposalServiceInterface;
use Carbon\Carbon;

class ProposalService implements ProposalServiceInterface
{
    public function getProposals()
    {
        return Proposal::with('lecturer')->get();
    }

    public function addProposal($data, $lecturer)
    {
        return Proposal::create([
            'title' => $data['title'],
            'field' => $data['field'],
            'description' => $data['description'] ?? null,
            'lecturer_id' => $lecturer->id,
        ]);
    }

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

    public function getInvitations($user)
    {
        if ($user->isStudent()) {
            return Invitation::where('student_id', $user->student->id)
                ->with(['lecturer', 'proposal'])
                ->get();
        }
        
        return Invitation::where('lecturer_id', $user->lecturer->id)
                ->with(['student', 'proposal'])
                ->get();
    }

    public function processInvitation($id, $action)
    {
        $invitation = Invitation::findOrFail($id);
        
        if ($action === 'accept' && !$invitation->proposal->canAcceptMoreMembers()) {
            throw new \Exception('Group has reached maximum members');
        }

        $invitation->process($action);
        
        // Send email notification
        Mail::to($invitation->student->user->email)
            ->send(new InvitationProcessedMail($invitation));

        return $invitation;
    }

    public function sendInvitation($student, $lecturer, $proposalId = null)
    {
        // Check if lecturer has reached max groups
        $activeGroups = Invitation::where('lecturer_id', $lecturer->id)
            ->where('status', 'accepted')
            ->distinct('proposal_id')
            ->count();

        if ($activeGroups >= 5) {
            throw new \Exception('Lecturer has reached maximum number of groups');
        }

        // Create invitation
        $invitation = Invitation::create([
            'student_id' => $student->id,
            'lecturer_id' => $lecturer->id,
            'proposal_id' => $proposalId,
            'status' => 'pending'
        ]);

        // Send email notification
        Mail::to($lecturer->user->email)
            ->send(new InvitationMail($invitation));

        return $invitation;
    }
} 