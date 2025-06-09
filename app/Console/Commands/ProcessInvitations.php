<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invitation;
use App\Mail\InvitationProcessedMail;
use Illuminate\Support\Facades\Mail;

class ProcessInvitations extends Command
{
    protected $signature = 'invitations:process';
    protected $description = 'Process pending invitations';

    public function handle()
    {
        $pendingInvitations = Invitation::where('status', 'pending')->get();

        foreach ($pendingInvitations as $invitation) {
            // Process the invitation
            Mail::to($invitation->email)->send(new InvitationProcessedMail($invitation));
            $invitation->update(['status' => 'processed']);
        }

        $this->info('Invitations processed successfully!');
    }
} 