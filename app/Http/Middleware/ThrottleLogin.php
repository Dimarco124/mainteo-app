<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ThrottleLogin
{
    protected $limiter;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->resolveRequestSignature($request);
        
        // Vérifier si l'utilisateur a dépassé la limite (5 tentatives)
        if ($this->limiter->tooManyAttempts($key, 5)) {
            $seconds = $this->limiter->availableIn($key);
            
            return back()->withErrors([
                'email' => "Trop de tentatives de connexion. Veuillez réessayer dans {$seconds} secondes."
            ])->withInput($request->only('email'));
        }

        // Enregistrer la tentative (expire après 60 secondes)
        $this->limiter->hit($key, 60);
        
        $response = $next($request);
        
        // Si la connexion réussit, effacer le compteur
        if ($response->isSuccessful() || $response->isRedirection()) {
            $this->limiter->clear($key);
        }
        
        return $response;
    }

    /**
     * Générer une clé unique basée sur l'IP et l'email
     */
    protected function resolveRequestSignature(Request $request): string
    {
        return sha1($request->ip() . '|' . $request->input('email'));
    }
}
