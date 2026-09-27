<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Vérifier que l'utilisateur connecté possède le rôle requis.
     * Usage dans les routes: ->middleware('role:admin') ou ->middleware('role:admin,gestionnaire')
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Non authentifié.'
            ], 401);
        }

        $userRole = $user->role instanceof \BackedEnum ? $user->role->value : $user->role;

        if (!in_array($userRole, $roles, true)) {
            return response()->json([
                'message' => 'Accès refusé. Vous n\'avez pas les permissions nécessaires.',
                'your_role' => $userRole,
                'required_roles' => $roles,
            ], 403);
        }

        return $next($request);
    }
}
