<?php

namespace App\Providers;

use App\Models\Account;
use App\Models\Order;
use App\Models\Transaction;
use App\Policies\AccountPolicy;
use App\Policies\OrderPolicy;
use App\Policies\TransactionPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
        Transaction::class => TransactionPolicy::class,
        Account::class => AccountPolicy::class,
        Order::class => OrderPolicy::class
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('ability', function ($user, $ability) {
            if (!$user->isAdmin()) {
                return true;
            }
            $abilities = $user->abilities();
            return $abilities->contains(Str::upper(Str::snake($ability)));
        });

        //
    }
}
