<?php

namespace App\Providers;

use App\Events\EmailRegistered;
use App\Events\ForgetPasswordEmailConfirmationEvent;
use App\Events\OrderUpdateEvent;
use App\Events\SendOtpEvent;
use App\Events\UpdateBalancesEvent;
use App\Events\UpdateSettingEvent;
use App\Events\UpdateUserRankEvent;
use App\Events\UserRegisteredPasswordEvent;
use App\Listeners\ForgetPasswordSendEmailListener;
use App\Listeners\SendCodeToEmail;
use App\Listeners\SendOtpListener;
use App\Listeners\SettingOtpListener;
use App\Listeners\UpdateBalancesListener;
use App\Listeners\UpdateOrderListener;
use App\Listeners\UpdateUserRankListener;
use App\Listeners\UserInitListener;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        EmailRegistered::class => [
            SendCodeToEmail::class
        ],
        ForgetPasswordEmailConfirmationEvent::class => [
            ForgetPasswordSendEmailListener::class
        ],
        SendOtpEvent::class => [
            SendOtpListener::class
        ],
        UserRegisteredPasswordEvent::class => [
            UserInitListener::class
        ],
        UpdateBalancesEvent::class => [
            UpdateBalancesListener::class
        ],
        UpdateSettingEvent::class => [
            SettingOtpListener::class
        ],
        UpdateUserRankEvent::class => [
            UpdateUserRankListener::class
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
