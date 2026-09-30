<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mainteo :: Connexion</title>
    <link rel="icon" type="image/x-icon" href="/images/favicon.ico">

    <!-- Varela Round Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Varela+Round&display=swap" rel="stylesheet">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --main-emerald: #059669;
            --main-emerald-light: #10b981;
            --emerald-50: #ecfdf5;
            --emerald-100: #d1fae5;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Varela Round', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            background-color: #f8fafc;
        }

        .login-container {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        /* LEFT SIDE - Image with overlay text */
        .login-left {
            flex: 1;
            background: linear-gradient(135deg, rgba(5, 150, 105, 0.95), rgba(16, 185, 129, 0.9)),
                url('https://images.unsplash.com/photo-1581092918484-8313e1ad2c2f?w=1200&auto=format&fit=crop') center/cover;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 4rem;
            color: #ffffff;
            position: relative;
            overflow: hidden;
        }

        .login-left::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background:
                radial-gradient(circle at 20% 30%, rgba(255, 255, 255, 0.1), transparent 50%),
                radial-gradient(circle at 80% 70%, rgba(255, 255, 255, 0.1), transparent 50%);
        }

        .left-content {
            position: relative;
            z-index: 1;
            max-width: 500px;
            text-align: center;
        }

        .brand-logo-large {
            width: 100px;
            height: 100px;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.95);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: var(--main-emerald);
            margin-bottom: 2rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .left-content h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }

        .left-content p {
            font-size: 1.25rem;
            line-height: 1.8;
            opacity: 0.95;
        }

        .features-list {
            margin-top: 3rem;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            text-align: left;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            background: rgba(255, 255, 255, 0.15);
            padding: 1rem 1.5rem;
            border-radius: 14px;
            backdrop-filter: blur(10px);
        }

        .feature-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .feature-text h4 {
            font-size: 1.1rem;
            margin-bottom: 0.25rem;
        }

        .feature-text p {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        /* RIGHT SIDE - Form */
        .login-right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4rem 2rem;
            background-color: #ffffff;
        }

        .form-container {
            width: 100%;
            max-width: 460px;
        }

        .form-header {
            margin-bottom: 3rem;
        }

        .form-header h2 {
            font-size: 2.25rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.75rem;
        }

        .form-header p {
            font-size: 1rem;
            color: #64748b;
        }

        /* Alert Error */
        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 1rem 1.25rem;
            border-radius: 12px;
            font-size: 0.95rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-error i {
            font-size: 1.2rem;
        }

        /* Form */
        .form-group {
            margin-bottom: 1.75rem;
        }

        .form-group label {
            display: block;
            font-size: 0.95rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.75rem;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 1.1rem;
            pointer-events: none;
            transition: color 0.2s;
        }

        .form-input {
            width: 100%;
            padding: 1rem 1.25rem 1rem 3.5rem;
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            color: #0f172a;
            font-size: 1rem;
            outline: none;
            transition: all 0.3s ease;
        }

        .form-input::placeholder {
            color: #94a3b8;
        }

        .form-input:focus {
            background: #ffffff;
            border-color: var(--main-emerald);
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
        }

        .form-input:focus+.input-icon {
            color: var(--main-emerald);
        }

        .form-password-wrapper {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            font-size: 1.1rem;
            cursor: pointer;
            padding: 0.5rem;
            transition: color 0.2s;
        }

        .toggle-password:hover {
            color: var(--main-emerald);
        }

        /* Submit Button */
        .btn-submit {
            width: 100%;
            padding: 1.15rem;
            background: linear-gradient(135deg, var(--main-emerald), var(--main-emerald-light));
            color: #ffffff;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(5, 150, 105, 0.3);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(5, 150, 105, 0.4);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        /* Back to Home */
        .back-home {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e2e8f0;
        }

        .back-home a {
            color: #64748b;
            text-decoration: none;
            font-size: 0.95rem;
            transition: color 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .back-home a:hover {
            color: var(--main-emerald);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .login-container {
                flex-direction: column;
                background-color: #f8fafc;
            }

            .login-left {
                min-height: 32vh;
                padding: 2.5rem 1.5rem 3.5rem;
            }

            .left-content h1 {
                font-size: 1.85rem;
                margin-bottom: 0.5rem;
            }

            .left-content p {
                font-size: 0.95rem;
            }

            .features-list {
                display: none;
            }

            .login-right {
                min-height: 68vh;
                padding: 0 1.25rem 2.5rem;
                background-color: #f8fafc;
                display: flex;
                justify-content: center;
                align-items: flex-start;
            }

            .form-container {
                background: #ffffff;
                padding: 2rem 1.5rem;
                border-radius: 1.5rem;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
                border: 1px solid #e2e8f0;
                margin-top: -2.5rem;
                position: relative;
                z-index: 10;
            }

            .form-header {
                margin-bottom: 2rem;
            }

            .form-header h2 {
                font-size: 1.65rem;
                color: #0f172a;
            }
        }

        @media (max-width: 640px) {
            .login-left {
                padding: 2rem 1.25rem 3.5rem;
            }

            .brand-logo-large {
                width: 75px;
                height: 75px;
                font-size: 2.25rem;
                margin-bottom: 1rem;
            }

            .left-content h1 {
                font-size: 1.65rem;
            }

            .left-content p {
                font-size: 0.9rem;
            }

            .login-right {
                padding: 0 1rem 2rem;
            }

            .form-container {
                padding: 1.75rem 1.25rem;
            }

            .form-header h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>

<body>

    <div class="login-container">
        <!-- LEFT SIDE - Image with text -->
        <div class="login-left">
            <div class="left-content">
                <div class="brand-logo-large">
                    <img src="{{ asset('logo/logo_soutarah.png') }}" alt="Soutarah Logo" style="max-width: 90%; max-height: 90%; object-fit: contain;">
                </div>
                <h1>Bienvenue sur Mainteo</h1>
                <p>Votre plateforme de gestion de maintenance et d'interventions</p>

                <div class="features-list">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fa-solid fa-chart-line"></i>
                        </div>
                        <div class="feature-text">
                            <h4>Suivi en temps réel</h4>
                            <p>Visualisez toutes vos interventions</p>
                        </div>
                    </div>

                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fa-solid fa-users-gear"></i>
                        </div>
                        <div class="feature-text">
                            <h4>Collaboration efficace</h4>
                            <p>Connectez équipes et clients</p>
                        </div>
                    </div>

                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fa-solid fa-shield-check"></i>
                        </div>
                        <div class="feature-text">
                            <h4>Sécurisé et fiable</h4>
                            <p>Vos données sont protégées</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT SIDE - Login Form -->
        <div class="login-right">
            <div class="form-container">
                <div class="form-header">
                    <h2><i class="fa-solid fa-right-to-bracket" style="color: var(--main-emerald); font-size: 1.3rem; margin-right: 0.35rem;"></i> Connexion</h2>
                    <p>Accédez à votre espace de gestion</p>
                </div>

                <!-- Error Alert -->
                @if($errors->any())
                <div class="alert-error">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
                @endif

                <!-- Warning Alert (session expirée) -->
                @if(session('warning'))
                <div class="alert-error" style="background: #fef3c7; border-color: #fde68a; color: #92400e;">
                    <i class="fa-solid fa-clock"></i>
                    <span>{{ session('warning') }}</span>
                </div>
                @endif

                <!-- Login Form -->
                <form action="{{ route('login.post') }}" method="POST">
                    @csrf

                    <!-- Email -->
                    <div class="form-group">
                        <label for="email">Adresse e-mail</label>
                        <div class="input-wrapper">
                            <input
                                type="email"
                                name="email"
                                id="email"
                                class="form-input"
                                value="{{ old('email') }}"
                                required
                                autofocus
                                placeholder="votre.email@mainteo.ci"
                                autocomplete="email">
                            <i class="fa-solid fa-envelope input-icon"></i>
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="form-group">
                        <label for="mot_de_passe">Mot de passe</label>
                        <div class="input-wrapper">
                            <div class="form-password-wrapper">
                                <input
                                    type="password"
                                    name="mot_de_passe"
                                    id="mot_de_passe"
                                    class="form-input"
                                    required
                                    placeholder="••••••••••"
                                    autocomplete="current-password">
                                <button type="button" class="toggle-password" onclick="togglePassword()">
                                    <i class="fa-solid fa-eye" id="toggleIcon"></i>
                                </button>
                            </div>
                            <i class="fa-solid fa-lock input-icon"></i>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn-submit">
                        <i class="fa-solid fa-right-to-bracket"></i>
                        Se connecter
                    </button>
                </form>

                <!-- Back to Home -->
                <div class="back-home">
                    <a href="{{ url('/') }}">
                        <i class="fa-solid fa-arrow-left"></i>
                        Retour à l'accueil
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        function togglePassword() {
            const passwordInput = document.getElementById('mot_de_passe');
            const toggleIcon = document.getElementById('toggleIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>

</body>

</html>