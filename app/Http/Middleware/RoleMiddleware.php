<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if(!$user) {
            abort(403);
        }

        $empleado = $user->empleado;

        if (!$empleado || !$empleado->hasAnyRole($roles)) {
            abort(403, 'No autorizado');
        }

        return $next($request);
    }
}
