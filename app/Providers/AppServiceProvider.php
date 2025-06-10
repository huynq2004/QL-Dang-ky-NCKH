<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\ProposalServiceInterface;
use App\Services\ProposalService;
use App\Contracts\InvitationServiceInterface;
use App\Services\InvitationService;
use App\Contracts\LecturerServiceInterface;
use App\Services\LecturerService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ProposalServiceInterface::class, ProposalService::class);
        $this->app->bind(InvitationServiceInterface::class, InvitationService::class);
        $this->app->bind(LecturerServiceInterface::class, LecturerService::class);

        $this->app->bind('proposal', ProposalService::class);
        $this->app->bind('invitation', InvitationService::class);
        $this->app->bind('lecturer', LecturerService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
