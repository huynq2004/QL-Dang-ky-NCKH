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
    public function deleteInvitation(int $id, \App\Models\User $actor): bool;
    public function canSendInvitation(Student $student, Lecturer $lecturer): bool;
    public function autoProcessExpiredInvitations(): void;
    public function findExistingInvitation(int $studentId, int $proposalId, ?int $lecturerId = null): ?Invitation;
    public function lecturerCanAcceptMore(int $lecturerId): bool;
    public function proposalHasCapacity(int $proposalId): bool;
} 