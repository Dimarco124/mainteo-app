@extends('layouts.app')

@section('title', 'Bases & Sites')

@section('content')
<div class="header">
    <div class="page-title">
        <h1>Bases & Sites ({{ $totalCount }})</h1>
        <p>Répertoriez tous les sites d'intervention rattachés à vos clients.</p>
    </div>
    <a href="{{ route('bases-sites.create') }}" class="btn-primary">
        <i class="fa-solid fa-plus"></i> Ajouter un Site
    </a>
</div>

<!-- Filtres -->
<div class="card" style="margin-bottom: 1.5rem;">
    <form action="{{ route('bases-sites.index') }}" method="GET" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 250px;">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher par Code Base, Nom, Client..." style="width: 100%; padding: 0.75rem 1rem;">
        </div>
        <div style="min-width: 200px;">
            <select name="client_id" style="width: 100%; padding: 0.75rem 1rem;">
                <option value="">Tous les clients</option>
                @foreach($clients as $cl)
                <option value="{{ $cl->id }}" {{ request('client_id') == $cl->id ? 'selected' : '' }}>{{ $cl->nom }}</option>
                @endforeach
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
                    <th>Code Base</th>
                    <th>Nom du Site</th>
                    <th>Client Propriétaire</th>
                    <th>Équipements</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bases as $b)
                <tr>
                    <td><strong style="color: #059669;">{{ $b->code_base }}</strong></td>
                    <td><strong>{{ $b->nom_base }}</strong></td>
                    <td>{{ $b->client->nom ?? 'N/A' }}</td>
                    <td><span class="badge badge-success">{{ $b->equipements->count() }} équip.</span></td>
                    <td style="text-align: right;">
                        <a href="{{ route('bases-sites.show', $b->id) }}" style="background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; padding: 0.4rem 0.75rem; border-radius: 0.5rem; text-decoration: none; font-size: 0.85rem; font-weight: 700; margin-right: 0.4rem;">
                            <i class="fa-solid fa-eye"></i> Voir
                        </a>
                        <a href="{{ route('bases-sites.edit', $b->id) }}" style="background-color: #fffbeb; color: #b45309; border: 1px solid #fde68a; padding: 0.4rem 0.75rem; border-radius: 0.5rem; text-decoration: none; font-size: 0.85rem; font-weight: 700;">
                            <i class="fa-solid fa-pen"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #94a3b8; padding: 2rem;">Aucun site enregistré.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 1.5rem;">
        {{ $bases->appends(request()->query())->links('vendor.pagination.custom') }}
    </div>
</div>
@endsection
