<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LanguageMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $languages = ['en', 'ar'];
        $language = $request->header('lang') ?? Session::get('lang');
        if (in_array($language, $languages)) {
            App::setLocale($language);
            Carbon::setLocale($language);
        }

        return $next($request);
    }
}
