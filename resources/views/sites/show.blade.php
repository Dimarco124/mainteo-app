@extends('layouts.app')

@section('title', 'Détails du Site - ' . $site->nom_site)

@section('content')
<div class="header">
    <div>
        <a href="{{ route('clients.combined', ['view' => 'sites']) }}" style="color: #94a3b8; text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.4rem; margin-bottom: 0.5rem;">
            <i class="fa-solid fa-arrow-left"></i> Retour
        </a>
        <h1><i class="fa-solid fa-map-marker-alt" style="color: #06b6d4;"></i> {{ $site->nom_site }} <span style="color: #64748b; font-size: 1rem;">({{ $site->code_site }})</span></h1>
    </div>
    @if(in_array(auth()->user()->type_utilisateur, ['admin', 'superviseur_soutarah']))
    <div style="display: flex; gap: 0.75rem;">
        <a href="{{ route('sites.edit', $site) }}" style="background-color: rgba(245, 158, 11, 0.15); color: #f59e0b; padding: 0.75rem 1.25rem; border-radius: 0.5rem; text-decoration: none; font-weight: 700;">
            <i class="fa-solid fa-pen"></i> Modifier
        </a>
    </div>
    @endif
</div>

<!-- Grille informations site -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
    <!-- Informations générales -->
    <div class="card">
        <h3 style="font-size: 1.1rem; font-weight: 700; color: #06b6d4; margin-bottom: 1.25rem;">
            <i class="fa-solid fa-info-circle"></i> Informations Générales
        </h3>
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.25rem;">
            <div>
                <p style="color: #94a3b8; font-size: 0.85rem;">Code Site</p>
                <p style="font-weight: 600; font-size: 1rem;"><code>{{ $site->code_site }}</code></p>
            </div>
            <div>
                <p style="color: #94a3b8; font-size: 0.85rem;">Nom du Site</p>
                <p style="font-weight: 600; font-size: 1rem;">{{ $site->nom_site }}</p>
            </div>
            <div>
                <p style="color: #94a3b8; font-size: 0.85rem;">Structure</p>
                <p style="font-weight: 600; font-size: 1rem;">
                    @if($site->baseSite)
                        <span class="badge badge-info">Avec Base</span>
                        <small style="color: #94a3b8; font-weight: 400;">(Client → Base → Site)</small>
                    @else
                        <span class="badge badge-warning">Client Direct</span>
                        <small style="color: #94a3b8; font-weight: 400;">(Client → Site)</small>
                    @endif
                </p>
            </div>
            @if($site->baseSite)
            <div>
                <p style="color: #94a3b8; font-size: 0.85rem;">Base</p>
                <p style="font-weight: 600; font-size: 1rem;">{{ $site->baseSite->nom_base }}</p>
            </div>
            @endif
            <div>
                <p style="color: #94a3b8; font-size: 0.85rem;">Client</p>
                <p style="font-weight: 600; font-size: 1rem;">
                    @if($site->baseSite && $site->baseSite->client)
                        {{ $site->baseSite->client->nom }}
                    @elseif($site->client)
                        {{ $site->client->nom }}
                    @else
                        N/A
                    @endif
                </p>
            </div>
            <div>
                <p style="color: #94a3b8; font-size: 0.85rem;">Adresse</p>
                <p style="font-weight: 600; font-size: 1rem;">{{ $site->adresse ?? '-' }}</p>
            </div>
            <div>
                <p style="color: #94a3b8; font-size: 0.85rem;">Ville</p>
                <p style="font-weight: 600; font-size: 1rem;">{{ $site->ville ?? '-' }}</p>
            </div>
            <div>
                <p style="color: #94a3b8; font-size: 0.85rem;">Téléphone</p>
                <p style="font-weight: 600; font-size: 1rem;">{{ $site->telephone ?? '-' }}</p>
            </div>
        </div>

        @if($site->observations)
        <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #334155;">
            <p style="color: #94a3b8; font-size: 0.85rem; margin-bottom: 0.4rem;">Observations</p>
            <p style="font-size: 0.95rem; line-height: 1.5;">{{ $site->observations }}</p>
        </div>
        @endif
    </div>

    <!-- Statistiques -->
    <div class="card">
        <h3 style="font-size: 1.1rem; font-weight: 700; color: #10b981; margin-bottom: 1.25rem;">
            <i class="fa-solid fa-chart-bar"></i> Statistiques
        </h3>
        
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <div style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); padding: 1.25rem; border-radius: 0.75rem; text-align: center;">
                <i class="fa-solid fa-tools" style="font-size: 2rem; color: rgba(255,255,255,0.9); margin-bottom: 0.5rem;"></i>
                <h2 style="font-size: 2rem; font-weight: 700; color: white; margin-bottom: 0.25rem;">{{ $site->equipements->count() }}</h2>
                <p style="color: rgba(255,255,255,0.8); font-size: 0.85rem;">Équipements</p>
            </div>

            <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 1.25rem; border-radius: 0.75rem; text-align: center;">
                <i class="fa-solid fa-users" style="font-size: 2rem; color: rgba(255,255,255,0.9); margin-bottom: 0.5rem;"></i>
                <h2 style="font-size: 2rem; font-weight: 700; color: white; margin-bottom: 0.25rem;">{{ $site->demandeurs->count() }}</h2>
                <p style="color: rgba(255,255,255,0.8); font-size: 0.85rem;">Demandeurs</p>
            </div>
        </div>
    </div>
</div>

<!-- Liste des équipements -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title" style="color: #06b6d4;"><i class="fa-solid fa-tools"></i> Équipements du Site ({{ $site->equipements->count() }})</h3>
    </div>
    @if($site->equipements->count() > 0)
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Nom Équipement</th>
                    <th>Type</th>
                    <th>État</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($site->equipements as $equipement)
                <tr>
                    <td><code>{{ $equipement->equipement_code }}</code></td>
                    <td style="font-weight: 600;">{{ $equipement->equipement_nom }}</td>
                    <td>{{ $equipement->type ?? '-' }}</td>
                    <td>
                        <span class="badge {{ $equipement->etat == 'Bon' ? 'badge-success' : 'badge-danger' }}">
                            {{ $equipement->etat ?? 'Non défini' }}
                        </span>
                    </td>
                    <td style="text-align: right;">
                        <a href="{{ route('equipements.show', $equipement) }}" style="background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; padding: 0.4rem 0.75rem; border-radius: 0.5rem; text-decoration: none; font-size: 0.85rem; font-weight: 700;">
                            <i class="fa-solid fa-eye"></i> Voir
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div style="padding: 2rem; text-align: center; color: #94a3b8;">
        <i class="fa-solid fa-info-circle" style="font-size: 2rem; margin-bottom: 0.5rem;"></i>
        <p>Aucun équipement associé à ce site.</p>
    </div>
    @endif
</div>

<!-- Liste des demandeurs -->
@if($site->demandeurs->count() > 0)
<div class="card">
    <div class="card-header">
        <h3 class="card-title" style="color: #10b981;"><i class="fa-solid fa-users"></i> Demandeurs Assignés ({{ $site->demandeurs->count() }})</h3>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Nom Complet</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                @foreach($site->demandeurs as $demandeur)
                <tr>
                    <td style="font-weight: 600;">{{ $demandeur->nom_complet }}</td>
                    <td>{{ $demandeur->email }}</td>
                    <td>{{ $demandeur->telephone ?? '-' }}</td>
                    <td>
                        <span class="badge {{ $demandeur->statut == 'actif' ? 'badge-success' : 'badge-secondary' }}">
                            {{ ucfirst($demandeur->statut) }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection
