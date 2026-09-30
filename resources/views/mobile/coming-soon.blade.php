@extends('mobile.layout')

@section('title', 'Bientôt disponible - MAINTEO Mobile')

@section('content')
<div class="coming-soon-container">
    
    <!-- Icône animée -->
    <div class="coming-soon-icon">
        🚀
    </div>
    
    <!-- Titre -->
    <h1 class="coming-soon-title">
        Interface Mobile<br>en Développement
    </h1>
    
    <!-- Message personnalisé -->
    <p class="coming-soon-greeting">
        Bonjour <strong>{{ $user->nom_complet }}</strong>,
    </p>
    <p class="coming-soon-message">
        L'interface mobile pour les <strong>{{ ucfirst($user->type_utilisateur) }}</strong> est en cours de développement et sera bientôt disponible.
    </p>
    
    <!-- Bouton retour -->
    <a href="{{ route('dashboard') }}" class="btn-back-to-web">
        <i class="fa-solid fa-arrow-left"></i> Retour à l'application web
    </a>
    
    <!-- Roadmap -->
    <div class="roadmap-card">
        <h3 class="roadmap-title">
            📅 Calendrier de déploiement
        </h3>
        
        @foreach($roadmap as $item)
        <div class="roadmap-item {{ $item['status'] === 'disponible' ? 'roadmap-item-available' : '' }}">
            <div class="roadmap-icon">{{ $item['icon'] }}</div>
            
            <div class="roadmap-info">
                <div class="roadmap-role">{{ $item['role'] }}</div>
                <div class="roadmap-date">{{ $item['date'] }}</div>
            </div>
            
            @if($item['status'] === 'disponible')
            <span class="roadmap-badge">DISPO</span>
            @endif
        </div>
        @endforeach
    </div>
    
    <!-- Note -->
    <p class="coming-soon-note">
        <i class="fa-solid fa-info-circle"></i> Vous serez notifié par email lors de la disponibilité
    </p>
</div>

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Varela Round', sans-serif;
    }
    
    body {
        background: linear-gradient(135deg, #f8fafc 0%, #ecfdf5 100%);
        min-height: 100vh;
    }
    
    .coming-soon-container {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 2rem 1.5rem;
        text-align: center;
    }
    
    .coming-soon-icon {
        font-size: 5rem;
        margin-bottom: 2rem;
        animation: bounce 2s infinite;
    }
    
    @keyframes bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-20px); }
    }
    
    .coming-soon-title {
        font-size: 1.75rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 1.5rem;
        line-height: 1.3;
    }
    
    .coming-soon-greeting {
        font-size: 1rem;
        color: #64748b;
        margin-bottom: 0.5rem;
    }
    
    .coming-soon-message {
        font-size: 0.95rem;
        color: #64748b;
        max-width: 500px;
        line-height: 1.6;
        margin-bottom: 2rem;
    }
    
    .btn-back-to-web {
        background: linear-gradient(135deg, #059669, #10b981);
        color: #ffffff;
        padding: 1rem 2rem;
        border-radius: 0.75rem;
        text-decoration: none;
        font-weight: 700;
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
        display: inline-block;
        margin-bottom: 3rem;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .btn-back-to-web:active {
        transform: scale(0.98);
    }
    
    .roadmap-card {
        background: #ffffff;
        border-radius: 1rem;
        padding: 2rem 1.5rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        max-width: 500px;
        width: 100%;
        margin-bottom: 2rem;
    }
    
    .roadmap-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 1.5rem;
        text-align: left;
    }
    
    .roadmap-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        border-radius: 0.75rem;
        margin-bottom: 0.75rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
    }
    
    .roadmap-item-available {
        background: #ecfdf5;
        border: 2px solid #059669;
    }
    
    .roadmap-icon {
        font-size: 1.5rem;
        flex-shrink: 0;
    }
    
    .roadmap-info {
        flex: 1;
        text-align: left;
    }
    
    .roadmap-role {
        font-weight: 700;
        color: #0f172a;
        font-size: 0.9rem;
        margin-bottom: 0.25rem;
    }
    
    .roadmap-date {
        font-size: 0.8rem;
        color: #64748b;
    }
    
    .roadmap-badge {
        background: #059669;
        color: #ffffff;
        padding: 0.25rem 0.75rem;
        border-radius: 999px;
        font-size: 0.7rem;
        font-weight: 700;
        flex-shrink: 0;
    }
    
    .coming-soon-note {
        font-size: 0.85rem;
        color: #94a3b8;
        margin-top: 1rem;
    }
</style>
@endsection
