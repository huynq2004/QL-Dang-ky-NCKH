<?php

namespace Database\Seeders;

use App\Models\Lecturer;
use App\Models\User;
use Illuminate\Database\Seeder;

class LecturerSeeder extends Seeder
{
    public function run(): void
    {
        $lecturerUsers = User::where('role', 'lecturer')->get();
        $departments = ['Computer Science', 'Information Systems', 'Software Engineering'];
        $titles = ['Professor', 'Associate Professor', 'Assistant Professor'];

        foreach ($lecturerUsers as $index => $user) {
            Lecturer::create([
                'user_id' => $user->id,
                'lecturer_id' => 'GV' . str_pad($index + 1, 3, '0', STR_PAD_LEFT), // GV001, GV002, etc.
                'department' => $departments[$index % count($departments)],
                'title' => $titles[$index % count($titles)],
                'specialization' => 'Information Technology',
            ]);
        }
    }
} 