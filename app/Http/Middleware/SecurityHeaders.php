<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // Protection contre le sniffing de type MIME
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        
        // Protection contre le clickjacking (empêche l'intégration dans des iframes externes)
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        
        // Protection XSS (Cross-Site Scripting)
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        
        // Force HTTPS pour 1 an (31536000 secondes)
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        
        // Politique de référent (limite les infos envoyées aux sites externes)
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        
        // Désactive géolocalisation, micro, caméra
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
        
        return $response;
    }
}
