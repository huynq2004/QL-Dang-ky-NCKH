<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\ProposalService;
use App\Services\InvitationService;

class ProposalServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind('proposal', function ($app) {
            return new ProposalService(
                $app->make(InvitationService::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
} 