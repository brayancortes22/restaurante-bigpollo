<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'No autenticado.'], 401);
            }
            return redirect()->guest('/login');
        }

        // Si el usuario es SuperAdmin o Admin, tiene acceso transversal de supervisión
        if ($user->role === 'superadmin' || $user->role === 'admin') {
            return $next($request);
        }

        // Validar si el rol del usuario está dentro de los roles autorizados para la ruta
        if (!in_array($user->role, $roles, true)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Acceso denegado: Su rol no cuenta con permisos para este recurso.',
                    'required_roles' => $roles,
                    'current_role' => $user->role,
                ], 403);
            }

            abort(403, 'Acceso restringido: No tienes permisos para acceder a esta área de Big Pollo.');
        }

        return $next($request);
    }
}
