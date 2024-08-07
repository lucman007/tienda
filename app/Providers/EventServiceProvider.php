<?php

namespace sysfact\Providers;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Auth\Events\Login;
use sysfact\Listeners\UpdateUserSessionId;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'sysfact\Events\Event' => [
            'sysfact\Listeners\EventListener',
        ],
        Login::class => [
            UpdateUserSessionId::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
