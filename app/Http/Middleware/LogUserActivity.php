<?php

namespace App\Http\Middleware;

use App\Services\ActivityLogService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogUserActivity
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Log login if this is the first request after authentication
            if (!$request->session()->get('activity_logged')) {
                ActivityLogService::login($user);
                $request->session()->put('activity_logged', true);
            }
        }

        return $next($request);
    }

    public function terminate($request, $response)
    {
        // Log logout when session ends
        if (Auth::check() && $request->session()->isStarted()) {
            // You might want to add additional logic here for proper logout detection
        }
    }
}
