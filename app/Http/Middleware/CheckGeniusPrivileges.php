<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckGeniusPrivileges
{
    public function handle(Request $request, Closure $next, $privilege)
    {
        // Obtener usuario autenticado con Sanctum
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        // Verificar que el usuario sea GENIUS
        if (!$user->is_genius) {
            return response()->json(['error' => 'Only genius users can perform this action'], 403);
        }

        // Verificar que el método exista en el modelo User
        if (!method_exists($user, $privilege)) {
            return response()->json(['error' => 'Privilege not found'], 500);
        }

        // Ejecutar el método dinámicamente
        if (!$user->$privilege()) {
            return response()->json(['error' => 'You do not have permission for this action'], 403);
        }

        return $next($request);
    }
}
