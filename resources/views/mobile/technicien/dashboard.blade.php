@extends('mobile.technicien.layout')

@section('title', 'Dashboard - MAINTEO Mobile')
@section('page-title', 'Dashboard')

@section('mobile-content')
<div style="padding: 1rem; padding-bottom: 100px;">
    
    {{-- SALUTATION --}}
    <div style="margin-bottom: 1.5rem;">
        <div style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 0.25rem;">☀️ Bonjour</div>
        <h2 style="font-size: 1.4rem; font-weight: 800; color: var(--text-dark); margin: 0;">
            {{ Auth::user()->prenom ?? Auth::user()->nom }}
        </h2>
        <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.25rem;">
            {{ \Carbon\Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}
        </div>
    </div>
    
    {{-- STATISTIQUES (6 CARTES) --}}
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem; margin-bottom: 1.5rem;">
        {{-- Total --}}
        <div style="background: linear-gradient(135deg, #0369a1, #0ea5e9); border-radius: 1rem; padding: 1rem; color: #fff;">
            <div style="font-size: 2rem; font-weight: 800; margin-bottom: 0.25rem;">{{ $stats['total_interventions'] }}</div>
            <div style="font-size: 0.8rem; opacity: 0.9;">Total</div>
        </div>
        
        {{-- En cours --}}
        <div style="background: linear-gradient(135deg, #c2410c, #f97316); border-radius: 1rem; padding: 1rem; color: #fff;">
            <div style="font-size: 2rem; font-weight: 800; margin-bottom: 0.25rem;">{{ $stats['interventions_en_cours'] }}</div>
            <div style="font-size: 0.8rem; opacity: 0.9;">En cours</div>
        </div>
        
        {{-- Résolues --}}
        <div style="background: linear-gradient(135deg, #059669, #10b981); border-radius: 1rem; padding: 1rem; color: #fff;">
            <div style="font-size: 2rem; font-weight: 800; margin-bottom: 0.25rem;">{{ $stats['interventions_resolues'] }}</div>
            <div style="font-size: 0.8rem; opacity: 0.9;">Résolues</div>
        </div>
        
        {{-- Maintenances actives --}}
        <div style="background: linear-gradient(135deg, #b45309, #f59e0b); border-radius: 1rem; padding: 1rem; color: #fff;">
            <div style="font-size: 2rem; font-weight: 800; margin-bottom: 0.25rem;">{{ $stats['maintenances_actives'] }}</div>
            <div style="font-size: 0.8rem; opacity: 0.9;">Maintenances actives</div>
        </div>
        
        {{-- Maintenances en cours --}}
        <div style="background: linear-gradient(135deg, #6b21a8, #8b5cf6); border-radius: 1rem; padding: 1rem; color: #fff;">
            <div style="font-size: 2rem; font-weight: 800; margin-bottom: 0.25rem;">{{ $stats['maintenances_en_cours'] }}</div>
            <div style="font-size: 0.8rem; opacity: 0.9;">Maintenances en cours</div>
        </div>
        
        {{-- Maintenances terminées --}}
        <div style="background: linear-gradient(135deg, #15803d, #22c55e); border-radius: 1rem; padding: 1rem; color: #fff;">
            <div style="font-size: 2rem; font-weight: 800; margin-bottom: 0.25rem;">{{ $stats['maintenances_terminees'] }}</div>
            <div style="font-size: 0.8rem; opacity: 0.9;">Maintenances terminées</div>
        </div>
    </div>
    
    {{-- MAINTENANCES À TRAITER --}}
    @if($mesMaintenances->whereIn('statut', ['planifiée', 'confirmée_client', 'en_cours'])->count() > 0)
    <div class="card" style="margin-bottom: 1.5rem;">
        <h3 style="font-size: 1.1rem; font-weight: 700; color: #8b5cf6; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-screwdriver-wrench"></i>
            Maintenances à Traiter ({{ $mesMaintenances->whereIn('statut', ['planifiée', 'confirmée_client', 'en_cours'])->count() }})
        </h3>
        
        @foreach($mesMaintenances->whereIn('statut', ['planifiée', 'confirmée_client', 'en_cours']) as $m)
        <div style="border: 1.5px solid #e9d5ff; border-radius: 0.75rem; padding: 1rem; margin-bottom: 0.75rem; background: #faf5ff;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                <div>
                    <div style="font-size: 0.8rem; color: #8b5cf6; font-weight: 700; margin-bottom: 0.25rem;">
                        {{ $m->numero_maintenance }}
                    </div>
                    <h4 style="font-size: 1rem; font-weight: 700; color: var(--text-dark); margin: 0;">
                        {{ $m->equipement->equipement_nom ?? 'N/A' }}
                    </h4>
                </div>
                
                @php
                    $statutBg = match($m->statut) {
                        'planifiée' => 'background: #fef3c7; color: #92400e;',
                        'confirmée_client' => 'background: #dbeafe; color: #1e40af;',
                        'en_cours' => 'background: #eff6ff; color: #1d4ed8;',
                        default => 'background: #f1f5f9; color: #64748b;'
                    };
                @endphp
                <span style="{{ $statutBg }} padding: 0.4rem 0.75rem; border-radius: 0.5rem; font-size: 0.75rem; font-weight: 700; white-space: nowrap;">
                    @if($m->statut === 'planifiée') ⏳ Planifiée
                    @elseif($m->statut === 'confirmée_client') ✔️ Confirmée
                    @else 🔄 En cours
                    @endif
                </span>
            </div>
            
            <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: var(--text-normal); margin-bottom: 0.5rem;">
                <i class="fa-solid fa-building" style="color: var(--main-emerald);"></i>
                <span>{{ $m->client->nom ?? 'N/A' }}</span>
            </div>
            
            <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.75rem;">
                <i class="fa-solid fa-calendar"></i>
                <span>{{ \Carbon\Carbon::parse($m->date_debut_prevue)->format('d/m/Y H:i') }}</span>
            </div>
            
            <div style="display: flex; gap: 0.5rem;">
                @php
                    $isChefDeCette = Auth::user()->isChefTechnicien() && (
                        ($m->equipe && $m->equipe->chef_equipe == Auth::id()) ||
                        ($m->equipes && $m->equipes->contains(fn($e) => $e->chef_equipe == Auth::id()))
                    );
                    $isTermineDeCette = ($m->nombre_equipements_prevus > 0 && $m->nombre_equipements_restants == 0);
                @endphp
                @if($m->statut === 'confirmée_client')
                    @if($isChefDeCette)
                        @php
                            $dateDebutPrevue = \Carbon\Carbon::parse($m->date_debut_prevue);
                            $maintenant = \Carbon\Carbon::now();
                            $peutDemarrer = $maintenant->gte($dateDebutPrevue);
                        @endphp
                        @if($peutDemarrer)
                        <form action="{{ route('mobile.technicien.maintenances.demarrer', $m->id) }}" method="POST" style="flex: 1;">
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
                    @endif
                @elseif($m->statut === 'en_cours')
                    @if($isChefDeCette)
                        @if($isTermineDeCette)
                        <a href="{{ route('mobile.technicien.maintenances.rapport', $m->id) }}" style="flex: 1; background: #f0fdf4; color: #047857; border: 1px solid #a7f3d0; padding: 0.65rem; border-radius: 0.5rem; font-size: 0.85rem; font-weight: 700; text-decoration: none; text-align: center; display: block;">
                            <i class="fa-solid fa-flag-checkered"></i> Terminer
                        </a>
                        @else
                        <span title="{{ $m->nombre_equipements_restants }} équipement(s) restant(s) à traiter" style="flex: 1; background: #f1f5f9; color: #94a3b8; border: 1px solid #cbd5e1; padding: 0.65rem; border-radius: 0.5rem; font-size: 0.8rem; font-weight: 600; text-align: center; display: block; cursor: not-allowed;">
                            <i class="fa-solid fa-lock"></i> Terminer ({{ $m->nombre_equipements_restants }} rest.)
                        </span>
                        @endif
                    @endif
                @endif
                
                <a href="{{ route('mobile.technicien.maintenances.details', $m->id) }}" style="background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; padding: 0.65rem 1rem; border-radius: 0.5rem; font-size: 0.85rem; font-weight: 700; text-decoration: none;">
                    <i class="fa-solid fa-eye"></i>
                </a>
            </div>
        </div>
        @endforeach
    </div>
    @endif
    
    {{-- MES INTERVENTIONS (5 premières) --}}
    <div class="card" style="margin-bottom: 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--main-emerald); margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-clipboard-list"></i>
                Mes Interventions
            </h3>
            <span style="font-size: 0.85rem; color: var(--text-muted);">{{ $stats['total_interventions'] }}</span>
        </div>
        
        @if($mesInterventions->count() > 0 || $mesMaintenances->count() > 0)
            {{-- Interventions (5 premières) --}}
            @foreach($mesInterventions->take(5) as $dep)
            <div style="border: 1.5px solid var(--border-color); border-radius: 0.75rem; padding: 1rem; margin-bottom: 0.75rem; cursor: pointer;" onclick="window.location.href='{{ route('mobile.technicien.interventions.details', $dep->id) }}'">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                    <div>
                        <span style="font-size: 0.75rem; color: var(--text-muted);">#{{ $dep->id }}</span>
                        @if($dep->type_intervention === 'Dépannage')
                            <span style="font-size: 0.75rem; color: #be123c; font-weight: 700; margin-left: 0.5rem;"><i class="fa-solid fa-wrench"></i> Dépannage</span>
                        @else
                            <span style="font-size: 0.75rem; color: #0369a1; font-weight: 700; margin-left: 0.5rem;"><i class="fa-solid fa-gears"></i> Installation</span>
                        @endif
                    </div>
                    
                    @php
                        $urgenceBadge = match($dep->urgence) {
                            'Critique' => 'background: #fef2f2; color: #dc2626; border: 1px solid #fca5a5;',
                            'Urgent' => 'background: #fff7ed; color: #c2410c; border: 1px solid #fdba74;',
                            default => 'background: #f0f9ff; color: #0369a1; border: 1px solid #bae6fd;'
                        };
                    @endphp
                    <span style="{{ $urgenceBadge }} padding: 0.35rem 0.65rem; border-radius: 0.5rem; font-size: 0.7rem; font-weight: 700;">
                        @if($dep->urgence === 'Critique') 🚨
                        @elseif($dep->urgence === 'Urgent') ⚠️
                        @endif
                        {{ $dep->urgence }}
                    </span>
                </div>
                
                <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--text-dark); margin: 0 0 0.5rem;">
                    {{ $dep->equipement_reference ?? 'N/A' }}
                </h4>
                
                @if($dep->description_panne)
                <p style="font-size: 0.85rem; color: var(--text-normal); margin-bottom: 0.5rem; line-height: 1.4;">
                    {{ Str::limit($dep->description_panne, 80) }}
                </p>
                @endif
                
                @php
                    $statutBadge = '';
                    $statutText = '';
                    
                    if(in_array($dep->statut, ['resolu','résolu']) && $dep->statut_validation_finale_client === 'validé') {
                        $statutBadge = 'background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;';
                        $statutText = '✅ Validé Client';
                    } elseif(in_array($dep->statut, ['resolu','résolu'])) {
                        $statutBadge = 'background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;';
                        $statutText = '✅ Résolu';
                    } elseif($dep->statut === 'en cours') {
                        $statutBadge = 'background: #dbeafe; color: #1e40af; border: 1px solid #93c5fd;';
                        $statutText = '🔄 En cours';
                    } else {
                        $statutBadge = 'background: #fef3c7; color: #92400e; border: 1px solid #fde68a;';
                        $statutText = '⏳ ' . ucfirst($dep->statut);
                    }
                @endphp
                <span style="{{ $statutBadge }} padding: 0.35rem 0.65rem; border-radius: 0.5rem; font-size: 0.75rem; font-weight: 700; display: inline-block;">
                    {{ $statutText }}
                </span>
            </div>
            @endforeach
            
            {{-- Maintenances (5 premières) --}}
            @foreach($mesMaintenances->take(5) as $m)
            <div style="border: 1.5px solid #e9d5ff; border-radius: 0.75rem; padding: 1rem; margin-bottom: 0.75rem; background: #faf5ff; cursor: pointer;" onclick="window.location.href='{{ route('mobile.technicien.maintenances.details', $m->id) }}'">>
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                    <div>
                        <span style="font-size: 0.75rem; color: #8b5cf6; font-weight: 700;">
                            <i class="fa-solid fa-screwdriver-wrench"></i> {{ $m->numero_maintenance }}
                        </span>
                    </div>
                    
                    @php
                        $maintenanceStatutBadge = match($m->statut) {
                            'planifiée' => 'background: #fef3c7; color: #92400e;',
                            'confirmée_client' => 'background: #dbeafe; color: #1e40af;',
                            'en_cours' => 'background: #eff6ff; color: #1d4ed8;',
                            'terminée' => 'background: #ecfdf5; color: #047857;',
                            default => 'background: #f1f5f9; color: #64748b;'
                        };
                        
                        $maintenanceStatutText = match($m->statut) {
                            'planifiée' => '⏳ Planifiée',
                            'confirmée_client' => '✔️ Confirmée',
                            'en_cours' => '🔄 En cours',
                            'terminée' => '✅ Terminée',
                            default => $m->statut
                        };
                    @endphp
                    <span style="{{ $maintenanceStatutBadge }} padding: 0.35rem 0.65rem; border-radius: 0.5rem; font-size: 0.7rem; font-weight: 700;">
                        {{ $maintenanceStatutText }}
                    </span>
                </div>
                
                <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--text-dark); margin: 0 0 0.5rem;">
                    {{ $m->equipement->equipement_nom ?? 'N/A' }}
                </h4>
                
                <div style="font-size: 0.85rem; color: var(--text-normal); margin-bottom: 0.5rem;">
                    {{ $m->client->nom ?? 'N/A' }}
                </div>
            </div>
            @endforeach
            
            <div style="padding-top: 0.75rem; text-align: center;">
                <a href="{{ route('mobile.technicien.interventions') }}" style="color: var(--main-emerald); font-weight: 700; text-decoration: none; font-size: 0.9rem;">
                    Voir toutes mes interventions ({{ $stats['total_interventions'] }}) →
                </a>
            </div>
        @else
        <div class="empty-state">
            <div class="empty-state-icon">📋</div>
            <p class="empty-state-text">Aucune intervention assignée</p>
        </div>
        @endif
    </div>
</div>
@endsection
