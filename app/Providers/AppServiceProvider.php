<?php

namespace App\Providers;

use App\Events\EventOne;
use App\Events\OrderPlaced;
use App\Listeners\ListenerOne;
use App\Listeners\SendOrderConfirmation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    protected $listen = [
        OrderPlaced::class => [
            SendOrderConfirmation::class
        ]
    ];

    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
