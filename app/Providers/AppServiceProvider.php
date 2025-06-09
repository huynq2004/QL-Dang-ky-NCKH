<?php

namespace App\Providers;

use App\Contracts\ProposalServiceInterface;
use App\Services\ProposalService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ProposalServiceInterface::class, ProposalService::class);
        
        $this->app->bind('proposal', function ($app) {
            return $app->make(ProposalServiceInterface::class);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
