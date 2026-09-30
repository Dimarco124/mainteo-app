<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MAINTEO GMAO - @yield('title', 'Gestion de Maintenance')</title>

    <!-- PWA Manifest -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#059669">
    <link rel="apple-touch-icon" href="{{ asset('images/icons/icon-192x192.png') }}">

    <!-- Google Fonts Varela Round -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Varela+Round&display=swap" rel="stylesheet">

    <!-- FontAwesome icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- jQuery (nécessaire pour Select2) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Select2 pour recherche d'équipements -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- PWA Installation Styles -->
    <link rel="stylesheet" href="{{ asset('css/pwa-install.css') }}">

    <style>
        html, body {
            overflow-x: hidden;
        }

        .select2-container {
            width: 100% !important;
            max-width: 100% !important;
        }

        .select2-dropdown {
            box-sizing: border-box !important;
            max-width: 100% !important;
            overflow-x: hidden !important;
        }

        :root {
            --main-emerald: #059669;
            --main-emerald-light: #10b981;
            --bg-body: #f8fafc;
            --bg-card: #ffffff;
            --border-slate: #e2e8f0;
            --text-dark: #0f172a;
            --text-muted: #64748b;
        }

        /* 🌙 MODE SOMBRE */
        @if(auth()->check() && auth()->user()->dark_mode)
        body {
            --bg-body: #0f172a;
            --bg-card: #1e293b;
            --border-slate: #334155;
            --text-dark: #f1f5f9;
            --text-muted: #94a3b8;
        }
        
        .sidebar {
            background-color: #1e293b !important;
            border-right-color: #334155 !important;
        }
        
        .sidebar .logo {
            color: #f1f5f9 !important;
        }
        
        .sidebar .nav-item a {
            color: #cbd5e1 !important;
        }
        
        .sidebar .nav-item a:hover {
            background-color: #334155 !important;
        }
        
        .sidebar .nav-item.active a {
            background-color: #059669 !important;
            color: white !important;
        }
        
        .header {
            background-color: transparent !important;
        }
        
        .header h1, .header p {
            color: var(--text-dark) !important;
        }
        
        .card {
            background-color: var(--bg-card) !important;
            border-color: var(--border-slate) !important;
            color: var(--text-dark) !important;
        }
        
        table {
            color: var(--text-dark) !important;
        }
        
        table thead {
            background-color: #334155 !important;
            color: #f1f5f9 !important;
        }
        
        table tbody tr {
            border-bottom-color: var(--border-slate) !important;
        }
        
        table tbody tr:hover {
            background-color: #1e293b !important;
        }
        
        input, select, textarea {
            background-color: #1e293b !important;
            color: #f1f5f9 !important;
            border-color: #334155 !important;
        }
        
        input::placeholder, textarea::placeholder {
            color: #64748b !important;
        }
        
        .badge {
            background-color: #334155 !important;
            color: #f1f5f9 !important;
        }
        
        .select2-container--default .select2-selection--single {
            background-color: #1e293b !important;
            color: #f1f5f9 !important;
            border-color: #334155 !important;
        }
        
        .select2-dropdown {
            background-color: #1e293b !important;
            border-color: #334155 !important;
        }
        
        .select2-results__option {
            color: #f1f5f9 !important;
        }
        
        .select2-results__option--highlighted {
            background-color: #059669 !important;
        }
        @endif

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Varela Round', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        body {
            background-color: var(--bg-body);
            color: var(--text-dark);
            display: flex;
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
            font-size: 13px;
        }

        /* Sidebar originale du projet PHP (Blanc + Émeraude) */
        .sidebar {
            width: 260px;
            background-color: #ffffff;
            border-right: 1px solid var(--border-slate);
            display: flex;
            flex-direction: column;
            padding: 0.75rem 1rem;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 50;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, margin-left 0.3s ease;
        }

        /* Quand la sidebar est fermée via le bouton Hamburger */
        body.sidebar-closed .sidebar {
            transform: translateX(-260px);
        }

        body.sidebar-closed .main-content {
            margin-left: 0;
            width: 100%;
        }

        .brand-badge {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.6rem 0.75rem;
            background-color: #ffffff;
            border: 1px solid var(--border-slate);
            border-radius: 0.9rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            margin-bottom: 1rem;
        }

        .brand-logo {
            width: 36px;
            height: 36px;
            border-radius: 0.7rem;
            background: linear-gradient(135deg, var(--main-emerald), var(--main-emerald-light));
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            box-shadow: 0 4px 10px rgba(5, 150, 105, 0.2);
        }

        .brand-title {
            font-size: 0.8rem;
            font-weight: 800;
            letter-spacing: -0.01em;
            color: #1e293b;
        }

        .brand-sub {
            font-size: 0.65rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        .nav-links {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            flex: 1;
            overflow-y: auto;
            scrollbar-width: none;       /* Firefox */
            -ms-overflow-style: none;    /* IE / Edge */
        }

        .nav-links::-webkit-scrollbar {
            display: none;               /* Chrome / Safari */
        }

        .nav-item a {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 0.55rem 0.75rem;
            color: #475569;
            text-decoration: none;
            border-radius: 0.7rem;
            font-size: 0.8rem;
            font-weight: 600;
            transition: all 0.2s ease;
            line-height: 1.4;
        }

        .nav-item a i {
            width: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .nav-item a:hover {
            background-color: #f1f5f9;
            color: #0f172a;
        }

        .nav-item.active a {
            background: linear-gradient(135deg, var(--main-emerald), var(--main-emerald-light));
            color: #ffffff;
            box-shadow: 0 8px 16px -4px rgba(5, 150, 105, 0.25);
        }

        .main-content {
            margin-left: 260px;
            flex: 1;
            padding: 0;
            width: calc(100% - 260px);
            transition: margin-left 0.3s ease, width 0.3s ease;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* Top Bar Header avec Hamburger - En haut, pleine largeur, FIXE */
        .top-navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: #ffffff;
            border-bottom: 1px solid var(--border-slate);
            padding: 0.5rem 1.5rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
            position: fixed;
            top: 0;
            left: 260px;
            right: 0;
            z-index: 100;
            transition: left 0.3s ease;
            height: 60px;
        }

        body.sidebar-closed .top-navbar {
            left: 0;
        }

        /* Conteneur de contenu avec padding et marge pour le header fixe */
        .content-wrapper {
            padding: 1.5rem 2rem 2rem 2rem;
            padding-top: calc(60px + 1.5rem);
            flex: 1;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
        }

        /* Menu profil déroulant */
        .profile-menu {
            position: relative;
        }

        .profile-trigger {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 0.75rem;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .profile-trigger:hover {
            background-color: #f1f5f9;
            border-color: #cbd5e1;
        }

        .profile-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--main-emerald), var(--main-emerald-light));
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            color: #ffffff;
            font-size: 0.8rem;
        }

        .profile-info {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .profile-name {
            font-size: 0.82rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.2;
        }

        .profile-role {
            font-size: 0.7rem;
            color: #64748b;
            text-transform: capitalize;
        }

        .profile-dropdown {
            position: absolute;
            top: calc(100% + 0.5rem);
            right: 0;
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            min-width: 280px;
            display: none;
            z-index: 1000;
            padding: 0.75rem;
        }

        .profile-dropdown.active {
            display: block;
            animation: slideDown 0.2s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .profile-dropdown-header {
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
            margin-bottom: 0.5rem;
        }

        .profile-dropdown-header h4 {
            font-size: 0.95rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.25rem;
        }

        .profile-dropdown-header p {
            font-size: 0.8rem;
            color: #64748b;
            margin-bottom: 0.25rem;
        }

        .profile-dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: #475569;
            text-decoration: none;
            border-radius: 0.5rem;
            transition: all 0.2s;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .profile-dropdown-item:hover {
            background-color: #f8fafc;
            color: var(--main-emerald);
        }

        .profile-dropdown-item i {
            width: 20px;
            text-align: center;
        }

        .profile-dropdown-divider {
            height: 1px;
            background-color: #e2e8f0;
            margin: 0.5rem 0;
        }

        .profile-dropdown-logout {
            background-color: #fff1f2;
            color: #be123c;
        }

        .profile-dropdown-logout:hover {
            background-color: #ffe4e6;
            color: #be123c;
        }

        .btn-hamburger {
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            color: #1e293b;
            width: 36px;
            height: 36px;
            border-radius: 0.6rem;
            font-size: 1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s;
        }

        .btn-hamburger:hover {
            background-color: #e2e8f0;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .page-title h1 {
            font-size: 1.15rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.01em;
        }

        .page-title p {
            color: var(--text-muted);
            font-size: 0.75rem;
            margin-top: 0.1rem;
        }

        /* Header de page avec titre et boutons d'action */
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.75rem;
        }

        /* Espacement vertical global */
        .card {
            background-color: var(--bg-card);
            border: 1px solid var(--border-slate);
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1.75rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.25rem;
            margin-bottom: 1.75rem;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.25rem;
        }

        .card-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: #0f172a;
        }

        /* Style des Champs de Formulaires (Fond blanc, texte sombre, bordure claire) */
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="date"],
        input[type="number"],
        select,
        textarea {
            background-color: #ffffff !important;
            border: 1px solid #cbd5e1 !important;
            color: #0f172a !important;
            border-radius: 0.6rem !important;
            font-size: 0.8rem !important;
            transition: border-color 0.2s, box-shadow 0.2s !important;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: var(--main-emerald-light) !important;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15) !important;
            outline: none !important;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.25rem;
            margin-bottom: 1.75rem;
        }

        .stat-card {
            background-color: #ffffff;
            border: 1px solid var(--border-slate);
            border-radius: 1rem;
            padding: 1.75rem;
            display: flex;
            align-items: center;
            gap: 1.25rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
            transition: transform 0.2s, box-shadow 0.2s;
            min-height: 120px;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }

        .stat-val {
            font-size: 1.6rem;
            font-weight: 800;
            color: #0f172a;
        }

        .stat-label {
            font-size: 0.78rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            /* Firefox */
            -ms-overflow-style: none;
            /* IE et Edge */
        }

        .table-responsive::-webkit-scrollbar {
            display: none;
            /* Chrome, Safari et Opera */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.8rem;
            min-width: 900px;
        }

        th {
            padding: 0.7rem 0.9rem;
            color: #64748b;
            background-color: #f8fafc;
            border-bottom: 1px solid var(--border-slate);
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.68rem;
            letter-spacing: 0.05em;
            white-space: nowrap;
        }

        td {
            padding: 0.9rem;
            border-bottom: 1px solid var(--border-slate);
            color: #334155;
            white-space: nowrap;
        }

        td strong,
        td code {
            white-space: nowrap;
        }

        tr:hover td {
            background-color: #f8fafc;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.2rem 0.6rem;
            border-radius: 9999px;
            font-size: 0.68rem;
            font-weight: 700;
        }

        .badge-success {
            background-color: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
        }

        .badge-warning {
            background-color: #fffbeb;
            color: #b45309;
            border: 1px solid #fde68a;
        }

        .badge-danger {
            background-color: #fff1f2;
            color: #be123c;
            border: 1px solid #fecdd3;
        }

        .badge-info {
            background-color: #f0f9ff;
            color: #0369a1;
            border: 1px solid #bae6fd;
        }

        /* Boutons originaux (Émeraude) */
        .btn-primary {
            background: linear-gradient(135deg, var(--main-emerald), var(--main-emerald-light));
            color: #ffffff !important;
            padding: 0.65rem 1.1rem;
            border-radius: 0.75rem;
            text-decoration: none;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.2);
            border: none;
            cursor: pointer;
            transition: opacity 0.2s;
            font-size: 0.8rem;
        }

        .btn-primary:hover {
            opacity: 0.95;
        }

        /* Icône de notification dans le header */
        .notification-bell {
            position: relative;
            margin-right: 1.5rem;
        }

        .notification-bell-btn {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #64748b;
            width: 40px;
            height: 40px;
            border-radius: 0.75rem;
            font-size: 1.1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            position: relative;
        }

        .notification-bell-btn:hover {
            background-color: #f1f5f9;
            border-color: #cbd5e1;
            color: var(--main-emerald);
        }

        .notification-bell-btn.has-notifications {
            color: var(--main-emerald);
        }

        .notification-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            background-color: #be123c;
            color: #ffffff;
            border-radius: 9999px;
            padding: 0.1rem 0.4rem;
            font-size: 0.65rem;
            font-weight: 800;
            min-width: 18px;
            text-align: center;
            border: 2px solid #ffffff;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }
        }

        .notification-dropdown {
            position: absolute;
            top: calc(100% + 0.75rem);
            right: 0;
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            width: 380px;
            max-height: 500px;
            display: none;
            z-index: 1000;
            overflow: hidden;
        }

        .notification-dropdown.active {
            display: block;
            animation: slideDown 0.2s ease;
        }

        .notification-dropdown-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #f8fafc;
        }

        .notification-dropdown-header h3 {
            font-size: 0.95rem;
            font-weight: 700;
            color: #0f172a;
        }

        .notification-dropdown-header .mark-all-read {
            font-size: 0.75rem;
            color: var(--main-emerald);
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
        }

        .notification-dropdown-header .mark-all-read:hover {
            text-decoration: underline;
        }

        .notification-list {
            max-height: 400px;
            overflow-y: auto;
        }

        .notification-item {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #f1f5f9;
            cursor: pointer;
            transition: background-color 0.2s;
            position: relative;
        }

        .notification-item:hover {
            background-color: #f8fafc;
        }

        .notification-item.unread {
            background-color: #f0fdf4;
        }

        .notification-item.unread::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background-color: var(--main-emerald);
        }

        .notification-item-title {
            font-size: 0.85rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.25rem;
        }

        .notification-item-message {
            font-size: 0.8rem;
            color: #64748b;
            margin-bottom: 0.5rem;
            line-height: 1.4;
        }

        .notification-item-time {
            font-size: 0.7rem;
            color: #94a3b8;
        }

        .notification-empty {
            padding: 3rem 1.25rem;
            text-align: center;
            color: #94a3b8;
        }

        .notification-empty i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }

        .notification-footer {
            padding: 0.75rem 1.25rem;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            background-color: #f8fafc;
        }

        .notification-footer a {
            font-size: 0.8rem;
            color: var(--main-emerald);
            text-decoration: none;
            font-weight: 600;
        }

        .notification-footer a:hover {
            text-decoration: underline;
        }

        /* ══════════════════════════════════════════════════════════════
           POPUP NOTIFICATION MODAL (centré à l'écran)
           ══════════════════════════════════════════════════════════════ */

        .notif-popup-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(4px);
            z-index: 999999;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .notif-popup-overlay.visible {
            display: flex;
            animation: fadeInOverlay 0.25s ease;
        }

        @keyframes fadeInOverlay {
            from { opacity: 0; }
            to   { opacity: 1; }
        }

        .notif-popup-card {
            background: #ffffff;
            border-radius: 1.25rem;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.18);
            padding: 2rem 2rem 1.75rem;
            max-width: 440px;
            width: 100%;
            position: relative;
            animation: popupSlideUp 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        @keyframes popupSlideUp {
            from { transform: translateY(30px) scale(0.95); opacity: 0; }
            to   { transform: translateY(0)   scale(1);    opacity: 1; }
        }

        .notif-popup-icon {
            width: 52px;
            height: 52px;
            border-radius: 1rem;
            background: linear-gradient(135deg, #059669, #10b981);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            color: #ffffff;
            margin-bottom: 1.1rem;
            box-shadow: 0 6px 16px rgba(5, 150, 105, 0.3);
        }

        .notif-popup-tag {
            display: inline-block;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #059669;
            background: #ecfdf5;
            padding: 0.2rem 0.6rem;
            border-radius: 999px;
            margin-bottom: 0.5rem;
        }

        .notif-popup-title {
            font-size: 1.05rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 0.45rem;
            line-height: 1.3;
        }

        .notif-popup-message {
            font-size: 0.88rem;
            color: #475569;
            line-height: 1.6;
            margin-bottom: 1.4rem;
        }

        .notif-popup-actions {
            display: flex;
            gap: 0.65rem;
        }

        .notif-popup-btn-primary {
            flex: 1;
            padding: 0.65rem 1rem;
            background: linear-gradient(135deg, #059669, #10b981);
            color: #ffffff;
            border: none;
            border-radius: 0.75rem;
            font-size: 0.85rem;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
            transition: all 0.2s;
        }

        .notif-popup-btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(5, 150, 105, 0.4);
        }

        .notif-popup-btn-close {
            padding: 0.65rem 1rem;
            background: #f1f5f9;
            color: #64748b;
            border: none;
            border-radius: 0.75rem;
            font-size: 0.85rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
        }

        .notif-popup-btn-close:hover {
            background: #e2e8f0;
            color: #0f172a;
        }

        .notif-popup-close-x {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: #f1f5f9;
            border: none;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            font-size: 0.8rem;
            color: #94a3b8;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .notif-popup-close-x:hover {
            background: #e2e8f0;
            color: #0f172a;
        }

        .notif-popup-progress {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            background: linear-gradient(90deg, #059669, #10b981);
            border-radius: 0 0 1.25rem 1.25rem;
            animation: progressShrink 8s linear forwards;
        }

        @keyframes progressShrink {
            from { width: 100%; }
            to   { width: 0%; }
        }

        /* ══════════════════════════════════════════════════════════════
           RESPONSIVE MOBILE — Sidebar drawer + header adaptatif
           ══════════════════════════════════════════════════════════════ */

        /* Overlay sombre derrière la sidebar sur mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.45);
            z-index: 40;
            backdrop-filter: blur(2px);
            -webkit-backdrop-filter: blur(2px);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }

        .sidebar-overlay.visible {
            opacity: 1;
            pointer-events: auto;
        }

        /* ── Grilles profil & paramètres (desktop : 2 colonnes) ── */
        .profile-grid,
        .settings-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.75rem;
        }

        /* Pied de la section système dans Paramètres */
        .settings-system-footer {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        @media (max-width: 768px) {

            /* ── Sidebar : drawer depuis la gauche ── */
            .sidebar {
                width: 280px;
                transform: translateX(-280px);
                z-index: 50;
                box-shadow: none;
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1),
                            box-shadow 0.3s ease;
            }

            /* Sidebar ouverte sur mobile → classe ajoutée par JS */
            .sidebar.mobile-open {
                transform: translateX(0);
                box-shadow: 4px 0 24px rgba(0, 0, 0, 0.15);
                padding-top: calc(56px + 0.75rem); /* Pousse le brand sous la top-navbar fixe */
            }

            /* L'overlay s'affiche sur mobile */
            .sidebar-overlay {
                display: block;
            }

            /* Le main-content prend toute la largeur sur mobile */
            .main-content {
                margin-left: 0 !important;
                width: 100% !important;
            }

            /* Top navbar : pleine largeur sur mobile */
            .top-navbar {
                left: 0 !important;
                padding: 0.5rem 1rem;
                height: 56px;
            }

            /* Masquer le nom/rôle utilisateur sur mobile pour gagner de la place */
            .profile-info {
                display: none;
            }

            /* Réduire le trigger profil sur mobile */
            .profile-trigger {
                padding: 0.4rem 0.6rem;
                gap: 0.5rem;
            }

            /* Décaler le content-wrapper pour le header fixe plus petit */
            .content-wrapper {
                padding: 1rem;
                padding-top: calc(56px + 1rem);
            }

            /* ── Header de page : titre + boutons empilés ── */
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
                margin-bottom: 1.25rem;
            }

            /* Les boutons d'action passent en-dessous, alignés à gauche */
            .header > *:not(:first-child),
            .header .header-actions,
            .header .btn-primary,
            .header a.btn-primary,
            .header button.btn-primary {
                align-self: flex-start;
            }

            /* Notification dropdown parfaitement centré et adapté sur mobile */
            .notification-dropdown {
                position: fixed !important;
                top: 65px !important;
                left: 1rem !important;
                right: 1rem !important;
                width: auto !important;
                max-width: calc(100vw - 2rem) !important;
                max-height: 75vh !important;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25) !important;
            }

            /* Sidebar fermée via hamburger : on ignore l'état sidebar-closed sur mobile */
            body.sidebar-closed .sidebar {
                transform: translateX(-280px);
            }

            body.sidebar-closed .main-content {
                margin-left: 0;
                width: 100%;
            }

            body.sidebar-closed .top-navbar {
                left: 0;
            }

            /* ── Grilles profil & paramètres : 1 colonne sur mobile ── */
            .profile-grid,
            .settings-grid {
                grid-template-columns: 1fr;
                gap: 1.25rem;
            }

            /* Footer système : empiler texte + lien sur mobile */
            .settings-system-footer {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
            }
        }

        /* ═══════════════════════════════════════════════════════════════
           SELECT2 - Style personnalisé pour recherche d'équipements
        ══════════════════════════════════════════════════════════════════ */
        .select2-container--default .select2-selection--single {
            height: 45px;
            border: 1.5px solid #e2e8f0;
            border-radius: 0.5rem;
            padding: 0.5rem;
            background-color: #ffffff;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 30px;
            color: #0f172a;
            padding-left: 8px;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 43px;
            right: 8px;
        }
        
        .select2-dropdown {
            border: 1.5px solid #e2e8f0;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .select2-container--default .select2-search--dropdown .select2-search__field {
            border: 1.5px solid #cbd5e1;
            border-radius: 0.375rem;
            padding: 0.5rem;
            font-size: 0.95rem;
        }
        
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #059669;
            color: white;
        }
        
        .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: #ecfdf5;
            color: #059669;
        }
        
        .select2-container {
            width: 100% !important;
        }
    </style>
