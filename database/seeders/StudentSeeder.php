<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $studentUsers = User::where('role', 'student')->get();

        foreach ($studentUsers as $user) {
            Student::create([
                'user_id' => $user->id,
                'student_id' => 'SV' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                'major' => 'Computer Science',
                'class' => 'CS' . rand(1, 4),
            ]);
        }
    }
} 