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
    public function createProposal(array $data): Proposal;
    public function updateProposal(Proposal $proposal, array $data): Proposal;
    public function deleteProposal(Proposal $proposal): bool;
    public function getStudentProposals(Student $student): Collection;
    
    // Invitation methods
    public function getInvitations(User $user): Collection;
    public function processInvitation(int $id, string $action): Invitation;
    public function sendInvitation(Student $student, Lecturer $lecturer, ?int $proposalId = null): Invitation;
    
    public function autoProcessExpiredInvitations(): void;
    
    public function withdrawInvitation(int $id): void;
    
    public function getStudentInvitations(Student $student): Collection;
    
    public function canSendInvitation(Student $student, Lecturer $lecturer): bool;
    
    public function getActiveProposalsCount(Lecturer $lecturer): int;
    
    public function getActiveStudentsCount(Lecturer $lecturer): int;
    
    public function createInvitation(array $data): Invitation;
} 