<?php

namespace App\Providers;

use App\Models\Ticket;
use App\Policies\TicketPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Ticket::class, TicketPolicy::class);

        throw_if(
            blank(config('app.url')),
            \RuntimeException::class,
            'APP_URL is required in .env'
        );

        throw_if(
            blank(config('database.default')),
            \RuntimeException::class,
            'DB_CONNECTION is required in .env'
        );

        throw_if(
            blank(config('session.driver')),
            \RuntimeException::class,
            'SESSION_DRIVER is required in .env'
        );
    }
}
