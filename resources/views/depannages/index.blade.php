@extends('layouts.app')

@section('title', 'Gestion des Interventions & Dépannages')

@section('content')
<div class="header">
    <div class="page-title">
        <h1>
            @if(Auth::user()->type_utilisateur === 'utilisateur')
            Mes Demandes d'Intervention ({{ $totalCount }})
            @elseif(in_array(Auth::user()->type_utilisateur, ['technicien', 'chef technicien']))
            Mes Interventions Attribuées ({{ $totalCount }})
            @else
            Interventions & Dépannages Globaux ({{ $totalCount }})
            @endif
        </h1>
        <p>Suivi en temps réel des pannes signalées, des photos jointes, des urgences et des affectations.</p>
    </div>

    <a href="{{ route('depannages.create') }}" style="background: linear-gradient(135deg, #be123c, #e11d48); color: #ffffff; padding: 0.75rem 1.25rem; border-radius: 0.75rem; text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 0.5rem; box-shadow: 0 4px 10px rgba(190, 18, 60, 0.2);">
        <i class="fa-solid fa-plus"></i>
        @if(Auth::user()->type_utilisateur === 'utilisateur')
        Signaler une Panne
        @else
        Nouvelle Demande
        @endif
    </a>
</div>

<!-- Onglets par Type d'Intervention -->
<div style="display: flex; gap: 0.75rem; margin-bottom: 1.5rem;">
    <a href="{{ route('depannages.index', array_merge(request()->except('type'), ['type' => 'Installation'])) }}"
        style="padding: 0.85rem 1.5rem; text-decoration: none; font-weight: 700; font-size: 0.9rem; border-bottom: 3px solid transparent; transition: all 0.2s; display: flex; align-items: center; gap: 0.5rem; {{ request('type') == 'Installation' || (!request('type') && !request('statut') && !request('urgence') && !request('search')) ? 'border-bottom-color: #0ea5e9; color: #0369a1;' : 'color: #94a3b8;' }}">
        <i class="fa-solid fa-gears"></i>
        <span>Installations</span>
        <span style="background-color: {{ request('type') == 'Installation' || (!request('type') && !request('statut') && !request('urgence') && !request('search')) ? '#0ea5e9' : '#cbd5e1' }}; color: #ffffff; padding: 0.15rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 800; min-width: 24px; text-align: center;">
            {{ $installationsCount }}
        </span>
    </a>

    <a href="{{ route('depannages.index', array_merge(request()->except('type'), ['type' => 'Dépannage'])) }}"
        style="padding: 0.85rem 1.5rem; text-decoration: none; font-weight: 700; font-size: 0.9rem; border-bottom: 3px solid transparent; transition: all 0.2s; display: flex; align-items: center; gap: 0.5rem; {{ request('type') == 'Dépannage' ? 'border-bottom-color: #be123c; color: #be123c;' : 'color: #94a3b8;' }}">
        <i class="fa-solid fa-wrench"></i>
        <span>Dépannages</span>
        <span style="background-color: {{ request('type') == 'Dépannage' ? '#be123c' : '#cbd5e1' }}; color: #ffffff; padding: 0.15rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 800; min-width: 24px; text-align: center;">
            {{ $depannagesCount }}
        </span>
    </a>
</div>

