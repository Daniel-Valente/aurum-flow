<?php

namespace App\Providers;

use App\Listeners\LogAuthenticationActivity;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Login::class => [
            LogAuthenticationActivity::class . '@handleLogin',
        ],
        Logout::class => [
            LogAuthenticationActivity::class . '@handleLogout',
        ],
        Failed::class => [
            LogAuthenticationActivity::class . '@handleFailed',
        ],
        Registered::class => [
            LogAuthenticationActivity::class . '@handleRegistered',
        ],
        PasswordReset::class => [
            LogAuthenticationActivity::class . '@handlePasswordReset',
        ],
    ];

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
