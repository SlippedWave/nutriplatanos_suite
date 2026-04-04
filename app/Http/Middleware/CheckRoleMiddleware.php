<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        $userRole = $user->role ?? 'guest';

        // Convert roles to lowercase for case-insensitive comparison
        $allowedRoles = array_map('strtolower', $roles);
        $userRole = strtolower($userRole);

        if (!in_array($userRole, $allowedRoles)) {
            // Log the access attempt
            logger()->warning('Intento de acceso no autorizado', [
                'user_id' => $user->id,
                'user_role' => $userRole,
                'required_roles' => $allowedRoles,
                'url' => $request->url(),
            ]);

            abort(403, "Acceso denegado. Necesitas uno de estos roles: " . implode(', ', $roles));
        }

        return $next($request);
    }
}
