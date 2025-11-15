<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth', ['title' => 'verify_email'])] class extends Component {
	/**
	 * Send an email verification notification to the user.
	 */
	public function sendVerification(): void
	{
		if (Auth::user()->hasVerifiedEmail()) {
			$this->redirectIntended(default: route('dashboard', absolute: false));

			return;
		}

		Auth::user()->sendEmailVerificationNotification();

		Session::flash('status', 'verification-link-sent');
	}

	/**
	 * Log the current user out of the application.
	 */
	public function logout(Logout $logout): void
	{
		$logout();

		$this->redirect('/');
	}
}; ?>

<div>
	<x-card class="flex flex-col gap-6 border border-gray-300 dark:border-gray-700 text-lg font-medium rounded-xl dark:text-gray-300  dark:bg-gray-900  transition-colors duration-200 " shadow separator>
		<x-auth-header :title="__('lang.please_verify_your_email')"/>

		@if (session('status') == 'verification-link-sent')
			<x-alert title="{{ __('lang.resend_verification_email_successful') }}" class="text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 my-3 text-center"/>
		@endif

		<div class="flex flex-col items-center justify-between space-y-3 mt-6">
			<x-button wire:click="sendVerification" variant="primary" class="w-full" spinner="sendVerification">
				{{ __('lang.resend_verification_email') }}
			</x-button>
			<a class="link text-sm cursor-pointer" wire:click="logout">
				{{ __('lang.logout') }}
			</a>
		</div>
	</x-card>
</div>
