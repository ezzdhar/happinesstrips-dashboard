<?php

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth', ['title' => 'login'])] class extends Component {
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

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
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
}; ?>

<div>
    <x-card class="flex flex-col gap-6 border border-gray-300 dark:border-gray-700 text-lg font-medium rounded-xl dark:text-gray-300
			dark:bg-gray-900  transition-colors duration-200" shadow separator>
        <x-auth-header :title="__('lang.log_account')"/>

        @session('status')
        <x-alert title="{{ session('status') }}" class="text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 my-4 text-center"/>
        @endsession


        <form wire:submit="login" class="flex flex-col gap-6 mt-3">
            <!-- Email Address -->
            <x-input
                    wire:model="email"
                    :label="__('lang.email')"
                    type="email"
                    required
                    autofocus
                    autocomplete="email"
                    placeholder="email@example.com"
            />

            <!-- Password -->
            <div class="relative">
                <x-input
                        wire:model="password"
                        :label="__('lang.password')"
                        type="password"
                        required
                        autocomplete="current-password"
                        :placeholder="__('lang.password')"
                        viewable
                />

                @if (Route::has('password.request'))
                    <a class="absolute end-0 top-0 text-sm link" href="{{route('password.request')}}" >
                        {{ __('lang.forgot_password') }}
                    </a>
                @endif
            </div>

            <!-- Remember Me -->
            <x-checkbox wire:model="remember" :label="__('lang.remember_me')"/>

            <div class="flex items-center justify-end">
                <x-button variant="primary" type="submit" class="w-full" spinner="login">{{ __('lang.login') }}</x-button>
            </div>
        </form>

        @if (Route::has('register'))
            <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400 mt-3">
                {{ __('lang.don_have_account') }}
                <a class="link" href="{{route('register')}}" >{{ __('lang.register') }}</a>
            </div>
        @endif
    </x-card>
</div>
