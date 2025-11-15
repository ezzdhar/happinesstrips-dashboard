<?php

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth', ['title' => 'reset_password'])] class extends Component {
    #[Locked]
    public string $token = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Mount the component.
     */
    public function mount(string $token): void
    {
        $this->token = $token;

        $this->email = request()->string('email');
    }

    /**
     * Reset the password for the given user.
     */
    public function resetPassword(): void
    {
        $this->validate([
            'token' => ['required'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            $this->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) {
                $user->forceFill([
                    'password' => Hash::make($this->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        if ($status != Password::PasswordReset) {
            $this->addError('email', __($status));

            return;
        }

        Session::flash('status', __($status));

        $this->redirectRoute('login');
    }
}; ?>

<div>
    <x-card class="flex flex-col gap-6 border border-gray-300 dark:border-gray-700 text-lg font-medium rounded-xl dark:text-gray-300 bg-white dark:bg-gray-900  transition-colors duration-200 " shadow separator>
        <x-auth-header :title="__('lang.reset_password')" :description="__('lang.please_enter_your_new_password_below')"/>
        @session('status')
        <x-alert title="{{ session('status') }}" class="text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 my-4 text-center"/>
        @endsession

        <form wire:submit="resetPassword" class="flex flex-col gap-6">
            <!-- Email Address -->
            <x-input
                    wire:model="email"
                    :label="__('lang.email')"
                    type="email"
                    required
                    autocomplete="email"
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
                <x-button type="submit" variant="primary" class="w-full" spinner="resetPassword">
                    {{ __('lang.reset_password') }}
                </x-button>
            </div>
        </form>
    </x-card>
</div>
