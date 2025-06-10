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
 * @method static \App\Models\Proposal submitProposalWithInvitation(array $data)
 * @method static \App\Models\Proposal updateProposalAndNotify(\App\Models\Proposal $proposal, array $data)
 */
class ProposalFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'proposal';
    }
} 