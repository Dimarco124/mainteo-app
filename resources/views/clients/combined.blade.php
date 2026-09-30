@extends('layouts.app')

@section('title', 'Entreprises, Bases & Sites')

@section('content')
<style>
    /* Onglets scrollables */
    .combined-tabs {
        display: flex;
        gap: 0.25rem;
        margin-bottom: 1.5rem;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        -ms-overflow-style: none;
        padding-bottom: 2px;
    }
    .combined-tabs::-webkit-scrollbar { display: none; }
    .combined-tab {
        padding: 0.85rem 1.25rem;
        text-decoration: none;
        font-weight: 700;
        font-size: 0.85rem;
        border-bottom: 3px solid transparent;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        white-space: nowrap;
        flex-shrink: 0;
    }
    .combined-tab-badge {
        color: #ffffff;
        padding: 0.15rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.72rem;
        font-weight: 800;
        min-width: 24px;
        text-align: center;
    }
    /* Filtres empilés sur mobile */
    .combined-filters-form {
        display: flex;
        gap: 1rem;
        align-items: center;
        flex-wrap: wrap;
    }
    /* Boutons d'action du header */
    .header-actions {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
    }
    @media (max-width: 768px) {
        .combined-filters-form {
            flex-direction: column;
            align-items: stretch;
        }
        .combined-filters-form > * {
            min-width: unset !important;
            width: 100%;
        }
        .combined-filters-form .btn-primary {
            width: 100%;
            justify-content: center;
        }
        .header-actions {
            flex-direction: column;
            width: 100%;
        }
        .header-actions .btn-primary,
        .header-actions a.btn-primary {
            width: 100%;
            justify-content: center;
            font-size: 0.75rem;
            padding: 0.5rem 0.85rem;
        }
    }
</style>
<div class="header">
    <div class="page-title">
        <h1>Entreprises, Bases, Sites & Emplacements</h1>
        <p>Gérez les entreprises clientes, leurs bases, sites d'intervention et emplacements (bureaux/locaux).</p>
    </div>
    <div class="header-actions">
        @if($view === 'entreprises')
        <a href="{{ route('imports.clients') }}" class="btn-primary" style="background-color: #10b981;" title="Importer des entreprises depuis Excel">
            <i class="fa-solid fa-file-import"></i> Importer Excel
        </a>
        <a href="{{ route('clients.create') }}" class="btn-primary">
            <i class="fa-solid fa-building-circle-check"></i> Nouvelle Entreprise
        </a>
        @elseif($view === 'bases')
        <a href="{{ route('imports.bases') }}" class="btn-primary" style="background-color: #0ea5e9;" title="Importer des bases depuis Excel">
            <i class="fa-solid fa-file-import"></i> Importer Excel
        </a>
        <a href="{{ route('bases-sites.create') }}" class="btn-primary">
            <i class="fa-solid fa-plus"></i> Nouvelle Base
        </a>
        @elseif($view === 'emplacements')
        <a href="{{ route('imports.emplacements') }}" class="btn-primary" style="background-color: #8b5cf6;" title="Importer des emplacements depuis Excel">
            <i class="fa-solid fa-file-import"></i> Importer Excel
        </a>
        <a href="{{ route('zones.create') }}" class="btn-primary">
            <i class="fa-solid fa-plus"></i> Nouveau Emplacement
        </a>
        @else
        <a href="{{ route('imports.sites') }}" class="btn-primary" style="background-color: #f59e0b;" title="Importer des sites depuis Excel">
            <i class="fa-solid fa-file-import"></i> Importer Excel
        </a>
        <a href="{{ route('sites.create') }}" class="btn-primary">
            <i class="fa-solid fa-plus"></i> Nouveau Site
        </a>
        @endif
    </div>
</div>

