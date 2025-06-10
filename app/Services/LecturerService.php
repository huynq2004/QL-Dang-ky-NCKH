<?php

namespace App\Services;

use App\Models\Lecturer;
use App\Models\Proposal;
use App\Models\Invitation;
use App\Contracts\LecturerServiceInterface;
use Illuminate\Database\Eloquent\Collection;

class LecturerService implements LecturerServiceInterface
{
    public function getAvailableLecturers(): Collection
    {
        return Lecturer::with('user')
            ->where('status', 'active')
            ->get();
    }

    public function getActiveProposalsCount(Lecturer $lecturer): int
    {
        return Proposal::where('lecturer_id', $lecturer->id)
            ->where('status', 'active')
            ->count();
    }

    public function getActiveStudentsCount(Lecturer $lecturer): int
    {
        return Invitation::where('lecturer_id', $lecturer->id)
            ->where('status', 'accepted')
            ->count();
    }
} 