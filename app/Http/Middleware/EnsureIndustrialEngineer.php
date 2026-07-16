<?php

namespace App\Http\Middleware;

use App\Enums\Role;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureIndustrialEngineer
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user || $user->role_id !== Role::IndustrialEngineer->value) {
            abort(403, 'Only Industrial Engineers can access the sewing department dashboard.');
        }

        return $next($request);
    }
}
