<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use App\Auth\Responses\CustomRegisterResponse;
use App\Auth\Responses\CustomLoginResponse;

class FortifyResponseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(RegisterResponseContract::class, CustomRegisterResponse::class);
        $this->app->singleton(LoginResponseContract::class, CustomLoginResponse::class);
    }

    public function boot(): void
    {
        //
    }
}
