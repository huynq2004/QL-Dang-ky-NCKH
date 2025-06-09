<?php

namespace App\Traits;

use App\Models\Proposal;
use App\Models\Invitation;
use App\Models\Lecturer;
use Illuminate\Support\Facades\Auth;

trait HasCommonData
{
    protected function getCommonData()
    {
        $user = Auth::user();
        $data = [
            'studentProposals' => collect(),
            'proposals' => collect(),
            'lecturers' => collect(),
            'invitations' => collect()
        ];
        
        if ($user->role === 'student') {
            $data['proposals'] = Proposal::with('lecturer.user')
                ->where('status', 'active')
                ->get();
            $data['studentProposals'] = Proposal::where('student_id', $user->student->id)
                ->get();
            $data['lecturers'] = Lecturer::with('user')
                ->where('status', 'active')
                ->get();
            $data['invitations'] = Invitation::with(['lecturer.user', 'proposal'])
                ->where('student_id', $user->student->id)
                ->orderBy('created_at', 'desc')
                ->get();
        } elseif ($user->role === 'lecturer') {
            $data['proposals'] = Proposal::with('lecturer.user')
                ->where('lecturer_id', $user->lecturer->id)
                ->get();
        } else {
            $data['proposals'] = Proposal::with('lecturer.user')->get();
        }

        return $data;
    }
} 