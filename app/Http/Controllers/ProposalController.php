<?php

namespace App\Http\Controllers;

use App\Models\Proposal;
use App\Models\Invitation;
use Illuminate\Http\Request;
use App\Contracts\ProposalServiceInterface;
use Illuminate\Support\Facades\Auth;

class ProposalController extends Controller
{
    protected $proposalService;

    public function __construct(ProposalServiceInterface $proposalService)
    {
        $this->proposalService = $proposalService;
        $this->middleware('auth');
    }

    public function index()
    {
        $proposals = $this->proposalService->getProposals();
        return view('proposals.index', compact('proposals'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:200',
            'field' => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        $this->proposalService->addProposal($data, Auth::user()->lecturer);
        return redirect()->route('proposals.index')->with('success', 'Proposal created successfully');
    }

    public function invitations()
    {
        $invitations = $this->proposalService->getInvitations(Auth::user());
        return view('proposals.invitations', compact('invitations'));
    }

    public function processInvitation(Request $request, Invitation $invitation)
    {
        $action = $request->input('action');
        if (!in_array($action, ['accept', 'reject'])) {
            return back()->with('error', 'Invalid action');
        }

        $this->proposalService->processInvitation($invitation->id, $action);
        return back()->with('success', 'Invitation ' . $action . 'ed successfully');
    }
} 