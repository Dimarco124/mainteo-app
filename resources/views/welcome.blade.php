<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mainteo :: Plateforme de Gestion de Maintenance (GMAO & Interventions)</title>
    <link rel="icon" type="image/x-icon" href="/images/favicon.ico">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#059669">
    <link rel="apple-touch-icon" href="{{ asset('images/icons/icon-192x192.png') }}">
    
    <!-- Google Fonts: Plus Jakarta Sans & Varela Round -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Varela+Round&display=swap" rel="stylesheet">
    
    <!-- FontAwesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- PWA Installation Styles -->
    <link rel="stylesheet" href="{{ asset('css/pwa-install.css') }}">

    <style>
        :root {
            --primary: #059669;
            --primary-hover: #047857;
            --primary-light: #10b981;
            --primary-soft: #ecfdf5;
            --primary-border: #a7f3d0;
            --secondary: #0284c7;
            --secondary-soft: #f0f9ff;
            --accent: #f59e0b;
            --dark: #0f172a;
            --dark-muted: #334155;
            --light-bg: #f8fafc;
            --card-bg: #ffffff;
            --border-color: #e2e8f0;
            --shadow-sm: 0 2px 8px rgba(15, 23, 42, 0.04);
            --shadow-md: 0 10px 30px rgba(15, 23, 42, 0.08);
            --shadow-lg: 0 20px 45px rgba(5, 150, 105, 0.12);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', 'Varela Round', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            background-color: #ffffff;
            color: var(--dark);
            overflow-x: hidden;
            line-height: 1.6;
        }

        /* ── NAVBAR ── */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
            transition: all 0.3s ease;
        }

        .navbar.scrolled {
            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.06);
            background: rgba(255, 255, 255, 0.98);
        }

        .nav-container {
            max-width: 1320px;
            margin: 0 auto;
            padding: 0.85rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            text-decoration: none;
        }

        .brand-logo {
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary-soft);
            border-radius: 12px;
            border: 1px solid var(--primary-border);
            padding: 4px;
        }

        .brand-logo img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .brand-text h1 {
            font-size: 1.35rem;
            font-weight: 800;
            color: var(--dark);
            letter-spacing: -0.5px;
            line-height: 1.1;
        }

        .brand-text p {
            font-size: 0.72rem;
            color: #64748b;
            font-weight: 600;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 2rem;
            list-style: none;
        }

        .nav-links a {
            text-decoration: none;
            color: #475569;
            font-size: 0.92rem;
            font-weight: 600;
            transition: color 0.2s ease;
        }

        .nav-links a:hover {
            color: var(--primary);
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .btn-login {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.7rem 1.4rem;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: #ffffff;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.92rem;
            box-shadow: 0 4px 14px rgba(5, 150, 105, 0.25);
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 22px rgba(5, 150, 105, 0.35);
        }

        /* ── HERO SECTION ── */
        .hero {
            padding: 8.5rem 1.5rem 5rem;
            background: radial-gradient(circle at 10% 20%, rgba(236, 253, 245, 0.8) 0%, rgba(255, 255, 255, 1) 60%);
            position: relative;
            overflow: hidden;
        }

        .hero-container {
            max-width: 1320px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 3.5rem;
            align-items: center;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 1rem;
            background: var(--primary-soft);
            border: 1px solid var(--primary-border);
            border-radius: 9999px;
            color: var(--primary);
            font-size: 0.82rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }

        .hero-title {
            font-size: 3.2rem;
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -1px;
            color: var(--dark);
            margin-bottom: 1.25rem;
        }

        .hero-title .gradient-text {
            background: linear-gradient(135deg, var(--primary), #0284c7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-desc {
            font-size: 1.15rem;
            color: #475569;
            line-height: 1.7;
            margin-bottom: 2.25rem;
            max-width: 620px;
        }

        .hero-buttons {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 2.5rem;
        }

        .btn-hero-primary {
            display: inline-flex;
            align-items: center;
            gap: 0.65rem;
            padding: 0.95rem 2rem;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: #ffffff;
            text-decoration: none;
            border-radius: 14px;
            font-weight: 700;
            font-size: 1.02rem;
            box-shadow: var(--shadow-lg);
            transition: all 0.3s ease;
        }

        .btn-hero-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 28px rgba(5, 150, 105, 0.35);
        }

        .btn-hero-secondary {
            display: inline-flex;
            align-items: center;
            gap: 0.65rem;
            padding: 0.95rem 1.75rem;
            background: #ffffff;
            color: var(--dark);
            text-decoration: none;
            border-radius: 14px;
            font-weight: 700;
            font-size: 1.02rem;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
            transition: all 0.3s ease;
        }

        .btn-hero-secondary:hover {
            background: var(--light-bg);
            border-color: #cbd5e1;
            transform: translateY(-2px);
        }

        .hero-trust {
            display: flex;
            align-items: center;
            gap: 1.75rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e2e8f0;
            flex-wrap: wrap;
        }

        .trust-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            font-weight: 600;
            color: #64748b;
        }

        .trust-item i {
            color: var(--primary);
            font-size: 1rem;
        }

        /* Hero Image Container with floating cards */
        .hero-visual-wrapper {
            position: relative;
        }

        .hero-main-card {
            position: relative;
            background: #ffffff;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.18);
            border: 1px solid rgba(226, 232, 240, 0.8);
        }

        .hero-main-card img {
            width: 100%;
            height: 440px;
            object-fit: cover;
            display: block;
        }

        .hero-card-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, transparent 40%, rgba(15, 23, 42, 0.7) 100%);
            display: flex;
            align-items: flex-end;
            padding: 1.75rem;
            color: #ffffff;
        }

        .overlay-badge {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(8px);
            padding: 0.5rem 1rem;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            font-size: 0.85rem;
            font-weight: 700;
        }

        /* Floating Badge Cards */
        .floating-card-1 {
            position: absolute;
            top: -20px;
            right: -20px;
            background: #ffffff;
            padding: 1rem 1.25rem;
            border-radius: 18px;
            box-shadow: 0 15px 35px rgba(15, 23, 42, 0.12);
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 0.85rem;
            z-index: 10;
            animation: float 4s ease-in-out infinite;
        }

        .floating-card-2 {
            position: absolute;
            bottom: -20px;
            left: -20px;
            background: #ffffff;
            padding: 1rem 1.25rem;
            border-radius: 18px;
            box-shadow: 0 15px 35px rgba(15, 23, 42, 0.12);
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 0.85rem;
            z-index: 10;
            animation: float 4s ease-in-out infinite 2s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }

        .floating-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        /* ── STATS BANNER ── */
        .stats-banner {
            padding: 3.5rem 1.5rem;
            background: #ffffff;
            border-bottom: 1px solid var(--border-color);
        }

        .stats-grid {
            max-width: 1320px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.75rem;
        }

        .stat-box {
            background: var(--light-bg);
            border: 1px solid var(--border-color);
            border-radius: 18px;
            padding: 1.75rem 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
        }

        .stat-box:hover {
            transform: translateY(-4px);
            background: #ffffff;
            box-shadow: var(--shadow-md);
            border-color: var(--primary-border);
        }

        .stat-num {
            font-size: 2.3rem;
            font-weight: 800;
            color: var(--primary);
            line-height: 1.1;
            margin-bottom: 0.35rem;
        }

        .stat-desc {
            font-size: 0.9rem;
            color: #64748b;
            font-weight: 600;
        }

        /* ── SECTION HEADER ── */
        .section-header {
            text-align: center;
            max-width: 760px;
            margin: 0 auto 3.5rem;
        }

        .section-tag {
            display: inline-block;
            font-size: 0.8rem;
            font-weight: 800;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 1.2px;
            margin-bottom: 0.65rem;
        }

        .section-title {
            font-size: 2.3rem;
            font-weight: 800;
            color: var(--dark);
            line-height: 1.25;
            letter-spacing: -0.5px;
            margin-bottom: 0.85rem;
        }

        .section-subtitle {
            font-size: 1.05rem;
            color: #64748b;
        }

        /* ── FEATURES SECTION ── */
        .features-section {
            padding: 5.5rem 1.5rem;
            background: var(--light-bg);
        }

        .features-grid {
            max-width: 1320px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.75rem;
        }

        .feature-card {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 2.25rem 1.75rem;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .feature-card:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-md);
            border-color: var(--primary-border);
        }

        .feature-icon-wrapper {
            width: 54px;
            height: 54px;
            border-radius: 14px;
            background: var(--primary-soft);
            border: 1px solid var(--primary-border);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .feature-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.75rem;
        }

        .feature-desc {
            font-size: 0.95rem;
            color: #64748b;
            line-height: 1.65;
        }

        /* ── ROLES SHOWCASE SECTION ── */
        .roles-section {
            padding: 5.5rem 1.5rem;
            background: #ffffff;
        }

        .roles-grid {
            max-width: 1320px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
        }

        .role-card {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 1.75rem 1.5rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .role-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .role-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
            border-color: rgba(5, 150, 105, 0.3);
        }

        .role-card:hover::before {
            opacity: 1;
        }

        .role-badge-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            margin-bottom: 1.25rem;
        }

        .role-name {
            font-size: 1.15rem;
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }

        .role-target {
            font-size: 0.78rem;
            font-weight: 700;
            color: var(--primary);
            text-transform: uppercase;
            margin-bottom: 0.85rem;
            letter-spacing: 0.5px;
        }

        .role-desc {
            font-size: 0.88rem;
            color: #64748b;
            line-height: 1.6;
        }

        /* ── WORKFLOW SECTION WITH IMAGES ── */
        .workflow-section {
            padding: 5.5rem 1.5rem;
            background: var(--light-bg);
        }

        .workflow-container {
            max-width: 1320px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
        }

        .workflow-image-container {
            position: relative;
        }

        .workflow-img {
            width: 100%;
            height: 460px;
            object-fit: cover;
            border-radius: 24px;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
        }

        .workflow-steps {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .workflow-step {
            display: flex;
            gap: 1.25rem;
            background: #ffffff;
            padding: 1.25rem 1.5rem;
            border-radius: 16px;
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }

        .workflow-step:hover {
            border-color: var(--primary-border);
            box-shadow: var(--shadow-sm);
            transform: translateX(4px);
        }

        .step-num {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: #ffffff;
            font-weight: 800;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .step-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.25rem;
        }

        .step-text {
            font-size: 0.88rem;
            color: #64748b;
        }

        /* ── CALL TO ACTION ── */
        .cta-section {
            padding: 5rem 1.5rem;
            background: linear-gradient(135deg, #059669 0%, #047857 50%, #0f172a 100%);
            color: #ffffff;
            position: relative;
            overflow: hidden;
        }

        .cta-container {
            max-width: 900px;
            margin: 0 auto;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .cta-title {
            font-size: 2.6rem;
            font-weight: 800;
            margin-bottom: 1rem;
            letter-spacing: -0.5px;
        }

        .cta-desc {
            font-size: 1.15rem;
            margin-bottom: 2.25rem;
            opacity: 0.9;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }

        .btn-cta {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1.05rem 2.25rem;
            background: #ffffff;
            color: var(--primary);
            text-decoration: none;
            border-radius: 14px;
            font-weight: 800;
            font-size: 1.05rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        .btn-cta:hover {
            transform: translateY(-3px);
            box-shadow: 0 14px 35px rgba(0, 0, 0, 0.3);
            background: #f8fafc;
        }

        /* ── FOOTER ── */
        .footer {
            padding: 3rem 1.5rem;
            background: #0f172a;
            color: #94a3b8;
            border-top: 1px solid #1e293b;
        }

        .footer-container {
            max-width: 1320px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1.5rem;
        }

        .footer-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: #ffffff;
            font-weight: 700;
        }

        .footer-brand img {
            height: 36px;
        }

        .footer-copy {
            font-size: 0.88rem;
        }

        .footer-badges {
            display: flex;
            gap: 0.75rem;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .footer-badge-item {
            background: #1e293b;
            padding: 0.35rem 0.75rem;
            border-radius: 6px;
            color: #cbd5e1;
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 1024px) {
            .hero-container,
            .workflow-container {
                grid-template-columns: 1fr;
                gap: 3rem;
            }

            .hero-title {
                font-size: 2.6rem;
            }

            .features-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .roles-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .floating-card-1,
            .floating-card-2 {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }

            .hero {
                padding: 7rem 1.25rem 3.5rem;
            }

            .hero-title {
                font-size: 2.1rem;
            }

            .section-title {
                font-size: 1.8rem;
            }

            .features-grid,
            .roles-grid,
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .hero-buttons {
                flex-direction: column;
            }

            .btn-hero-primary,
            .btn-hero-secondary {
                width: 100%;
                justify-content: center;
            }

            .footer-container {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body>

    <!-- NAVBAR -->
    <nav class="navbar" id="navbar">
        <div class="nav-container">
            <a href="/" class="brand">
                <div class="brand-logo">
                    <img src="{{ asset('logo/logo_soutarah.png') }}" alt="Mainteo Logo">
                </div>
                <div class="brand-text">
                    <h1>Mainteo</h1>
                    <p>GMAO & Maintenance</p>
                </div>
            </a>

            <ul class="nav-links">
                <li><a href="#features">Fonctionnalités</a></li>
                <li><a href="#roles">Espaces Dédiés</a></li>
                <li><a href="#workflow">Processus</a></li>
                <li><a href="#stats">Indicateurs</a></li>
            </ul>

            <div class="nav-actions">
                <a href="{{ route('login') }}" class="btn-login" title="Se connecter">
                    <i class="fa-solid fa-right-to-bracket"></i>
                    <span>Connexion</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- HERO SECTION -->
    <section class="hero">
        <div class="hero-container">
            <div>
                <div class="hero-badge">
                    <i class="fa-solid fa-bolt"></i>
                    <span>Plateforme GMAO Nouvelle Génération</span>
                </div>
                
                <h2 class="hero-title">
                    Pilotez votre maintenance &amp; interventions <span class="gradient-text">avec précision</span>
                </h2>

                <p class="hero-desc">
                    Mainteo centralise la gestion de vos équipements, la planification des dépannages 
                    et le suivi des fiches F-GAS en temps réel avec une interface claire pour tous vos collaborateurs.
                </p>

                <div class="hero-buttons">
                    <a href="{{ route('login') }}" class="btn-hero-primary">
                        <i class="fa-solid fa-shield-check"></i>
                        Accéder à l'Espace Pro
                    </a>
                    <a href="#features" class="btn-hero-secondary">
                        <i class="fa-solid fa-compass"></i>
                        Découvrir les Fonctionnalités
                    </a>
                </div>

                <div class="hero-trust">
                    <div class="trust-item">
                        <i class="fa-solid fa-circle-check"></i>
                        <span>Conforme F-GAS</span>
                    </div>
                    <div class="trust-item">
                        <i class="fa-solid fa-circle-check"></i>
                        <span>Multi-Bases &amp; Sites</span>
                    </div>
                    <div class="trust-item">
                        <i class="fa-solid fa-circle-check"></i>
                        <span>Suivi Mobile Terrain</span>
                    </div>
                </div>
            </div>

            <!-- HERO VISUAL WITH FLOATING BADGES -->
            <div class="hero-visual-wrapper">
                <div class="hero-main-card">
                    <img src="{{ asset('images/hero_technicien_noir.jpg') }}" 
                         alt="Technicien qualifié Soutarah en intervention GMAO">
                    <div class="hero-card-overlay">
                        <div>
                            <div class="overlay-badge" style="margin-bottom: 0.5rem;">
                                <i class="fa-solid fa-wifi" style="color: #10b981;"></i> En direct du terrain
                            </div>
                            <h3 style="font-size: 1.25rem; font-weight: 800;">Intervention Clôturée avec Photos &amp; Signatures</h3>
                        </div>
                    </div>
                </div>

                <!-- Floating badge 1 -->
                <div class="floating-card-1">
                    <div class="floating-icon" style="background: #ecfdf5; color: #059669;">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                    <div>
                        <div style="font-size: 0.75rem; color: #64748b; font-weight: 700;">RÉSOLUTIONS</div>
                        <div style="font-size: 1.05rem; font-weight: 800; color: #0f172a;">-40% temps d'attente</div>
                    </div>
                </div>

                <!-- Floating badge 2 -->
                <div class="floating-card-2">
                    <div class="floating-icon" style="background: #f0f9ff; color: #0284c7;">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <div>
                        <div style="font-size: 0.75rem; color: #64748b; font-weight: 700;">TRAÇABILITÉ</div>
                        <div style="font-size: 1.05rem; font-weight: 800; color: #0f172a;">100% Conforme F-GAS</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- STATS BANNER -->
    <section id="stats" class="stats-banner">
        <div class="stats-grid">
            <div class="stat-box">
                <div class="stat-num">99.8%</div>
                <div class="stat-desc">Disponibilité des Équipements</div>
            </div>
            <div class="stat-box">
                <div class="stat-num">100%</div>
                <div class="stat-desc">Traçabilité des Interventions</div>
            </div>
            <div class="stat-box">
                <div class="stat-num">4 Rôles</div>
                <div class="stat-desc">Permissions Strictement Ciblées</div>
            </div>
            <div class="stat-box">
                <div class="stat-num">24/7</div>
                <div class="stat-desc">Visibilité Temps Réel</div>
            </div>
        </div>
    </section>

    <!-- PWA INSTALLATION HERO SECTION -->
    <section class="pwa-hero-section">
        <div class="pwa-hero-container">
            <div class="pwa-hero-content">
                <div class="pwa-hero-badge">
                    <i class="fa-solid fa-mobile-screen-button"></i>
                    <span>Application Mobile Progressive (PWA)</span>
                </div>
                
                <h2 class="pwa-hero-title">
                    Installez <span class="gradient-text">Mainteo Mobile</span> sur votre appareil
                </h2>

                <p class="pwa-hero-description">
                    Profitez d'une expérience native complète : accès hors-ligne, notifications push, 
                    icône sur l'écran d'accueil et performances optimales pour vos interventions terrain.
                </p>

                <div class="pwa-hero-features">
                    <div class="pwa-hero-feature">
                        <i class="fa-solid fa-circle-check"></i>
                        <span><strong>Mode Hors-ligne</strong> — Consultez vos données même sans connexion</span>
                    </div>
                    <div class="pwa-hero-feature">
                        <i class="fa-solid fa-circle-check"></i>
                        <span><strong>Notifications Instantanées</strong> — Restez informé des nouvelles interventions</span>
                    </div>
                    <div class="pwa-hero-feature">
                        <i class="fa-solid fa-circle-check"></i>
                        <span><strong>Accès Ultra-Rapide</strong> — Lancez l'app depuis votre écran d'accueil</span>
                    </div>
                    <div class="pwa-hero-feature">
                        <i class="fa-solid fa-circle-check"></i>
                        <span><strong>Mise à Jour Automatique</strong> — Toujours la dernière version disponible</span>
                    </div>
                </div>

                <div class="pwa-hero-buttons">
                    <button class="btn-install-hero" onclick="installPWA()">
                        <i class="fa-solid fa-download"></i>
                        Installer l'Application Maintenant
                    </button>
                    <a href="#features" class="btn-learn-more">
                        <i class="fa-solid fa-circle-info"></i>
                        En Savoir Plus
                    </a>
                </div>
            </div>

            <div class="pwa-hero-visual">
                <div class="pwa-phone-mockup">
                    <div class="pwa-phone-frame">
                        <div class="pwa-phone-notch"></div>
                        <div class="pwa-phone-screen">
                            <img src="{{ asset('images/technicien_mobile_noir.jpg') }}" 
                                 alt="Interface Mobile Mainteo">
                        </div>
                    </div>
                </div>

                <!-- Badges flottants -->
                <div class="pwa-floating-badge">
                    <i class="fa-solid fa-wifi"></i>
                    <span class="pwa-floating-badge-text">Mode Offline</span>
                </div>
                <div class="pwa-floating-badge">
                    <i class="fa-solid fa-bolt"></i>
                    <span class="pwa-floating-badge-text">Ultra Rapide</span>
                </div>
            </div>
        </div>
    </section>

    <!-- FEATURES SECTION -->
    <section id="features" class="features-section">
        <div class="section-header">
            <span class="section-tag">Performances &amp; Sécurité</span>
            <h3 class="section-title">Tout ce dont vous avez besoin pour votre GMAO</h3>
            <p class="section-subtitle">
                Une suite complète d'outils pensée pour fluidifier la communication entre clients, 
                superviseurs et techniciens de maintenance.
            </p>
        </div>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon-wrapper">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <h4 class="feature-title">Signalement Immédiat des Pannes</h4>
                <p class="feature-desc">
                    Les demandeurs et clients créent leurs demandes en quelques secondes avec indication du niveau d'urgence, 
                    description précise et photos de la panne.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon-wrapper" style="background: #f0f9ff; border-color: #bae6fd; color: #0284c7;">
                    <i class="fa-solid fa-calendar-days"></i>
                </div>
                <h4 class="feature-title">Planning Interactif FullCalendar</h4>
                <p class="feature-desc">
                    Vue mensuelle, hebdomadaire et liste des interventions. Basculement automatique responsive 
                    pour consulter les plannings sur mobile.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon-wrapper" style="background: #fffbeb; border-color: #fde68a; color: #d97706;">
                    <i class="fa-solid fa-snowflake"></i>
                </div>
                <h4 class="feature-title">Gestion F-GAS &amp; Frigorifique</h4>
                <p class="feature-desc">
                    Suivi réglementaire complet des fiches de suivi de fluide frigorigène (F-GAS) avec bilan 
                    et bilans périodiques automatiques.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon-wrapper" style="background: #fef2f2; border-color: #fecaca; color: #dc2626;">
                    <i class="fa-solid fa-camera"></i>
                </div>
                <h4 class="feature-title">Rapports &amp; Preuves Photos</h4>
                <p class="feature-desc">
                    Comptes-rendus rédigés directement par les techniciens sur le terrain avec photos avant/après 
                    et photo du carnet d'intervention manuscrit.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon-wrapper" style="background: #f3e8ff; border-color: #e9d5ff; color: #7c3aed;">
                    <i class="fa-solid fa-sitemap"></i>
                </div>
                <h4 class="feature-title">Architecture Multi-Bases &amp; Sites</h4>
                <p class="feature-desc">
                    Structure hiérarchique flexible s'adaptant aussi bien aux grands comptes avec multiples bases 
                    qu'aux entreprises directes avec sites rattachés.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon-wrapper" style="background: #ecfdf5; border-color: #a7f3d0; color: #059669;">
                    <i class="fa-solid fa-chart-pie"></i>
                </div>
                <h4 class="feature-title">Tableau de Bord &amp; Classement</h4>
                <p class="feature-desc">
                    Graphiques analytiques des performances par technicien, répartition par état et suivi 
                    des volumes d'interventions sur 7 jours.
                </p>
            </div>
        </div>
    </section>

    <!-- ROLES SHOWCASE SECTION -->
    <section id="roles" class="roles-section">
        <div class="section-header">
            <span class="section-tag">Une Interface Dédiée À Chaque Métier</span>
            <h3 class="section-title">Des accès sur mesure pour une efficacité maximale</h3>
            <p class="section-subtitle">Chaque utilisateur bénéficie d'un espace de travail personnalisé selon son rôle dans l'organisation.</p>
        </div>

        <div class="roles-grid">
            <div class="role-card">
                <div class="role-badge-icon" style="background: #ecfdf5; color: #059669;">
                    <i class="fa-solid fa-user-shield"></i>
                </div>
                <div class="role-target">Administration</div>
                <h4 class="role-name">Superviseur &amp; Admin</h4>
                <p class="role-desc">
                    Supervision globale du parc d'équipements, affectation des techniciens, gestion des droits et validation finale des comptes-rendus.
                </p>
            </div>

            <div class="role-card">
                <div class="role-badge-icon" style="background: #f0f9ff; color: #0284c7;">
                    <i class="fa-solid fa-user-tie"></i>
                </div>
                <div class="role-target">Client Entreprise</div>
                <h4 class="role-name">Superviseur Client</h4>
                <p class="role-desc">
                    Valide les demandes des sites, consulte l'avancement des interventions en temps réel et accède aux rapports certifiés.
                </p>
            </div>

            <div class="role-card">
                <div class="role-badge-icon" style="background: #fffbeb; color: #d97706;">
                    <i class="fa-solid fa-screwdriver-wrench"></i>
                </div>
                <div class="role-target">Intervention Terrain</div>
                <h4 class="role-name">Technicien Terrain</h4>
                <p class="role-desc">
                    Reçoit son planning journalier, confirme la prise en charge des tickets et soumet ses rapports avec photos d'intervention.
                </p>
            </div>

            <div class="role-card">
                <div class="role-badge-icon" style="background: #fdf2f8; color: #db2777;">
                    <i class="fa-solid fa-location-dot"></i>
                </div>
                <div class="role-target">Utilisateur Site</div>
                <h4 class="role-name">Demandeur Site</h4>
                <p class="role-desc">
                    Signale les pannes de son site spécifique en 3 clics, suit le statut d'avancement et confirme le passage des équipes.
                </p>
            </div>
        </div>
    </section>

    <!-- WORKFLOW SECTION -->
    <section id="workflow" class="workflow-section">
        <div class="section-header">
            <span class="section-tag">Flux de Travail Unifié</span>
            <h3 class="section-title">Du signalement de la panne à la clôture certifiée</h3>
            <p class="section-subtitle">Un processus étape par étape garantissant une rigueur irréprochable.</p>
        </div>

        <div class="workflow-container">
            <div class="workflow-image-container">
                <img src="{{ asset('images/workflow_equipe_noire.jpg') }}" 
                     alt="Techniciens de maintenance en action" class="workflow-img">
            </div>

            <div class="workflow-steps">
                <div class="workflow-step">
                    <div class="step-num">1</div>
                    <div>
                        <h4 class="step-title">Création de la Demande</h4>
                        <p class="step-text">Signalement précis de la panne par le demandeur de site avec photo et niveau d'urgence.</p>
                    </div>
                </div>

                <div class="workflow-step">
                    <div class="step-num">2</div>
                    <div>
                        <h4 class="step-title">Validation &amp; Assignation</h4>
                        <p class="step-text">Validation par le superviseur client puis affectation à un technicien ou une équipe terrain dédiée.</p>
                    </div>
                </div>

                <div class="workflow-step">
                    <div class="step-num">3</div>
                    <div>
                        <h4 class="step-title">Prise en Charge &amp; Intervention</h4>
                        <p class="step-text">Le technicien confirme la réception du ticket et réalise les opérations techniques nécessaires sur le site.</p>
                    </div>
                </div>

                <div class="workflow-step">
                    <div class="step-num">4</div>
                    <div>
                        <h4 class="step-title">Compte-Rendu &amp; Photos</h4>
                        <p class="step-text">Rédaction du rapport avec téléversement des photos de l'équipement et du carnet d'intervention.</p>
                    </div>
                </div>

                <div class="workflow-step">
                    <div class="step-num">5</div>
                    <div>
                        <h4 class="step-title">Clôture Conforme</h4>
                        <p class="step-text">Confirmation de la réalisation et validation finale par le client pour la mise en archive.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CALL TO ACTION SECTION -->
    <section class="cta-section">
        <div class="cta-container">
            <h3 class="cta-title">Prêt à moderniser la gestion de votre maintenance ?</h3>
            <p class="cta-desc">
                Accédez dès maintenant à la plateforme Mainteo et offrez une visibilité totale 
                à vos équipes de maintenance et vos clients.
            </p>
            <a href="{{ route('login') }}" class="btn-cta">
                <i class="fa-solid fa-right-to-bracket"></i>
                Se Connecter à l'Espace Mainteo
            </a>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-brand">
                <img src="{{ asset('logo/logo_soutarah.png') }}" alt="Mainteo Logo">
                <span>Mainteo GMAO</span>
            </div>
            
            <div class="footer-copy">
                &copy; {{ date('Y') }} Mainteo - Tous droits réservés. Plateforme de Maintenance &amp; Dépannage.
            </div>

            <div class="footer-badges">
                <span class="footer-badge-item"><i class="fa-solid fa-shield"></i> F-GAS Ready</span>
                <span class="footer-badge-item"><i class="fa-solid fa-mobile-screen"></i> Responsive</span>
            </div>
        </div>
    </footer>

    <!-- NAVBAR SCROLL SCRIPT -->
    <script>
        const navbar = document.getElementById('navbar');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 30) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    </script>

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
    });

    console.log('[PWA] Script d\'installation chargé');
    </script>

</body>
</html>
