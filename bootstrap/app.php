<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(function (): string {
            $isAjaxOrJson = (request()->wantsJson() || request()->isJson() || (request()->header('Accept') && str_contains(request()->header('Accept'), 'application/json')));
            if ($isAjaxOrJson) {
                return route('login');
            }
            return route('login') . '?return_to=' . urlencode(request()->fullUrl());
        });

        // Ajouter les headers de sécurité sur toutes les réponses
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        // Exclure /login des vérifications CSRF (évite les erreurs 419/500 sur PWA mobile lors des reconnexions)
        $middleware->validateCsrfTokens(except: [
            'login',
            '/login',
            'logout',
            '/logout',
            'api/*',
        ]);

        // Enregistrer le middleware de rate limiting
        $middleware->alias([
            'throttle.login' => \App\Http\Middleware\ThrottleLogin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontReport(\Illuminate\Auth\AuthenticationException::class);

        // Gestion automatique des erreurs CSRF (token expiré)
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, Request $request) {
            $request->session()->regenerateToken();
            return redirect()->route('login')
                ->withInput($request->only('email'))
                ->with('warning', 'Votre session a expiré. Veuillez vous reconnecter.');
        });

        // Gestion des erreurs HTTP 419 (CSRF token mismatch)
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, Request $request) {
            if ($e->getStatusCode() === 419 || str_contains(strtolower($e->getMessage()), 'csrf')) {
                $request->session()->regenerateToken();
                return redirect()->route('login')
                    ->withInput($request->only('email'))
                    ->with('warning', 'Votre session a expiré. Veuillez vous reconnecter.');
            }
        });

        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, Request $request) {
            $isAjax = $request->wantsJson() || $request->isJson() || ($request->header('Accept') && str_contains($request->header('Accept'), 'application/json'));
            if ($isAjax) {
                return response()->json([
                    'error' => true,
                    'message' => 'Session expirée. Veuillez vous reconnecter.',
                    'redirect' => route('login'),
                ], 401);
            }
            $returnTo = $request->fullUrl();
            try {
                if (str_contains($returnTo, route('login'))) $returnTo = route('dashboard');
            } catch (\Throwable $e) {
            }
            try {
                $loginUrl = route('login') . ($returnTo ? ('?return_to=' . urlencode($returnTo)) : '');
                return redirect()->guest($loginUrl)->with('warning', 'Votre session a expiré ou a été perdue. Veuillez vous reconnecter.');
            } catch (\Throwable $e2) {
                $fallbackUrl = '/login';
                $html = '<!doctype html><html lang="fr"><head><meta charset="utf-8"><title>Session expirée</title>'
                    . '<meta http-equiv="refresh" content="0;url=' . htmlspecialchars($fallbackUrl, ENT_QUOTES) . '">'
                    . '</head><body style="font-family:Arial,sans-serif;padding:40px">'
                    . '<h1 style="color:#991b1b">⚠️ Session expirée</h1>'
                    . '<p>Vous allez être redirigé vers la page de connexion…</p>'
                    . '<a href="' . htmlspecialchars($fallbackUrl) . '">Cliquez ici si rien ne se passe</a>'
                    . '</body></html>';
                return response($html, 302)->header('Location', $fallbackUrl)->header('Content-Type', 'text/html; charset=utf-8');
            }
        });

        $exceptions->report(function (Throwable $e) {
            try {
                $msg = '[GLOBAL-500] ' . get_class($e) . ' : ' . $e->getMessage();
                $ctx = [
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine(),
                    'url'     => request()?->fullUrl(),
                    'method'  => request()?->method(),
                    'ip'      => request()?->ip(),
                    'user_id' => auth()?->id(),
                    'trace'   => $e->getTraceAsString(),
                ];
                Log::emergency($msg, $ctx);
                $logDir = storage_path('logs');
                if (is_dir($logDir) && is_writable($logDir)) {
                    $line = '[' . date('Y-m-d H:i:s') . '] EMERGENCY: ' . $msg
                        . ' | file=' . $e->getFile() . ':' . $e->getLine()
                        . ' | url=' . (request()?->fullUrl() ?? '')
                        . PHP_EOL . 'TRACE: ' . $e->getTraceAsString() . PHP_EOL;
                    @file_put_contents($logDir . '/fatal_500.log', $line, FILE_APPEND);
                }
            } catch (\Throwable $eLog) {
                $dir = __DIR__ . '/../storage/logs';
                if (is_dir($dir) && is_writable($dir)) {
                    @file_put_contents(
                        $dir . '/fatal_500.log',
                        '[' . date('Y-m-d H:i:s') . '] LOGBOOT: ' . $eLog->getMessage()
                            . ' ORIGINAL: ' . get_class($e) . ' : ' . $e->getMessage()
                            . ' at ' . $e->getFile() . ':' . $e->getLine() . PHP_EOL,
                        FILE_APPEND
                    );
                }
            }
        });

        $exceptions->render(function (Throwable $e, Request $request) {
            try {
                // Ne JAMAIS intercepter les exceptions de validation de formulaire :
                // Laisser Laravel rediriger automatiquement en arrière avec les erreurs et les anciennes saisies (ou 422 JSON)
                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    return null;
                }

                // Ne pas intercepter les erreurs d'authentification / autorisation standard
                if ($e instanceof \Illuminate\Auth\AuthenticationException || $e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                    return null;
                }

                // Ne pas intercepter les erreurs HTTP client (404, 403, 405, etc.) qui ne sont pas des erreurs 500
                if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface && $e->getStatusCode() < 500 && $e->getStatusCode() !== 419) {
                    return null;
                }

                // Si c'est une erreur 419 ou CSRF / session expirée, redirection propre vers login
                if (($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException && $e->getStatusCode() === 419)
                    || ($e instanceof \Illuminate\Session\TokenMismatchException)
                    || str_contains(strtolower($e->getMessage()), 'csrf')) {
                    $request->session()->regenerateToken();
                    return redirect()->route('login')
                        ->withInput($request->only('email'))
                        ->with('warning', 'Votre session a expiré. Veuillez vous reconnecter.');
                }

                $msg = $e->getMessage();
                if (empty($msg)) $msg = get_class($e);
                $short = '[' . get_class($e) . '] ' . $msg . ' (ligne ' . $e->getLine() . ')';
                $wantJson = $request->wantsJson() || $request->isJson() || ($request->header('Accept') && str_contains($request->header('Accept'), 'json'));
                if ($wantJson) {
                    return response()->json([
                        'error' => true,
                        'message' => $msg,
                        'class'   => get_class($e),
                        'file'    => $e->getFile(),
                        'line'    => $e->getLine(),
                    ], 500);
                }
                $urlBack = $request->headers->get('referer') ?? url()->previous() ?? '/';
                $html = '<!doctype html><html lang="fr"><head><meta charset="utf-8">'
                    . '<title>Erreur 500 — ' . htmlspecialchars(get_class($e), ENT_QUOTES) . '</title>'
                    . '<meta name="viewport" content="width=device-width,initial-scale=1">'
                    . '<style>body{font-family:system-ui,Arial,sans-serif;max-width:760px;margin:40px auto;padding:0 16px;line-height:1.5}'
                    . '.box{padding:20px;border-radius:14px}.err{background:#fee2e2;border:2px solid #dc2626;color:#7f1d1d}'
                    . 'pre{background:#fff;border:1px solid #fecaca;padding:12px;border-radius:8px;overflow:auto;font-size:0.8rem;max-height:320px}'
                    . 'a{color:#1d4ed8;font-weight:700}.row{display:flex;gap:8px;flex-wrap:wrap;margin-top:14px}'
                    . '.btn{padding:8px 14px;border-radius:8px;text-decoration:none;font-weight:700;display:inline-block}'
                    . '.btn.primary{background:#1d4ed8;color:#fff}.btn.secondary{background:#f1f5f9;color:#0f172a;border:2px solid #cbd5e1}'
                    . '</style></head><body>'
                    . '<div class="box err">'
                    . '<strong style="font-size:1.05rem">❌ ERREUR 500 DÉTAILLÉE</strong><br><br>'
                    . '<strong>Type :</strong> ' . htmlspecialchars(get_class($e), ENT_QUOTES) . '<br>'
                    . '<strong>Message :</strong> ' . htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') . '<br>'
                    . '<strong>Fichier :</strong> ' . htmlspecialchars($e->getFile(), ENT_QUOTES) . '<br>'
                    . '<strong>Ligne :</strong> ' . (int)$e->getLine() . '<br>'
                    . '<strong>URL :</strong> ' . htmlspecialchars($request->fullUrl(), ENT_QUOTES) . '<br>'
                    . '<strong>Méthode :</strong> ' . htmlspecialchars($request->method(), ENT_QUOTES) . '<br>'
                    . '<hr style="border-color:#fecaca;margin:14px 0">'
                    . '<strong>Trace (extraits) :</strong>'
                    . '<pre>' . htmlspecialchars($e->getTraceAsString(), ENT_QUOTES, 'UTF-8') . '</pre>'
                    . '<div class="row">'
                    . '<a class="btn primary" href="' . htmlspecialchars($urlBack, ENT_QUOTES) . '">← Retour au formulaire</a>'
                    . '<a class="btn secondary" href="/">↩ Accueil</a>'
                    . '</div>'
                    . '</div></body></html>';
                return response($html, 500)->header('Content-Type', 'text/html; charset=utf-8');
            } catch (\Throwable $e2) {
                $html2 = '<!doctype html><html lang="fr"><head><meta charset="utf-8"><title>Erreur fatale</title></head>'
                    . '<body style="font-family:Arial,sans-serif;background:#fee2e2;color:#7f1d1d;padding:30px">'
                    . '<h1>❌ ERREUR 500</h1>'
                    . '<p><strong>Classe :</strong> ' . htmlspecialchars(get_class($e)) . '</p>'
                    . '<p><strong>Message :</strong> ' . htmlspecialchars($e->getMessage()) . '</p>'
                    . '<p><strong>Fichier :</strong> ' . htmlspecialchars($e->getFile()) . '</p>'
                    . '<p><strong>Ligne :</strong> ' . (int)$e->getLine() . '</p>'
                    . '<pre style="background:#fff;padding:10px;border-radius:6px">' . htmlspecialchars($e->getTraceAsString()) . '</pre>'
                    . '</body></html>';
                return response($html2, 500)->header('Content-Type', 'text/html; charset=utf-8');
            }
        });
    })->create();
