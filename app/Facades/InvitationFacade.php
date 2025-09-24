<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Database\Eloquent\Collection getInvitations(\App\Models\User $user)
 * @method static \Illuminate\Database\Eloquent\Collection getStudentInvitations(\App\Models\Student $student)
 * @method static \App\Models\Invitation createInvitation(array $data)
 * @method static \App\Models\Invitation processInvitation(int $id, string $action)
 * @method static void withdrawInvitation(int $id)
 * @method static bool canSendInvitation(\App\Models\Student $student, \App\Models\Lecturer $lecturer)
 * @method static void autoProcessExpiredInvitations()
 * @method static null|\App\Models\Invitation findExistingInvitation(int $studentId, int $proposalId, ?int $lecturerId = null)
 * @method static bool deleteInvitation(int $id, \App\Models\User $actor)
 */
class InvitationFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'invitation';
    }
} 