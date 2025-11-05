<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    public function __invoke($lang)
    {
        $lang = in_array($lang, ['en', 'ar']) ? $lang : config('app.locale', 'en');
        Session::put('lang', $lang);

        return back();
    }
}
