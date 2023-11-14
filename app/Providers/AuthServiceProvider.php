<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Pokemon;
use App\Policies\PokemonPolicy;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
        // Pokemon::class => PokemonPolicy::class,
        'App\Models\CartItem' => 'App\Policies\CartItemPolicy',


    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //     $this->registerPolicies();

        // VerifyEmail::toMailUsing(function ($notifiable, $url) {
        //     return (new MailMessage)
        //         ->line('點擊以下按鈕來驗證您的電子郵件地址。')
        //         ->action('驗證電子郵件地址', str_replace('api/email/verify', 'http://localhost:8000', $url))
        //         ->line('如果您沒有嘗試註冊帳號，則不需要進一步的操作。');
        // });
    }
}