</head>

<body>

    <!-- Overlay mobile pour fermer la sidebar en cliquant à l'extérieur -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar originale avec les 9 onglets exacts du projet PHP legacy -->
    <aside class="sidebar" id="mainSidebar">
        <div class="brand-badge">
            <div class="brand-logo">
                <i class="fa-solid fa-wrench"></i>
            </div>
            <div>
                <div class="brand-title">MAINTEO</div>
                <div class="brand-sub">GMAO & Interventions</div>
            </div>
        </div>

        <ul class="nav-links">
            @auth
            @php $role = Auth::user()->type_utilisateur; @endphp

            {{-- ══════════════════════════════════════════════════ --}}
            {{-- 1. TABLEAU DE BORD — Visible par tous             --}}
            {{-- ══════════════════════════════════════════════════ --}}
            <li class="nav-item {{ request()->routeIs('*.dashboard') || request()->routeIs('dashboard') ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}"><i class="fa-solid fa-chart-pie"></i> Tableau de bord</a>
            </li>

            {{-- ══════════════════════════════════════════════════════════════ --}}
            {{-- 1B. MAINTEO IA — ASSISTANT COPILOTE (Tous les rôles)           --}}
            {{-- ══════════════════════════════════════════════════════════════ --}}
            <li class="nav-item {{ request()->routeIs('ai.*') ? 'active' : '' }}">
                <a href="{{ route('ai.index') }}" style="display: flex; align-items: center; justify-content: space-between;">
                    <span style="display: flex; align-items: center; gap: 0.6rem;">
                        <i class="fa-solid fa-wand-magic-sparkles" style="color: #10b981;"></i>
                        <span>Mainteo IA</span>
                    </span>
                    <span style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff; padding: 0.12rem 0.45rem; border-radius: 9999px; font-size: 0.65rem; font-weight: 800; letter-spacing: 0.5px;">
                        IA
                    </span>
                </a>
            </li>
            {{-- ══════════════════════════════════════════════════════════════ --}}
            {{-- 2. DEMANDES D'INTERVENTION (Nouveau Workflow)                --}}
            {{-- Tous les rôles sauf techniciens                              --}}
            {{-- ══════════════════════════════════════════════════════════════ --}}
            @if(!in_array($role, ['technicien', 'chef technicien']))
            <li class="nav-item {{ request()->routeIs('demandes.*') ? 'active' : '' }}">
                <a href="{{ route('demandes.index') }}">
                    <i class="fa-solid fa-file-lines"></i> Demandes
                    @if($role === 'admin' || $role === 'superviseur_client' || $role === 'superviseur_soutarah')
                    @php
                    $countDemandes = 0;
                    if($role === 'admin') {
                        // Admin : demandes validées par client en attente de validation Soutarah
                        $countDemandes = \App\Models\Demande::where('statut', 'validated_by_client')->count();
                    } elseif($role === 'superviseur_client') {
                        // Superviseur Client : demandes en attente de validation client de SA base
                        $countDemandes = \App\Models\Demande::where('base_id', Auth::user()->base_id)
                        ->where('statut', 'pending_client_validation')->count();
                    } elseif($role === 'superviseur_soutarah') {
                        // Superviseur Soutarah : demandes validées par client de SA base/client
                        $assignment = \App\Models\Assignment::where('superviseur_soutarah_id', Auth::id())->first();
                        if($assignment) {
                            if($assignment->base_id) {
                                $countDemandes = \App\Models\Demande::where('base_id', $assignment->base_id)
                                ->where('statut', 'validated_by_client')->count();
                            } elseif($assignment->client_id) {
                                $countDemandes = \App\Models\Demande::where('client_id', $assignment->client_id)
                                ->where('statut', 'validated_by_client')->count();
                            }
                        }
                    }
                    @endphp
                    {{-- Afficher le badge uniquement pour admin et superviseur_soutarah (même si 0), pas pour superviseur_client --}}
                    @if($role === 'admin' || $role === 'superviseur_soutarah')
                    <span style="background-color: {{ $countDemandes > 0 ? '#fffbeb' : '#f1f5f9' }}; color: {{ $countDemandes > 0 ? '#b45309' : '#64748b' }}; border: 1px solid {{ $countDemandes > 0 ? '#fde68a' : '#cbd5e1' }}; padding: 0.1rem 0.55rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 800; margin-left: auto;">
                        {{ $countDemandes }}
                    </span>
                    @elseif($role === 'superviseur_client' && $countDemandes > 0)
                    <span style="background-color: #fffbeb; color: #b45309; border: 1px solid #fde68a; padding: 0.1rem 0.55rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 800; margin-left: auto;">
                        {{ $countDemandes }}
                    </span>
                    @endif
                    @endif
                </a>
            </li>
            @endif

            {{-- ══════════════════════════════════════════════════════════ --}}
            {{-- 2B. MES INTERVENTIONS (Techniciens uniquement)           --}}
            {{-- Vue des interventions assignées au technicien             --}}
            {{-- ══════════════════════════════════════════════════════════ --}}
            @if(in_array($role, ['technicien', 'chef technicien']))
            <li class="nav-item {{ request()->routeIs('technicien.interventions') ? 'active' : '' }}">
                <a href="{{ route('technicien.interventions') }}">
                    <i class="fa-solid fa-clipboard-check"></i> Mes Interventions
                    @php
                    $equipesIds = Auth::user()->equipes->pluck('id')->toArray();
                    $countInterventions = \App\Models\Depannage::where(function($q) use ($equipesIds) {
                    $q->where('technicien_id', Auth::id());
                    if (!empty($equipesIds)) {
                    $q->orWhereIn('equipe_id', $equipesIds);
                    }
                    })
                    ->where('statut', 'en cours')
                    ->count();
                    @endphp
                    @if($countInterventions > 0)
                    <span style="background-color: #fffbeb; color: #b45309; border: 1px solid #fde68a; padding: 0.1rem 0.55rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 800; margin-left: auto;">
                        {{ $countInterventions }}
                    </span>
                    @endif
                </a>
            </li>
            @endif

            {{-- ══════════════════════════════════════════════════════════ --}}
            {{-- 3. OPÉRATIONS / AFFECTATIONS                            --}}
            {{-- Admin + Superviseur Soutarah uniquement                   --}}
            {{-- ══════════════════════════════════════════════════════════ --}}
            @if($role === 'admin' || $role === 'superviseur_soutarah')
            @php
            $countInterventionsAAffecter = \App\Models\Depannage::where('statut', 'en attente')
            ->whereNull('technicien_id')
            ->whereNull('equipe_id');

            if($role === 'superviseur_soutarah') {
            $assignment = \App\Models\Assignment::where('superviseur_soutarah_id', Auth::id())->first();
            if($assignment) {
            if($assignment->base_id) {
            $countInterventionsAAffecter->whereHas('equipement.site', function($q) use ($assignment) {
            $q->where('base_id', $assignment->base_id);
            });
            } elseif($assignment->client_id) {
            $countInterventionsAAffecter->whereHas('equipement.site.baseSite', function($q) use ($assignment) {
            $q->where('client_id', $assignment->client_id);
            });
            }
            } else {
            $countInterventionsAAffecter->whereRaw('1 = 0');
            }
            }
            $countInterventionsAAffecter = $countInterventionsAAffecter->count();
            @endphp
            <li class="nav-item {{ request()->routeIs('operations.*') ? 'active' : '' }}">
                <a href="{{ route('operations.index') }}">
                    <i class="fa-solid fa-gears"></i> Interventions
                    <span style="background-color: {{ $countInterventionsAAffecter > 0 ? '#fff1f2' : '#f1f5f9' }}; color: {{ $countInterventionsAAffecter > 0 ? '#be123c' : '#64748b' }}; border: 1px solid {{ $countInterventionsAAffecter > 0 ? '#fecdd3' : '#cbd5e1' }}; padding: 0.1rem 0.55rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 800; margin-left: auto;">
                        {{ $countInterventionsAAffecter }}
                    </span>
                </a>
            </li>
            @endif

            {{-- ════════════════════════════════════════════════════════════════ --}}
            {{-- 4. ENTREPRISES, BASES & SITES                                 --}}
            {{-- Admin et Superviseur Soutarah : page combinée                 --}}
            {{-- ════════════════════════════════════════════════════════════════ --}}
            @if(in_array($role, ['admin', 'superviseur_soutarah']))
            <li class="nav-item {{ request()->routeIs('clients.*') || request()->routeIs('bases-sites.*') || request()->routeIs('sites.*') ? 'active' : '' }}">
                <a href="{{ route('clients.combined') }}"><i class="fa-solid fa-building"></i> Entreprises, Bases & Sites</a>
            </li>
            @endif

            {{-- ═══════════════════════════════════════════════════════════════════ --}}
            {{-- 5. ÉQUIPEMENTS                                                  --}}
            {{-- Admin : CRUD complet sur tous les équipements                      --}}
            {{-- Superviseur Soutarah : CRUD complet sur équipements de SA base     --}}
            {{-- Superviseur Client : CRUD complet sur équipements de SA base       --}}
            {{-- ═══════════════════════════════════════════════════════════════════ --}}
            @if(in_array($role, ['admin', 'superviseur_soutarah', 'superviseur_client']))
            <li class="nav-item {{ request()->routeIs('equipements.*') ? 'active' : '' }}">
                <a href="{{ route('equipements.index') }}"><i class="fa-solid fa-toolbox"></i> Équipements</a>
            </li>
            @endif

            {{-- ═══════════════════════════════════════════════════════════ --}}
            {{-- 6. COMPTES                                                   --}}
            {{-- Admin uniquement : gestion des comptes utilisateurs         --}}
            {{-- ═══════════════════════════════════════════════════════════ --}}
            @if($role === 'admin')
            <li class="nav-item {{ request()->routeIs('utilisateurs.*') ? 'active' : '' }}">
                <a href="{{ route('utilisateurs.index') }}"><i class="fa-solid fa-user-shield"></i> Comptes</a>
            </li>
            @endif

            {{-- ═══════════════════════════════════════════════════════════ --}}
            {{-- 6A. SITES                                                    --}}
            {{-- Superviseur Client : gestion de SES sites (si base)        --}}
            {{-- Superviseur Soutarah : gestion des sites de SA base assignée --}}
            {{-- ═══════════════════════════════════════════════════════════ --}}
            @if($role === 'superviseur_client' && Auth::user()->base_id)
            <li class="nav-item {{ request()->routeIs('sites.*') ? 'active' : '' }}">
                <a href="{{ route('sites.index') }}"><i class="fa-solid fa-map-marker-alt"></i> Mes Sites</a>
            </li>
            @elseif($role === 'superviseur_soutarah')
            @php
            $soutarahAssignmentForSites = \App\Models\Assignment::where('superviseur_soutarah_id', Auth::id())->first();
            @endphp
            @if($soutarahAssignmentForSites && $soutarahAssignmentForSites->base_id)
            <li class="nav-item {{ request()->routeIs('sites.*') ? 'active' : '' }}">
                <a href="{{ route('sites.index') }}"><i class="fa-solid fa-map-marker-alt"></i> Mes Sites</a>
            </li>
            @endif
            @endif

            @if(in_array($role, ['superviseur_soutarah', 'superviseur_client']))
            <li class="nav-item {{ request()->routeIs('zones.*') ? 'active' : '' }}">
                <a href="{{ route('zones.index') }}"><i class="fa-solid fa-layer-group"></i> Mes Emplacements</a>
            </li>
            @endif


            {{-- ═══════════════════════════════════════════════════════════ --}}
            {{-- 6B. DEMANDEURS                                              --}}
            {{-- Superviseur Client : gestion de SES demandeurs             --}}
            {{-- Visible UNIQUEMENT s'il y a des sites (Structure 1 et 3)   --}}
            {{-- Masqué pour Structure 2 (client direct sans sites)         --}}
            {{-- ═══════════════════════════════════════════════════════════ --}}
            @if($role === 'superviseur_client')
            @php
            $hasSites = false;
            if (Auth::user()->base_id) {
            // Structure 1 : Via base
            $hasSites = \App\Models\Site::where('base_id', Auth::user()->base_id)->exists();
            } elseif (Auth::user()->client_id) {
            // Structure 3 : Via client direct
            $hasSites = \App\Models\Site::where('client_id', Auth::user()->client_id)->exists();
            }
            @endphp
            @if($hasSites)
            <li class="nav-item {{ request()->routeIs('demandeurs.*') ? 'active' : '' }}">
                <a href="{{ route('demandeurs.index') }}"><i class="fa-solid fa-users"></i> Demandeurs</a>
            </li>
            @endif
            @endif

            {{-- ═══════════════════════════════════════════════════════════ --}}
            {{-- 7. ÉQUIPES                                                 --}}
            {{-- Admin + Superviseur Soutarah : Consultation des équipes    --}}
            {{-- ═══════════════════════════════════════════════════════════ --}}
            @if(in_array($role, ['admin', 'superviseur_soutarah']))
            <li class="nav-item {{ request()->routeIs('equipes.*') ? 'active' : '' }}">
                <a href="{{ route('equipes.index') }}"><i class="fa-solid fa-hard-hat"></i> Équipes</a>
            </li>
            @endif

            {{-- ═══════════════════════════════════════════════════════════ --}}
            {{-- 9. AFFECTATIONS                                            --}}
            {{-- Admin uniquement : assigner superviseurs Soutarah           --}}
            {{-- ═══════════════════════════════════════════════════════════ --}}
            @if($role === 'admin')
            <li class="nav-item {{ request()->routeIs('assignments.*') ? 'active' : '' }}">
                <a href="{{ route('assignments.index') }}"><i class="fa-solid fa-user-tie"></i> Affectations</a>
            </li>
            @endif

            {{-- ════════════════════════════════════════════════════════════ --}}
            {{-- 10. PLANNING                                                 --}}
            {{-- Admin + Superviseur Soutarah : planification complète      --}}
            {{-- Superviseur Client : LECTURE SEULE des opérations planifiées --}}
            {{-- Technicien : vue calendrier de SES interventions uniquement --}}
            {{-- ════════════════════════════════════════════════════════════ --}}
            @if(in_array($role, ['admin', 'superviseur_soutarah', 'superviseur_client', 'technicien', 'chef technicien', 'demandeur']))
            <li class="nav-item {{ request()->routeIs('planning.*') ? 'active' : '' }}">
                <a href="{{ route('planning.index') }}">
                    <i class="fa-solid fa-calendar-days"></i>
                    @if(in_array($role, ['technicien', 'chef technicien']))
                    Mon Planning
                    @elseif($role === 'demandeur')
                    Planning
                    @else
                    Planning
                    @endif
                </a>
            </li>
            @endif

            @if(in_array($role, ['admin', 'superviseur_soutarah']))
            <li class="nav-item {{ request()->routeIs('kpis.*') ? 'active' : '' }}">
                <a href="{{ route('kpis.equipes') }}">
                    <i class="fa-solid fa-gauge-high"></i> Performance Équipes
                </a>
            </li>
            @endif

            {{-- ═══════════════════════════════════════════════════════════════════ --}}
            {{-- 11. RAPPORTS & FICHES FROID                                         --}}
            {{-- Admin : rapports complets de tout le système                      --}}
            {{-- Superviseur Soutarah : rapports de SA base/client assigné(e)      --}}
            {{-- Superviseur Client : rapports de SA base uniquement               --}}
            {{-- Technicien : uniquement ses propres fiches froid (F-GAS)          --}}
            {{-- ═══════════════════════════════════════════════════════════════════ --}}
            @if(in_array($role, ['admin', 'superviseur_soutarah', 'superviseur_client', 'technicien', 'chef technicien']))
            <li class="nav-item {{ request()->routeIs('rapports.*') || request()->routeIs('fiches-froid.*') ? 'active' : '' }}">
                <a href="{{ route('rapports.index') }}"><i class="fa-solid fa-chart-line"></i> Statistiques</a>
            </li>
            @endif

            {{-- ═══════════════════════════════════════════════════════════════════ --}}
            {{-- 12. COMPTES-RENDUS D'INTERVENTIONS                                  --}}
            {{-- Admin + Superviseurs + Techniciens : accès aux CR                  --}}
            {{-- ═══════════════════════════════════════════════════════════════════ --}}
            @if(in_array($role, ['admin', 'superviseur', 'superviseur_soutarah', 'superviseur_client', 'technicien', 'chef technicien']))
            <li class="nav-item {{ request()->routeIs('comptes-rendus.*') ? 'active' : '' }}">
                <a href="{{ route('comptes-rendus.index') }}"><i class="fa-solid fa-file-lines"></i> Comptes-Rendus</a>
            </li>
            @endif

            {{-- ═══════════════════════════════════════════════════════════════════ --}}
            {{-- SÉPARATEUR                                                          --}}
            {{-- ═══════════════════════════════════════════════════════════════════ --}}
            <li style="margin: 0.75rem 0; border-top: 1px solid #e2e8f0;"></li>

            {{-- ═══════════════════════════════════════════════════════════════════ --}}
            {{-- 10. MON PROFIL                                                      --}}
            {{-- Tous les utilisateurs : gestion de leur profil personnel          --}}
            {{-- ═══════════════════════════════════════════════════════════════════ --}}
            <li class="nav-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                <a href="{{ route('profile.show') }}"><i class="fa-solid fa-user-circle"></i> Mon Profil</a>
            </li>

            {{-- ═══════════════════════════════════════════════════════════════════ --}}
            {{-- 11. PARAMÈTRES                                                      --}}
            {{-- Tous les utilisateurs : préférences et paramètres de l'app        --}}
            {{-- ═══════════════════════════════════════════════════════════════════ --}}
            <li class="nav-item {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                <a href="{{ route('settings.index') }}"><i class="fa-solid fa-gear"></i> Paramètres</a>
            </li>

            @endauth
        </ul>

        <!-- Bouton Installation PWA (caché si déjà installé) -->
        <div class="sidebar-pwa-install" id="sidebar-pwa-button" style="display: none;">
            <button class="btn-install-pwa pulse" onclick="installPWA()">
                <i class="fa-solid fa-mobile-screen-button"></i>
                <div class="btn-install-pwa-text">
                    <span class="btn-install-pwa-title">Installer l'App Mobile</span>
                    <span class="btn-install-pwa-subtitle">Accès rapide depuis votre écran</span>
                </div>
            </button>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Navbar Header avec Bouton Hamburger -->
        <header class="top-navbar">
            <div class="header-left">
                <button id="sidebarToggle" class="btn-hamburger" title="Réduire / Agrandir le menu">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>
            @auth
            <!-- Zone header droite avec notifications et profil -->
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <!-- Icône de notification (Tous les rôles sauf guest) -->
                @if(in_array(Auth::user()->type_utilisateur, ['admin', 'superviseur_client', 'superviseur_soutarah', 'demandeur', 'technicien', 'chef technicien']))
                <div class="notification-bell">
                    <button class="notification-bell-btn {{ $notificationsNonLues > 0 ? 'has-notifications' : '' }}" id="notificationTrigger" title="Notifications">
                        <i class="fa-solid fa-bell"></i>
                        @if($notificationsNonLues > 0)
                        <span class="notification-badge" id="notificationBadge">{{ $notificationsNonLues }}</span>
                        @endif
                    </button>

                    <div class="notification-dropdown" id="notificationDropdown">
                        <div class="notification-dropdown-header">
                            <h3><i class="fa-solid fa-bell"></i> Notifications</h3>
                            @if($notificationsNonLues > 0)
                            <a href="#" class="mark-all-read" onclick="markAllAsRead(event)">
                                Tout marquer lu
                            </a>
                            @endif
                        </div>

                        <div class="notification-list" id="notificationList">
                            @php
                            // Dropdown : Afficher UNIQUEMENT les notifications NON LUES (max 10)
                            $notifsDropdown = \App\Models\InterventionNotification::where('user_id', Auth::id())
                            ->where('statut', 'non_lu')
                            ->orderBy('created_at', 'desc')
                            ->limit(10)
                            ->get();
                            @endphp

                            @forelse($notifsDropdown as $notif)
                            <div class="notification-item unread"
                                data-id="{{ $notif->id }}"
                                data-demande-id="{{ $notif->demande_id }}"
                                data-intervention-id="{{ $notif->intervention_id }}"
                                data-maintenance-id="{{ $notif->maintenance_id }}"
                                onclick="markAsReadAndRedirect({{ $notif->id }}, {{ $notif->demande_id ?? 'null' }}, {{ $notif->intervention_id ?? 'null' }}, {{ $notif->maintenance_id ?? 'null' }})">
                                <div class="notification-item-title">{{ ucfirst(str_replace('_', ' ', $notif->type)) }}</div>
                                <div class="notification-item-message">{{ $notif->message }}</div>
                                <div class="notification-item-time">
                                    <i class="fa-solid fa-clock"></i> {{ $notif->created_at->diffForHumans() }}
                                </div>
                            </div>
                            @empty
                            <div class="notification-empty">
                                <i class="fa-solid fa-bell-slash"></i>
                                <p style="font-size: 0.95rem; font-weight: 600; margin-bottom: 0.25rem;">Aucune notification</p>
                                <p style="font-size: 0.8rem;">Vous êtes à jour !</p>
                            </div>
                            @endforelse
                        </div>

                        <div class="notification-footer">
                            <a href="{{ route('notifications.index') }}">Voir toutes les notifications</a>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Menu profil -->
                <div class="profile-menu">
                    <div class="profile-trigger" id="profileTrigger">
                        <div class="profile-avatar">{{ strtoupper(substr(Auth::user()->nom ?? 'U', 0, 1)) }}</div>
                        <div class="profile-info">
                            <div class="profile-name">{{ Auth::user()->nom_complet }}</div>
                            <div class="profile-role">{{ Auth::user()->type_utilisateur }}</div>
                        </div>
                        <i class="fa-solid fa-chevron-down" style="color: #94a3b8; font-size: 0.75rem;"></i>
                    </div>

                    <div class="profile-dropdown" id="profileDropdown">
                        <div class="profile-dropdown-header">
                            <h4>{{ Auth::user()->nom_complet }}</h4>
                            <p>{{ Auth::user()->email ?? 'Aucun email' }}</p>
                            <p style="font-size: 0.75rem; color: #94a3b8;">
                                <i class="fa-solid fa-circle" style="color: #10b981; font-size: 0.5rem;"></i> Connecté en tant que {{ Auth::user()->type_utilisateur }}
                            </p>
                        </div>

                        <a href="{{ route('dashboard') }}" class="profile-dropdown-item">
                            <i class="fa-solid fa-chart-pie"></i>
                            <span>Tableau de bord</span>
                        </a>

                        @if(Auth::user()->type_utilisateur === 'admin')
                        <a href="{{ route('utilisateurs.index') }}" class="profile-dropdown-item">
                            <i class="fa-solid fa-users-gear"></i>
                            <span>Gestion des utilisateurs</span>
                        </a>
                        @endif

                        <a href="{{ route('profile.show') }}" class="profile-dropdown-item">
                            <i class="fa-solid fa-user-circle"></i>
                            <span>Mon profil</span>
                        </a>

                        <a href="{{ route('settings.index') }}" class="profile-dropdown-item">
                            <i class="fa-solid fa-gear"></i>
                            <span>Paramètres</span>
                        </a>

                        <div class="profile-dropdown-divider"></div>

                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="profile-dropdown-item profile-dropdown-logout" style="width: 100%; border: none; cursor: pointer; background: transparent;">
                                <i class="fa-solid fa-right-from-bracket"></i>
                                <span>Déconnexion</span>
                            </button>
                        </form>
                    </div>
                </div>
                @endauth
        </header>

        <div class="content-wrapper">
            @if(session('success'))
            <div style="background-color: #ecfdf5; border: 1px solid #a7f3d0; color: #047857; padding: 1rem; border-radius: 0.75rem; margin-bottom: 1.75rem; font-weight: 600;">
                <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
            </div>
            @endif

            @if(session('error'))
            <div style="background-color: #fff1f2; border: 1px solid #fecdd3; color: #be123c; padding: 1rem; border-radius: 0.75rem; margin-bottom: 1.75rem; font-weight: 600;">
                <i class="fa-solid fa-triangle-exclamation"></i> {{ session('error') }}
            </div>
            @endif

            @yield('content')
        </div>
    </main>

    <!-- Script JS pour l'actionneur du Menu Hamburger -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const btnToggle = document.getElementById("sidebarToggle");
            const sidebar   = document.getElementById("mainSidebar");
            const overlay   = document.getElementById("sidebarOverlay");

            function isMobile() {
                return window.innerWidth <= 768;
            }

            // ── Ouvrir / fermer la sidebar ──────────────────────────────
            function openSidebar() {
                sidebar.classList.add("mobile-open");
                overlay.classList.add("visible");
                document.body.style.overflow = "hidden"; // bloquer le scroll
            }

            function closeSidebar() {
                sidebar.classList.remove("mobile-open");
                overlay.classList.remove("visible");
                document.body.style.overflow = "";
            }

            btnToggle.addEventListener("click", function() {
                if (isMobile()) {
                    // Mode mobile : drawer toggle
                    if (sidebar.classList.contains("mobile-open")) {
                        closeSidebar();
                    } else {
                        openSidebar();
                    }
                } else {
                    // Mode desktop : collapse classique
                    document.body.classList.toggle("sidebar-closed");

                    // Forcer le redimensionnement des graphiques Chart.js après la transition
                    setTimeout(function() {
                        if (typeof Chart !== 'undefined') {
                            Chart.helpers.each(Chart.instances, function(instance) {
                                instance.resize();
                            });
                        }
                    }, 350);
                }
            });

            // Fermer en cliquant sur l'overlay
            overlay.addEventListener("click", closeSidebar);

            // Fermer la sidebar mobile quand on clique sur un lien de nav
            sidebar.querySelectorAll(".nav-item a").forEach(function(link) {
                link.addEventListener("click", function() {
                    if (isMobile()) {
                        closeSidebar();
                    }
                });
            });

            // Réinitialiser au redimensionnement
            window.addEventListener("resize", function() {
                if (!isMobile()) {
                    closeSidebar();
                    document.body.style.overflow = "";
                }
            });

            // Menu profil déroulant
            const profileTrigger = document.getElementById('profileTrigger');
            const profileDropdown = document.getElementById('profileDropdown');

            if (profileTrigger && profileDropdown) {
                profileTrigger.addEventListener('click', function(e) {
                    e.stopPropagation();
                    profileDropdown.classList.toggle('active');
                });

                // Fermer le menu en cliquant ailleurs
                document.addEventListener('click', function(e) {
                    if (!profileTrigger.contains(e.target) && !profileDropdown.contains(e.target)) {
                        profileDropdown.classList.remove('active');
                    }
                });

                // Empêcher la fermeture en cliquant dans le dropdown
                profileDropdown.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            }

            // Menu notifications déroulant
            const notificationTrigger = document.getElementById('notificationTrigger');
            const notificationDropdown = document.getElementById('notificationDropdown');

            if (notificationTrigger && notificationDropdown) {
                notificationTrigger.addEventListener('click', function(e) {
                    e.stopPropagation();
                    notificationDropdown.classList.toggle('active');
                    // Fermer le menu profil si ouvert
                    if (profileDropdown) {
                        profileDropdown.classList.remove('active');
                    }
                });

                // Fermer le menu en cliquant ailleurs
                document.addEventListener('click', function(e) {
                    if (!notificationTrigger.contains(e.target) && !notificationDropdown.contains(e.target)) {
                        notificationDropdown.classList.remove('active');
                    }
                });

                // Empêcher la fermeture en cliquant dans le dropdown
                notificationDropdown.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            }
        });

        // Fonctions pour gérer les notifications
        function markAsReadAndRedirect(notificationId, demandeId, interventionId, maintenanceId) {
            // Rediriger directement vers la route qui marque comme lu et redirige
            // Le backend gérera la redirection correctement
            window.location.href = `{{ url('/') }}/notifications/${notificationId}/mark-as-read`;
        }

        function markAllAsRead(event) {
            event.preventDefault();
            fetch('{{ route('notifications.markAllAsRead') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                }).then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Vider la liste des notifications dans le dropdown (toutes sont lues maintenant)
                        const notificationList = document.getElementById('notificationList');
                        notificationList.innerHTML = `
                        <div class="notification-empty">
                            <i class="fa-solid fa-bell-slash"></i>
                            <p style="font-size: 0.95rem; font-weight: 600; margin-bottom: 0.25rem;">Aucune notification</p>
                            <p style="font-size: 0.8rem;">Vous êtes à jour !</p>
                        </div>
                    `;

                        // Mettre à jour le badge (le supprimer)
                        const badge = document.getElementById('notificationBadge');
                        if (badge) {
                            badge.remove();
                        }

                        // Retirer la classe has-notifications du bouton
                        const btn = document.getElementById('notificationTrigger');
                        if (btn) {
                            btn.classList.remove('has-notifications');
                        }

                        // Masquer le lien "Tout marquer lu"
                        const markAllReadLink = document.querySelector('.mark-all-read');
                        if (markAllReadLink) {
                            markAllReadLink.style.display = 'none';
                        }
                    }
                });
        }

        // ─── Gestion de la file d'attente des Popups de Notification ─────────
        let notificationQueue = [];
        let isPopupOpen = false;

        function queueNotifPopup(notif) {
            // Éviter les doublons dans la file d'attente
            if (!notificationQueue.some(n => n.id === notif.id)) {
                notificationQueue.push(notif);
            }
            processNotificationQueue();
        }

        function processNotificationQueue() {
            if (isPopupOpen || notificationQueue.length === 0) return;

            const notif = notificationQueue.shift();
            displayNotifModal(notif);
        }

        function displayNotifModal(notif) {
            const overlay = document.getElementById('notifPopupOverlay');
            if (!overlay) return;

            isPopupOpen = true;

            // Marquer comme affiché dans cette session pour cet utilisateur
            @auth
            const userId = '{{ Auth::id() }}';
            markPopupAsSeenInSession(userId, notif.id);
            @endauth

            // Jouer un bip discret
            playNotificationChime();

            const notifType = notif.type ? notif.type.replace(/_/g, ' ') : 'Notification';
            const typeLabel = notifType.charAt(0).toUpperCase() + notifType.slice(1);

            overlay.innerHTML = `
                <div class="notif-popup-card">
                    <button class="notif-popup-close-x" onclick="closeNotifPopup()"><i class="fa-solid fa-xmark"></i></button>
                    <div class="notif-popup-icon"><i class="fa-solid fa-bell"></i></div>
                    <div class="notif-popup-tag">${typeLabel}</div>
                    <div class="notif-popup-title">Nouvelle Notification</div>
                    <div class="notif-popup-message">${escapeHtmlNotif(notif.message)}</div>
                    <div class="notif-popup-actions">
                        <button class="notif-popup-btn-primary" onclick="closeNotifPopup(); markAsReadAndRedirect(${notif.id})">
                            <i class="fa-solid fa-arrow-right"></i> Voir
                        </button>
                        <button class="notif-popup-btn-close" onclick="closeNotifPopup()">
                            <i class="fa-solid fa-check"></i> Ignorer
                        </button>
                    </div>
                </div>
            `;

            overlay.classList.add('visible');

            // Fermer automatiquement après 8 secondes
            if (window._notifPopupTimer) clearTimeout(window._notifPopupTimer);
            window._notifPopupTimer = setTimeout(closeNotifPopup, 8000);
        }

        function closeNotifPopup() {
            const overlay = document.getElementById('notifPopupOverlay');
            if (overlay) overlay.classList.remove('visible');
            if (window._notifPopupTimer) clearTimeout(window._notifPopupTimer);
            isPopupOpen = false;

            // Afficher la notification suivante de la file après une courte transition
            setTimeout(processNotificationQueue, 350);
        }

        function playNotificationChime() {
            try {
                const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(587.33, audioCtx.currentTime); // Ré5
                osc.frequency.exponentialRampToValueAtTime(880, audioCtx.currentTime + 0.12); // La5
                gain.gain.setValueAtTime(0.12, audioCtx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.35);
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.start();
                osc.stop(audioCtx.currentTime + 0.35);
            } catch(e) {}
        }

        function getSeenPopupIds(userId) {
            try {
                const stored = sessionStorage.getItem('mainteo_seen_popups_' + userId);
                return stored ? JSON.parse(stored) : [];
            } catch(e) {
                return [];
            }
        }

        function markPopupAsSeenInSession(userId, notifId) {
            try {
                const seen = getSeenPopupIds(userId);
                if (!seen.includes(notifId)) {
                    seen.push(notifId);
                    sessionStorage.setItem('mainteo_seen_popups_' + userId, JSON.stringify(seen));
                }
            } catch(e) {}
        }

        function escapeHtmlNotif(text) {
            const div = document.createElement('div');
            div.textContent = text || '';
            return div.innerHTML;
        }

        // Fermer le popup en cliquant sur l'overlay
        document.addEventListener('DOMContentLoaded', function() {
            const overlay = document.getElementById('notifPopupOverlay');
            if (overlay) {
                overlay.addEventListener('click', function(e) {
                    if (e.target === overlay) closeNotifPopup();
                });
            }
        });

        // ─── Polling des nouvelles notifications (toutes les 5 secondes) ───────
        @auth
        const currentUserId = '{{ Auth::id() }}';

        function checkNewNotifications() {
            fetch(`{{ url('/') }}/notifications/check-new`)
                .then(response => response.json())
                .then(data => {
                    // Mettre à jour le badge de la cloche
                    updateNotificationBadge(data.count);

                    if (data.notifications && data.notifications.length > 0) {
                        const seenIds = getSeenPopupIds(currentUserId);

                        data.notifications.forEach(notif => {
                            // 1. Ajouter à la liste du menu déroulant si pas déjà présent
                            addNotificationToList(notif);

                            // 2. Si pas encore affiché en popup dans cette session, l'ajouter à la file
                            if (!seenIds.includes(notif.id)) {
                                queueNotifPopup(notif);
                                showBrowserNotification('MAINT&O - Nouvelle notification', notif.message);
                            }
                        });
                    }
                })
                .catch(error => console.error('Erreur notifications:', error));
        }

        function updateNotificationBadge(count) {
            const btn = document.getElementById('notificationTrigger');
            let badge = document.getElementById('notificationBadge');

            if (count > 0) {
                if (badge) {
                    badge.textContent = count;
                } else if (btn) {
                    badge = document.createElement('span');
                    badge.id = 'notificationBadge';
                    badge.className = 'notification-badge';
                    badge.textContent = count;
                    btn.appendChild(badge);
                }
                if (btn) btn.classList.add('has-notifications');
            } else {
                if (badge) {
                    badge.remove();
                }
                if (btn) btn.classList.remove('has-notifications');
            }
        }

        function addNotificationToList(notif) {
            const notificationList = document.getElementById('notificationList');
            if (!notificationList) return;

            // Supprimer le message "Aucune notification" s'il existe
            const emptyMessage = notificationList.querySelector('.notification-empty');
            if (emptyMessage) {
                emptyMessage.remove();
            }

            // Éviter les doublons dans la liste HTML du menu
            if (notificationList.querySelector(`[data-id="${notif.id}"]`)) return;

            // Créer le nouvel élément de notification
            const notifType = notif.type ? notif.type.replace(/_/g, ' ') : 'Notification';
            const newNotifHtml = `
                <div class="notification-item unread" 
                     data-id="${notif.id}"
                     onclick="markAsReadAndRedirect(${notif.id})">
                    <div class="notification-item-title">${notifType.charAt(0).toUpperCase() + notifType.slice(1)}</div>
                    <div class="notification-item-message">${escapeHtmlNotif(notif.message)}</div>
                    <div class="notification-item-time">
                        <i class="fa-solid fa-clock"></i> ${notif.created_at}
                    </div>
                </div>
            `;

            // Insérer au début de la liste
            notificationList.insertAdjacentHTML('afterbegin', newNotifHtml);

            // Limiter à 10 notifications affichées
            const items = notificationList.querySelectorAll('.notification-item');
            if (items.length > 10) {
                items[items.length - 1].remove();
            }
        }

        function showBrowserNotification(title, message) {
            if (!('Notification' in window)) return;

            if (Notification.permission === 'granted') {
                try {
                    const notification = new Notification(title, {
                        body: message,
                        icon: '/favicon.ico',
                        tag: 'mainteo-notification-' + Date.now(),
                        requireInteraction: false
                    });
                    setTimeout(() => notification.close(), 6000);
                } catch (e) {
                    console.error('Erreur notification navigateur:', e);
                }
            } else if (Notification.permission === 'default') {
                Notification.requestPermission();
            }
        }

        // Demander la permission notification navigateur au 1er clic sur la page
        document.addEventListener('click', function requestNotifPermOnce() {
            if ('Notification' in window && Notification.permission === 'default') {
                Notification.requestPermission();
            }
            document.removeEventListener('click', requestNotifPermOnce);
        }, { once: true });

        // Démarrer le polling toutes les 5 secondes
        setInterval(checkNewNotifications, 5000);

        // Vérifier immédiatement au chargement de la page
        document.addEventListener('DOMContentLoaded', checkNewNotifications);
        checkNewNotifications();
        @endauth
    </script>

    <!-- ═══════════════════════════════════════════════════════════════
         POPUP MODAL NOTIFICATION CENTRÉ (POUR TOUS LES UTILISATEURS)
         ═══════════════════════════════════════════════════════════════ -->
    @auth
    <div class="notif-popup-overlay" id="notifPopupOverlay"></div>
    @endauth

    <!-- PWA Installation Script (Inline) -->
    <script>
    /**
     * MAINTEO PWA - Installation Button
     */
    let deferredPrompt;
    let installButton;

    window.addEventListener('beforeinstallprompt', (e) => {
        console.log('[PWA] Installation disponible');
        e.preventDefault();
        deferredPrompt = e;
        showInstallButton();
    });

    function showInstallButton() {
        if (document.getElementById('pwa-install-btn')) return;
        installButton = document.createElement('div');
        installButton.id = 'pwa-install-btn';
        installButton.innerHTML = '<div class="pwa-install-card"><button class="pwa-install-button" onclick="installPWA()"><i class="fa-solid fa-mobile-screen-button"></i><div class="pwa-install-text"><div class="pwa-install-title">Installer l\'Application Mobile</div><div class="pwa-install-subtitle">Accès rapide depuis votre écran d\'accueil</div></div></button><button class="pwa-install-close" onclick="closeInstallButton()" aria-label="Fermer"><i class="fa-solid fa-times"></i></button></div>';
        document.body.appendChild(installButton);
        setTimeout(() => { installButton.classList.add('pwa-install-visible'); }, 500);
    }

    window.installPWA = async function() {
        console.log('[PWA] Fonction installPWA() appelée');
        console.log('[PWA] deferredPrompt disponible:', !!deferredPrompt);
        
        // Détecter le navigateur
        const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
        const isAndroid = /Android/.test(navigator.userAgent);
        const isChrome = /Chrome/.test(navigator.userAgent) && /Google Inc/.test(navigator.vendor);
        const isSafari = /Safari/.test(navigator.userAgent) && /Apple Computer/.test(navigator.vendor);
        
        // Si deferredPrompt disponible → Installation automatique (Chrome, Edge, etc.)
        if (deferredPrompt) {
            try {
                console.log('[PWA] Affichage du prompt natif...');
                deferredPrompt.prompt();
                const { outcome } = await deferredPrompt.userChoice;
                console.log('[PWA] Réponse utilisateur: ' + outcome);
                if (outcome === 'accepted') {
                    console.log('[PWA] Installation acceptée');
                    closeInstallButton();
                    showNotification('✅ Application installée avec succès !', 'success');
                } else {
                    console.log('[PWA] Installation refusée');
                }
                deferredPrompt = null;
            } catch (error) {
                console.error('[PWA] Erreur lors de l\'installation:', error);
            }
            return;
        }
        
        // iOS Safari → Instructions spécifiques
        if (isIOS && isSafari) {
            const instructions = document.createElement('div');
            instructions.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.9);z-index:99999;display:flex;align-items:center;justify-content:center;padding:20px;';
            instructions.innerHTML = '<div style="background:#fff;border-radius:16px;padding:30px;max-width:400px;text-align:center;"><div style="font-size:48px;margin-bottom:20px;">📱</div><h2 style="margin:0 0 20px;font-size:22px;color:#0f172a;">Installer Mainteo</h2><p style="margin:0 0 25px;color:#64748b;line-height:1.6;">1. Appuyez sur <strong style="color:#059669;">Partager</strong> <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" style="vertical-align:middle;"><path d="M4 12v8a2 2 0 002 2h12a2 2 0 002-2v-8M16 6l-4-4-4 4M12 2v13"/></svg><br>2. Choisir <strong style="color:#059669;">Sur l\'écran d\'accueil</strong><br>3. Appuyez sur <strong style="color:#059669;">Ajouter</strong></p><button onclick="this.parentElement.parentElement.remove()" style="background:linear-gradient(135deg,#059669,#10b981);color:#fff;border:none;padding:12px 30px;border-radius:10px;font-size:16px;font-weight:700;cursor:pointer;width:100%;">J\'ai compris</button></div>';
            document.body.appendChild(instructions);
            return;
        }
        
        // Android Chrome → Instructions si pas de prompt
        if (isAndroid && isChrome) {
            const instructions = document.createElement('div');
            instructions.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.9);z-index:99999;display:flex;align-items:center;justify-content:center;padding:20px;';
            instructions.innerHTML = '<div style="background:#fff;border-radius:16px;padding:30px;max-width:400px;text-align:center;"><div style="font-size:48px;margin-bottom:20px;">📱</div><h2 style="margin:0 0 20px;font-size:22px;color:#0f172a;">Installer Mainteo</h2><p style="margin:0 0 25px;color:#64748b;line-height:1.6;">1. Appuyez sur <strong style="color:#059669;">Menu</strong> (⋮)<br>2. Choisir <strong style="color:#059669;">Installer l\'application</strong><br>3. Confirmez l\'installation</p><button onclick="this.parentElement.parentElement.remove()" style="background:linear-gradient(135deg,#059669,#10b981);color:#fff;border:none;padding:12px 30px;border-radius:10px;font-size:16px;font-weight:700;cursor:pointer;width:100%;">J\'ai compris</button></div>';
            document.body.appendChild(instructions);
            return;
        }
        
        // Autres navigateurs → Instructions génériques
        const instructions = document.createElement('div');
        instructions.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.9);z-index:99999;display:flex;align-items:center;justify-content:center;padding:20px;';
        instructions.innerHTML = '<div style="background:#fff;border-radius:16px;padding:30px;max-width:400px;text-align:center;"><div style="font-size:48px;margin-bottom:20px;">📱</div><h2 style="margin:0 0 20px;font-size:22px;color:#0f172a;">Installer Mainteo</h2><p style="margin:0 0 25px;color:#64748b;line-height:1.6;">Ouvrez le menu de votre navigateur et recherchez l\'option <strong style="color:#059669;">Installer l\'application</strong> ou <strong style="color:#059669;">Ajouter à l\'écran d\'accueil</strong>.</p><button onclick="this.parentElement.parentElement.remove()" style="background:linear-gradient(135deg,#059669,#10b981);color:#fff;border:none;padding:12px 30px;border-radius:10px;font-size:16px;font-weight:700;cursor:pointer;width:100%;">J\'ai compris</button></div>';
        document.body.appendChild(instructions);
    };

    window.closeInstallButton = function() {
        const btn = document.getElementById('pwa-install-btn');
        if (btn) {
            btn.classList.remove('pwa-install-visible');
            setTimeout(() => { btn.remove(); }, 300);
        }
        localStorage.setItem('pwa-install-dismissed', Date.now());
    };



    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = 'pwa-notification pwa-notification-' + type;
        notification.textContent = message;
        document.body.appendChild(notification);
        setTimeout(() => { notification.classList.add('pwa-notification-visible'); }, 100);
        setTimeout(() => { notification.classList.remove('pwa-notification-visible'); setTimeout(() => notification.remove(), 300); }, 3000);
    }

    window.addEventListener('DOMContentLoaded', () => {
        // Enregistrer le Service Worker immédiatement
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js')
                .then(registration => {
                    console.log('[PWA] Service Worker enregistré:', registration.scope);
                })
                .catch(error => {
                    console.error('[PWA] Erreur Service Worker:', error);
                });
        }

        // Afficher/cacher le bouton dans la sidebar selon si l'app est installée
        const sidebarPwaButton = document.getElementById('sidebar-pwa-button');
        if (sidebarPwaButton) {
            if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone) {
                console.log('[PWA] Application déjà installée - bouton sidebar caché');
                sidebarPwaButton.style.display = 'none';
            } else {
                console.log('[PWA] Application non installée - bouton sidebar visible');
                sidebarPwaButton.style.display = 'block';
            }
        }

        if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone) {
            console.log('[PWA] Application déjà installée');
            return;
        }
        const dismissed = localStorage.getItem('pwa-install-dismissed');
        if (dismissed) {
            const daysSince = (Date.now() - parseInt(dismissed)) / (1000 * 60 * 60 * 24);
            if (daysSince < 7) {
                console.log('[PWA] Bouton masqué (fermé il y a moins de 7 jours)');
                return;
            }
        }
    });

    window.addEventListener('appinstalled', () => {
        console.log('[PWA] Application installée avec succès');
        showNotification('✅ Application installée ! Vous pouvez maintenant y accéder depuis votre écran d\'accueil.', 'success');
        closeInstallButton();
        // Cacher le bouton sidebar après installation
        const sidebarPwaButton = document.getElementById('sidebar-pwa-button');
        if (sidebarPwaButton) sidebarPwaButton.style.display = 'none';
    });

    console.log('[PWA] Script d\'installation chargé');
    </script>

    <!-- ═══════════════════════════════════════════════════════════════
         SELECT2 - Initialisation automatique
    ══════════════════════════════════════════════════════════════════ -->
    <script>
        $(document).ready(function() {
            // Activer Select2 sur tous les selects d'équipements avec dropdown scanné au parent
            $('select[name="equipement_id"], #equipement_select, #equipement_id, #equipement_id_direct').each(function() {
                var $el = $(this);
                $el.select2({
                    width: '100%',
                    dropdownParent: $el.parent(),
                    placeholder: "🔍 Rechercher un équipement...",
                    allowClear: true,
                    language: {
                        noResults: function() {
                            return "Aucun équipement trouvé";
                        },
                        searching: function() {
                            return "Recherche en cours...";
                        }
                    }
                });
            });
        });
    </script>

    <!-- ═══════════════════════════════════════════════════════════════
         ASSISTANT CONVERSATIONNEL FLOTTANT MAINTEO IA
    ══════════════════════════════════════════════════════════════════ -->
    @include('partials.mainteo_ai_widget')

</body>


</html>