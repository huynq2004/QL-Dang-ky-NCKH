<?php

namespace App\Contracts;

interface ProposalServiceInterface
{
    // Proposal methods
    public function getProposals();
    public function addProposal(array $data, $lecturer);
    
    // Invitation methods
    public function getInvitations($user);
    public function processInvitation($id, $action);
    public function sendInvitation($student, $lecturer, $proposalId = null);
} 