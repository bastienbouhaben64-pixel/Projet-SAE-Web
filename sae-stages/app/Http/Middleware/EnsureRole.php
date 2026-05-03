<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $utilisateur = $request->user();
        if (! $utilisateur || ! in_array($utilisateur->role, $roles, true)) {
            abort(403, "Accès refusé pour ce rôle.");
        }

        if (! $utilisateur->is_active) {
            abort(403, "Votre compte est en attente d'activation par un administrateur.");
        }

        return $next($request);
    }
}