<!-- Onglets Entreprises / Bases / Sites / Emplacements -->
<div class="combined-tabs">
    <a href="{{ route('clients.combined', array_merge(request()->except('view'), ['view' => 'entreprises'])) }}"
       class="combined-tab"
       style="{{ $view === 'entreprises' ? 'border-bottom-color: #059669; color: #047857;' : 'color: #94a3b8;' }}">
        <i class="fa-solid fa-building"></i>
        <span>Entreprises</span>
        <span class="combined-tab-badge" style="background-color: {{ $view === 'entreprises' ? '#059669' : '#cbd5e1' }};">
            {{ $totalClients }}
        </span>
    </a>

    <a href="{{ route('clients.combined', array_merge(request()->except('view'), ['view' => 'bases'])) }}"
       class="combined-tab"
       style="{{ $view === 'bases' ? 'border-bottom-color: #0ea5e9; color: #0369a1;' : 'color: #94a3b8;' }}">
        <i class="fa-solid fa-map-location-dot"></i>
        <span>Bases</span>
        <span class="combined-tab-badge" style="background-color: {{ $view === 'bases' ? '#0ea5e9' : '#cbd5e1' }};">
            {{ $totalBases }}
        </span>
    </a>

    <a href="{{ route('clients.combined', array_merge(request()->except('view'), ['view' => 'sites'])) }}"
       class="combined-tab"
       style="{{ $view === 'sites' ? 'border-bottom-color: #f59e0b; color: #d97706;' : 'color: #94a3b8;' }}">
        <i class="fa-solid fa-map-marker-alt"></i>
        <span>Sites</span>
        <span class="combined-tab-badge" style="background-color: {{ $view === 'sites' ? '#f59e0b' : '#cbd5e1' }};">
            {{ $totalSites }}
        </span>
    </a>

    <a href="{{ route('clients.combined', array_merge(request()->except('view'), ['view' => 'emplacements'])) }}"
       class="combined-tab"
       style="{{ $view === 'emplacements' ? 'border-bottom-color: #8b5cf6; color: #6d28d9;' : 'color: #94a3b8;' }}">
        <i class="fa-solid fa-layer-group"></i>
        <span>Emplacements</span>
        <span class="combined-tab-badge" style="background-color: {{ $view === 'emplacements' ? '#8b5cf6' : '#cbd5e1' }};">
            {{ $totalEmplacements }}
        </span>
    </a>
</div>

<!-- Filtres -->
<div class="card" style="margin-bottom: 1.5rem;">
    <form action="{{ route('clients.combined') }}" method="GET" class="combined-filters-form">
        <input type="hidden" name="view" value="{{ $view }}">
        
        <div style="flex: 1; min-width: 250px;">
            <input type="text" name="search" value="{{ request('search') }}" 
                   placeholder="{{ $view === 'entreprises' ? 'Rechercher par Code, Nom, E-mail, Ville...' : ($view === 'bases' ? 'Rechercher par Code Base, Nom, Client...' : 'Rechercher par Code Site, Nom...') }}" 
                   style="width: 100%; padding: 0.75rem 1rem;">
        </div>
        
        @if($view === 'bases')
        <div style="min-width: 200px;">
            <select name="client_id" style="width: 100%; padding: 0.75rem 1rem;">
                <option value="">Tous les clients</option>
                @foreach($allClients as $cl)
                <option value="{{ $cl->id }}" {{ request('client_id') == $cl->id ? 'selected' : '' }}>{{ $cl->nom }}</option>
                @endforeach
            </select>
        </div>
        @endif
        
        @if($view === 'sites')
        <div style="min-width: 200px;">
            <select name="base_id" style="width: 100%; padding: 0.75rem 1rem;">
                <option value="">Toutes les bases</option>
                @foreach($allBases as $base)
                <option value="{{ $base->id }}" {{ request('base_id') == $base->id ? 'selected' : '' }}>{{ $base->nom_base }}</option>
                @endforeach
            </select>
        </div>
        <div style="min-width: 200px;">
            <select name="client_id" style="width: 100%; padding: 0.75rem 1rem;">
                <option value="">Tous les clients</option>
                @foreach($allClients as $cl)
                <option value="{{ $cl->id }}" {{ request('client_id') == $cl->id ? 'selected' : '' }}>{{ $cl->nom }}</option>
                @endforeach
            </select>
        </div>
        @endif

        @if($view === 'emplacements')
        {{-- Cascade Client → Base → Site --}}
        <div style="min-width: 180px;">
            <select id="filter-client" name="client_id" style="width: 100%; padding: 0.75rem 1rem;">
                <option value="">Tous les clients</option>
                @foreach($allClients as $cl)
                <option value="{{ $cl->id }}" {{ request('client_id') == $cl->id ? 'selected' : '' }}>{{ $cl->nom }}</option>
                @endforeach
            </select>
        </div>
        <div style="min-width: 180px;">
            <select id="filter-base" name="base_id" style="width: 100%; padding: 0.75rem 1rem;">
                <option value="">-- Base (selon client) --</option>
                @foreach($allBases as $base)
                <option value="{{ $base->id }}"
                    data-client="{{ $base->client_id }}"
                    {{ request('base_id') == $base->id ? 'selected' : '' }}>
                    {{ $base->nom_base }}
                </option>
                @endforeach
            </select>
        </div>
        <div style="min-width: 180px;">
            <select id="filter-site" name="site_id" style="width: 100%; padding: 0.75rem 1rem;">
                <option value="">-- Site (selon base/client) --</option>
                @foreach($allSites as $s)
                @php
                    $effectiveClientId = $s->client_id ?? ($s->baseSite ? $s->baseSite->client_id : null);
                @endphp
                <option value="{{ $s->id }}"
                    data-client="{{ $effectiveClientId }}"
                    data-base="{{ $s->base_id }}"
                    {{ request('site_id') == $s->id ? 'selected' : '' }}>
                    {{ $s->nom_site }}
                </option>
                @endforeach

            </select>
        @endif
        
        <button type="submit" class="btn-primary">
            <i class="fa-solid fa-search"></i> {{ $view === 'entreprises' ? 'Rechercher' : 'Filtrer' }}
        </button>
    </form>
