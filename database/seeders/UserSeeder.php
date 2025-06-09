<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin'
        ]);

        // Create 5 student users
        for ($i = 1; $i <= 5; $i++) {
            User::create([
                'name' => "Student $i",
                'email' => "student$i@example.com",
                'password' => Hash::make('password'),
                'role' => 'student'
            ]);
        }

        // Create 3 lecturer users
        for ($i = 1; $i <= 3; $i++) {
            User::create([
                'name' => "Lecturer $i",
                'email' => "lecturer$i@example.com",
                'password' => Hash::make('password'),
                'role' => 'lecturer'
            ]);
        }
    }
} 