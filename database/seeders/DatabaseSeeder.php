<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\Lecturer;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create demo student
        Student::create([
            'name' => 'Demo Student',
            'email' => 'student@example.com',
            'password' => Hash::make('password'),
            'student_id' => 'ST001',
            'class' => 'Class A',
            'major' => 'Computer Science'
        ]);

        // Create demo lecturer
        Lecturer::create([
            'name' => 'Demo Lecturer',
            'email' => 'lecturer@example.com',
            'password' => Hash::make('password'),
            'department' => 'Computer Science',
            'title' => 'Professor',
            'specialization' => 'Software Engineering'
        ]);
    }
}
