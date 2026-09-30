@extends('layouts.app')

@section('title', 'Comptes-Rendus d\'Interventions')

@section('content')
<style>
    /* ── Onglets de navigation CR ── */
    .cr-tabs {
        display: flex;
        gap: 0.25rem;
        margin-bottom: 1.5rem;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        -ms-overflow-style: none;
        /* Petit indicateur visuel qu'on peut scroller */
        padding-bottom: 2px;
    }

    .cr-tabs::-webkit-scrollbar {
        display: none;
    }

    .cr-tab {
        padding: 0.85rem 1.25rem;
        text-decoration: none;
        font-weight: 700;
        font-size: 0.85rem;
        border-bottom: 3px solid transparent;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .cr-tab-badge {
        color: #ffffff;
        padding: 0.15rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.72rem;
        font-weight: 800;
        min-width: 24px;
        text-align: center;
    }

    /* ── Formulaire de filtres ── */
    .cr-filters-form {
        display: flex;
        gap: 1rem;
        align-items: center;
        flex-wrap: wrap;
    }

    .cr-filter-search {
        flex: 1;
        min-width: 220px;
    }

    .cr-filter-date {
        min-width: 150px;
    }

    /* ── Mobile ── */
    @media (max-width: 768px) {
        .cr-tab {
            padding: 0.7rem 1rem;
            font-size: 0.8rem;
        }

        .cr-filters-form {
            flex-direction: column;
            align-items: stretch;
            gap: 0.75rem;
        }

        .cr-filter-search,
        .cr-filter-date {
            min-width: unset;
            width: 100%;
        }

        .cr-filters-form .btn-primary {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<div class="header">
    <div class="page-title">
        <h1>Comptes-Rendus d'Interventions ({{ $totalCount }})</h1>
        <p>Consultez tous les rapports techniques des interventions résolues avec suivi détaillé.</p>
    </div>
</div>

{{-- ── Onglets par Type d'Intervention ── --}}
<div class="cr-tabs">
    <a href="{{ route('comptes-rendus.index', array_merge(request()->except('type'), [])) }}"
       class="cr-tab"
       style="{{ !request('type') ? 'border-bottom-color: #10b981; color: #047857;' : 'color: #94a3b8;' }}">
        <i class="fa-solid fa-list"></i>
        <span>Tous les CR</span>
        <span class="cr-tab-badge" style="background-color: {{ !request('type') ? '#10b981' : '#cbd5e1' }};">
            {{ $totalCount }}
        </span>
    </a>

    <a href="{{ route('comptes-rendus.index', array_merge(request()->except('type'), ['type' => 'Installation'])) }}"
       class="cr-tab"
       style="{{ request('type') == 'Installation' ? 'border-bottom-color: #0ea5e9; color: #0369a1;' : 'color: #94a3b8;' }}">
        <i class="fa-solid fa-gears"></i>
        <span>Installations</span>
        <span class="cr-tab-badge" style="background-color: {{ request('type') == 'Installation' ? '#0ea5e9' : '#cbd5e1' }};">
            {{ $installationsCount }}
        </span>
    </a>

    <a href="{{ route('comptes-rendus.index', array_merge(request()->except('type'), ['type' => 'Maintenance'])) }}"
       class="cr-tab"
       style="{{ request('type') == 'Maintenance' ? 'border-bottom-color: #f59e0b; color: #b45309;' : 'color: #94a3b8;' }}">
        <i class="fa-solid fa-screwdriver-wrench"></i>
        <span>Maintenances</span>
        <span class="cr-tab-badge" style="background-color: {{ request('type') == 'Maintenance' ? '#f59e0b' : '#cbd5e1' }};">
            {{ $maintenancesCount }}
        </span>
    </a>

    <a href="{{ route('comptes-rendus.index', array_merge(request()->except('type'), ['type' => 'Dépannage'])) }}"
       class="cr-tab"
       style="{{ request('type') == 'Dépannage' ? 'border-bottom-color: #be123c; color: #be123c;' : 'color: #94a3b8;' }}">
        <i class="fa-solid fa-wrench"></i>
        <span>Dépannages</span>
        <span class="cr-tab-badge" style="background-color: {{ request('type') == 'Dépannage' ? '#be123c' : '#cbd5e1' }};">
            {{ $depannagesCount }}
        </span>
    </a>
</div>

{{-- ── Filtres dynamiques ── --}}
<div class="card" style="margin-bottom: 1.5rem;">
    <form action="{{ route('comptes-rendus.index') }}" method="GET" class="cr-filters-form">
        @if(request('type'))
        <input type="hidden" name="type" value="{{ request('type') }}">
        @endif

        <div class="cr-filter-search">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Rechercher par Équipement, Rapport, Description..."
                   style="width: 100%; padding: 0.75rem 1rem;">
        </div>
        <div class="cr-filter-date">
            <input type="date" name="date_debut" value="{{ request('date_debut') }}"
                   placeholder="Date début" style="width: 100%; padding: 0.75rem 1rem;">
        </div>
        <div class="cr-filter-date">
            <input type="date" name="date_fin" value="{{ request('date_fin') }}"
                   placeholder="Date fin" style="width: 100%; padding: 0.75rem 1rem;">
        </div>
        <button type="submit" class="btn-primary">
            <i class="fa-solid fa-search"></i> Filtrer
        </button>
    </form>
</div>

{{-- ── Tableau ── --}}
<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th># Ticket</th>
                    <th>Type</th>
                    <th>Réf. Équipement</th>
                    <th>Problème Signalé</th>
                    <th>Compte-Rendu Technicien</th>
                    <th>Technicien</th>
                    <th>Date Résolution</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rapports as $rapport)
                <tr>
                    @if($isMaintenanceView ?? false)
                        {{-- Affichage pour les Maintenances --}}
                        <td><code>{{ $rapport->numero_maintenance }}</code></td>
                        <td>
                            <span class="badge badge-warning"><i class="fa-solid fa-screwdriver-wrench"></i> Maintenance</span>
                        </td>
                        <td><strong style="color: #059669;">{{ $rapport->equipement->equipement_nom ?? 'N/A' }}</strong></td>
                        <td>{{ Str::limit($rapport->description, 60) }}</td>
                        <td>{{ Str::limit($rapport->rapport_technicien, 80) }}</td>
                        <td>
                            @if($rapport->technicien)
                                {{ $rapport->technicien->nom_complet }}
                            @elseif($rapport->equipe)
                                Équipe {{ $rapport->equipe->nom_equipe }}
                            @else
                                Non assigné
                            @endif
                        </td>
                        <td><code>{{ $rapport->date_fin_reelle ? \Carbon\Carbon::parse($rapport->date_fin_reelle)->format('d/m/Y') : 'N/A' }}</code></td>
                        <td style="text-align: right;">
                            <a href="{{ route('maintenances.show', $rapport->id) }}"
                               style="background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; padding: 0.4rem 0.75rem; border-radius: 0.5rem; text-decoration: none; font-size: 0.85rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.4rem; white-space: nowrap;">
                                <i class="fa-solid fa-eye"></i> Voir Fiche
                            </a>
                        </td>
                    @else
                        {{-- Affichage pour les Interventions (Dépannage/Installation) --}}
                        <td><code>#{{ $rapport->id }}</code></td>
                        <td>
                            @if($rapport->type_intervention === 'Dépannage')
                                <span class="badge badge-danger"><i class="fa-solid fa-wrench"></i> Dépannage</span>
                            @elseif($rapport->type_intervention === 'Maintenance')
                                <span class="badge badge-warning"><i class="fa-solid fa-screwdriver-wrench"></i> Maintenance</span>
                            @elseif($rapport->type_intervention === 'Installation')
                                <span class="badge badge-info"><i class="fa-solid fa-gears"></i> Installation</span>
                            @endif
                        </td>
                        <td><strong style="color: #059669;">{{ $rapport->equipement_reference ?? 'N/A' }}</strong></td>
                        <td>{{ Str::limit($rapport->description_panne, 60) }}</td>
                        <td>{{ Str::limit($rapport->rapport, 80) }}</td>
                        <td>
                            @if($rapport->technicien)
                                {{ $rapport->technicien->nom_complet }}
                            @elseif($rapport->equipe)
                                Équipe {{ $rapport->equipe->nom_equipe }}
                            @else
                                Non assigné
                            @endif
                        </td>
                        <td><code>{{ \Carbon\Carbon::parse($rapport->date_realisation)->format('d/m/Y') }}</code></td>
                        <td style="text-align: right;">
                            <a href="{{ route('depannages.show', $rapport->id) }}"
                               style="background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; padding: 0.4rem 0.75rem; border-radius: 0.5rem; text-decoration: none; font-size: 0.85rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.4rem; white-space: nowrap;">
                                <i class="fa-solid fa-eye"></i> Voir Fiche
                            </a>
                        </td>
                    @endif
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: #94a3b8; padding: 2rem;">Aucun compte-rendu trouvé.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 1.5rem;">
        {{ $rapports->appends(request()->query())->links('vendor.pagination.custom') }}
    </div>
</div>
@endsection