</div>

@if($view === 'entreprises')
{{-- ═══════════════════════════════════════════════════════════════
     ONGLET ENTREPRISES
═══════════════════════════════════════════════════════════════ --}}
<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Nom Entreprise / Raison Sociale</th>
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
                        <a href="{{ route('clients.show', $c->id) }}" style="background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; padding: 0.4rem 0.75rem; border-radius: 0.5rem; text-decoration: none; font-size: 0.85rem; font-weight: 700; margin-right: 0.4rem;">
                            <i class="fa-solid fa-eye"></i> Voir
                        </a>
                        <a href="{{ route('clients.edit', $c->id) }}" style="background-color: #fffbeb; color: #b45309; border: 1px solid #fde68a; padding: 0.4rem 0.75rem; border-radius: 0.5rem; text-decoration: none; font-size: 0.85rem; font-weight: 700; margin-right: 0.4rem;">
                            <i class="fa-solid fa-pen"></i>
                        </a>
                        <form action="{{ route('clients.destroy', $c->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette entreprise ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background-color: #fef2f2; color: #dc2626; border: 1px solid #fecaca; padding: 0.4rem 0.75rem; border-radius: 0.5rem; font-size: 0.85rem; font-weight: 700; cursor: pointer;">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: #94a3b8; padding: 2rem;">Aucune entreprise enregistrée.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 1.5rem;">
        {{ $clients->appends(array_merge(request()->query(), ['view' => 'entreprises']))->links('vendor.pagination.custom') }}
    </div>
</div>

@elseif($view === 'bases')
{{-- ═══════════════════════════════════════════════════════════════
     ONGLET BASES
═══════════════════════════════════════════════════════════════ --}}
<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Code Base</th>
                    <th>Nom de la Base</th>
                    <th>Entreprise Propriétaire</th>
                    <th>Sites Rattachés</th>
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
                    <td><span class="badge badge-info">{{ $b->sites->count() }} site(s)</span></td>
                    <td><span class="badge badge-success">{{ $b->equipements->count() }} équip.</span></td>
                    <td style="text-align: right;">
                        <a href="{{ route('bases-sites.show', $b->id) }}" style="background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; padding: 0.4rem 0.75rem; border-radius: 0.5rem; text-decoration: none; font-size: 0.85rem; font-weight: 700; margin-right: 0.4rem;">
                            <i class="fa-solid fa-eye"></i> Voir
                        </a>
                        <a href="{{ route('bases-sites.edit', $b->id) }}" style="background-color: #fffbeb; color: #b45309; border: 1px solid #fde68a; padding: 0.4rem 0.75rem; border-radius: 0.5rem; text-decoration: none; font-size: 0.85rem; font-weight: 700; margin-right: 0.4rem;">
                            <i class="fa-solid fa-pen"></i>
                        </a>
                        <form action="{{ route('bases-sites.destroy', $b->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette base ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background-color: #fef2f2; color: #dc2626; border: 1px solid #fecaca; padding: 0.4rem 0.75rem; border-radius: 0.5rem; font-size: 0.85rem; font-weight: 700; cursor: pointer;">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #94a3b8; padding: 2rem;">Aucune base enregistrée.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 1.5rem;">
        {{ $bases->appends(array_merge(request()->query(), ['view' => 'bases']))->links('vendor.pagination.custom') }}
    </div>
