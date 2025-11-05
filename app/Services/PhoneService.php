<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class PhoneService
{
    public static function phoneCodes(): Collection
    {
        return Cache::rememberForever('countries', function () {
            $json = file_get_contents(public_path('countries-phone-codes.json'));
            $dataArray = json_decode($json, true);

            return collect($dataArray);
        });
    }

    public static function formatNumber($number): int
    {
        $number = preg_replace('/\D/', '', $number);

        return (int) ltrim($number, '0');
    }
}
