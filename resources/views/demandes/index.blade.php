@extends('layouts.app')

@section('title', 'Demandes d\'Intervention')

@section('content')
<style>
@keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.85; transform: scale(1.05); }
}
</style>
<div class="header">
    <div class="page-title">
        <h1>Demandes d'Intervention ({{ $totalCount }})</h1>
        <p>Gestion des demandes d'installation et de dépannage</p>
    </div>
    <div>
        @if(Auth::user()->isSuperviseurClient() || Auth::user()->isDemandeur())
        <a href="{{ route('demandes.create') }}" class="btn-primary" style="background: linear-gradient(135deg, #be123c, #e11d48);">
            <i class="fa-solid fa-wrench"></i> Signaler Panne / Dépannage
        </a>
        @endif
    </div>
</div>



@php
    $typeVip = $typeVip ?? 'all';
    $user = Auth::user();
    $showTabs = in_array($user->type_utilisateur, ['admin', 'superviseur_soutarah', 'technicien']);
    
    // Calculer les compteurs pour les onglets Standards/VIP
    if ($showTabs) {
        $baseQueryStandard = \App\Models\Demande::where(function($q) {
            $q->where('est_vip', false)->orWhereNull('est_vip');
        });
        $baseQueryVip = \App\Models\Demande::where('est_vip', true);
        
        if ($user->type_utilisateur === 'admin') {
            $baseQueryStandard = $baseQueryStandard->whereIn('statut', ['validated_by_client', 'needs_technical_operation', 'confirmed_by_demandeur', 'closed', 'rejected_by_client', 'rejected_by_soutarah']);
            $baseQueryVip = $baseQueryVip->whereIn('statut', ['validated_by_client', 'needs_technical_operation', 'confirmed_by_demandeur', 'closed', 'rejected_by_client', 'rejected_by_soutarah']);
        } elseif ($user->type_utilisateur === 'superviseur_soutarah') {
            $assignment = \App\Models\Assignment::where('superviseur_soutarah_id', $user->id)->first();
            if ($assignment) {
                if ($assignment->base_id) {
                    $baseQueryStandard = $baseQueryStandard->where('base_id', $assignment->base_id)->whereIn('statut', ['validated_by_client', 'needs_technical_operation', 'confirmed_by_demandeur', 'closed']);
                    $baseQueryVip = $baseQueryVip->where('base_id', $assignment->base_id)->whereIn('statut', ['validated_by_client', 'needs_technical_operation', 'confirmed_by_demandeur', 'closed']);
                } elseif ($assignment->client_id) {
                    $baseQueryStandard = $baseQueryStandard->where('client_id', $assignment->client_id)->whereIn('statut', ['validated_by_client', 'needs_technical_operation', 'confirmed_by_demandeur', 'closed']);
                    $baseQueryVip = $baseQueryVip->where('client_id', $assignment->client_id)->whereIn('statut', ['validated_by_client', 'needs_technical_operation', 'confirmed_by_demandeur', 'closed']);
                }
            }
        }
        
        $countStandard = (clone $baseQueryStandard)->count();
        $countVip = (clone $baseQueryVip)->count();
        
        // Stats détaillées pour chaque onglet
        $statsStandard = [
            'total' => $countStandard,
            'pending_client' => (clone $baseQueryStandard)->where('statut', 'pending_client_validation')->count(),
            'validated' => (clone $baseQueryStandard)->where('statut', 'validated_by_client')->count(),
            'in_progress' => (clone $baseQueryStandard)->where('statut', 'needs_technical_operation')->count(),
            'closed' => (clone $baseQueryStandard)->where('statut', 'closed')->count(),
        ];
        
        $statsVip = [
            'total' => $countVip,
            'pending_client' => (clone $baseQueryVip)->where('statut', 'pending_client_validation')->count(),
            'validated' => (clone $baseQueryVip)->where('statut', 'validated_by_client')->count(),
            'in_progress' => (clone $baseQueryVip)->where('statut', 'needs_technical_operation')->count(),
            'closed' => (clone $baseQueryVip)->where('statut', 'closed')->count(),
        ];
    }
@endphp

