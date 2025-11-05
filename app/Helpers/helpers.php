<?php

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

if (! function_exists('getTimezoneByIP')) {
    function getTimezoneByIP($ip): ?string
    {
        if (app()->isProduction()) {
            $request = Http::get('https://freeipapi.com/api/json/'.$ip);
            if ($request->json() && array_key_exists('timeZones', $request->json())) {
                return $request['timeZones'][0];
            }
        }

        return config('app.timezone');
    }
}

if (! function_exists('formatDate')) {
    function formatDate($date = null, $with_time = false): ?string
    {
        return ($date) ? Carbon::parse($date)->translatedFormat($with_time ? 'Y-m-d h:i A' : 'Y-m-d') : null;
    }
}

if (! function_exists('randomOtpCode')) {
    function randomOtpCode(): string
    {
        return 123456;
    }
}
