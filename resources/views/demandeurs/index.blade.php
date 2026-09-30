@extends('layouts.app')

@section('title', 'Mes Demandeurs')

@section('content')
<div class="header">
    <div class="page-title">
        <h1>Gestion des Demandeurs</h1>
        <p>Créez et gérez les comptes demandeurs de votre base</p>
    </div>
    <a href="{{ route('demandeurs.create') }}" class="btn-primary">
        <i class="fa-solid fa-plus"></i> Créer un Demandeur
    </a>
</div>

<!-- Recherche -->
<div class="card" style="margin-bottom: 1.5rem;">
    <form action="{{ route('demandeurs.index') }}" method="GET" style="display: flex; gap: 1rem; align-items: center;">
        <div style="flex: 1;">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher par nom, email, téléphone..." style="width: 100%; padding: 0.75rem 1rem;">
        </div>
        <button type="submit" class="btn-primary">
            <i class="fa-solid fa-search"></i> Rechercher
        </button>
        @if(request('search'))
        <a href="{{ route('demandeurs.index') }}" style="padding: 0.65rem 1.1rem; border-radius: 0.75rem; background-color: #f1f5f9; color: #64748b; text-decoration: none; font-weight: 700;">
            <i class="fa-solid fa-redo"></i>
        </a>
        @endif
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Nom Complet</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Sites Assignés</th>
                    <th>Statut</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($demandeurs as $demandeur)
                <tr>
                    <td>
                        <div style="font-weight: 700; color: #0f172a;">{{ $demandeur->nom_complet }}</div>
                    </td>
                    <td>
                        <a href="mailto:{{ $demandeur->email }}" style="color: #1d4ed8; text-decoration: none;">
                            {{ $demandeur->email }}
                        </a>
                    </td>
                    <td>
                        @if($demandeur->telephone)
                        <div style="display: inline-flex; align-items: center; gap: 0.4rem; color: #047857;">
                            <i class="fa-solid fa-phone"></i>
                            <strong>{{ $demandeur->telephone }}</strong>
                        </div>
                        @else
                        <span style="color: #cbd5e1;">—</span>
                        @endif
                    </td>
                    <td>
                        @php
                            $sitesCount = $demandeur->sitesAssignes->count();
                        @endphp
                        <span class="badge" style="background-color: #dbeafe; color: #1d4ed8; border: 1px solid #bfdbfe;">
                            <i class="fa-solid fa-location-dot"></i> {{ $sitesCount }} site(s)
                        </span>
                        @if($sitesCount > 0)
                        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;">
                            {{ $demandeur->sitesAssignes->pluck('nom_site')->take(3)->join(', ') }}
                            @if($sitesCount > 3)
                                <em>et {{ $sitesCount - 3 }} autre(s)...</em>
                            @endif
                        </div>
                        @endif
                    </td>
                    <td>
                        @if($demandeur->statut === 'actif')
                        <span class="badge" style="background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;">
                            <i class="fa-solid fa-check-circle"></i> Actif
                        </span>
                        @else
                        <span class="badge" style="background-color: #fee2e2; color: #dc2626; border: 1px solid #fecaca;">
                            <i class="fa-solid fa-times-circle"></i> Inactif
                        </span>
                        @endif
                    </td>
                    <td style="text-align: right;">
                        <div style="display: inline-flex; gap: 0.5rem;">
                            <a href="{{ route('demandeurs.show', $demandeur) }}" class="btn-primary" style="padding: 0.55rem 0.9rem; font-size: 0.85rem;">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <a href="{{ route('demandeurs.edit', $demandeur) }}" class="btn-primary" style="padding: 0.55rem 0.9rem; font-size: 0.85rem; background-color: #f59e0b;">
                                <i class="fa-solid fa-edit"></i>
                            </a>
                            <form action="{{ route('demandeurs.destroy', $demandeur) }}" method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce demandeur ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="padding: 0.55rem 0.9rem; font-size: 0.85rem; border: none; border-radius: 0.75rem; background-color: #dc2626; color: white; cursor: pointer; font-weight: 700;">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #94a3b8; padding: 3rem;">
                        <i class="fa-solid fa-user-plus" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                        <p style="font-weight: 700; margin-bottom: 0.5rem;">Aucun demandeur trouvé</p>
                        <p style="font-size: 0.9rem;">Créez votre premier demandeur avec le bouton ci-dessus</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 1.5rem;">
        {{ $demandeurs->appends(request()->query())->links('vendor.pagination.custom') }}
    </div>
</div>
@endsection
