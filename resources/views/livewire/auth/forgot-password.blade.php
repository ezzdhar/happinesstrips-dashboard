<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth', ['title' => 'forgot_password'])] class extends Component {
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        Password::sendResetLink($this->only('email'));

        session()->flash('status', __('lang.reset_link_sent_account_successfully'));
    }
}; ?>

<div>
    <x-card class="flex flex-col gap-6 border border-gray-300 dark:border-gray-700 text-lg font-medium rounded-xl dark:text-gray-300 bg-white dark:bg-gray-900  transition-colors duration-200 " shadow separator>
        <x-auth-header :title="__('lang.forgot_password')" :description="__('lang.enter_email_receive_password_reset_link')"/>

        @session('status')
        <x-alert title="{{ session('status') }}" class="text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 my-4 text-center"/>
        @endsession


        <form wire:submit="sendPasswordResetLink" class="flex flex-col gap-6">
            <!-- Email Address -->
            <x-input
                    wire:model="email"
                    :label="__('lang.email')"
                    type="email"
                    required
                    autofocus
                    placeholder="email@example.com"
                    viewable
            />

            <x-button variant="primary" type="submit" class="w-full" spinner="sendPasswordResetLink">{{ __('lang.email_password_reset_link') }}</x-button>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-400 mt-3">
            {{ __('lang.or_return_to') }}
            <a class="link" href="{{route('login')}}" >{{ __('lang.login') }}</a>
        </div>
    </x-card>

</div>
