<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        \App\Events\AuctionEnded::class => [
            \App\Listeners\SendAuctionEndedEmail::class,
             \App\Listeners\SendAuctionPaymentLink::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();
    }

    /**
     * Disable auto-discovery (optional)
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