<!-- Onglets Standards / VIP style pill (visible seulement pour Admin, Superviseur Soutarah, Technicien) -->
@if($showTabs)
<div style="display: flex; gap: 0.75rem; margin-bottom: 1.5rem; border-bottom: 2px solid #e2e8f0; padding-bottom: 0;">
    <a href="{{ route('demandes.standard') }}"
        style="padding: 0.85rem 1.5rem; text-decoration: none; font-weight: 700; font-size: 0.9rem; border-bottom: 3px solid transparent; transition: all 0.2s; display: flex; align-items: center; gap: 0.5rem; {{ $typeVip === 'standard' ? 'border-bottom-color: #3b82f6; color: #1e40af;' : 'color: #94a3b8;' }}">
        <i class="fa-solid fa-list"></i>
        <span>Demandes Standards</span>
        <span style="background-color: {{ $typeVip === 'standard' ? '#3b82f6' : '#cbd5e1' }}; color: #ffffff; padding: 0.15rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 800; min-width: 24px; text-align: center;">
            {{ $countStandard }}
        </span>
    </a>

    <a href="{{ route('demandes.vip') }}"
        style="padding: 0.85rem 1.5rem; text-decoration: none; font-weight: 700; font-size: 0.9rem; border-bottom: 3px solid transparent; transition: all 0.2s; display: flex; align-items: center; gap: 0.5rem; {{ $typeVip === 'vip' ? 'border-bottom-color: #dc2626; color: #dc2626;' : 'color: #94a3b8;' }}">
        <i class="fa-solid fa-siren-on"></i>
        <span>🚨 Demandes VIP</span>
        <span style="background-color: {{ $typeVip === 'vip' ? '#dc2626' : '#cbd5e1' }}; color: #ffffff; padding: 0.15rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 800; min-width: 24px; text-align: center;">
            {{ $countVip }}
        </span>
    </a>
</div>
@endif

<!-- Stats cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div style="flex: 1;">
            <div class="stat-val">{{ $totalCount }}</div>
            <div class="stat-label">Total demandes</div>
        </div>
        <div class="stat-icon" style="background-color: #dbeafe; color: #1d4ed8;">
            <i class="fa-solid fa-clipboard-list"></i>
        </div>
    </div>

    <div class="stat-card">
        <div style="flex: 1;">
            <div class="stat-val">{{ $pendingClientValidation }}</div>
            <div class="stat-label">En attente validation client</div>
        </div>
        <div class="stat-icon" style="background-color: #fef3c7; color: #b45309;">
            <i class="fa-solid fa-clock"></i>
        </div>
    </div>

    <div class="stat-card">
        <div style="flex: 1;">
            <div class="stat-val">{{ $validatedByClient }}</div>
            <div class="stat-label">Validées par client</div>
        </div>
        <div class="stat-icon" style="background-color: #ddd6fe; color: #7c3aed;">
            <i class="fa-solid fa-check-circle"></i>
        </div>
    </div>

    <div class="stat-card">
        <div style="flex: 1;">
            <div class="stat-val">{{ $needsTechnicalOperation }}</div>
            <div class="stat-label">Prêtes pour opération</div>
        </div>
        <div class="stat-icon" style="background-color: #d1fae5; color: #047857;">
            <i class="fa-solid fa-tools"></i>
        </div>
    </div>

    <div class="stat-card">
        <div style="flex: 1;">
            <div class="stat-val">{{ $closedCount }}</div>
            <div class="stat-label">Clôturées</div>
        </div>
        <div class="stat-icon" style="background-color: #fee2e2; color: #059669;">
            <i class="fa-solid fa-check-double"></i>
        </div>
    </div>
</div>

