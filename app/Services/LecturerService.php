<?php

namespace App\Services;

use App\Models\Lecturer;
use App\Models\Proposal;
use App\Models\Invitation;
use App\Contracts\LecturerServiceInterface;
use Illuminate\Database\Eloquent\Collection;

class LecturerService implements LecturerServiceInterface
{
    public function searchAvailableLecturersBy(string $by = 'name', ?string $q = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = Lecturer::with('user')->where('status', 'active');

        $term = trim((string) $q);
        if ($term !== '') {
            switch ($by) {
                case 'code':
                    $query->where('lecturer_id', 'like', "%{$term}%");
                    break;
                case 'department':
                    $query->where('department', 'like', "%{$term}%");
                    break;
                case 'name':
                default:
                    $query->whereHas('user', function ($q2) use ($term) {
                        $q2->where('name', 'like', "%{$term}%");
                    });
                    break;
            }
        }

        return $query->orderBy('id', 'desc')->get();
    }
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