<!-- Filtres dynamiques -->
<div class="card" style="margin-bottom: 1.5rem;">
    <form action="{{ route('depannages.index') }}" method="GET" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
        <!-- Champ caché pour conserver le type sélectionné -->
        @if(request('type'))
        <input type="hidden" name="type" value="{{ request('type') }}">
        @endif

        <div style="flex: 1; min-width: 250px;">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher par Équipement, Demandeur, Description..." style="width: 100%; padding: 0.75rem 1rem;">
        </div>
        <div style="min-width: 160px;">
            <select name="statut" style="width: 100%; padding: 0.75rem 1rem;">
                <option value="">Tous les statuts</option>
                <option value="en attente" {{ request('statut') == 'en attente' ? 'selected' : '' }}>En attente ({{ $attenteCount }})</option>
                <option value="en cours" {{ request('statut') == 'en cours' ? 'selected' : '' }}>En cours</option>
                <option value="resolu" {{ request('statut') == 'resolu' ? 'selected' : '' }}>✅ Résolu & Terminé</option>
                <option value="en_attente_piece" {{ request('statut') == 'en_attente_piece' ? 'selected' : '' }}>📦 En attente de pièce</option>
                <option value="partiellement_resolu" {{ request('statut') == 'partiellement_resolu' ? 'selected' : '' }}>⚠️ Partiellement résolu</option>
                <option value="non_resolu" {{ request('statut') == 'non_resolu' ? 'selected' : '' }}>⛔ Non résolu / Bloqué</option>
            </select>
        </div>
        <div style="min-width: 160px;">
            <select name="urgence" style="width: 100%; padding: 0.75rem 1rem;">
                <option value="">Toutes les urgences</option>
                <option value="Urgent" {{ request('urgence') == 'Urgent' ? 'selected' : '' }}>🚨 Urgent</option>
                <option value="Normal" {{ request('urgence') == 'Normal' ? 'selected' : '' }}>⚠️ Normal</option>
                <option value="Faible" {{ request('urgence') == 'Faible' ? 'selected' : '' }}>ℹ️ Faible</option>
            </select>
        </div>
        <button type="submit" class="btn-primary">
            <i class="fa-solid fa-search"></i> Filtrer
        </button>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th># Ticket</th>
                    <th>Type</th>
                    <th>Réf. Équipement</th>
                    <th>RI Soutarah</th>
                    <th>Panne / Description</th>
                    <th>Demandeur</th>
                    <th>Urgence</th>
                    <th>Date Souhaitée</th>
                    <th>Technicien</th>
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
                        @else
                        <span class="badge" style="background-color: #f1f5f9; color: #64748b;">N/A</span>
                        @endif
                    </td>
                    <td><strong style="color: #059669;">{{ $dep->equipement_reference ?? 'N/A' }}</strong></td>
                    <td>
                        @if(!empty($dep->ri_soutarah))
                        <span style="font-weight: 800; color: #1d4ed8; background: #eff6ff; padding: 0.2rem 0.5rem; border-radius: 0.3rem; border: 1.5px solid #bfdbfe; font-size: 0.85rem;">
                            {{ $dep->ri_soutarah }}
                        </span>
                        @else
                        <span style="color: #94a3b8; font-size: 0.85rem;">—</span>
                        @endif
                    </td>
                    <td>
                        {{ Str::limit($dep->description_panne, 45) }}
                        @if($dep->photo_panne || $dep->fichier_joint)
                        <span title="Fichier / Photo jointe" style="color: #059669; margin-left: 0.3rem;"><i class="fa-solid fa-paperclip"></i></span>
                        @endif
                    </td>
                    <td>{{ $dep->demandeur->nom_complet ?? $dep->demandeur_nom ?? 'Client' }}</td>
                    <td>
                        @if($dep->urgence === 'Urgent')
                        <span class="badge badge-danger"><i class="fa-solid fa-fire"></i> Urgent</span>
                        @elseif($dep->urgence === 'Normal')
                        <span class="badge badge-warning">Normal</span>
                        @else
                        <span class="badge badge-info">Faible</span>
                        @endif
                    </td>
                    <td>{{ $dep->technicien->nom_complet ?? 'Non assigné' }}</td>
                    <td>
                        @if($dep->statut_rapport_technicien === 'rejete')
                        <span class="badge badge-danger">Rapport à corriger</span>
                        @elseif(in_array($dep->statut, ['resolu','résolu']) && $dep->statut_validation_finale_client === 'validé')
                        <span class="badge badge-success">✅ Résolu & Clôturé</span>
                        @elseif($dep->statut === 'non_resolu')
                        <span class="badge" style="background: #fef2f2; color: #b91c1c; border: 1.5px solid #fca5a5;">⛔ Non résolu</span>
                        @elseif($dep->statut === 'en_attente_piece')
                        <span class="badge" style="background: #eff6ff; color: #1e40af; border: 1.5px solid #bfdbfe;">📦 Att. pièce</span>
                        @elseif($dep->statut === 'partiellement_resolu')
                        <span class="badge" style="background: #fffbeb; color: #92400e; border: 1.5px solid #fde68a;">⚠️ Partiel</span>
                        @elseif($dep->statut_rapport_technicien === 'soumis' || $dep->statut_rapport_technicien === 'transmis_client')
                        <span class="badge badge-info">Examen Rapport</span>
                        @elseif($dep->statut === 'en cours')
                        <span class="badge badge-info">En cours</span>
                        @else
                        <span class="badge badge-warning">En attente</span>
                        @endif
                    </td>
                    <td style="text-align: right;">
                        <a href="{{ route('depannages.show', $dep->id) }}" style="background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; padding: 0.4rem 0.75rem; border-radius: 0.5rem; text-decoration: none; font-size: 0.85rem; font-weight: 700;">
                            <i class="fa-solid fa-eye"></i> Voir Fiche
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" style="text-align: center; color: #94a3b8; padding: 2rem;">Aucune demande d'intervention trouvée.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 1.5rem;">
        {{ $depannages->appends(request()->query())->links('vendor.pagination.custom') }}
    </div>
</div>
@endsection