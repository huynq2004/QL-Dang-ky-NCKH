<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\UserManagementInterface;
use App\Services\UserManagementService;

class UserManagementServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind('user-management', UserManagementService::class);
        $this->app->bind(UserManagementInterface::class, UserManagementService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
} 