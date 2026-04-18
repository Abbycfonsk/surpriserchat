<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;

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

        ResetPassword::createUrlUsing(function ($notifiable, string $token) {
            return url('/api/reset-password?token=' . $token . '&email=' . $notifiable->email);
        });

        Route::middleware('api')
            ->prefix('api')
            ->group(base_path('routes/api.php'));
        //
    }
}
