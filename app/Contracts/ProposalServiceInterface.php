<?php

namespace App\Contracts;

use App\Models\Invitation;
use App\Models\Lecturer;
use App\Models\Proposal;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface ProposalServiceInterface
{
    // Proposal methods
    public function getProposals(): Collection;
    public function getLecturerProposals(Lecturer $lecturer): Collection;
    public function getLecturerActiveProposals(Lecturer $lecturer): Collection;
    public function createProposal(array $data): Proposal;
    public function updateProposal(Proposal $proposal, array $data): Proposal;
    public function deleteProposal(Proposal $proposal): bool;
    public function getStudentProposals(Student $student): Collection;

    /**
     * Submit a proposal and create an invitation
     * 
     * @param array $data
     * @return mixed
     */
    public function submitProposalWithInvitation(array $data);
} 