@extends('layouts.app')

@section('title', 'Mes Interventions Assignées')

@section('content')
<div class="header">
    <div class="page-title">
        <h1>Mes Interventions Assignées ({{ $totalCount }})</h1>
        <p>Interventions qui vous sont attribuées directement ou via votre équipe</p>
    </div>
</div>

<!-- Onglets par Type d'Intervention -->
<div style="display: flex; gap: 0.75rem; margin-bottom: 1.5rem;">
    <a href="{{ route('technicien.interventions', array_merge(request()->except('type'), ['type' => 'Installation'])) }}" 
       style="padding: 0.85rem 1.5rem; text-decoration: none; font-weight: 700; font-size: 0.9rem; border-bottom: 3px solid transparent; transition: all 0.2s; display: flex; align-items: center; gap: 0.5rem; {{ request('type') == 'Installation' || (!request('type') && !request('statut')) ? 'border-bottom-color: #0ea5e9; color: #0369a1;' : 'color: #94a3b8;' }}">
        <i class="fa-solid fa-gears"></i>
        <span>Installations</span>
        <span style="background-color: {{ request('type') == 'Installation' || (!request('type') && !request('statut')) ? '#0ea5e9' : '#cbd5e1' }}; color: #ffffff; padding: 0.15rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 800; min-width: 24px; text-align: center;">
            {{ $installationsCount }}
        </span>
    </a>
    
    <a href="{{ route('technicien.interventions', array_merge(request()->except('type'), ['type' => 'Dépannage'])) }}"
       style="padding: 0.85rem 1.5rem; text-decoration: none; font-weight: 700; font-size: 0.9rem; border-bottom: 3px solid transparent; transition: all 0.2s; display: flex; align-items: center; gap: 0.5rem; {{ request('type') == 'Dépannage' ? 'border-bottom-color: #be123c; color: #be123c;' : 'color: #94a3b8;' }}">
        <i class="fa-solid fa-wrench"></i>
        <span>Dépannages</span>
        <span style="background-color: {{ request('type') == 'Dépannage' ? '#be123c' : '#cbd5e1' }}; color: #ffffff; padding: 0.15rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 800; min-width: 24px; text-align: center;">
            {{ $depannagesCount }}
        </span>
    </a>
    
    <a href="{{ route('technicien.interventions', array_merge(request()->except('type'), ['type' => 'Maintenance'])) }}"
       style="padding: 0.85rem 1.5rem; text-decoration: none; font-weight: 700; font-size: 0.9rem; border-bottom: 3px solid transparent; transition: all 0.2s; display: flex; align-items: center; gap: 0.5rem; {{ request('type') == 'Maintenance' ? 'border-bottom-color: #8b5cf6; color: #6b21a8;' : 'color: #94a3b8;' }}">
        <i class="fa-solid fa-screwdriver-wrench"></i>
        <span>Maintenances</span>
        <span style="background-color: {{ request('type') == 'Maintenance' ? '#8b5cf6' : '#cbd5e1' }}; color: #ffffff; padding: 0.15rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 800; min-width: 24px; text-align: center;">
            {{ $maintenancesCount }}
        </span>
    </a>
</div>

<!-- Filtres -->
<div class="card" style="margin-bottom: 1.5rem;">
    <form action="{{ route('technicien.interventions') }}" method="GET" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
        @if(request('type'))
        <input type="hidden" name="type" value="{{ request('type') }}">
        @endif
        
        <div style="flex: 1; min-width: 250px;">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher par Équipement, Description..." style="width: 100%; padding: 0.75rem 1rem;">
        </div>
        <div style="min-width: 160px;">
            <select name="statut" style="width: 100%; padding: 0.75rem 1rem;">
                @if(request('type') == 'Maintenance')
                <option value="">Tous les statuts</option>
                <option value="planifiée" {{ request('statut') == 'planifiée' ? 'selected' : '' }}>⏳ Planifiée</option>
                <option value="confirmée_client" {{ request('statut') == 'confirmée_client' ? 'selected' : '' }}>✔️ Confirmée</option>
                <option value="en_cours" {{ request('statut') == 'en_cours' ? 'selected' : '' }}>🔄 En cours</option>
                <option value="terminée" {{ request('statut') == 'terminée' ? 'selected' : '' }}>✅ Terminée</option>
                @else
                <option value="">Tous les statuts</option>
                <option value="en cours" {{ request('statut') == 'en cours' ? 'selected' : '' }}>En cours</option>
                <option value="resolu" {{ request('statut') == 'resolu' ? 'selected' : '' }}>✅ Résolu & Terminé</option>
                <option value="en_attente_piece" {{ request('statut') == 'en_attente_piece' ? 'selected' : '' }}>📦 En attente de pièce</option>
                <option value="partiellement_resolu" {{ request('statut') == 'partiellement_resolu' ? 'selected' : '' }}>⚠️ Partiellement résolu</option>
                <option value="non_resolu" {{ request('statut') == 'non_resolu' ? 'selected' : '' }}>⛔ Non résolu / Bloqué</option>
                @endif
            </select>
        </div>
        <button type="submit" class="btn-primary">
            <i class="fa-solid fa-search"></i> Filtrer
        </button>
    </form>
