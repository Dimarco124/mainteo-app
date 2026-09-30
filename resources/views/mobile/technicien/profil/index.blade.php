@extends('mobile.technicien.layout')

@section('title', 'Profil - MAINTEO Mobile')

@section('mobile-content')
<!-- Header Profil -->
<div class="mobile-header" style="text-align: center;">
    <div style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #ffffff, #f0fdf4); border: 4px solid rgba(255, 255, 255, 0.5); display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 2rem; font-weight: 800; color: var(--main-emerald);">
        {{ strtoupper(substr(Auth::user()->nom, 0, 1)) }}{{ strtoupper(substr(Auth::user()->prenom ?? '', 0, 1)) }}
    </div>
    <div class="mobile-header-title">{{ Auth::user()->nom_complet }}</div>
    <div class="mobile-header-date" style="text-transform: capitalize;">
        {{ ucfirst(Auth::user()->type_utilisateur) }}
    </div>
</div>

<!-- Statistiques personnelles -->
<div style="padding: 1.5rem; background: #ffffff;">
    <h3 style="font-size: 1rem; font-weight: 700; color: var(--text-dark); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
        <i class="fa-solid fa-chart-line"></i>
        Mes Statistiques
    </h3>
    
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
        <div style="background: var(--emerald-bg); padding: 1.25rem; border-radius: 0.75rem; text-align: center;">
            <div style="font-size: 2rem; font-weight: 800; color: var(--main-emerald); margin-bottom: 0.25rem;">
                {{ $stats['total_interventions'] }}
            </div>
            <div style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">
                Total interventions
            </div>
        </div>
        
        <div style="background: #eff6ff; padding: 1.25rem; border-radius: 0.75rem; text-align: center;">
            <div style="font-size: 2rem; font-weight: 800; color: #0369a1; margin-bottom: 0.25rem;">
                {{ $stats['interventions_mois'] }}
            </div>
            <div style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">
                Ce mois-ci
            </div>
        </div>
        
        <div style="background: #fef3c7; padding: 1.25rem; border-radius: 0.75rem; text-align: center; grid-column: span 2;">
            <div style="font-size: 2rem; font-weight: 800; color: #b45309; margin-bottom: 0.25rem;">
                {{ $stats['taux_resolution'] }}%
            </div>
            <div style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">
                Taux de résolution
            </div>
        </div>
    </div>
</div>

<!-- Séparateur -->
<div style="height: 0.75rem; background: var(--bg-body);"></div>

<!-- Informations personnelles -->
<div style="padding: 1.5rem; background: #ffffff;">
    <h3 style="font-size: 1rem; font-weight: 700; color: var(--text-dark); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
        <i class="fa-solid fa-user"></i>
        Informations
    </h3>
    
    <div style="display: flex; flex-direction: column; gap: 1rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 0.75rem; border-bottom: 1px solid var(--border-color);">
            <span style="color: var(--text-muted); font-size: 0.9rem;">
                <i class="fa-solid fa-envelope" style="width: 20px;"></i>
                Email
            </span>
            <span style="font-weight: 600; color: var(--text-dark); font-size: 0.9rem;">
                {{ Auth::user()->email }}
            </span>
        </div>
        
        @if(Auth::user()->telephone)
        <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 0.75rem; border-bottom: 1px solid var(--border-color);">
            <span style="color: var(--text-muted); font-size: 0.9rem;">
                <i class="fa-solid fa-phone" style="width: 20px;"></i>
                Téléphone
            </span>
            <a href="tel:{{ Auth::user()->telephone }}" style="font-weight: 600; color: var(--main-emerald); text-decoration: none; font-size: 0.9rem;">
                {{ Auth::user()->telephone }}
            </a>
        </div>
        @endif
        
        @if(Auth::user()->equipes && Auth::user()->equipes->count() > 0)
        <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 0.75rem; border-bottom: 1px solid var(--border-color);">
            <span style="color: var(--text-muted); font-size: 0.9rem;">
                <i class="fa-solid fa-users" style="width: 20px;"></i>
                Équipe(s)
            </span>
            <span style="font-weight: 600; color: var(--text-dark); font-size: 0.9rem;">
                {{ Auth::user()->equipes->pluck('nom')->join(', ') }}
            </span>
        </div>
        @endif
    </div>
