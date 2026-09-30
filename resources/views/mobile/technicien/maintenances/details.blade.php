@extends('mobile.technicien.layout')

@section('title', 'Détails Maintenance - MAINTEO Mobile')
@section('page-title', 'Détails Maintenance')

@section('mobile-content')
<div style="padding: 1rem; padding-bottom: 100px;">
    
    {{-- En-tête avec retour --}}
    <div style="margin-bottom: 1.5rem;">
        <a href="{{ route('mobile.technicien.dashboard') }}" style="display: inline-flex; align-items: center; gap: 0.5rem; color: var(--text-muted); text-decoration: none; font-size: 0.9rem; margin-bottom: 1rem;">
            <i class="fa-solid fa-arrow-left"></i> Retour
        </a>
        
        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, #6b21a8, #8b5cf6); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">
                <i class="fa-solid fa-screwdriver-wrench"></i>
            </div>
            <div>
                <div style="font-size: 0.8rem; color: #8b5cf6; font-weight: 700;">{{ $maintenance->numero_maintenance }}</div>
                <h1 style="font-size: 1.3rem; font-weight: 800; color: var(--text-dark); margin: 0;">Maintenance</h1>
            </div>
        </div>
        
        @php
            $statutBadge = match($maintenance->statut) {
                'planifiée' => ['bg' => '#fef3c7', 'color' => '#92400e', 'icon' => '⏳', 'text' => 'Planifiée'],
                'confirmée_client' => ['bg' => '#dbeafe', 'color' => '#1e40af', 'icon' => '✔️', 'text' => 'Confirmée'],
                'en_cours' => ['bg' => '#eff6ff', 'color' => '#1d4ed8', 'icon' => '🔄', 'text' => 'En cours'],
                'terminée' => ['bg' => '#ecfdf5', 'color' => '#047857', 'icon' => '✅', 'text' => 'Terminée'],
                default => ['bg' => '#f1f5f9', 'color' => '#64748b', 'icon' => '', 'text' => $maintenance->statut]
            };
        @endphp
        <div style="display: inline-block; background: {{ $statutBadge['bg'] }}; color: {{ $statutBadge['color'] }}; padding: 0.5rem 1rem; border-radius: 0.75rem; font-size: 0.85rem; font-weight: 700;">
            {{ $statutBadge['icon'] }} {{ $statutBadge['text'] }}
        </div>
    </div>
    
    {{-- Informations principales --}}
    <div class="card" style="margin-bottom: 1rem;">
        <h3 style="font-size: 1rem; font-weight: 700; color: var(--text-dark); margin-bottom: 1rem;">📋 Informations</h3>
        
        <div style="display: grid; gap: 1rem;">
            <div>
                <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">Type</div>
                <div style="font-size: 0.9rem; font-weight: 600; color: var(--text-dark);">{{ $maintenance->type_maintenance }}</div>
            </div>
            
            <div>
                <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">Client</div>
                <div style="font-size: 0.9rem; font-weight: 600; color: var(--text-dark);">{{ $maintenance->client->nom ?? 'N/A' }}</div>
            </div>
            
            @if($maintenance->site)
            <div>
                <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">Site</div>
                <div style="font-size: 0.9rem; font-weight: 600; color: var(--text-dark);">{{ $maintenance->site->nom_site }}</div>
            </div>
            @endif
            
            @if($maintenance->equipe)
            <div>
                <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">Équipe</div>
                <div style="font-size: 0.9rem; font-weight: 600; color: var(--text-dark);">{{ $maintenance->equipe->nom_equipe }}</div>
            </div>
            @endif
        </div>
    </div>
    
    {{-- Dates --}}
    <div class="card" style="margin-bottom: 1rem;">
        <h3 style="font-size: 1rem; font-weight: 700; color: var(--text-dark); margin-bottom: 1rem;">📅 Planning</h3>
        
        <div style="display: grid; gap: 1rem;">
            <div>
                <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">Date de début prévue</div>
                <div style="font-size: 0.9rem; font-weight: 600; color: var(--text-dark);">
                    {{ \Carbon\Carbon::parse($maintenance->date_debut_prevue)->format('d/m/Y à H:i') }}
                </div>
            </div>
            
            @if($maintenance->date_fin_prevue)
            <div>
                <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">Date de fin prévue</div>
                <div style="font-size: 0.9rem; font-weight: 600; color: var(--text-dark);">
                    {{ \Carbon\Carbon::parse($maintenance->date_fin_prevue)->format('d/m/Y à H:i') }}
                </div>
            </div>
            @endif
            
            @if($maintenance->date_debut_reelle)
            <div>
                <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">Date de début réelle</div>
                <div style="font-size: 0.9rem; font-weight: 600; color: #1d4ed8;">
                    {{ \Carbon\Carbon::parse($maintenance->date_debut_reelle)->format('d/m/Y à H:i') }}
                </div>
            </div>
            @endif
        </div>
    </div>
    
    {{-- Description --}}
    @if($maintenance->description)
    <div class="card" style="margin-bottom: 1rem;">
        <h3 style="font-size: 1rem; font-weight: 700; color: var(--text-dark); margin-bottom: 0.75rem;">📝 Description</h3>
        <p style="font-size: 0.9rem; color: var(--text-normal); line-height: 1.6; margin: 0;">{{ $maintenance->description }}</p>
    </div>
    @endif
    
    {{-- Tâches prévues --}}
    @if($maintenance->taches_prevues)
    <div class="card" style="margin-bottom: 1rem;">
        <h3 style="font-size: 1rem; font-weight: 700; color: var(--text-dark); margin-bottom: 0.75rem;">🔧 Tâches prévues</h3>
        <p style="font-size: 0.9rem; color: var(--text-normal); line-height: 1.6; margin: 0; white-space: pre-wrap;">{{ $maintenance->taches_prevues }}</p>
    </div>
    @endif
    
    {{-- Pièces prévues --}}
    @if($maintenance->pieces_prevues)
    <div class="card" style="margin-bottom: 1rem;">
        <h3 style="font-size: 1rem; font-weight: 700; color: var(--text-dark); margin-bottom: 0.75rem;">🔩 Pièces prévues</h3>
        <p style="font-size: 0.9rem; color: var(--text-normal); line-height: 1.6; margin: 0; white-space: pre-wrap;">{{ $maintenance->pieces_prevues }}</p>
    </div>
    @endif
    
    {{-- Actions --}}
    @php
        $isChefDeCette = Auth::user()->isChefTechnicien() && (
            ($maintenance->equipe && $maintenance->equipe->chef_equipe == Auth::id()) ||
            ($maintenance->equipes && $maintenance->equipes->contains(fn($e) => $e->chef_equipe == Auth::id()))
        );
        $isTermineDeCette = ($maintenance->nombre_equipements_prevus > 0 && $maintenance->nombre_equipements_restants == 0);
    @endphp

    @if($isChefDeCette && in_array($maintenance->statut, ['confirmée_client', 'planifiée']))
        @php
            $dateDebutPrevue = \Carbon\Carbon::parse($maintenance->date_debut_prevue);
            $maintenant = \Carbon\Carbon::now();
            $peutDemarrer = $maintenant->gte($dateDebutPrevue);
        @endphp
        
        @if($peutDemarrer)
        <div style="position: fixed; bottom: 70px; left: 1rem; right: 1rem; z-index: 10;">
            <form action="{{ route('mobile.technicien.maintenances.demarrer', $maintenance->id) }}" method="POST">
                @csrf
                <button type="submit" style="width: 100%; background: linear-gradient(135deg, #1d4ed8, #3b82f6); color: white; border: none; padding: 1rem; border-radius: 1rem; font-size: 1rem; font-weight: 700; cursor: pointer; box-shadow: 0 4px 12px rgba(29, 78, 216, 0.3);">
                    <i class="fa-solid fa-play"></i> Démarrer la Maintenance
                </button>
            </form>
        </div>
        @else
        <div style="position: fixed; bottom: 70px; left: 1rem; right: 1rem; z-index: 10;">
            <button type="button" disabled style="width: 100%; background: #fef3c7; color: #92400e; border: 1px solid #fde68a; padding: 1rem; border-radius: 1rem; font-size: 0.95rem; font-weight: 700; cursor: not-allowed; opacity: 0.8; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
                <i class="fa-solid fa-clock"></i> Programmé pour le {{ $dateDebutPrevue->format('d/m/Y') }}
            </button>
        </div>
        @endif
    @elseif($isChefDeCette && $maintenance->statut === 'en_cours')
        @if($isTermineDeCette)
        <div style="position: fixed; bottom: 70px; left: 1rem; right: 1rem; z-index: 10;">
            <a href="{{ route('mobile.technicien.maintenances.rapport', $maintenance->id) }}" style="display: block; width: 100%; background: linear-gradient(135deg, #047857, #10b981); color: white; border: none; padding: 1rem; border-radius: 1rem; font-size: 1rem; font-weight: 700; text-align: center; text-decoration: none; box-shadow: 0 4px 12px rgba(4, 120, 87, 0.3);">
                <i class="fa-solid fa-flag-checkered"></i> Terminer la Maintenance
            </a>
        </div>
        @else
        <div style="position: fixed; bottom: 70px; left: 1rem; right: 1rem; z-index: 10;">
            <button type="button" disabled title="{{ $maintenance->nombre_equipements_restants }} équipement(s) restant(s)" style="width: 100%; background: #94a3b8; color: white; border: none; padding: 1rem; border-radius: 1rem; font-size: 0.9rem; font-weight: 700; text-align: center; cursor: not-allowed; box-shadow: 0 4px 12px rgba(148, 163, 184, 0.3);">
                <i class="fa-solid fa-lock"></i> Terminer ({{ $maintenance->nombre_equipements_restants }} rest.)
            </button>
        </div>
        @endif
    @endif
</div>
@endsection
