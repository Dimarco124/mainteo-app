@extends('layouts.app')

@section('title', 'Gestion des Techniciens & Équipes')

@section('content')
<div class="header">
    <div class="page-title">
        <h1>Personnel & Techniciens Terrain ({{ $totalTechniciens }})</h1>
        <p>Annuaire des intervenants, gestion des équipes et spécialités techniques.</p>
    </div>
    <a href="{{ route('techniciens.create') }}" class="btn-primary" style="background: linear-gradient(135deg, #10b981, #059669); color: #ffffff; padding: 0.75rem 1.25rem; border-radius: 0.5rem; text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 0.5rem;">
        <i class="fa-solid fa-user-plus"></i> Nouveau Technicien
    </a>
</div>

<!-- Filtre & Recherche -->
<div class="card" style="margin-bottom: 1.5rem;">
    <form action="{{ route('techniciens.index') }}" method="GET" style="display: flex; gap: 1rem; align-items: center;">
        <div style="flex: 1;">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher par Nom, Prénom, Spécialité, Téléphone..." style="width: 100%; padding: 0.75rem 1rem; background-color: #0f172a; border: 1px solid #334155; border-radius: 0.5rem; color: #fff; outline: none;">
        </div>
        <button type="submit" style="padding: 0.75rem 1.25rem; background-color: #334155; border: none; color: #fff; border-radius: 0.5rem; font-weight: 600; cursor: pointer;">
            <i class="fa-solid fa-search"></i> Rechercher
        </button>
    </form>
</div>

<!-- Grille des Techniciens -->
<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Nom & Prénom</th>
                    <th>Rôle</th>
                    <th>Spécialité</th>
                    <th>Téléphone</th>
                    <th>E-mail</th>
                    <th>Équipe</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                @forelse($techniciens as $tech)
                <tr>
                    <td>
                        <strong style="color: #38bdf8;">{{ $tech->nom_complet }}</strong>
                    </td>
                    <td>
                        <span class="badge {{ $tech->isChefTechnicien() ? 'badge-amber' : 'badge-info' }}" style="background-color: {{ $tech->isChefTechnicien() ? 'rgba(245, 158, 11, 0.15)' : 'rgba(56, 189, 248, 0.15)' }}; color: {{ $tech->isChefTechnicien() ? '#f59e0b' : '#38bdf8' }};">
                            {{ $tech->type_utilisateur }}
                        </span>
                    </td>
                    <td>{{ $tech->specialite ?? 'CVC / Froid' }}</td>
                    <td><code>{{ $tech->telephone ?? 'N/A' }}</code></td>
                    <td>{{ $tech->email }}</td>
                    <td>{{ $tech->equipe->nom_equipe ?? 'Équipe Générale' }}</td>
                    <td>
                        <span class="badge badge-success">{{ $tech->statut ?? 'actif' }}</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: #94a3b8; padding: 2rem;">Aucun technicien trouvé.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div style="margin-top: 1.5rem;">
        {{ $techniciens->appends(request()->query())->links('vendor.pagination.custom') }}
    </div>
</div>
@endsection