</div>

@if(request('type') == 'Maintenance')
{{-- Tableau des Maintenances --}}
<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>N° Maintenance</th>
                    <th>Type</th>
                    <th>Équipement</th>
                    <th>Client / Site</th>
                    <th>Date Prévue</th>
                    <th>Statut</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($maintenances as $m)
                <tr>
                    <td><strong style="color: #8b5cf6;">{{ $m->numero_maintenance }}</strong></td>
                    <td>
                        @if($m->type_maintenance === 'préventive')
                        <span style="font-size: 0.8rem; color: #1d4ed8;">🛡️ Préventive</span>
                        @else
                        <span style="font-size: 0.8rem; color: #b45309;">🔧 Corrective</span>
                        @endif
                    </td>
                    <td>
                        <strong>{{ $m->equipement->equipement_nom ?? 'N/A' }}</strong><br>
                        <small style="color: #64748b;">{{ $m->equipement->equipement_code ?? '' }}</small>
                    </td>
                    <td>
                        <strong>{{ $m->client->nom ?? 'N/A' }}</strong><br>
                        <small style="color: #64748b;">
                            <i class="fa-solid fa-location-dot"></i>
                            {{ $m->site->nom_site ?? ($m->base->nom_base ?? 'N/A') }}
                        </small>
                    </td>
                    <td><code>{{ \Carbon\Carbon::parse($m->date_debut_prevue)->format('d/m/Y H:i') }}</code></td>
                    <td>
                        @if($m->statut === 'planifiée')
                            <span class="badge badge-warning">⏳ Planifiée</span>
                        @elseif($m->statut === 'confirmée_client')
                            <span class="badge badge-info">✔️ Confirmée</span>
                        @elseif($m->statut === 'en_cours')
                            <span class="badge" style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;">🔄 En Cours</span>
                        @elseif($m->statut === 'terminée')
                            @if($m->statut_validation_client === 'validé')
                                <span class="badge" style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;">✅ Validée Client</span>
                            @else
                                <span class="badge badge-success">✅ Terminée</span>
                            @endif
                        @endif
                    </td>
                    <td style="text-align: right;">
                        @if($m->statut === 'confirmée_client')
                            @php
                                $dateDebutPrevue = \Carbon\Carbon::parse($m->date_debut_prevue);
                                $maintenant = \Carbon\Carbon::now();
                                $peutDemarrer = $maintenant->gte($dateDebutPrevue);
                                $isChefDeCette = Auth::user()->isChefTechnicien()
                                    && (
                                        ($m->equipe && $m->equipe->chef_equipe == Auth::id())
                                        || ($m->equipes && $m->equipes->contains(fn($e) => $e->chef_equipe == Auth::id()))
                                    );
                            @endphp
                            @if($isChefDeCette)
                                @if($peutDemarrer)
                                <form action="{{ route('maintenances.demarrer', $m->id) }}" method="POST" style="display: inline; margin-right: 0.5rem;">
                                    @csrf
                                    <button type="submit" style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; padding: 0.5rem 0.75rem; border-radius: 0.5rem; font-size: 0.8rem; font-weight: 600; cursor: pointer;">
                                        <i class="fa-solid fa-play"></i> Démarrer
                                    </button>
                                </form>
                                @else
                                <button type="button" disabled style="background: #fef3c7; color: #92400e; border: 1px solid #fde68a; padding: 0.5rem 0.75rem; border-radius: 0.5rem; font-size: 0.8rem; font-weight: 600; cursor: not-allowed; opacity: 0.6; margin-right: 0.5rem;" title="Démarrage prévu le {{ $dateDebutPrevue->format('d/m/Y à H:i') }}">
                                    <i class="fa-solid fa-clock"></i> Programmé
                                </button>
                                @endif
                            @endif
                        @elseif($m->statut === 'en_cours')
                            @php
                                $isChefDeCette = Auth::user()->isChefTechnicien()
                                    && (
                                        ($m->equipe && $m->equipe->chef_equipe == Auth::id())
                                        || ($m->equipes && $m->equipes->contains(fn($e) => $e->chef_equipe == Auth::id()))
                                    );
                                $isTermineDeCette = ($m->nombre_equipements_prevus > 0 && $m->nombre_equipements_restants == 0);
                            @endphp
                            @if($isChefDeCette && $isTermineDeCette)
                            <a href="{{ route('maintenances.rapportForm', $m->id) }}" style="background: #f0fdf4; color: #047857; border: 1px solid #a7f3d0; padding: 0.5rem 0.75rem; border-radius: 0.5rem; font-size: 0.8rem; font-weight: 600; text-decoration: none; display: inline-block; margin-right: 0.5rem;">
                                <i class="fa-solid fa-flag-checkered"></i> Terminer
                            </a>
                            @endif
                        @endif
                        
                        <a href="{{ route('maintenances.show', $m->id) }}" style="background: #059669; color: #fff; padding: 0.5rem 0.75rem; border-radius: 0.5rem; text-decoration: none; font-size: 0.8rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.4rem;">
                            <i class="fa-solid fa-eye"></i> Voir
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 3rem; color: #94a3b8;">
                        <i class="fa-solid fa-screwdriver-wrench" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                        <p style="font-size: 1.1rem; font-weight: 600;">Aucune maintenance assignée</p>
                        <p style="font-size: 0.9rem;">Les maintenances qui vous seront attribuées apparaîtront ici</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($maintenances->hasPages())
