@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-map-marker-alt"></i> Gestion des Sites</h4>
                    <a href="{{ route('sites.create') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-plus"></i> Nouveau Site
                    </a>
                </div>
                
                <div class="card-body">
                    <!-- Système de filtrage intelligent par onglets horizontaux -->
                    <div class="card" style="margin-bottom: 1.5rem; padding: 0; overflow: hidden; background: #0f172a; border: 1px solid #334155;">
                        <!-- Titre section -->
                        <div style="padding: 1rem 1.5rem; background: linear-gradient(135deg, #1e293b 0%, #334155 100%); border-bottom: 1px solid #475569;">
                            <h3 style="margin: 0; color: #e2e8f0; font-size: 0.95rem; font-weight: 600;">
                                <i class="fas fa-filter"></i> Filtrer par Base / Entreprise
                            </h3>
                        </div>

                        <!-- Onglets Bases -->
                        <div style="padding: 1rem; background: #0f172a;">
                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem;">
                                <span style="font-size: 0.85rem; color: #94a3b8; font-weight: 600; min-width: 80px;">
                                    <i class="fas fa-warehouse"></i> Bases:
                                </span>
                                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; flex: 1;">
                                    <button type="button" class="filter-pill {{ !request('base_id') ? 'active' : '' }}" onclick="filterByBase(null)">
                                        <i class="fas fa-globe"></i> Toutes
                                    </button>
                                    @foreach($bases as $base)
                                    <button type="button" class="filter-pill {{ request('base_id') == $base->id ? 'active' : '' }}" onclick="filterByBase({{ $base->id }})">
                                        {{ $base->nom_base }}
                                    </button>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Onglets Entreprises -->
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <span style="font-size: 0.85rem; color: #94a3b8; font-weight: 600; min-width: 80px;">
                                    <i class="fas fa-building"></i> Entreprises:
                                </span>
                                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; flex: 1;">
                                    <button type="button" class="filter-pill {{ !request('client_id') ? 'active' : '' }}" onclick="filterByClient(null)">
                                        <i class="fas fa-globe"></i> Toutes
                                    </button>
                                    @foreach($clients as $client)
                                    <button type="button" class="filter-pill {{ request('client_id') == $client->id ? 'active' : '' }}" onclick="filterByClient({{ $client->id }})">
                                        {{ $client->nom }}
                                    </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filtres de recherche -->
                    <form method="GET" action="{{ route('sites.index') }}" class="mb-4" id="siteFilterForm">
                        <input type="hidden" name="base_id" id="base_id_input" value="{{ request('base_id') }}">
                        <input type="hidden" name="client_id" id="client_id_input" value="{{ request('client_id') }}">
                        
                        <div class="row g-3">
                            <div class="col-md-8">
                                <input type="text" name="search" class="form-control" 
                                       placeholder="🔍 Rechercher un site..." 
                                       value="{{ request('search') }}">
                            </div>
                            
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-search"></i> Rechercher
                                </button>
                                @if(request()->hasAny(['search', 'base_id', 'client_id']))
                                <a href="{{ route('sites.index') }}" style="padding: 0.65rem 1.1rem; background: #f1f5f9; color: #64748b; border-radius: 0.75rem; text-decoration: none; font-weight: 700; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 0.5rem; border: 1px solid #cbd5e1;">
                                    <i class="fas fa-redo"></i> Réinitialiser
                                </a>
                                @endif
                            </div>
                        </div>
                    </form>

                    <style>
                    .filter-pill {
                        padding: 0.5rem 1rem;
                        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
                        border: 1px solid #475569;
                        border-radius: 2rem;
                        color: #cbd5e1;
                        font-size: 0.85rem;
                        font-weight: 500;
                        cursor: pointer;
                        transition: all 0.3s ease;
                        display: inline-flex;
                        align-items: center;
                        gap: 0.4rem;
                        white-space: nowrap;
                    }

                    .filter-pill:hover {
                        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
                        border-color: #3b82f6;
                        color: #ffffff;
                        transform: translateY(-2px);
                        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
                    }

                    .filter-pill.active {
                        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
                        border-color: #10b981;
                        color: #ffffff;
                        font-weight: 600;
                        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
                    }

                    .filter-pill i {
                        font-size: 0.8rem;
                    }
                    </style>

                    <script>
                    function filterByBase(baseId) {
                        document.getElementById('base_id_input').value = baseId || '';
                        document.getElementById('siteFilterForm').submit();
                    }

                    function filterByClient(clientId) {
                        document.getElementById('client_id_input').value = clientId || '';
                        document.getElementById('siteFilterForm').submit();
                    }
                    </script>

                    <!-- Tableau des sites -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Code</th>
                                    <th>Nom du Site</th>
                                    <th>Structure</th>
                                    <th>Base / Client</th>
                                    <th>Ville</th>
                                    <th>Équipements</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sites as $site)
                                    <tr>
                                        <td><span class="badge bg-info">{{ $site->code_site }}</span></td>
                                        <td><strong>{{ $site->nom_site }}</strong></td>
                                        <td>
                                            @if($site->baseSite)
                                                <span class="badge bg-primary">Avec Base</span>
                                            @else
                                                <span class="badge bg-warning text-dark">Client Direct</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($site->baseSite)
                                                <strong>{{ $site->baseSite->nom_base }}</strong><br>
                                                <small class="text-muted">{{ $site->baseSite->client ? $site->baseSite->client->nom : 'N/A' }}</small>
                                            @elseif($site->client)
                                                <strong>{{ $site->client->nom }}</strong><br>
                                                <small class="text-muted fst-italic">Sans base</small>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>{{ $site->ville ?? '-' }}</td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                {{ $site->equipements->count() }} équip.
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('sites.show', $site) }}" 
                                                   class="btn btn-info" title="Voir">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('sites.edit', $site) }}" 
                                                   class="btn btn-warning" title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('sites.destroy', $site) }}" 
                                                      method="POST" 
                                                      onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce site ?');"
                                                      style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger" title="Supprimer">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">Aucun site trouvé</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-3">
                        {{ $sites->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
