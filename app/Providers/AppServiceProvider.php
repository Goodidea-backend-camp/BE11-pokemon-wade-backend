<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

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
     * 啟動任何應用服務。
     *
     * 擴展 Laravel 的驗證器，添加了一個名為 'alpha_unicode' 的自定義驗證規則。
     * 這個規則使用正則表達式來檢查輸入值是否僅包含英文字母和/或中文字符。
     *
     * @return void
     */
    public function boot(): void
    {
        Validator::extend('alpha_unicode', function ($attribute, $value, $parameters, $validator) {
            $pattern = '/^[A-Za-z\x{4e00}-\x{9fa5}]+$/u';

            return preg_match($pattern, $value) === 1;
        });
    }
}
