<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Vérifie que l'utilisateur connecté possède un des rôles autorisés.
 * En cas d'échec, redirige vers son propre tableau de bord au lieu d'un 403 brutal.
 *
 * Usage dans les routes :
 *   ->middleware('role:admin')
 *   ->middleware('role:admin,superviseur')
 */
class EnsureUserRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Aucun rôle spécifié = tout le monde passe
        if (empty($roles)) {
            return $next($request);
        }

        // Nettoyer les espaces éventuels autour des rôles
        $roles = array_map('trim', $roles);

        // L'utilisateur possède un des rôles autorisés
        if (in_array($user->type_utilisateur, $roles)) {
            return $next($request);
        }

        // Redirection vers son propre dashboard plutôt qu'un 403 sec
        return redirect()->route('dashboard')->with(
            'error',
            'Vous n\'avez pas accès à cette section.'
        );
    }
}
