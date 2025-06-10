<?php

namespace App\Contracts;

use App\Models\Invitation;
use App\Models\Student;
use App\Models\Lecturer;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface InvitationServiceInterface
{
    public function getInvitations(User $user): Collection;
    public function getStudentInvitations(Student $student): Collection;
    public function createInvitation(array $data): Invitation;
    public function processInvitation(int $id, string $action): Invitation;
    public function withdrawInvitation(int $id): void;
    public function canSendInvitation(Student $student, Lecturer $lecturer): bool;
    public function autoProcessExpiredInvitations(): void;
} 