<!-- Filtres -->
<div class="card" style="margin-bottom: 1.5rem;">
    <form action="{{ $showTabs && $typeVip === 'vip' ? route('demandes.vip') : ($showTabs && $typeVip === 'standard' ? route('demandes.standard') : route('demandes.index')) }}" method="GET" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 250px;">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher par numéro, équipement, description..." style="width: 100%; padding: 0.75rem 1rem;">
        </div>
        <div style="min-width: 200px;">
            <select name="statut" style="width: 100%; padding: 0.75rem 1rem;">
                <option value="">Tous les statuts</option>
                <option value="pending_client_validation" {{ request('statut') == 'pending_client_validation' ? 'selected' : '' }}>En attente validation client</option>
                <option value="validated_by_client" {{ request('statut') == 'validated_by_client' ? 'selected' : '' }}>Validée par client</option>
                <option value="rejected_by_client" {{ request('statut') == 'rejected_by_client' ? 'selected' : '' }}>Rejetée par client</option>
                <option value="needs_technical_operation" {{ request('statut') == 'needs_technical_operation' ? 'selected' : '' }}>Prête pour opération</option>
                <option value="closed" {{ request('statut') == 'closed' ? 'selected' : '' }}>Clôturée</option>
            </select>
        </div>
        <div style="min-width: 180px;">
            <select name="niveau_urgence" style="width: 100%; padding: 0.75rem 1rem;">
                <option value="">Toutes urgences</option>
                <option value="critique" {{ request('niveau_urgence') == 'critique' ? 'selected' : '' }}>Critique</option>
                <option value="urgent" {{ request('niveau_urgence') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                <option value="moyen" {{ request('niveau_urgence') == 'moyen' ? 'selected' : '' }}>Moyen</option>
                <option value="faible" {{ request('niveau_urgence') == 'faible' ? 'selected' : '' }}>Faible</option>
            </select>
        </div>
        <div style="min-width: 180px;">
            <input type="date" name="date_debut" value="{{ request('date_debut') }}" placeholder="Date début" style="width: 100%; padding: 0.75rem 1rem;">
        </div>
        <div style="min-width: 180px;">
            <input type="date" name="date_fin" value="{{ request('date_fin') }}" placeholder="Date fin" style="width: 100%; padding: 0.75rem 1rem;">
        </div>
        <button type="submit" class="btn-primary">
            <i class="fa-solid fa-search"></i> Filtrer
        </button>
        @if(request('search') || request('statut') || request('niveau_urgence') || request('date_debut') || request('date_fin'))
        <a href="{{ $showTabs && $typeVip === 'vip' ? route('demandes.vip') : ($showTabs && $typeVip === 'standard' ? route('demandes.standard') : route('demandes.index')) }}" style="padding: 0.65rem 1.1rem; border-radius: 0.75rem; background-color: #f1f5f9; color: #64748b; text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-redo"></i>
        </a>
        @endif
    </form>
</div>

<!-- Liste des demandes -->
<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>N° App</th>
                    <th>N° Référence Externe</th>
                    <th>Type</th>
                    <th>Date</th>
                    <th>Créée par</th>
                    <th>Site / Équipement</th>
                    <th>Description</th>
                    <th>Urgence</th>
                    <th>Statut</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($demandes as $demande)
                <tr>
                    <td>
                        <code style="background-color: #f0f9ff; color: #0369a1; padding: 0.35rem 0.7rem; border-radius: 0.5rem; font-weight: 700; font-size: 0.8rem;">
                            {{ $demande->numero_demande }}
                        </code>
                        @if($demande->est_vip && in_array(Auth::user()->type_utilisateur, ['admin', 'superviseur_soutarah', 'technicien', 'superviseur_client']))
                        <span class="badge" style="background: linear-gradient(135deg, #dc2626, #b91c1c); color: white; font-size: 0.7rem; padding: 0.3rem 0.6rem; display: inline-flex; align-items: center; gap: 0.25rem; margin-left: 0.5rem; animation: pulse 2s infinite;">
                            🚨 VIP
                        </span>
                        @endif
                    </td>
                    <td>
                        <code style="background-color: #fffbeb; color: #92400e; padding: 0.35rem 0.7rem; border-radius: 0.5rem; font-weight: 700; font-size: 0.85rem; font-family: 'Courier New', monospace;">
                            {{ $demande->numero_reference_externe }}
                        </code>
                    </td>
                    <td>
                        @if($demande->type_intervention === 'Installation')
                        <span class="badge" style="background-color: #dbeafe; color: #1d4ed8; font-size: 0.8rem; padding: 0.4rem 0.75rem; display: inline-flex; align-items: center; gap: 0.35rem;">
                            <i class="fa-solid fa-gears"></i> Installation
                        </span>
                        @else
                        <span class="badge" style="background-color: #fff7ed; color: #c2410c; font-size: 0.8rem; padding: 0.4rem 0.75rem; display: inline-flex; align-items: center; gap: 0.35rem;">
                            <i class="fa-solid fa-wrench"></i> Dépannage
                        </span>
                        @endif
                    </td>
                    <td>
                        <div style="font-size: 0.85rem; color: #475569;">
                            {{ $demande->created_at->format('d/m/Y') }}
                        </div>
                        <div style="font-size: 0.75rem; color: #94a3b8;">
                            {{ $demande->created_at->format('H:i') }}
                        </div>
                    </td>
                    <td>
                        <div style="font-weight: 700; color: #0f172a;">{{ $demande->createdBy->nom_complet ?? 'N/A' }}</div>
                        <div style="font-size: 0.75rem; color: #64748b;">
                            @if($demande->created_by_role === 'demandeur')
                            <span class="badge" style="background-color: #dbeafe; color: #1d4ed8;">Demandeur</span>
                            @else
                            <span class="badge" style="background-color: #ddd6fe; color: #7c3aed;">Superviseur Client</span>
                            @endif
                        </div>
                    </td>
                    <td>
                        <div style="font-weight: 700; color: #0f172a;">{{ $demande->site->nom_site ?? 'N/A' }}</div>
                        <div style="font-size: 0.8rem; color: #64748b;">
                            {{ $demande->equipement->equipement_nom ?? 'N/A' }}
                        </div>
                    </td>
                    <td>
                        <div style="font-size: 0.85rem; color: #475569;">
                            {{ Str::limit($demande->description, 50) }}
                        </div>
                    </td>
                    <td>
                        @if($demande->niveau_urgence === 'critique')
                        <span class="badge badge-danger"><i class="fa-solid fa-circle-exclamation"></i> Critique</span>
                        @elseif($demande->niveau_urgence === 'urgent')
                        <span class="badge badge-warning"><i class="fa-solid fa-exclamation-triangle"></i> Urgent</span>
                        @elseif($demande->niveau_urgence === 'moyen')
                        <span class="badge badge-info"><i class="fa-solid fa-circle-info"></i> Moyen</span>
                        @else
                        <span class="badge badge-success"><i class="fa-solid fa-circle-check"></i> Faible</span>
                        @endif
                    </td>
                    <td>
                        @if($demande->statut === 'pending_client_validation')
                        <span class="badge badge-warning">En attente client</span>
                        @elseif($demande->statut === 'validated_by_client')
                        <span class="badge" style="background-color: #ddd6fe; color: #7c3aed;">Validée client</span>
                        @elseif($demande->statut === 'rejected_by_client')
                        <span class="badge badge-danger">Rejetée client</span>
                        @elseif($demande->statut === 'needs_technical_operation')
                            @if($demande->technicalOperation && ($demande->technicalOperation->equipe || $demande->technicalOperation->technicien))
                            <span class="badge" style="background-color: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;">Planifiée</span>
                            @else
                            <span class="badge" style="background-color: #d1fae5; color: #047857;">Prête pour opération</span>
                            @endif
                        @elseif($demande->statut === 'closed')
                        <span class="badge badge-success">Clôturée</span>
                        @else
                        <span class="badge">{{ $demande->statut }}</span>
                        @endif

                        {{-- Équipe ou Technicien assigné --}}
                        @if($demande->technicalOperation)
                            @if($demande->technicalOperation->equipe)
                            <div style="font-size: 0.75rem; color: #1e40af; font-weight: 700; margin-top: 0.3rem; display: flex; align-items: center; gap: 0.25rem;">
                                <i class="fa-solid fa-hard-hat" style="color: #0284c7;"></i> {{ $demande->technicalOperation->equipe->nom_equipe }}
                            </div>
                            @elseif($demande->technicalOperation->technicien)
                            <div style="font-size: 0.75rem; color: #1e40af; font-weight: 700; margin-top: 0.3rem; display: flex; align-items: center; gap: 0.25rem;">
                                <i class="fa-solid fa-user-gear" style="color: #0284c7;"></i> {{ $demande->technicalOperation->technicien->nom_complet }}
                            </div>
                            @endif
                            @if($demande->technicalOperation->date_prevue)
                            <div style="font-size: 0.7rem; color: #047857; margin-top: 0.15rem;">
                                <i class="fa-solid fa-calendar"></i> Prévue le {{ \Carbon\Carbon::parse($demande->technicalOperation->date_prevue)->format('d/m/Y') }}
                            </div>
                            @endif
                        @endif
                    </td>
                    <td style="text-align: right;">
                        <div style="display: inline-flex; gap: 0.5rem;">
                            <a href="{{ route('demandes.show', $demande) }}" class="btn-primary" style="padding: 0.55rem 0.9rem; font-size: 0.85rem;">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" style="text-align: center; color: #94a3b8; padding: 3rem;">
                        <i class="fa-solid fa-clipboard-list" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                        <p style="font-weight: 700; margin-bottom: 0.5rem;">Aucune demande trouvée</p>
                        <p style="font-size: 0.9rem;">
                            @if(Auth::user()->isSuperviseurClient() || Auth::user()->isDemandeur())
                            Créez votre première demande avec le bouton ci-dessus
                            @else
                            Aucune demande pour le moment
                            @endif
                        </p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 1.5rem;">
        {{ $demandes->appends(request()->query())->links('vendor.pagination.custom') }}
    </div>
</div>
@endsection
