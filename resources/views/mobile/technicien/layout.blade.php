@extends('mobile.layout')

@section('content')
<div class="mobile-layout">
    <!-- Header fixe avec logo et menu 3 points -->
    <div class="mobile-top-header">
        <div class="mobile-top-header-content">
            <div style="flex: 1;">
                <img src="{{ asset('logo/logo_soutarah.png') }}" alt="Soutarah" style="height: 28px; width: auto; display: block; margin-bottom: 0.25rem;">
                <h1 class="mobile-page-title" style="font-size: 1rem; margin: 0;">@yield('page-title', 'MAINTEO')</h1>
            </div>
            <button class="mobile-menu-trigger" id="mobileMenuTrigger">
                <i class="fa-solid fa-ellipsis-vertical"></i>
            </button>
        </div>
    </div>

    <!-- Menu flottant (caché par défaut) -->
    <div class="mobile-menu-overlay" id="mobileMenuOverlay"></div>
    <div class="mobile-menu-card" id="mobileMenuCard">
        <div class="mobile-menu-header">
            <h3 class="mobile-menu-title">Menu</h3>
            <button class="mobile-menu-close" id="mobileMenuClose">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="mobile-menu-items">
            <a href="{{ route('mobile.technicien.statistiques') }}" class="mobile-menu-item">
                <div class="mobile-menu-item-icon" style="background: #eff6ff; color: #0369a1;">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
                <div class="mobile-menu-item-content">
                    <div class="mobile-menu-item-title">Statistiques</div>
                    <div class="mobile-menu-item-desc">Performances et indicateurs</div>
                </div>
                <i class="fa-solid fa-chevron-right" style="color: #cbd5e1;"></i>
            </a>
            
            <a href="{{ route('mobile.technicien.comptes-rendus') }}" class="mobile-menu-item">
                <div class="mobile-menu-item-icon" style="background: #fef3c7; color: #b45309;">
                    <i class="fa-solid fa-file-lines"></i>
                </div>
                <div class="mobile-menu-item-content">
                    <div class="mobile-menu-item-title">Comptes-Rendus</div>
                    <div class="mobile-menu-item-desc">Rapports d'interventions</div>
                </div>
                <i class="fa-solid fa-chevron-right" style="color: #cbd5e1;"></i>
            </a>
            
            <a href="{{ route('mobile.technicien.parametres') }}" class="mobile-menu-item">
                <div class="mobile-menu-item-icon" style="background: #f3f4f6; color: #6b7280;">
                    <i class="fa-solid fa-gear"></i>
                </div>
                <div class="mobile-menu-item-content">
                    <div class="mobile-menu-item-title">Paramètres</div>
                    <div class="mobile-menu-item-desc">Préférences et configuration</div>
                </div>
                <i class="fa-solid fa-chevron-right" style="color: #cbd5e1;"></i>
            </a>
            
            <div class="mobile-menu-divider"></div>
            
            <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                @csrf
                <button type="submit" class="mobile-menu-item mobile-menu-item-danger">
                    <div class="mobile-menu-item-icon" style="background: #fef2f2; color: #dc2626;">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </div>
                    <div class="mobile-menu-item-content">
                        <div class="mobile-menu-item-title">Déconnexion</div>
                        <div class="mobile-menu-item-desc">Quitter l'application</div>
                    </div>
                    <i class="fa-solid fa-chevron-right" style="color: #cbd5e1;"></i>
                </button>
            </form>
        </div>
    </div>
    
    <!-- Contenu principal avec padding pour header -->
    <div class="mobile-content" style="padding-top: 60px;">
        @yield('mobile-content')
    </div>
    
    <!-- Bottom Navigation Tabs (4 onglets principaux) -->
    <nav class="bottom-tabs">
        <a href="{{ route('mobile.technicien.dashboard') }}" class="bottom-tab {{ request()->routeIs('mobile.technicien.dashboard') ? 'active' : '' }}">
            <i class="fa-solid fa-house"></i>
            <span class="bottom-tab-label">Dashboard</span>
        </a>
        
        <a href="{{ route('mobile.technicien.interventions') }}" class="bottom-tab {{ request()->routeIs('mobile.technicien.interventions*') ? 'active' : '' }}">
            <i class="fa-solid fa-clipboard-list"></i>
            <span class="bottom-tab-label">Interventions</span>
        </a>
        
        <a href="{{ route('mobile.technicien.planning') }}" class="bottom-tab {{ request()->routeIs('mobile.technicien.planning') ? 'active' : '' }}">
            <i class="fa-solid fa-calendar-days"></i>
            <span class="bottom-tab-label">Planning</span>
        </a>
        
        <a href="{{ route('mobile.technicien.profil') }}" class="bottom-tab {{ request()->routeIs('mobile.technicien.profil*') ? 'active' : '' }}">
            <i class="fa-solid fa-user"></i>
            <span class="bottom-tab-label">Profil</span>
        </a>
    </nav>
</div>

<!-- Script pour gérer le menu -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const menuTrigger = document.getElementById('mobileMenuTrigger');
    const menuCard = document.getElementById('mobileMenuCard');
    const menuOverlay = document.getElementById('mobileMenuOverlay');
    const menuClose = document.getElementById('mobileMenuClose');
    
    // Ouvrir le menu
    menuTrigger.addEventListener('click', function() {
        menuCard.classList.add('active');
        menuOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    });
    
    // Fermer le menu
    function closeMenu() {
        menuCard.classList.remove('active');
        menuOverlay.classList.remove('active');
        document.body.style.overflow = '';
    }
    
    menuClose.addEventListener('click', closeMenu);
    menuOverlay.addEventListener('click', closeMenu);
});
</script>
@endsection
