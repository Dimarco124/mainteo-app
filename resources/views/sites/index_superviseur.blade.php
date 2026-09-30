@extends('layouts.app')

@section('title', 'Mes Sites')

@section('content')
<div class="header">
    <div class="page-title">
        <h1>{{ isset($readOnly) && $readOnly ? 'Sites de ma Base' : 'Gestion de Mes Sites' }}</h1>
        @if(isset($base))
            <p>Base : <strong>{{ $base->nom_base }}</strong> - {{ $sites->total() }} site(s)</p>
        @elseif(isset($client))
            <p>Entreprise : <strong>{{ $client->nom }}</strong> (Structure directe) - {{ $sites->total() }} site(s)</p>
        @else
            <p>{{ $sites->total() }} site(s)</p>
        @endif
    </div>
    @if(!isset($readOnly) || !$readOnly)
    <a href="{{ route('sites.create') }}" class="btn-primary">
        <i class="fa-solid fa-plus"></i> Créer un Nouveau Site
    </a>
    @endif
</div>


<!-- Info Base ou Client -->
@if(isset($base))
<div class="card" style="margin-bottom: 1.5rem; background: linear-gradient(135deg, #eff6ff, #dbeafe); border: 2px solid #bfdbfe;">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <div style="margin-bottom: 0.5rem;">
                <span class="badge" style="background-color: #3b82f6; color: white; padding: 0.35rem 0.75rem; font-size: 0.75rem;">
                    <i class="fa-solid fa-sitemap"></i> Avec Base
                </span>
            </div>
            <h3 style="font-size: 1.1rem; font-weight: 800; color: #1e40af; margin-bottom: 0.5rem;">
                <i class="fa-solid fa-building"></i> {{ $base->nom_base }}
            </h3>
            <div style="display: flex; gap: 1.5rem; font-size: 0.85rem; color: #475569;">
                @if($base->client)
                <div><strong>Entreprise :</strong> {{ $base->client->nom }}</div>
                @endif
                @if($base->code_base)
                <div><strong>Code :</strong> <code>{{ $base->code_base }}</code></div>
                @endif
            </div>
        </div>
        <div style="text-align: right;">
            <div style="font-size: 2rem; font-weight: 800; color: #1d4ed8;">{{ $sites->total() }}</div>
            <div style="font-size: 0.85rem; color: #64748b;">Site(s) total</div>
        </div>
    </div>
</div>
@elseif(isset($client))
<div class="card" style="margin-bottom: 1.5rem; background: linear-gradient(135deg, #fef3c7, #fde68a); border: 2px solid #f59e0b;">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <div style="margin-bottom: 0.5rem;">
                <span class="badge" style="background-color: #f59e0b; color: white; padding: 0.35rem 0.75rem; font-size: 0.75rem;">
                    <i class="fa-solid fa-building"></i> Client Direct
                </span>
            </div>
            <h3 style="font-size: 1.1rem; font-weight: 800; color: #92400e; margin-bottom: 0.5rem;">
                <i class="fa-solid fa-briefcase"></i> {{ $client->nom }}
            </h3>
            <div style="display: flex; gap: 1.5rem; font-size: 0.85rem; color: #78350f;">
                @if($client->code)
                <div><strong>Code :</strong> <code>{{ $client->code }}</code></div>
                @endif
                <div><strong>Structure :</strong> Client → Site (sans base)</div>
            </div>
        </div>
        <div style="text-align: right;">
            <div style="font-size: 2rem; font-weight: 800; color: #d97706;">{{ $sites->total() }}</div>
            <div style="font-size: 0.85rem; color: #78350f;">Site(s) total</div>
        </div>
    </div>
</div>
@endif

<!-- Recherche -->
<div class="card" style="margin-bottom: 1.5rem;">
    <form action="{{ route('sites.index') }}" method="GET" style="display: flex; gap: 1rem; align-items: center;">
        <div style="flex: 1;">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher par nom, code site, adresse..." style="width: 100%; padding: 0.75rem 1rem;">
        </div>
        <button type="submit" class="btn-primary">
            <i class="fa-solid fa-search"></i> Rechercher
        </button>
        @if(request('search'))
        <a href="{{ route('sites.index') }}" style="padding: 0.65rem 1.1rem; border-radius: 0.75rem; background-color: #f1f5f9; color: #64748b; text-decoration: none; font-weight: 700;">
            <i class="fa-solid fa-redo"></i>
        </a>
        @endif
    </form>
</div>

<!-- Liste des Sites -->
<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Code Site</th>
                    <th>Nom du Site</th>
                    <th>Adresse</th>
                    <th>Équipements</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sites as $site)
                <tr>
                    <td>
                        <code style="background-color: #f0f9ff; color: #0369a1; padding: 0.35rem 0.7rem; border-radius: 0.5rem; font-weight: 700;">
                            {{ $site->code_site ?? 'N/A' }}
                        </code>
                    </td>
                    <td>
                        <div style="font-weight: 700; color: #0f172a;">{{ $site->nom_site }}</div>
                        @if($site->description)
                        <div style="font-size: 0.8rem; color: #64748b; margin-top: 0.25rem;">{{ Str::limit($site->description, 60) }}</div>
                        @endif
                    </td>
                    <td>
                        @if($site->adresse)
                        <div style="font-size: 0.85rem; color: #475569;">
                            <i class="fa-solid fa-location-dot"></i> {{ Str::limit($site->adresse, 50) }}
                        </div>
                        @if($site->ville)
                        <div style="font-size: 0.75rem; color: #94a3b8; margin-top: 0.25rem;">{{ $site->ville }}</div>
                        @endif
                        @else
                        <span style="color: #cbd5e1;">—</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge" style="background-color: #dbeafe; color: #1d4ed8; border: 1px solid #bfdbfe;">
                            <i class="fa-solid fa-toolbox"></i> {{ $site->equipements_count ?? 0 }} équipement(s)
                        </span>
                    </td>
                    <td style="text-align: right;">
                        <div style="display: inline-flex; gap: 0.5rem;">
                            <a href="{{ route('sites.show', $site) }}" class="btn-primary" style="padding: 0.55rem 0.9rem; font-size: 0.85rem;">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            @if(!isset($readOnly) || !$readOnly)
                            <a href="{{ route('sites.edit', $site) }}" class="btn-primary" style="padding: 0.55rem 0.9rem; font-size: 0.85rem; background-color: #f59e0b;">
                                <i class="fa-solid fa-edit"></i>
                            </a>
                            <form action="{{ route('sites.destroy', $site) }}" method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce site ? Tous les équipements associés seront également supprimés.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="padding: 0.55rem 0.9rem; font-size: 0.85rem; border: none; border-radius: 0.75rem; background-color: #dc2626; color: white; cursor: pointer; font-weight: 700;">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #94a3b8; padding: 3rem;">
                        <i class="fa-solid fa-map-marker-alt" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                        <p style="font-weight: 700; margin-bottom: 0.5rem;">Aucun site trouvé</p>
                        <p style="font-size: 0.9rem;">Créez votre premier site avec le bouton ci-dessus</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 1.5rem;">
        {{ $sites->appends(request()->query())->links('vendor.pagination.custom') }}
    </div>
</div>
@endsection
