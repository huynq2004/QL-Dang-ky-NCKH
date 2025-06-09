<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\ProposalService;
use App\Contracts\ProposalServiceInterface;

class ProposalServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind('proposal', function ($app) {
            return new ProposalService();
        });

        $this->app->bind(ProposalServiceInterface::class, ProposalService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
} 