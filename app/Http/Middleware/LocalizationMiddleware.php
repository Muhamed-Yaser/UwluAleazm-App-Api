<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class LocalizationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // $locale = $request->header('Accept-Language');
        // if ($locale) {
        //     app()->setLocale($locale);
        //     }

        $language = $request->header('Accept-Language', $request->user()->language ?? 'ar');

        if (in_array($language, ['en', 'ar'])) {
            App::setLocale($language);
        } else {
            App::setLocale('ar'); // Default to Arabic if no valid language found
        }
        return $next($request);
    }
}
