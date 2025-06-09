<?php

namespace Database\Seeders;

use App\Models\Lecturer;
use App\Models\Proposal;
use Illuminate\Database\Seeder;

class ProposalSeeder extends Seeder
{
    public function run(): void
    {
        $lecturers = Lecturer::all();
        $fields = [
            'Artificial Intelligence',
            'Machine Learning',
            'Web Development',
            'Mobile Development',
            'Data Science',
            'Cybersecurity',
            'Cloud Computing'
        ];

        $proposals = [
            [
                'title' => 'AI-Powered Student Performance Prediction',
                'field' => 'Artificial Intelligence',
                'description' => 'Research on using AI to predict student academic performance based on various factors.',
            ],
            [
                'title' => 'Blockchain-based Academic Credential System',
                'field' => 'Cybersecurity',
                'description' => 'Developing a secure system for managing academic credentials using blockchain technology.',
            ],
            [
                'title' => 'Smart Campus Mobile Application',
                'field' => 'Mobile Development',
                'description' => 'Creating a comprehensive mobile app for campus services and information.',
            ],
            [
                'title' => 'Cloud-based Learning Management System',
                'field' => 'Cloud Computing',
                'description' => 'Implementing a scalable LMS using cloud technologies.',
            ],
            [
                'title' => 'Data Analytics for Education',
                'field' => 'Data Science',
                'description' => 'Analyzing educational data to improve teaching and learning outcomes.',
            ]
        ];

        foreach ($lecturers as $lecturer) {
            // Add 1-2 proposals per lecturer
            $numProposals = rand(1, 2);
            for ($i = 0; $i < $numProposals; $i++) {
                $proposal = $proposals[array_rand($proposals)];
                Proposal::create([
                    'title' => $proposal['title'],
                    'field' => $proposal['field'],
                    'description' => $proposal['description'],
                    'lecturer_id' => $lecturer->id,
                    'status' => 'active',
                    'is_visible' => true,
                ]);
            }
        }
    }
} 