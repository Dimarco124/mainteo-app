@extends('layouts.app')

@section('title', 'Fiche Client - ' . $client->nom)

@section('content')
<div class="header">
    <div>
        <a href="{{ route('clients.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.4rem; margin-bottom: 0.5rem;">
            <i class="fa-solid fa-arrow-left"></i> Revenir à l'annuaire
        </a>
        <h1>{{ $client->nom }} <span style="color: #059669; font-size: 1.1rem;">({{ $client->code ?? 'C-'.$client->id }})</span></h1>
    </div>
    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
        <a href="{{ route('utilisateurs.create') }}?client_id={{ $client->id }}&role=utilisateur" style="background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; padding: 0.75rem 1.25rem; border-radius: 0.75rem; text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-user-plus"></i> Créer Compte Utilisateur
        </a>
        <a href="{{ route('clients.edit', $client->id) }}" style="background-color: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; padding: 0.75rem 1.25rem; border-radius: 0.75rem; text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-pen"></i> Modifier Fiche
        </a>
        <a href="{{ route('bases-sites.create') }}?client_id={{ $client->id }}" class="btn-primary">
            <i class="fa-solid fa-plus"></i> Ajouter Base / Site
        </a>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
    <div>
        <!-- 1. Bases & Sites Déclarés -->
        <div class="card" style="margin-bottom: 1.5rem;">
            <h3 style="font-size: 1.1rem; font-weight: 800; color: #059669; margin-bottom: 1rem;">
                <i class="fa-solid fa-network-wired"></i> Bases & Sites Déclarés ({{ $client->bases->count() }})
            </h3>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Code Base</th>
                            <th>Nom du Site / Implantation</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($client->bases as $b)
                        <tr>
                            <td><strong style="color: #059669;">{{ $b->code_base }}</strong></td>
                            <td>{{ $b->nom_base }}</td>
                            <td>
                                <a href="{{ route('utilisateurs.create') }}?client_id={{ $client->id }}&base_id={{ $b->id }}&role=utilisateur" style="font-size: 0.8rem; color: #059669; font-weight: 700; text-decoration: none;">
                                    <i class="fa-solid fa-user-plus"></i> Compte sur ce site
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" style="text-align: center; color: #94a3b8; padding: 1.5rem;">Aucun site enregistré pour ce client.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 2. Dépannages Récents sur ce Client -->
        <div class="card">
            <h3 style="font-size: 1.1rem; font-weight: 800; color: #b45309; margin-bottom: 1rem;">
                <i class="fa-solid fa-wrench"></i> Dépannages Récents sur le Site
            </h3>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Réf. Équipement</th>
                            <th>Panne</th>
                            <th>Date</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($client->depannages as $d)
                        <tr>
                            <td><strong>{{ $d->equipement_reference }}</strong></td>
                            <td>{{ Str::limit($d->description_panne, 40) }}</td>
                            <td><code>{{ $d->date_demande }}</code></td>
                            <td><span class="badge badge-warning">{{ $d->statut }}</span></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" style="text-align: center; color: #94a3b8; padding: 1.5rem;">Aucun dépannage récent enregistré.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div>
        <!-- Coordonnées Client -->
        <div class="card">
            <h3 style="font-size: 1.1rem; font-weight: 800; color: #059669; margin-bottom: 1rem;">
                <i class="fa-solid fa-address-card"></i> Coordonnées & Contact
            </h3>
            <p style="color: #64748b; font-size: 0.85rem;">Téléphone</p>
            <p style="font-weight: 800; font-size: 1.05rem; margin-bottom: 0.75rem; color: #0f172a;">{{ $client->telephone ?? 'N/A' }}</p>

            <p style="color: #64748b; font-size: 0.85rem;">Adresse E-mail</p>
            <p style="font-weight: 600; margin-bottom: 0.75rem; color: #0f172a;">{{ $client->email ?? $client->mail ?? 'N/A' }}</p>

            <p style="color: #64748b; font-size: 0.85rem;">Adresse Géographique</p>
            <p style="font-weight: 600; margin-bottom: 0.75rem; color: #0f172a;">{{ $client->adresse ?? 'Non spécifiée' }}</p>

            <p style="color: #64748b; font-size: 0.85rem;">Ville & Pays</p>
            <p style="font-weight: 600; color: #0f172a;">{{ $client->ville ?? '-' }} {{ $client->pays ? '('.$client->pays.')' : '' }}</p>
        </div>
    </div>
</div>
@endsection