</div>

@elseif($view === 'sites')
{{-- ═══════════════════════════════════════════════════════════════
     ONGLET SITES
═══════════════════════════════════════════════════════════════ --}}
<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Code Site</th>
                    <th>Nom du Site</th>
                    <th>Structure</th>
                    <th>Base / Entreprise</th>
                    <th>Équipements</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sites as $s)
                <tr>
                    <td><strong style="color: #059669;">{{ $s->code_site }}</strong></td>
                    <td><strong>{{ $s->nom_site }}</strong></td>
                    <td>
                        @if($s->baseSite)
                            <span class="badge badge-info" style="background-color: #dbeafe; color: #1e40af; border: 1px solid #93c5fd;">
                                <i class="fa-solid fa-sitemap"></i> Avec Base
                            </span>
                        @else
                            <span class="badge badge-warning" style="background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a;">
                                <i class="fa-solid fa-building"></i> Client Direct
                            </span>
                        @endif
                    </td>
                    <td>
                        @if($s->baseSite)
                            <strong>{{ $s->baseSite->nom_base }}</strong><br>
                            <small style="color: #64748b;">{{ $s->baseSite->client ? $s->baseSite->client->nom : 'N/A' }}</small>
                        @elseif($s->client)
                            <strong>{{ $s->client->nom }}</strong><br>
                            <small style="color: #64748b; font-style: italic;">Sans base</small>
                        @else
                            <span style="color: #94a3b8;">N/A</span>
                        @endif
                    </td>
                    <td><span class="badge badge-success">{{ $s->equipements->count() }} équip.</span></td>
                    <td style="text-align: right;">
                        <a href="{{ route('sites.show', $s->id) }}" style="background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; padding: 0.4rem 0.75rem; border-radius: 0.5rem; text-decoration: none; font-size: 0.85rem; font-weight: 700; margin-right: 0.4rem;">
                            <i class="fa-solid fa-eye"></i> Voir
                        </a>
                        <a href="{{ route('sites.edit', $s->id) }}" style="background-color: #fffbeb; color: #b45309; border: 1px solid #fde68a; padding: 0.4rem 0.75rem; border-radius: 0.5rem; text-decoration: none; font-size: 0.85rem; font-weight: 700; margin-right: 0.4rem;">
                            <i class="fa-solid fa-pen"></i>
                        </a>
                        <form action="{{ route('sites.destroy', $s->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce site ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background-color: #fef2f2; color: #dc2626; border: 1px solid #fecaca; padding: 0.4rem 0.75rem; border-radius: 0.5rem; font-size: 0.85rem; font-weight: 700; cursor: pointer;">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #94a3b8; padding: 2rem;">Aucun site enregistré.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 1.5rem;">
        {{ $sites->appends(array_merge(request()->query(), ['view' => 'sites']))->links('vendor.pagination.custom') }}
    </div>
