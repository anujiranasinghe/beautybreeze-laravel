<?php

namespace App\Auth\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class CustomLoginResponse implements LoginResponseContract
{
    public function toResponse($request): RedirectResponse
    {
        $user = Auth::user();
        $intended = $request->session()->get('url.intended');

        if ($intended) {
            $isAdminPath = str_contains($intended, '/admin');

            if ($isAdminPath) {
                if ($user && ($user->is_admin ?? false)) {
                    return redirect()->intended(route('admin.dashboard'));
                }
                // Non-admin tried to reach admin; send to home with error
                return redirect()->route('home')->with('error', 'Unauthorized access.');
            }

            // If an admin tried to log in via generic login (non-admin intended), block it
            if ($user && ($user->is_admin ?? false)) {
                \Illuminate\Support\Facades\Auth::logout();
                return redirect()->route('login')->with('error', 'Admins must log in via /admin');
            }

            // Non-admin intended route (e.g., checkout) — honor it (falls back to home if none)
            return redirect()->intended(route('home'));
        }

        // No intended route: if admin, block generic login
        if ($user && ($user->is_admin ?? false)) {
            \Illuminate\Support\Facades\Auth::logout();
            return redirect()->route('login')->with('error', 'Admins must log in via /admin');
        }

        // Regular users with no intended -> home
        return redirect()->route('home');
    }
}
