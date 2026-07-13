<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetAdminLocale
{
    public function handle($request, Closure $next)
    {
        $locale = Session::get('admin_locale', config('app.locale', 'de'));
        
        // Only allow valid locales
        if (!in_array($locale, ['en', 'de'])) {
            $locale = 'de';
        }
        
        App::setLocale($locale);
        
        return $next($request);
    }
}