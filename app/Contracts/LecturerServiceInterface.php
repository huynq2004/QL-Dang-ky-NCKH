<?php

namespace App\Contracts;

use App\Models\Lecturer;
use Illuminate\Database\Eloquent\Collection;

interface LecturerServiceInterface
{
    public function getAvailableLecturers(): Collection;
    public function getActiveProposalsCount(Lecturer $lecturer): int;
    public function getActiveStudentsCount(Lecturer $lecturer): int;
} 