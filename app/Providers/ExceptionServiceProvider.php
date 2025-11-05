<?php

namespace App\Providers;

use App\Exceptions\ApiExceptionHandlerException;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\ServiceProvider;

class ExceptionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ExceptionHandler::class, ApiExceptionHandlerException::class);
    }

    public function boot(): void {}
}
