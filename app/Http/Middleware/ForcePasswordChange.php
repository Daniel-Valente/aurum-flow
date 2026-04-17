<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    public function handle($request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->must_change_password) {
            if (!$request->routeIs('password.change')) {
                return redirect()->route('password.change');
            }
        }

        return $next($request);
    }
}
