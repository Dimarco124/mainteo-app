@extends('layouts.app')

@section('title', 'Parc des Équipements')

@section('content')

@if(config('app.debug') && auth()->user()->type_utilisateur === 'superviseur_client')
<div style="background-color: #fef3c7; border: 2px solid #f59e0b; padding: 1rem; margin-bottom: 1rem; border-radius: 0.5rem;">
    <strong>🔍 DEBUG MODE:</strong> 
    Superviseur: {{ auth()->user()->nom }} {{ auth()->user()->prenom }} |
    Base ID: <strong>{{ auth()->user()->base_id ?? '❌ NULL (PROBLÈME!)' }}</strong> |
    Client ID: {{ auth()->user()->client_id ?? 'NULL' }}
    @if(!auth()->user()->base_id && auth()->user()->client_id)
        <br><span style="color: #dc2626; font-weight: bold;">⚠️ PROBLÈME DÉTECTÉ: Ce superviseur n'a pas de base_id assignée!</span>
        <br>→ Consulter le fichier <code>FIX_SUPERVISEUR_FILTRAGE.md</code> pour résoudre ce problème.
    @endif
</div>
@endif

<div class="header">
    <div class="page-title">
        <h1>Parc des Équipements ({{ $totalCount }})</h1>
        <p>Gérez le matériel, les groupes froid, CVC et les équipements de climatisation.</p>
    </div>
    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
        @if(in_array(auth()->user()->type_utilisateur, ['admin', 'superviseur_soutarah', 'superviseur_client']))
        <a href="{{ route('imports.equipements.export-existing') }}" class="btn-primary" style="background-color: #059669;" title="Exporter les équipements avec la colonne Emplacement vide">
            <i class="fa-solid fa-file-export"></i> Exporter (pour Emplacements)
        </a>
        @endif
        @if(auth()->user()->type_utilisateur === 'admin')
        <a href="{{ route('imports.equipements') }}" class="btn-primary" style="background-color: #8b5cf6;" title="Importer des équipements depuis Excel">
            <i class="fa-solid fa-file-import"></i> Importer Excel
        </a>
        @endif
        <a href="{{ route('equipements.create') }}" class="btn-primary">
            <i class="fa-solid fa-plus"></i> Ajouter un Équipement
        </a>
    </div>
</div>