</div>

<!-- Séparateur -->
<div style="height: 0.75rem; background: var(--bg-body);"></div>

<!-- Actions -->
<div style="padding: 1.5rem; background: #ffffff;">
    <h3 style="font-size: 1rem; font-weight: 700; color: var(--text-dark); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
        <i class="fa-solid fa-gear"></i>
        Paramètres
    </h3>
    
    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
        <a href="{{ route('profile.show') }}" style="display: flex; align-items: center; justify-content: space-between; padding: 1rem; background: var(--bg-body); border-radius: 0.75rem; text-decoration: none; color: var(--text-dark); font-weight: 600;">
            <span>
                <i class="fa-solid fa-user-pen" style="color: var(--main-emerald); margin-right: 0.75rem;"></i>
                Modifier mon profil
            </span>
            <i class="fa-solid fa-chevron-right" style="color: var(--text-muted); font-size: 0.85rem;"></i>
        </a>
        
        <a href="{{ route('dashboard') }}" style="display: flex; align-items: center; justify-content: space-between; padding: 1rem; background: var(--bg-body); border-radius: 0.75rem; text-decoration: none; color: var(--text-dark); font-weight: 600;">
            <span>
                <i class="fa-solid fa-desktop" style="color: #0369a1; margin-right: 0.75rem;"></i>
                Version bureau
            </span>
            <i class="fa-solid fa-chevron-right" style="color: var(--text-muted); font-size: 0.85rem;"></i>
        </a>
    </div>
</div>

<!-- Séparateur -->
<div style="height: 0.75rem; background: var(--bg-body);"></div>

<!-- À propos -->
<div style="padding: 1.5rem; background: #ffffff;">
    <h3 style="font-size: 1rem; font-weight: 700; color: var(--text-dark); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
        <i class="fa-solid fa-circle-info"></i>
        À propos
    </h3>
    
    <div style="display: flex; flex-direction: column; gap: 0.75rem; font-size: 0.9rem; color: var(--text-muted);">
        <div style="display: flex; justify-content: space-between;">
            <span>Application</span>
            <span style="font-weight: 600; color: var(--text-dark);">MAINTEO Mobile</span>
        </div>
        <div style="display: flex; justify-content: space-between;">
            <span>Version</span>
            <span style="font-weight: 600; color: var(--text-dark);">1.0.0</span>
        </div>
        <div style="display: flex; justify-content: space-between;">
            <span>Mode</span>
            <span style="font-weight: 600; color: var(--main-emerald);" id="network-status">
                En ligne
            </span>
        </div>
    </div>
</div>

<!-- Séparateur -->
<div style="height: 0.75rem; background: var(--bg-body);"></div>

<!-- Déconnexion -->
<div style="padding: 1.5rem;">
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn-mobile btn-danger-mobile btn-full-width">
            <i class="fa-solid fa-right-from-bracket"></i>
            Se déconnecter
        </button>
    </form>
</div>

<div style="height: 2rem;"></div>

@push('scripts')
<script>
// Mise à jour statut réseau
function updateNetworkStatus() {
    const statusEl = document.getElementById('network-status');
    if (navigator.onLine) {
        statusEl.textContent = 'En ligne';
        statusEl.style.color = 'var(--main-emerald)';
    } else {
        statusEl.textContent = 'Hors ligne';
        statusEl.style.color = '#b45309';
    }
}

window.addEventListener('online', updateNetworkStatus);
window.addEventListener('offline', updateNetworkStatus);
updateNetworkStatus();
</script>
@endpush
@endsection
