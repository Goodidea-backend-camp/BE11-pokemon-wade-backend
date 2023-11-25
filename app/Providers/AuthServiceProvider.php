<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;


class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        'App\Models\CartItem' => 'App\Policies\CartItemPolicy',


    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
    
    }
}