<div style="margin-top: 1.5rem;">
    {{ $maintenances->links('vendor.pagination.custom') }}
</div>
@endif

@else
{{-- Tableau des Interventions (Installation & Dépannage) --}}

<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th># Ticket</th>
                    <th>Type</th>
                    <th>Équipement</th>
                    <th>Base</th>
                    <th>Description</th>
                    <th>Urgence</th>
                    <th>Date Prévue</th>
                    <th>Statut</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($depannages as $dep)
                <tr>
                    <td><code>#{{ $dep->id }}</code></td>
                    <td>
                        @if($dep->type_intervention === 'Dépannage')
                            <span class="badge badge-danger"><i class="fa-solid fa-wrench"></i> Dépannage</span>
                        @elseif($dep->type_intervention === 'Maintenance')
                            <span class="badge badge-warning"><i class="fa-solid fa-screwdriver-wrench"></i> Maintenance</span>
                        @elseif($dep->type_intervention === 'Installation')
                            <span class="badge badge-info"><i class="fa-solid fa-gears"></i> Installation</span>
                        @endif
                    </td>
                    <td><strong style="color: #059669;">{{ $dep->equipement_reference ?? 'N/A' }}</strong></td>
                    <td>{{ $dep->equipement->baseSite->nom_base ?? 'N/A' }}</td>
                    <td>{{ Str::limit($dep->description_panne, 40) }}</td>
                    <td>
                        @if($dep->urgence === 'Urgent')
                            <span class="badge badge-danger">🚨 Urgent</span>
                        @elseif($dep->urgence === 'Normal')
                            <span class="badge badge-warning">⚠️ Normal</span>
                        @else
                            <span class="badge badge-info">ℹ️ Faible</span>
                        @endif
                    </td>
                    <td>{{ $dep->date_prevue ?? '-' }}</td>
                    <td>
                        @if($dep->statut_rapport_technicien === 'rejete')
                            <span class="badge badge-danger"><i class="fa-solid fa-triangle-exclamation"></i> À corriger</span>
                        @elseif(in_array($dep->statut, ['resolu','résolu']) && $dep->statut_validation_finale_client === 'validé')
                            <span class="badge" style="background: #ecfdf5; color: #047857; border: 1.5px solid #a7f3d0;"><i class="fa-solid fa-circle-check"></i> ✅ Validé Client</span>
                        @elseif(in_array($dep->statut, ['resolu','résolu']))
                            <span class="badge badge-success"><i class="fa-solid fa-circle-check"></i> Résolu</span>
                        @elseif($dep->statut === 'non_resolu')
                            <span class="badge" style="background: #fef2f2; color: #b91c1c; border: 1.5px solid #fca5a5;">⛔ Non résolu</span>
                        @elseif($dep->statut === 'en_attente_piece')
                            <span class="badge" style="background: #eff6ff; color: #1e40af; border: 1.5px solid #bfdbfe;">📦 Att. pièce</span>
                        @elseif($dep->statut === 'partiellement_resolu')
                            <span class="badge" style="background: #fffbeb; color: #92400e; border: 1.5px solid #fde68a;">⚠️ Partiel</span>
                        @elseif($dep->statut_rapport_technicien === 'soumis' || $dep->statut_rapport_technicien === 'transmis_client')
                            <span class="badge badge-info"><i class="fa-solid fa-hourglass-half"></i> Rapport transmis</span>
                        @elseif($dep->statut === 'en cours')
                            <span class="badge badge-info"><i class="fa-solid fa-spinner"></i> En cours</span>
                        @else
                            <span class="badge badge-warning">En attente</span>
                        @endif
                    </td>
                    <td style="text-align: right;">
                        <a href="{{ route('depannages.show', $dep->id) }}" style="background: #059669; color: #fff; padding: 0.5rem 1rem; border-radius: 0.5rem; text-decoration: none; font-size: 0.85rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.4rem;">
                            <i class="fa-solid fa-eye"></i> Voir
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align: center; padding: 3rem; color: #94a3b8;">
                        <i class="fa-solid fa-clipboard-list" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                        <p style="font-size: 1.1rem; font-weight: 600;">Aucune intervention assignée</p>
                        <p style="font-size: 0.9rem;">Les interventions qui vous seront attribuées apparaîtront ici</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($depannages->hasPages())
<div style="margin-top: 1.5rem;">
    {{ $depannages->links('vendor.pagination.custom') }}
</div>
@endif
@endif
@endsection
