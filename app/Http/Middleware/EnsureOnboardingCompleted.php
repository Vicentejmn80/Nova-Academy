<?php
// app/Http/Middleware/EnsureOnboardingCompleted.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureOnboardingCompleted
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return $next($request);
        }

        if ($request->routeIs('onboarding') || $request->routeIs('onboarding.save')) {
            return $next($request);
        }

        $user = Auth::user()->fresh();
        if ($user && !$user->onboarding_completed) {
            return redirect()->route('onboarding');
        }

        return $next($request);
    }
}