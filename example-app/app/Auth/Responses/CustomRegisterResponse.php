<?php

namespace App\Auth\Responses;

use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class CustomRegisterResponse implements RegisterResponseContract
{
    public function toResponse($request): RedirectResponse
    {
        // If Jetstream/Fortify auto-logged the new user in, log them out immediately
        if (Auth::check()) {
            Auth::logout();
        }

        // Redirect to login without flushing the session so intended() is preserved
        return redirect()->route('login')->with('status', 'Account created. Please sign in to continue.');
    }
}
