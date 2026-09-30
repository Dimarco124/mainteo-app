@extends('mobile.technicien.layout')

@section('title', 'Mes Interventions - MAINTEO Mobile')
@section('page-title', 'Mes Interventions')

@section('mobile-content')
<div style="padding: 1rem; padding-bottom: 100px;">
    
    {{-- ONGLETS PAR TYPE --}}
    <div style="display: flex; gap: 0.5rem; margin-bottom: 1rem; overflow-x: auto; -webkit-overflow-scrolling: touch;">
        <a href="{{ route('mobile.technicien.interventions', ['type' => 'Installation'] + request()->except('type')) }}" 
           style="flex-shrink: 0; padding: 0.75rem 1rem; border-radius: 0.75rem; text-decoration: none; font-weight: 700; font-size: 0.85rem; display: flex; align-items: center; gap: 0.5rem; {{ request('type') == 'Installation' || (!request('type') && !request('filtre')) ? 'background: linear-gradient(135deg, #0ea5e9, #0369a1); color: #fff;' : 'background: #f1f5f9; color: #64748b;' }}">
            <i class="fa-solid fa-gears"></i>
            <span>Installations</span>
            <span style="background: {{ request('type') == 'Installation' || (!request('type') && !request('filtre')) ? 'rgba(255,255,255,0.3)' : '#cbd5e1' }}; color: {{ request('type') == 'Installation' || (!request('type') && !request('filtre')) ? '#fff' : '#475569' }}; padding: 0.15rem 0.5rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 800; min-width: 20px; text-align: center;">
                {{ $installationsCount }}
            </span>
        </a>
        
        <a href="{{ route('mobile.technicien.interventions', ['type' => 'Dépannage'] + request()->except('type')) }}" 
           style="flex-shrink: 0; padding: 0.75rem 1rem; border-radius: 0.75rem; text-decoration: none; font-weight: 700; font-size: 0.85rem; display: flex; align-items: center; gap: 0.5rem; {{ request('type') == 'Dépannage' ? 'background: linear-gradient(135deg, #be123c, #881337); color: #fff;' : 'background: #f1f5f9; color: #64748b;' }}">
            <i class="fa-solid fa-wrench"></i>
            <span>Dépannages</span>
            <span style="background: {{ request('type') == 'Dépannage' ? 'rgba(255,255,255,0.3)' : '#cbd5e1' }}; color: {{ request('type') == 'Dépannage' ? '#fff' : '#475569' }}; padding: 0.15rem 0.5rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 800; min-width: 20px; text-align: center;">
                {{ $depannagesCount }}
            </span>
        </a>
        
        <a href="{{ route('mobile.technicien.interventions', ['type' => 'Maintenance'] + request()->except('type')) }}" 
           style="flex-shrink: 0; padding: 0.75rem 1rem; border-radius: 0.75rem; text-decoration: none; font-weight: 700; font-size: 0.85rem; display: flex; align-items: center; gap: 0.5rem; {{ request('type') == 'Maintenance' ? 'background: linear-gradient(135deg, #8b5cf6, #6b21a8); color: #fff;' : 'background: #f1f5f9; color: #64748b;' }}">
            <i class="fa-solid fa-screwdriver-wrench"></i>
            <span>Maintenances</span>
            <span style="background: {{ request('type') == 'Maintenance' ? 'rgba(255,255,255,0.3)' : '#cbd5e1' }}; color: {{ request('type') == 'Maintenance' ? '#fff' : '#475569' }}; padding: 0.15rem 0.5rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 800; min-width: 20px; text-align: center;">
                {{ $maintenancesCount }}
            </span>
        </a>
    </div>
    
    {{-- FILTRES --}}
    <div class="card" style="margin-bottom: 1rem; padding: 1rem;">
        <form action="{{ route('mobile.technicien.interventions') }}" method="GET">
            @if(request('type'))
            <input type="hidden" name="type" value="{{ request('type') }}">
            @endif
            
            {{-- Recherche --}}
            <div style="margin-bottom: 0.75rem;">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="🔍 Rechercher..." 
                       style="width: 100%; padding: 0.75rem 1rem; border: 1.5px solid #e2e8f0; border-radius: 0.75rem; font-size: 0.95rem;">
            </div>
            
            {{-- Statut --}}
            <div style="margin-bottom: 0.75rem;">
                <select name="statut" 
                        style="width: 100%; padding: 0.75rem 1rem; border: 1.5px solid #e2e8f0; border-radius: 0.75rem; font-size: 0.95rem; background: #fff;">
                    @if(request('type') == 'Maintenance')
                    <option value="">Tous les statuts</option>
                    <option value="planifiée" {{ request('statut') == 'planifiée' ? 'selected' : '' }}>⏳ Planifiée</option>
                    <option value="confirmée_client" {{ request('statut') == 'confirmée_client' ? 'selected' : '' }}>✔️ Confirmée</option>
                    <option value="en_cours" {{ request('statut') == 'en_cours' ? 'selected' : '' }}>🔄 En cours</option>
                    <option value="terminée" {{ request('statut') == 'terminée' ? 'selected' : '' }}>✅ Terminée</option>
                    @else
                    <option value="">Tous les statuts</option>
                    <option value="en cours" {{ request('statut') == 'en cours' ? 'selected' : '' }}>🔄 En cours</option>
                    <option value="resolu" {{ request('statut') == 'resolu' ? 'selected' : '' }}>✅ Résolu</option>
                    <option value="en_attente_piece" {{ request('statut') == 'en_attente_piece' ? 'selected' : '' }}>📦 Attente pièce</option>
                    <option value="partiellement_resolu" {{ request('statut') == 'partiellement_resolu' ? 'selected' : '' }}>⚠️ Partiel</option>
                    <option value="non_resolu" {{ request('statut') == 'non_resolu' ? 'selected' : '' }}>⛔ Non résolu</option>
                    @endif
                </select>
            </div>
            
            <button type="submit" 
                    class="btn-primary" 
                    style="width: 100%; padding: 0.75rem; border-radius: 0.75rem; background: var(--main-emerald); color: #fff; border: none; font-weight: 700; font-size: 0.95rem;">
                <i class="fa-solid fa-filter"></i> Filtrer
            </button>
        </form>
    </div>
    
    {{-- COMPTEUR --}}
    <div style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 1rem; padding: 0 0.25rem;">
        @if(request('type') == 'Maintenance')
            {{ $maintenances->total() }} maintenance(s)
        @else
            {{ $depannages->total() }} intervention(s)
        @endif
    </div>
    
    {{-- LISTE DES MAINTENANCES --}}
    @if(request('type') == 'Maintenance')
        @forelse($maintenances as $m)
        <div class="card" 
             style="margin-bottom: 1rem; cursor: pointer;" 
             onclick="window.location.href='{{ route('maintenances.show', $m->id) }}'">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                <div>
                    <div style="font-size: 0.8rem; color: #8b5cf6; font-weight: 700; margin-bottom: 0.25rem;">
                        {{ $m->numero_maintenance }}
                    </div>
                    <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--text-dark); margin: 0;">
                        {{ $m->equipement->equipement_nom ?? 'N/A' }}
                    </h3>
                </div>
                
                @php
                    $statutBg = match($m->statut) {
                        'planifiée' => 'background: #fef3c7; color: #92400e;',
                        'confirmée_client' => 'background: #dbeafe; color: #1e40af;',
                        'en_cours' => 'background: #eff6ff; color: #1d4ed8;',
                        'terminée' => 'background: #ecfdf5; color: #047857;',
                        default => 'background: #f1f5f9; color: #64748b;'
                    };
                @endphp
                <span style="{{ $statutBg }} padding: 0.4rem 0.75rem; border-radius: 0.5rem; font-size: 0.75rem; font-weight: 700;">
                    @if($m->statut === 'planifiée') ⏳ Planifiée
                    @elseif($m->statut === 'confirmée_client') ✔️ Confirmée
                    @elseif($m->statut === 'en_cours') 🔄 En cours
                    @elseif($m->statut === 'terminée') ✅ Terminée
                    @endif
                </span>
            </div>
            
            <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: var(--text-normal); margin-bottom: 0.5rem;">
                <i class="fa-solid fa-building" style="color: var(--main-emerald);"></i>
                <span>{{ $m->client->nom ?? 'N/A' }}</span>
            </div>
            
            <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: var(--text-normal); margin-bottom: 0.75rem;">
                <i class="fa-solid fa-map-marker-alt" style="color: var(--main-emerald);"></i>
                <span>{{ $m->site->nom_site ?? ($m->base->nom_base ?? 'N/A') }}</span>
            </div>
            
            <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: var(--text-muted);">
                <i class="fa-solid fa-calendar"></i>
                <span>{{ \Carbon\Carbon::parse($m->date_debut_prevue)->format('d/m/Y H:i') }}</span>
            </div>
            
            @if($m->statut === 'confirmée_client' || $m->statut === 'en_cours')
            @php
                $isChefDeCette = Auth::user()->isChefTechnicien()
                    && (
                        ($m->equipe && $m->equipe->chef_equipe == Auth::id())
                        || ($m->equipes && $m->equipes->contains(fn($e) => $e->chef_equipe == Auth::id()))
                    );
            @endphp
            @if($isChefDeCette)
            <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border-color); display: flex; gap: 0.5rem;">
                @if($m->statut === 'confirmée_client')
                    @php
                        $dateDebutPrevue = \Carbon\Carbon::parse($m->date_debut_prevue);
                        $maintenant = \Carbon\Carbon::now();
                        $peutDemarrer = $maintenant->gte($dateDebutPrevue);
                    @endphp
                    @if($peutDemarrer)
                    <form action="{{ route('maintenances.demarrer', $m->id) }}" method="POST" style="flex: 1;">
                        @csrf
                        <button type="submit" style="width: 100%; background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; padding: 0.65rem; border-radius: 0.5rem; font-size: 0.85rem; font-weight: 700; cursor: pointer;">
                            <i class="fa-solid fa-play"></i> Démarrer
                        </button>
                    </form>
                    @else
                    <button type="button" disabled style="width: 100%; background: #fef3c7; color: #92400e; border: 1px solid #fde68a; padding: 0.65rem; border-radius: 0.5rem; font-size: 0.85rem; font-weight: 700; cursor: not-allowed; opacity: 0.6;">
                        <i class="fa-solid fa-clock"></i> Programmé pour le {{ $dateDebutPrevue->format('d/m/Y') }}
                    </button>
                    @endif
                @elseif($m->statut === 'en_cours')
                    @php $isTermineDeCette = ($m->nombre_equipements_prevus > 0 && $m->nombre_equipements_restants == 0); @endphp
                    @if($isTermineDeCette)
                    <a href="{{ route('maintenances.rapportForm', $m->id) }}" style="flex: 1; background: #f0fdf4; color: #047857; border: 1px solid #a7f3d0; padding: 0.65rem; border-radius: 0.5rem; font-size: 0.85rem; font-weight: 700; text-decoration: none; text-align: center; display: block;">
                        <i class="fa-solid fa-flag-checkered"></i> Terminer
                    </a>
                    @endif
                @endif
            </div>
            @endif
            @endif
        </div>
        @empty
        <div class="empty-state">
            <div class="empty-state-icon">🔧</div>
            <p class="empty-state-text">Aucune maintenance assignée</p>
        </div>
        @endforelse
        
        @if($maintenances->hasPages())
        <div style="margin-top: 1rem;">
            {{ $maintenances->appends(request()->except('page'))->links('vendor.pagination.custom') }}
        </div>
        @endif
    @else
    {{-- LISTE DES INTERVENTIONS (Installation & Dépannage) --}}
        @forelse($depannages as $dep)
        @php
            $urgenceClass = match($dep->urgence) {
                'Critique' => 'border-left: 4px solid #dc2626;',
                'Urgent' => 'border-left: 4px solid #f97316;',
                default => ''
            };
        @endphp
        
        <div class="card" 
             style="margin-bottom: 1rem; cursor: pointer; {{ $urgenceClass }}" 
             onclick="window.location.href='{{ route('mobile.technicien.interventions.details', $dep->id) }}'">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                <div>
                    <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.25rem;">
                        #{{ $dep->id }}
                        @if($dep->type_intervention === 'Dépannage')
                            <span style="color: #be123c; font-weight: 700;"><i class="fa-solid fa-wrench"></i> Dépannage</span>
                        @else
                            <span style="color: #0369a1; font-weight: 700;"><i class="fa-solid fa-gears"></i> Installation</span>
                        @endif
                    </div>
                    <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--text-dark); margin: 0;">
                        {{ $dep->equipement_reference ?? 'N/A' }}
                    </h3>
                </div>
                
                @php
                    $urgenceBadge = match($dep->urgence) {
                        'Critique' => 'background: #fef2f2; color: #dc2626; border: 1px solid #fca5a5;',
                        'Urgent' => 'background: #fff7ed; color: #c2410c; border: 1px solid #fdba74;',
                        default => 'background: #f0f9ff; color: #0369a1; border: 1px solid #bae6fd;'
                    };
                @endphp
                <span style="{{ $urgenceBadge }} padding: 0.4rem 0.75rem; border-radius: 0.5rem; font-size: 0.75rem; font-weight: 700;">
                    @if($dep->urgence === 'Critique') 🚨 CRITIQUE
                    @elseif($dep->urgence === 'Urgent') ⚠️ URGENT
                    @else {{ $dep->urgence }}
                    @endif
                </span>
            </div>
            
            @if($dep->description_panne)
            <p style="font-size: 0.9rem; color: var(--text-normal); margin-bottom: 0.75rem; line-height: 1.4;">
                {{ Str::limit($dep->description_panne, 100) }}
            </p>
            @endif
            
            <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: var(--text-normal); margin-bottom: 0.5rem;">
                <i class="fa-solid fa-map-marker-alt" style="color: var(--main-emerald);"></i>
                <span>{{ $dep->equipement->baseSite->nom_base ?? 'N/A' }}</span>
            </div>
            
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div style="font-size: 0.85rem; color: var(--text-muted);">
                    <i class="fa-solid fa-calendar"></i>
                    {{ $dep->date_prevue ?? '-' }}
                </div>
                
                @php
                    $statutBadge = '';
                    $statutText = '';
                    
                    if($dep->statut_rapport_technicien === 'rejete') {
                        $statutBadge = 'background: #fef2f2; color: #dc2626; border: 1px solid #fca5a5;';
                        $statutText = '⚠️ À corriger';
                    } elseif(in_array($dep->statut, ['resolu','résolu']) && $dep->statut_validation_finale_client === 'validé') {
                        $statutBadge = 'background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;';
                        $statutText = '✅ Validé Client';
                    } elseif(in_array($dep->statut, ['resolu','résolu'])) {
                        $statutBadge = 'background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;';
                        $statutText = '✅ Résolu';
                    } elseif($dep->statut === 'non_resolu') {
                        $statutBadge = 'background: #fef2f2; color: #b91c1c; border: 1px solid #fca5a5;';
                        $statutText = '⛔ Non résolu';
                    } elseif($dep->statut === 'en_attente_piece') {
                        $statutBadge = 'background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe;';
                        $statutText = '📦 Attente pièce';
                    } elseif($dep->statut === 'partiellement_resolu') {
                        $statutBadge = 'background: #fffbeb; color: #92400e; border: 1px solid #fde68a;';
                        $statutText = '⚠️ Partiel';
                    } elseif($dep->statut === 'en cours') {
                        $statutBadge = 'background: #dbeafe; color: #1e40af; border: 1px solid #93c5fd;';
                        $statutText = '🔄 En cours';
                    } else {
                        $statutBadge = 'background: #fef3c7; color: #92400e; border: 1px solid #fde68a;';
                        $statutText = '⏳ En attente';
                    }
                @endphp
                <span style="{{ $statutBadge }} padding: 0.35rem 0.65rem; border-radius: 0.5rem; font-size: 0.75rem; font-weight: 700;">
                    {{ $statutText }}
                </span>
            </div>
        </div>
        @empty
        <div class="empty-state">
            <div class="empty-state-icon">📋</div>
            <p class="empty-state-text">Aucune intervention assignée</p>
        </div>
        @endforelse
        
        @if($depannages->hasPages())
        <div style="margin-top: 1rem;">
            {{ $depannages->appends(request()->except('page'))->links('vendor.pagination.custom') }}
        </div>
        @endif
    @endif
</div>
@endsection
