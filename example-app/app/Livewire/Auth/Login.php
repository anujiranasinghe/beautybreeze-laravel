<?php

namespace App\Livewire\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Laravel\Fortify\Features;

#[Layout('components.layouts.auth')]
class Login extends Component
{
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->ensureIsNotRateLimited();

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        Session::regenerate();

        $user = Auth::user();
        $forceAdmin = session('force_admin_login', false);

        // If 2FA is enabled and the user has it configured, hand off to Fortify's two-factor challenge
        if (Features::enabled(Features::twoFactorAuthentication()) && $user && !is_null($user->two_factor_secret ?? null)) {
            Auth::logout();
            // Persist login context for Fortify's two-factor challenge
            Session::put('login.id', $user->getAuthIdentifier());
            Session::put('login.remember', $this->remember);
            $this->redirect(route('two-factor.login', absolute: false), navigate: false);
            return;
        }

        if ($forceAdmin) {
            // Admin flow: only admins allowed
            session()->forget('force_admin_login');
            if ($user && ($user->is_admin ?? false)) {
                $this->redirect(route('admin.dashboard', absolute: false), navigate: false);
                return;
            }
            Auth::logout();
            Session::invalidate();
            Session::regenerateToken();
            $this->addError('email', __('Unauthorized access.'));
            $this->redirect(route('login', absolute: false), navigate: true);
            return;
        }

        // Generic/customer flow: block admins, allow customers
        if ($user && ($user->is_admin ?? false)) {
            Auth::logout();
            Session::invalidate();
            Session::regenerateToken();
            $this->addError('email', __('Admins must log in via /admin'));
            $this->redirect(route('login', absolute: false), navigate: true);
            return;
        }

        $this->redirectIntended(default: route('home', absolute: false), navigate: true);
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }
}
