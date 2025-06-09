<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Database\Eloquent\Collection getProposals()
 * @method static \App\Models\Proposal addProposal(array $data, \App\Models\Lecturer $lecturer)
 * @method static \Illuminate\Database\Eloquent\Collection getInvitations(\App\Models\User $user)
 * @method static \App\Models\Invitation processInvitation(int $id, string $action)
 * @method static \App\Models\Invitation sendInvitation(\App\Models\Student $student, \App\Models\Lecturer $lecturer, ?int $proposalId = null)
 * @method static void autoProcessExpiredInvitations()
 * @method static void withdrawInvitation(int $id)
 * @method static \Illuminate\Database\Eloquent\Collection getLecturerProposals(\App\Models\Lecturer $lecturer)
 * @method static \Illuminate\Database\Eloquent\Collection getStudentInvitations(\App\Models\Student $student)
 * @method static bool canSendInvitation(\App\Models\Student $student, \App\Models\Lecturer $lecturer)
 * @method static int getActiveProposalsCount(\App\Models\Lecturer $lecturer)
 * @method static int getActiveStudentsCount(\App\Models\Lecturer $lecturer)
 * @method static \Illuminate\Database\Eloquent\Collection getStudentProposals(\App\Models\Student $student)
 * @method static \Illuminate\Database\Eloquent\Collection getAvailableLecturers()
 */
class ProposalFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'proposal';
    }
} 