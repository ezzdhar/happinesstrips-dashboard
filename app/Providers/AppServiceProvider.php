<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
	    Schema::defaultStringLength(191);

	    Gate::define('viewLogViewer', function (User $user) {
            return (auth()->check() && auth()->user()->email === 'superadmin@admin.com') || app()->environment('local');
        });

        Gate::define('viewPulse', function (User $user) {
            return (auth()->check() && auth()->user()->email === 'superadmin@admin.com') || app()->environment('local');
        });

        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            return (new MailMessage)
                ->subject(__('lang.verify_email_address'))
                ->greeting(__('lang.reset_password_greeting', ['name' => $notifiable->name]))
                ->line(__('lang.click_the_button_below_to_verify_your_email_address'))
                ->action(__('lang.verify_email_address'), $url);
        });

        ResetPassword::toMailUsing(function (object $notifiable, string $token) {
            $url = url(route('password.reset', ['token' => $token, 'email' => $notifiable->getEmailForPasswordReset()], false));

            return (new MailMessage)
                ->subject(__('lang.reset_password_subject'))
                ->greeting(__('lang.reset_password_greeting', ['name' => $notifiable->name]))
                ->line(__('lang.reset_password_line_1'))
                ->action(__('lang.reset_password_action'), $url)
                ->line(__('lang.reset_password_line_2', ['count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire')]))
                ->line(__('lang.reset_password_line_3'))
                ->salutation(__('lang.reset_password_salutation'));
        });

    }
}
