<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Database\Eloquent\Collection getProposals()
 * @method static \App\Models\Proposal createProposal(array $data)
 * @method static \App\Models\Proposal updateProposal(\App\Models\Proposal $proposal, array $data)
 * @method static bool deleteProposal(\App\Models\Proposal $proposal)
 * @method static \Illuminate\Database\Eloquent\Collection getLecturerProposals(\App\Models\Lecturer $lecturer)
 * @method static \Illuminate\Database\Eloquent\Collection getStudentProposals(\App\Models\Student $student)
 * 
 * @method static \App\Models\Proposal submitProposalWithInvitation(array $data)
 * @method static \App\Models\Proposal updateProposalAndNotify(\App\Models\Proposal $proposal, array $data)
 */
class ProposalFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'proposal';
    }

    /**
     * Submit a new proposal and create an invitation in one step
     */
    public static function submitProposalWithInvitation(array $data)
    {
        $proposal = static::createProposal($data);
        
        if (isset($data['student_id']) && isset($data['lecturer_id'])) {
            InvitationFacade::createInvitation([
                'student_id' => $data['student_id'],
                'lecturer_id' => $data['lecturer_id'],
                'proposal_id' => $proposal->id,
                'status' => 'pending'
            ]);
        }

        return $proposal;
    }

    /**
     * Update a proposal and notify relevant parties
     */
    public static function updateProposalAndNotify(Proposal $proposal, array $data)
    {
        $updatedProposal = static::updateProposal($proposal, $data);
        
        // Here you would add notification logic
        // For example: NotificationFacade::notifyProposalUpdate($updatedProposal);
        
        return $updatedProposal;
    }
} 