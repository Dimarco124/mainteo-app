@extends('layouts.app')

@section('title', 'Mes Emplacements')

@section('content')
<div class="header">
    <div class="page-title">
        <h1>Gestion de Mes Emplacements</h1>
        <p>Gérez les sous-sites, bureaux, villas et locaux d'intervention.</p>
    </div>
    @if(in_array(auth()->user()->type_utilisateur, ['admin', 'superviseur_client', 'superviseur_soutarah']))
    <a href="{{ route('zones.create') }}" class="btn-primary" style="background-color: #8b5cf6;">
        <i class="fa-solid fa-plus"></i> Nouveau Emplacement
    </a>
    @endif

</div>

<!-- Filtres et Recherche -->
<div class="card" style="margin-bottom: 1.5rem;">
    <form action="{{ route('zones.index') }}" method="GET" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 220px;">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher par nom, code, bureau..." style="width: 100%; padding: 0.75rem 1rem;">
        </div>
        
        <div style="min-width: 180px;">
            <select name="site_id" onchange="this.form.submit()" style="width: 100%; padding: 0.75rem 1rem;">
                <option value="">Tous les sites</option>
                @foreach($sites as $s)
                <option value="{{ $s->id }}" {{ request('site_id') == $s->id ? 'selected' : '' }}>{{ $s->nom_site }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn-primary" style="background-color: #8b5cf6;">
            <i class="fa-solid fa-search"></i> Rechercher
        </button>
        @if(request('search') || request('site_id'))
        <a href="{{ route('zones.index') }}" style="padding: 0.65rem 1.1rem; border-radius: 0.75rem; background-color: #f1f5f9; color: #64748b; text-decoration: none; font-weight: 700;">
            <i class="fa-solid fa-redo"></i>
        </a>
        @endif
    </form>
</div>

<!-- Notifications -->
@if(session('success'))
<div style="background-color: #d1fae5; padding: 1rem; margin-bottom: 1.5rem; border-radius: 0.75rem; border: 1px solid #a7f3d0;">
    <p style="margin: 0; color: #047857; font-weight: 700;">
        <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
    </p>
</div>
@endif

<!-- Tableau des Emplacements -->
<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Nom Emplacement</th>
                    <th>Code</th>
                    <th>Site parent</th>
                    <th>Base / Client</th>
                    <th>Équipements</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($zones as $z)
                <tr>
                    <td>
                        <strong>{{ $z->nom_zone }}</strong>
                        @if($z->observations)
                        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.2rem;">{{ Str::limit($z->observations, 40) }}</div>
                        @endif
                    </td>
                    <td>
                        @if($z->code_zone)
                        <span class="badge" style="background-color: #f3e8ff; color: #6b21a8; border: 1px solid #d8b4fe;">{{ $z->code_zone }}</span>
                        @else
                        <span style="color: #94a3b8;">-</span>
                        @endif
                    </td>
                    <td>
                        <strong style="color: #059669;"><i class="fa-solid fa-map-marker-alt"></i> {{ $z->site->nom_site ?? 'N/A' }}</strong>
                    </td>
                    <td>
                        @if($z->baseSite)
                            <strong>{{ $z->baseSite->nom_base }}</strong><br>
                            <small style="color: #64748b;">{{ $z->baseSite->client ? $z->baseSite->client->nom : '' }}</small>
                        @elseif($z->client)
                            <strong>{{ $z->client->nom }}</strong><br>
                            <small style="color: #64748b; font-style: italic;">Sans base</small>
                        @else
                            <span style="color: #94a3b8;">N/A</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-success" style="background-color: #e0e7ff; color: #3730a3; border: 1px solid #c7d2fe;">{{ $z->equipements_count }} équip.</span>
                    </td>
                    <td style="text-align: right;">
                        @if(in_array(auth()->user()->type_utilisateur, ['admin', 'superviseur_client']))
                        <a href="{{ route('zones.show', $z->id) }}" style="background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; padding: 0.4rem 0.75rem; border-radius: 0.5rem; text-decoration: none; font-size: 0.85rem; font-weight: 700; margin-right: 0.4rem;">
                            <i class="fa-solid fa-eye"></i> Voir
                        </a>
                        <a href="{{ route('zones.edit', $z->id) }}" style="background-color: #fffbeb; color: #b45309; border: 1px solid #fde68a; padding: 0.4rem 0.75rem; border-radius: 0.5rem; text-decoration: none; font-size: 0.85rem; font-weight: 700; margin-right: 0.4rem;">
                            <i class="fa-solid fa-pen"></i> Modifier
                        </a>
                        <form action="{{ route('zones.destroy', $z->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet emplacement ?');">
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
                    <td colspan="6" style="text-align: center; color: #94a3b8; padding: 2rem;">
                        <i class="fa-solid fa-inbox fa-2x" style="display: block; margin-bottom: 0.5rem; color: #cbd5e1;"></i>
                        Aucun emplacement trouvé.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($zones->hasPages())
    <div style="margin-top: 1.5rem;">
        {{ $zones->appends(request()->query())->links('vendor.pagination.custom') }}
    </div>
    @endif
</div>
@endsection
