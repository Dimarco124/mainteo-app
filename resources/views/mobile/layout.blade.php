<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#059669">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'MAINTEO Mobile')</title>
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="/manifest.json">
    
    <!-- Icons -->
    <link rel="icon" type="image/png" sizes="192x192" href="/images/icons/icon-192x192.png">
    <link rel="apple-touch-icon" href="/images/icons/icon-192x192.png">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Varela+Round&display=swap" rel="stylesheet">
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Mobile Styles -->
    <link rel="stylesheet" href="{{ asset('css/mobile.css') }}?v={{ time() }}">
    
    @stack('styles')
</head>
<body>
    @yield('content')
    
    <!-- Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(registration => {
                        console.log('[PWA] Service Worker enregistré:', registration.scope);
                    })
                    .catch(error => {
                        console.error('[PWA] Erreur Service Worker:', error);
                    });
            });
        }
        
        // Install prompt
        let deferredPrompt;
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            console.log('[PWA] Installation disponible');
            // Vous pouvez afficher un bouton d'installation personnalisé ici
        });
    </script>
    
    <script src="/js/mobile.js"></script>
    @stack('scripts')

    <!-- Widget Flottant Mainteo IA -->
    @include('partials.mainteo_ai_widget')
</body>
</html>

