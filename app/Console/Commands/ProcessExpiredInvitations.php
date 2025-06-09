<?php

namespace App\Console\Commands;

use App\Facades\ProposalFacade;
use Illuminate\Console\Command;

class ProcessExpiredInvitations extends Command
{
    protected $signature = 'invitations:process-expired';
    
    protected $description = 'Process all expired invitations';

    public function handle()
    {
        $this->info('Processing expired invitations...');
        
        ProposalFacade::autoProcessExpiredInvitations();
        
        $this->info('Finished processing expired invitations.');
        
        return Command::SUCCESS;
    }
} 