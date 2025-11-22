<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class AppCurrencyMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $currencies = ['egp', 'usd'];
        $currency = $request->header('currency') ?? config('app.currency');
        if (in_array($currency, $currencies)) {
	        $request->attributes->set('currency', $currency);
        }
        return $next($request);
    }
}
