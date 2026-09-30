@extends('layouts.app')

@section('title', 'Gestion des Clients')

@section('content')
<div class="header">
    <div class="page-title">
        <h1>Annuaire des Clients ({{ $totalClients }})</h1>
        <p>Gérez les entreprises clientes, leurs bases/sites et la création de leurs comptes d'accès.</p>
    </div>
    <a href="{{ route('clients.create') }}" class="btn-primary">
        <i class="fa-solid fa-building-circle-check"></i> Nouveau Client
    </a>
</div>

<!-- Filtre & Recherche -->
<div class="card" style="margin-bottom: 1.5rem;">
    <form action="{{ route('clients.index') }}" method="GET" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 250px;">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher par Code, Nom, E-mail, Ville..." style="width: 100%; padding: 0.75rem 1rem;">
        </div>
        <button type="submit" class="btn-primary">
            <i class="fa-solid fa-search"></i> Rechercher
        </button>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Nom Client / Raison Sociale</th>
                    <th>Téléphone</th>
                    <th>Ville & Pays</th>
                    <th>Sites Rattachés</th>
                    <th>Compte d'Accès</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clients as $c)
                <tr>
                    <td><strong style="color: #059669;">{{ $c->code ?? 'C-'.$c->id }}</strong></td>
                    <td><strong>{{ $c->nom }}</strong></td>
                    <td><code>{{ $c->telephone ?? 'N/A' }}</code></td>
                    <td>{{ $c->ville ?? 'Non renseigné' }} {{ $c->pays ? '('.$c->pays.')' : '' }}</td>
                    <td>
                        @php
                            // Compter TOUS les sites (via bases + directs)
                            $sitesViaBases = \App\Models\Site::whereHas('baseSite', function($q) use ($c) {
                                $q->where('client_id', $c->id);
                            })->count();
                            $sitesDirects = \App\Models\Site::where('client_id', $c->id)->whereNull('base_id')->count();
                            $totalSites = $sitesViaBases + $sitesDirects;
                        @endphp
                        @if($totalSites > 0)
                            <span class="badge badge-info" style="background-color: #dbeafe; color: #1e40af;">
                                <i class="fa-solid fa-map-marker-alt"></i> {{ $totalSites }} site(s)
                            </span>
                        @else
                            <span class="badge badge-secondary" style="background-color: #f1f5f9; color: #64748b;">Aucun site</span>
                        @endif
                    </td>
                    <td>
                        @if($c->utilisateurs && $c->utilisateurs->count() > 0)
                            <span class="badge badge-success"><i class="fa-solid fa-user-check"></i> {{ $c->utilisateurs->count() }} compte(s)</span>
                        @else
                            <a href="{{ route('utilisateurs.create') }}?client_id={{ $c->id }}&role=utilisateur" class="badge badge-warning" style="text-decoration: none;" title="Créer un compte d'accès pour ce client">
                                <i class="fa-solid fa-user-plus"></i> Sans compte (Créer)
                            </a>
                        @endif
                    </td>
                    <td style="text-align: right;">
                        <a href="{{ route('clients.show', $c->id) }}" style="background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; padding: 0.4rem 0.75rem; border-radius: 0.5rem; text-decoration: none; font-size: 0.85rem; font-weight: 700;">
                            <i class="fa-solid fa-eye"></i> Fiche Client
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: #94a3b8; padding: 2rem;">Aucun client enregistré.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 1.5rem;">
        {{ $clients->appends(request()->query())->links('vendor.pagination.custom') }}
    </div>
</div>
@endsection
