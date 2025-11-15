<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth', ['title' => 'register'])] class extends Component {
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered(($user = User::create($validated))));

        Auth::login($user);

        $this->redirectIntended(route('dashboard', absolute: false));
    }
}; ?>

<div>
    <x-card class="flex flex-col gap-6 border border-gray-300 dark:border-gray-700 text-lg font-medium rounded-xl dark:text-gray-300  dark:bg-gray-900  transition-colors duration-200 " shadow separator>

        <x-auth-header :title="__('lang.create_account')" :description="__('lang.enter_your_details_below_to_create_your_account')"/>

        @session('status')
        <x-alert title="{{ session('status') }}" class="text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 my-4 text-center"/>
        @endsession

        <form wire:submit="register" class="flex flex-col gap-6">
            <!-- Name -->
            <x-input
                    wire:model="name"
                    :label="__('lang.name')"
                    type="text"
                    required
                    autofocus
                    autocomplete="name"
                    :placeholder="__('lang.full_name')"
            />

            <!-- Email Address -->
            <x-input
                    wire:model="email"
                    :label="__('lang.email')"
                    type="email"
                    required
                    autocomplete="email"
                    placeholder="email@example.com"
            />

            <!-- Password -->
            <x-input
                    wire:model="password"
                    :label="__('lang.password')"
                    type="password"
                    required
                    autocomplete="new-password"
                    :placeholder="__('lang.password')"
                    viewable
            />

            <!-- Confirm Password -->
            <x-input
                    wire:model="password_confirmation"
                    :label="__('lang.password_confirmation')"
                    type="password"
                    required
                    autocomplete="new-password"
                    :placeholder="__('lang.password_confirmation')"
                    viewable
            />

            <div class="flex items-center justify-end">
                <x-button type="submit" variant="primary" class="w-full" spinner="register">
                    {{ __('lang.create_account') }}
                </x-button>
            </div>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400 mt-3">
            {{ __('lang.already_have_an_account') }}
            <a class="link" href="{{route('login')}}" >{{ __('lang.login') }}</a>
        </div>
    </x-card>
</div>
