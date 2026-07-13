<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;

class SetGermanLocale
{
    public function handle($request, Closure $next)
    {
        App::setLocale('de');
        return $next($request);
    }
}