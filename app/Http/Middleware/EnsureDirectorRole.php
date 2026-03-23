<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDirectorRole
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->role !== 'director') {
            abort(403, 'Esta sección es exclusiva para Directores Institucionales.');
        }

        return $next($request);
    }
}
