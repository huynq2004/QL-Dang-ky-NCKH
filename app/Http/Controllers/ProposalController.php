<?php

namespace App\Http\Controllers;

use App\Models\Proposal;
use App\Services\ProposalService;
use Illuminate\Http\Request;

class ProposalController extends Controller
{
    protected $proposalService;

    public function __construct(ProposalService $proposalService)
    {
        $this->proposalService = $proposalService;
    }

    public function index()
    {
        $proposals = $this->proposalService->getAllProposals();
        return view('proposals.index', compact('proposals'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'student_id' => 'required|exists:students,id',
            'lecturer_id' => 'required|exists:lecturers,id',
        ]);

        $proposal = $this->proposalService->createProposal($validated);
        return redirect()->route('proposals.index')->with('success', 'Proposal created successfully');
    }
} 