@extends('layouts.app')

@section('title', 'Registre Fiches Froid & Fluides (F-GAS)')

@section('content')
<div class="header">
    <div class="page-title">
        <h1>Registre des Fiches Froid & Fluides ({{ $totalCount }})</h1>
        <p>Traçabilité des fluides frigorigènes et des contrôles d'étanchéité réglementaires.</p>
    </div>
    <a href="{{ route('fiches-froid.create') }}" class="btn-primary" style="background: linear-gradient(135deg, #06b6d4, #38bdf8); color: #0f172a; padding: 0.75rem 1.25rem; border-radius: 0.5rem; text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 0.5rem;">
        <i class="fa-solid fa-plus-circle"></i> Nouvelle Fiche Froid
    </a>
</div>

<!-- Filtres et Recherche -->
<div class="card" style="margin-bottom: 1.5rem;">
    <form action="{{ route('fiches-froid.index') }}" method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: center;">
        <div style="flex: 1; min-width: 250px;">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher par Fréon, Équipement, Observations..." style="width: 100%; padding: 0.75rem 1rem; background-color: #0f172a; border: 1px solid #334155; border-radius: 0.5rem; color: #fff; outline: none;">
        </div>
        <div style="min-width: 180px;">
            <select name="freon" style="width: 100%; padding: 0.75rem 1rem; background-color: #0f172a; border: 1px solid #334155; border-radius: 0.5rem; color: #fff; outline: none;">
                <option value="">Tous les fréons</option>
                <option value="R410A" {{ request('freon') == 'R410A' ? 'selected' : '' }}>R410A</option>
                <option value="R32" {{ request('freon') == 'R32' ? 'selected' : '' }}>R32</option>
                <option value="R134a" {{ request('freon') == 'R134a' ? 'selected' : '' }}>R134a</option>
                <option value="R404A" {{ request('freon') == 'R404A' ? 'selected' : '' }}>R404A</option>
                <option value="R407C" {{ request('freon') == 'R407C' ? 'selected' : '' }}>R407C</option>
            </select>
        </div>
        <button type="submit" style="padding: 0.75rem 1.25rem; background-color: #334155; border: none; color: #fff; border-radius: 0.5rem; font-weight: 600; cursor: pointer;">
            <i class="fa-solid fa-filter"></i> Filtrer
        </button>
    </form>
</div>

<!-- Liste des Fiches Froid -->
<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Date Saisie</th>
                    <th>Équipement</th>
                    <th>Gaz / Fréon</th>
                    <th>État Filtres</th>
                    <th>Cuivre & Armaflex</th>
                    <th>Technicien</th>
                    <th>État Général</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($fichesFroid as $f)
                <tr>
                    <td>{{ $f->date_saisie ?? '-' }}</td>
                    <td>
                        <strong style="color: #38bdf8;">{{ $f->equipement->equipement_code ?? 'EQ-'.$f->equipement_id }}</strong>
                        <br><span style="color: #94a3b8; font-size: 0.8rem;">{{ $f->equipement->equipement_nom ?? '' }}</span>
                    </td>
                    <td><span class="badge badge-info">{{ $f->freon ?? 'N/A' }}</span></td>
                    <td>{{ $f->etat_filtres ?? '-' }}</td>
                    <td>{{ $f->cuivre ?? '-' }} / {{ $f->armaflex ?? '-' }}</td>
                    <td>
                        @if($f->technicien)
                        <span style="color: #10b981; font-weight: 600;"><i class="fa-solid fa-user-check"></i> {{ $f->technicien->nom_complet }}</span>
                        @else
                        <span style="color: #94a3b8;">N/A</span>
                        @endif
                    </td>
                    <td><span class="badge badge-success">{{ $f->etat_general ?? 'Conforme' }}</span></td>
                    <td style="text-align: right;">
                        <a href="{{ route('fiches-froid.show', $f->id) }}" style="background-color: rgba(6, 182, 212, 0.15); color: #06b6d4; padding: 0.4rem 0.75rem; border-radius: 0.375rem; text-decoration: none; font-size: 0.85rem; font-weight: 600;">
                            <i class="fa-solid fa-eye"></i> Consulter
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: #94a3b8; padding: 2rem;">Aucune fiche froid enregistrée.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div style="margin-top: 1.5rem;">
        {{ $fichesFroid->appends(request()->query())->links('vendor.pagination.custom') }}
    </div>
</div>
@endsection
