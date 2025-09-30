<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class SanctumServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Use default PersonalAccessToken model; config via sanctum.php if needed
    }
}