<!-- Filtres de recherche standards -->
<div class="card" style="margin-bottom: 1.5rem;">
    <form action="{{ route('equipements.index') }}" method="GET" id="filterForm">
        
        <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
            @if(auth()->user()->type_utilisateur === 'admin' || auth()->user()->type_utilisateur === 'superviseur_soutarah')
            <div style="min-width: 200px;">
                <select name="client_id" style="width: 100%; padding: 0.75rem 1rem;">
                    <option value="">Tous les clients</option>
                    @foreach($allClients ?? [] as $client)
                    <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>{{ $client->nom }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div style="flex: 1; min-width: 250px;">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="🔍 Rechercher par Code, Nom, Marque..." style="width: 100%; padding: 0.75rem 1rem;">
            </div>
            <div style="min-width: 180px;">
                <select name="type" style="width: 100%; padding: 0.75rem 1rem;">
                    <option value="">Tous les types</option>
                    @foreach($allTypes as $t)
                    <option value="{{ $t }}" {{ request('type') == $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div style="min-width: 150px;">
                <select name="emplacement" style="width: 100%; padding: 0.75rem 1rem;">
                    <option value="">Tous emplacements</option>
                    <option value="interne" {{ request('emplacement') == 'interne' ? 'selected' : '' }}>Interne</option>
                    <option value="externe" {{ request('emplacement') == 'externe' ? 'selected' : '' }}>Externe</option>
                </select>
            </div>
            <button type="submit" class="btn-primary">
                <i class="fa-solid fa-search"></i> Filtrer
            </button>
            @if(request()->hasAny(['search', 'type', 'emplacement', 'client_id']))
            <a href="{{ route('equipements.index') }}" class="btn-secondary">
                <i class="fa-solid fa-times"></i> Réinitialiser
            </a>
            @endif
        </div>
    </form>
</div>

<!-- Cartes Statistiques -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.25rem; margin-bottom: 1.75rem;">
    
    <!-- Carte : Par Base (pour superviseurs avec base_id) -->
    @if(auth()->user()->type_utilisateur === 'superviseur_client' && auth()->user()->base_id && isset($statsByBase) && $statsByBase->isNotEmpty())
        <div class="card" style="padding: 1.5rem;">
            <h3 style="font-size: 0.95rem; font-weight: 700; color: #059669; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-map-marker-alt"></i> Par Base
            </h3>
            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                @foreach($statsByBase as $stat)
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 0.85rem; color: #64748b; font-weight: 600;">{{ $stat->nom }}</span>
                    <span style="font-size: 1.1rem; font-weight: 800; color: #059669;">{{ $stat->count }}</span>
                </div>
                @endforeach
            </div>
        </div>
    @endif
    
    <!-- Carte : Par Client (pour admin et superviseurs sans base OU superviseurs avec client_id) -->
    @if(
        (auth()->user()->type_utilisateur === 'admin' || 
         (auth()->user()->type_utilisateur === 'superviseur_client' && !auth()->user()->base_id && auth()->user()->client_id) ||
         auth()->user()->type_utilisateur === 'superviseur_soutarah') 
        && isset($statsByClient) && $statsByClient->isNotEmpty()
    )
        <div class="card" style="padding: 1.5rem;">
            <h3 style="font-size: 0.95rem; font-weight: 700; color: #059669; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-building"></i> Par Client
            </h3>
            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                @foreach($statsByClient as $stat)
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 0.85rem; color: #64748b; font-weight: 600;">{{ $stat->nom }}</span>
                    <span style="font-size: 1.1rem; font-weight: 800; color: #059669;">{{ $stat->count }}</span>
                </div>
                @endforeach
            </div>
        </div>
    @endif
    
    <!-- Carte : Par Type -->
    @if($statsByType->isNotEmpty())
    <div class="card" style="padding: 1.5rem;">
        <h3 style="font-size: 0.95rem; font-weight: 700; color: #3b82f6; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-layer-group"></i> Par Type
        </h3>
        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
            @foreach($statsByType as $stat)
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 0.85rem; color: #64748b; font-weight: 600;">{{ $stat->type }}</span>
                <span style="font-size: 1.1rem; font-weight: 800; color: #3b82f6;">{{ $stat->count }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif
    
    <!-- Carte : Par Emplacement -->
    @if($statsByEmplacement->isNotEmpty())
    <div class="card" style="padding: 1.5rem;">
        <h3 style="font-size: 0.95rem; font-weight: 700; color: #f59e0b; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-location-dot"></i> Par Emplacement
        </h3>
        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
            @foreach($statsByEmplacement as $stat)
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 0.85rem; color: #64748b; font-weight: 600; text-transform: capitalize;">{{ $stat->emplacement }}</span>
                <span style="font-size: 1.1rem; font-weight: 800; color: #f59e0b;">{{ $stat->count }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif
    
</div>

<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Nom / Désignation</th>
                    @if(auth()->user()->type_utilisateur === 'admin')
                    <th>Base</th>
                    @endif
                    <th>Type</th>
                    <th>Emplacement</th>
                    <th>Marque</th>
                    <th>État</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($equipements as $eq)
                <tr>
                    <td>
                        <strong style="color: #059669;">{{ $eq->equipement_code }}</strong>
                    </td>
                    <td><strong>{{ $eq->equipement_nom }}</strong></td>
                    @if(auth()->user()->type_utilisateur === 'admin')
                    <td>
                        @if($eq->site && $eq->site->baseSite)
                            <span class="badge badge-info">{{ $eq->site->baseSite->nom_base }}</span>
                        @else
                            <span style="color: #94a3b8; font-style: italic;">N/A</span>
                        @endif
                    </td>
                    @endif
                    <td><span class="badge badge-info">{{ $eq->type ?? 'N/A' }}</span></td>
                    <td>
                        @if($eq->zone)
                            <div style="font-weight: 700; color: #6b21a8; font-size: 0.85rem; margin-bottom: 0.25rem;">
                                <i class="fa-solid fa-layer-group"></i> {{ $eq->zone->nom_zone }}
                            </div>
                        @endif
                        @if($eq->emplacement === 'interne')
                            <span class="badge" style="background-color: #dbeafe; color: #1e40af; border: 1px solid #93c5fd;">
                                <i class="fa-solid fa-building"></i> Interne
                            </span>
                        @else
                            <span class="badge" style="background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a;">
                                <i class="fa-solid fa-tree"></i> Externe
                            </span>
                        @endif
                    </td>
                    <td>{{ $eq->marque ?? '-' }}</td>
                    <td>
                        @if($eq->etat === 'bon')
                            <span class="badge badge-success">Bon</span>
                        @elseif($eq->etat === 'mauvais' || $eq->etat === 'hors service')
                            <span class="badge badge-danger">{{ $eq->etat }}</span>
                        @else
                            <span class="badge badge-warning">{{ $eq->etat ?? 'N/A' }}</span>
                        @endif
                    </td>
                    <td style="text-align: right;">
                        <a href="{{ route('equipements.show', $eq->id) }}" style="background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; padding: 0.4rem 0.75rem; border-radius: 0.5rem; text-decoration: none; font-size: 0.85rem; font-weight: 700; margin-right: 0.4rem;">
                            <i class="fa-solid fa-eye"></i> Fiche
                        </a>
                        @if(in_array(auth()->user()->type_utilisateur, ['admin', 'superviseur_soutarah']))
                        <a href="{{ route('equipements.edit', $eq->id) }}" style="background-color: #fffbeb; color: #b45309; border: 1px solid #fde68a; padding: 0.4rem 0.75rem; border-radius: 0.5rem; text-decoration: none; font-size: 0.85rem; font-weight: 700; margin-right: 0.4rem;">
                            <i class="fa-solid fa-pen"></i>
                        </a>
                        <form action="{{ route('equipements.destroy', $eq->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet équipement ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background-color: #fef2f2; color: #dc2626; border: 1px solid #fecaca; padding: 0.4rem 0.75rem; border-radius: 0.5rem; font-size: 0.85rem; font-weight: 700; cursor: pointer;">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ auth()->user()->type_utilisateur === 'admin' ? '9' : '8' }}" style="text-align: center; color: #94a3b8; padding: 2rem;">Aucun équipement enregistré.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 1.5rem;">
        {{ $equipements->appends(request()->query())->links('vendor.pagination.custom') }}
    </div>
</div>
@endsection
