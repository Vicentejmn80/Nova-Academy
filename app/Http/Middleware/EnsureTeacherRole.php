<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTeacherRole
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->role !== 'profesor') {
            // Directors trying to hit operational teacher routes get a clear 403
            abort(403, 'Esta sección es exclusiva para Docentes. Los Directores tienen acceso de solo lectura en su panel.');
        }

        return $next($request);
    }
}
