<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\PersonalAccessToken; // IMPORTANT
use Laravel\Sanctum\Sanctum;            // IMPORTANT
use Carbon\Carbon;

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
        Sanctum::authenticateAccessTokensUsing(
            static function (PersonalAccessToken $accessToken, bool $isValid) {
                // 1. Check if token is already invalid (e.g. past the 24h hard limit)
                if (!$isValid) return false;

                // 2. Define your idle timeout (in minutes)
                $idleTimeout = 30;

                // 3. Compare current time with last usage
                // If never used before, use the creation time
                $lastActivity = $accessToken->last_used_at ?? $accessToken->created_at;

                return $lastActivity->gt(now()->subMinutes($idleTimeout));
            }
        );
    }
}
