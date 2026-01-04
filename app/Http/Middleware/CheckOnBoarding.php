<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckOnBoarding
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            if (Auth::user()->on_boarding_required && ($request->is('dashboard')  || $request->is('survey') )) {
                return redirect()->route('on-boarding');
            }
            if (!Auth::user()->on_boarding_required && $request->is('on-boarding')) {
                return redirect()->route('dashboard');
            }
        }

        return $next($request);
    }
}