</div>
@elseif($view === 'emplacements')
{{-- ═══════════════════════════════════════════════════════════════
     ONGLET EMPLACEMENTS (SOUS-SITES / BUREAUX / LOCAUX)
     ═══════════════════════════════════════════════════════════════ --}}
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
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($emplacements as $emp)
                <tr>
                    <td>
                        <strong>{{ $emp->nom_zone }}</strong>
                        @if($emp->observations)
                        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.2rem;">{{ Str::limit($emp->observations, 40) }}</div>
                        @endif
                    </td>
                    <td>
                        @if($emp->code_zone)
                            <span class="badge badge-info" style="background-color: #f3e8ff; color: #6b21a8; border: 1px solid #d8b4fe;">{{ $emp->code_zone }}</span>
                        @else
                            <span style="color: #94a3b8;">-</span>
                        @endif
                    </td>
                    <td>
                        <strong style="color: #059669;"><i class="fa-solid fa-map-marker-alt"></i> {{ $emp->site->nom_site ?? 'N/A' }}</strong>
                    </td>
                    <td>
                        @if($emp->baseSite)
                            <strong>{{ $emp->baseSite->nom_base }}</strong><br>
                            <small style="color: #64748b;">{{ $emp->baseSite->client ? $emp->baseSite->client->nom : '' }}</small>
                        @elseif($emp->client)
                            <strong>{{ $emp->client->nom }}</strong><br>
                            <small style="color: #64748b; font-style: italic;">Sans base</small>
                        @else
                            <span style="color: #94a3b8;">N/A</span>
                        @endif
                    </td>
                    <td><span class="badge badge-success" style="background-color: #e0e7ff; color: #3730a3; border: 1px solid #c7d2fe;">{{ $emp->equipements->count() }} équip.</span></td>
                    <td style="text-align: right;">
                        <a href="{{ route('zones.show', $emp->id) }}" style="background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; padding: 0.4rem 0.75rem; border-radius: 0.5rem; text-decoration: none; font-size: 0.85rem; font-weight: 700; margin-right: 0.4rem;">
                            <i class="fa-solid fa-eye"></i> Voir
                        </a>
                        <a href="{{ route('zones.edit', $emp->id) }}" style="background-color: #fffbeb; color: #b45309; border: 1px solid #fde68a; padding: 0.4rem 0.75rem; border-radius: 0.5rem; text-decoration: none; font-size: 0.85rem; font-weight: 700; margin-right: 0.4rem;">
                            <i class="fa-solid fa-pen"></i> Modifier
                        </a>
                        <form action="{{ route('zones.destroy', $emp->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet emplacement ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background-color: #fef2f2; color: #dc2626; border: 1px solid #fecaca; padding: 0.4rem 0.75rem; border-radius: 0.5rem; font-size: 0.85rem; font-weight: 700; cursor: pointer;">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
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

    <div style="margin-top: 1.5rem;">
        {{ $emplacements->appends(array_merge(request()->query(), ['view' => 'emplacements']))->links('vendor.pagination.custom') }}
    </div>
</div>
@endif

@if($view === 'emplacements')
<script>
(function() {
    const selClient = document.getElementById('filter-client');
    const selBase   = document.getElementById('filter-base');
    const selSite   = document.getElementById('filter-site');

    if (!selClient || !selBase || !selSite) return;

    // Cache toutes les options initiales
    const allBaseOpts = Array.from(selBase.options).slice(1); // skip "-- Base --"
    const allSiteOpts = Array.from(selSite.options).slice(1); // skip "-- Site --"

    function filterBases(clientId) {
        // Vider et repeupler selBase
        selBase.innerHTML = '<option value="">-- Toutes les bases --</option>';
        allBaseOpts.forEach(opt => {
            if (!clientId || opt.dataset.client == clientId) {
                selBase.appendChild(opt.cloneNode(true));
            }
        });
        // Restaurer valeur sélectionnée si encore présente
        const currentBase = '{{ request('base_id') }}';
        if (currentBase) {
            const match = selBase.querySelector(`option[value="${currentBase}"]`);
            if (match) match.selected = true;
        }
    }

    function filterSites(clientId, baseId) {
        selSite.innerHTML = '<option value="">-- Tous les sites --</option>';
        allSiteOpts.forEach(opt => {
            const okClient = !clientId || opt.dataset.client == clientId;
            const okBase   = !baseId   || opt.dataset.base   == baseId;
            if (okClient && okBase) {
                selSite.appendChild(opt.cloneNode(true));
            }
        });
        // Restaurer valeur sélectionnée si encore présente
        const currentSite = '{{ request('site_id') }}';
        if (currentSite) {
            const match = selSite.querySelector(`option[value="${currentSite}"]`);
            if (match) match.selected = true;
        }
    }

    // Événements
    selClient.addEventListener('change', function() {
        filterBases(this.value);
        filterSites(this.value, '');
        selBase.value = '';
        selSite.value = '';
    });

    selBase.addEventListener('change', function() {
        const clientId = selClient.value;
        filterSites(clientId, this.value);
        selSite.value = '';
    });

    // Init au chargement (restaure le filtrage depuis l'URL)
    const initClient = '{{ request('client_id') }}';
    const initBase   = '{{ request('base_id') }}';
    filterBases(initClient);
    filterSites(initClient, initBase);
    if (initBase) selBase.value = initBase;
    if ('{{ request('site_id') }}') selSite.value = '{{ request('site_id') }}';
})();
</script>
@endif

@endsection
