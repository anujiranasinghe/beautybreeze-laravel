<?php

namespace App\Livewire\Actions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Logout
{
    /**
     * Log the current user out of the application.
     */
    public function __invoke()
    {
        $cameFromAdmin = false;
        try {
            $ref = url()->previous();
            $cameFromAdmin = is_string($ref) && str_contains($ref, '/admin');
        } catch (\Throwable $e) {
            $cameFromAdmin = false;
        }

        Auth::guard('web')->logout();

        Session::invalidate();
        Session::regenerateToken();

        if ($cameFromAdmin) {
            // Redirect admins back to the admin login entry
            return redirect()->route('admin.entry');
        }

        return redirect('/');
    }
}
