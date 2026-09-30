@extends('layouts.app')

@section('title', 'Gestion des Comptes')

@section('content')
    <div class="header">
        <div class="page-title">
            <h1>Gestion des Comptes d'Accès ({{ $totalCount }})</h1>
            <p>Gérez séparément le personnel Soutarah et le personnel des entreprises clientes.</p>
        </div>
        <a href="{{ route('utilisateurs.create') }}" class="btn-primary">
            <i class="fa-solid fa-user-plus"></i> Nouveau Compte
        </a>
    </div>

    <!-- Statistiques en cartes -->
    <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
        <div class="stat-card">
            <div style="flex: 1;">
                <div class="stat-val">{{ $totalSoutarah }}</div>
                <div class="stat-label">Personnel Soutarah</div>
            </div>
            <div class="stat-icon" style="background-color: #d1fae5; color: #047857;">
                <i class="fa-solid fa-building-shield"></i>
            </div>
        </div>

        <div class="stat-card">
            <div style="flex: 1;">
                <div class="stat-val">{{ $totalClient }}</div>
                <div class="stat-label">Personnel Client</div>
            </div>
            <div class="stat-icon" style="background-color: #dbeafe; color: #1d4ed8;">
                <i class="fa-solid fa-building-user"></i>
            </div>
        </div>

        <div class="stat-card">
            <div style="flex: 1;">
                <div class="stat-val">{{ $totalBasesCount }}</div>
                <div class="stat-label">Total des Bases</div>
            </div>
            <div class="stat-icon" style="background-color: #fef3c7; color: #b45309;">
                <i class="fa-solid fa-map-location-dot"></i>
            </div>
        </div>

        <div class="stat-card">
            <div style="flex: 1;">
                <div class="stat-val">{{ $clientsSansAssign }}</div>
                <div class="stat-label">Company sans Superviseur Soutarah</div>
            </div>
            <div class="stat-icon" style="background-color: #ffe4e6; color: #be123c;">
                <i class="fa-solid fa-user-tie"></i>
            </div>
        </div>
    </div>

    <!-- Onglets de catégorie -->
    <div class="card" style="padding: 0.75rem 1rem; margin-bottom: 1.5rem;">
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <a href="{{ route('utilisateurs.index', array_merge(request()->query(), ['categorie' => 'all'])) }}"
                style="padding: 0.5rem 1rem; border-radius: 0.6rem; font-weight: 700; font-size: 0.8rem; text-decoration: none; transition: all 0.2s;
                      {{ $categorie == 'all' ? 'background: linear-gradient(135deg, #059669, #10b981); color: #fff; box-shadow: 0 4px 10px rgba(5,150,105,0.2);' : 'background: #f1f5f9; color: #475569;' }}">
                <i class="fa-solid fa-layer-group"></i> Tous ({{ $totalCount }})
            </a>
            <a href="{{ route('utilisateurs.index', array_merge(request()->query(), ['categorie' => 'soutarah'])) }}"
                style="padding: 0.5rem 1rem; border-radius: 0.6rem; font-weight: 700; font-size: 0.8rem; text-decoration: none; transition: all 0.2s;
                      {{ $categorie == 'soutarah' ? 'background: linear-gradient(135deg, #059669, #10b981); color: #fff; box-shadow: 0 4px 10px rgba(5,150,105,0.2);' : 'background: #ecfdf5; color: #047857;' }}">
                <i class="fa-solid fa-building-shield"></i> 🏢 Soutarah ({{ $totalSoutarah }})
            </a>
            <a href="{{ route('utilisateurs.index', array_merge(request()->query(), ['categorie' => 'client'])) }}"
                style="padding: 0.5rem 1rem; border-radius: 0.6rem; font-weight: 700; font-size: 0.8rem; text-decoration: none; transition: all 0.2s;
                      {{ $categorie == 'client' ? 'background: linear-gradient(135deg, #2563eb, #3b82f6); color: #fff; box-shadow: 0 4px 10px rgba(37,99,235,0.2);' : 'background: #eff6ff; color: #1d4ed8;' }}">
                <i class="fa-solid fa-building-user"></i> 👥 Client ({{ $totalClient }})
            </a>
        </div>
    </div>

    <!-- Filtre & Recherche -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <form action="{{ route('utilisateurs.index') }}" method="GET"
            style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
            <input type="hidden" name="categorie" value="{{ request('categorie', 'all') }}">
            <div style="flex: 1; min-width: 250px;">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Rechercher par Nom, Prénom, E-mail..." style="width: 100%; padding: 0.75rem 1rem;">
            </div>
            <div style="min-width: 200px;">
                <select name="role" id="roleFilter" style="width: 100%; padding: 0.75rem 1rem;">
                    <option value="">Tous les rôles</option>
                    @if($categorie == 'all' || $categorie == 'soutarah')
                        <optgroup label="🏢 Soutarah">
                            <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>👑 Admin</option>
                            <option value="superviseur_soutarah" {{ request('role') == 'superviseur_soutarah' ? 'selected' : '' }}>👔 Superviseur Soutarah</option>
                            <option value="technicien" {{ request('role') == 'technicien' ? 'selected' : '' }}>🔧 Technicien
                            </option>
                        </optgroup>
                    @endif
                    @if($categorie == 'all' || $categorie == 'client')
                        <optgroup label="👥 Client">
                            <option value="superviseur_client" {{ request('role') == 'superviseur_client' ? 'selected' : '' }}>🔍
                                Superviseur Client (Base)</option>
                            <option value="demandeur" {{ request('role') == 'demandeur' ? 'selected' : '' }}>📝 Demandeur (Site)
                            </option>
                        </optgroup>
                    @endif
                </select>
            </div>
            <button type="submit" class="btn-primary">
                <i class="fa-solid fa-search"></i> Filtrer
            </button>
            @if(request('categorie') || request('search') || request('role'))
                <a href="{{ route('utilisateurs.index') }}"
                    style="padding: 0.65rem 1.1rem; background: #f1f5f9; color: #64748b; border-radius: 0.75rem; text-decoration: none; font-weight: 700; font-size: 0.8rem;">
                    <i class="fa-solid fa-times"></i> Réinitialiser
                </a>
            @endif
        </form>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Nom & Prénom</th>
                        <th>E-mail</th>
                        <th>Catégorie</th>
                        <th>Rôle</th>
                        <th>Affectation</th>
                        <th>Téléphone</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $u)
                        <tr>
                            <td><strong style="color: #059669;">{{ $u->nom_complet }}</strong></td>
                            <td>{{ $u->email }}</td>
                            <td>
                                @php
                                    $isSoutarah = in_array($u->type_utilisateur, ['admin', 'superviseur_soutarah', 'technicien', 'chef technicien', 'superviseur']);
                                @endphp
                                @if($isSoutarah)
                                    <span class="badge badge-success"
                                        style="background: #ecfdf5; color: #047857; border-color: #a7f3d0;">
                                        <i class="fa-solid fa-building-shield"></i> Soutarah
                                    </span>
                                @else
                                    <span class="badge badge-info"
                                        style="background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe;">
                                        <i class="fa-solid fa-building-user"></i> Client
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($u->type_utilisateur === 'admin')
                                    <span class="badge badge-danger">👑 Admin</span>
                                @elseif($u->type_utilisateur === 'superviseur_soutarah')
                                    <span class="badge badge-warning"
                                        style="background: #fffbeb; color: #92400e; border-color: #fde68a;">👔 Superviseur
                                        Soutarah</span>
                                @elseif($u->type_utilisateur === 'superviseur_client')
                                    <span class="badge badge-info"
                                        style="background: #dbeafe; color: #1e40af; border-color: #93c5fd;">🔍 Superviseur
                                        Client</span>
                                @elseif($u->type_utilisateur === 'demandeur')
                                    <span class="badge badge-info"
                                        style="background: #fef3c7; color: #92400e; border-color: #fcd34d;">📝 Demandeur</span>
                                @elseif($u->isChefTechnicien())
                                    <span class="badge badge-info"
                                        style="background: #fef3c7; color: #92400e; border-color: #fcd34d;">👷 Chef
                                        Technicien</span>
                                    @if($u->role_equipe_label)
                                        <div style="font-size: 0.68rem; color: #b45309; margin-top: 0.2rem;"><i
                                                class="fa-solid fa-users-gear"></i> {{ $u->role_equipe_label }}</div>
                                    @endif
                                @elseif($u->type_utilisateur === 'technicien' || $u->type_utilisateur === 'chef technicien')
                                    <span class="badge badge-info">🔧 Technicien</span>
                                    @if($u->role_equipe_label)
                                        <div style="font-size: 0.68rem; color: #0369a1; margin-top: 0.2rem;"><i
                                                class="fa-solid fa-users"></i> {{ $u->role_equipe_label }}</div>
                                    @endif
                                @endif
                            </td>
                            <td>
                                @if($u->client)
                                    <strong>🏢 {{ $u->client->nom }}</strong>
                                    @if($u->baseSite)
                                        <br><small style="color: #64748b;"><i class="fa-solid fa-location-dot"></i>
                                            {{ $u->baseSite->nom_base }}</small>
                                    @endif
                                @elseif($u->baseSite)
                                    <small style="color: #64748b;"><i class="fa-solid fa-location-dot"></i>
                                        {{ $u->baseSite->nom_base }}</small>
                                    @if($u->baseSite->client)
                                        <br><small style="color: #94a3b8;">({{ $u->baseSite->client->nom }})</small>
                                    @endif
                                @elseif($u->siteAssigne)
                                    <small style="color: #b45309;"><i class="fa-solid fa-map-pin"></i> Site:
                                        {{ $u->siteAssigne->nom_site }}</small>
                                    @if($u->siteAssigne->baseSite)
                                        <br><small style="color: #64748b;">Base: {{ $u->siteAssigne->baseSite->nom_base }}</small>
                                    @endif
                                @elseif($u->equipe)
                                    <small style="color: #b45309;"><i class="fa-solid fa-users-gear"></i>
                                        {{ $u->equipe->nom_equipe }}</small>
                                @elseif($u->assignment)
                                    <small style="color: #0369a1; font-weight: 600;">
                                        <i class="fa-solid fa-link"></i> Assigné à :
                                        @if($u->assignment->base)
                                            {{ $u->assignment->base->nom_base }} (Base)
                                        @elseif($u->assignment->client)
                                            {{ $u->assignment->client->nom }} (Company)
                                        @endif
                                    </small>
                                @else
                                    <span style="color: #94a3b8; font-size: 0.85rem;">— Interne —</span>
                                @endif
                            </td>
                            <td><code>{{ $u->telephone ?? 'N/A' }}</code></td>
                            <td style="text-align: right;">
                                <!-- Bouton Assigner Sites (pour demandeurs uniquement) -->
                                @if($u->type_utilisateur === 'demandeur')
                                <a href="{{ route('demandeurs.sites.edit', $u->id) }}"
                                    style="background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; padding: 0.4rem 0.75rem; border-radius: 0.5rem; text-decoration: none; font-size: 0.85rem; font-weight: 700; margin-right: 0.3rem;"
                                    title="Assigner des sites à ce demandeur">
                                    <i class="fa-solid fa-map-marker-alt"></i> Sites
                                </a>
                                @endif
                                
                                <a href="{{ route('utilisateurs.edit', $u->id) }}"
                                    style="background-color: #fffbeb; color: #b45309; border: 1px solid #fde68a; padding: 0.4rem 0.75rem; border-radius: 0.5rem; text-decoration: none; font-size: 0.85rem; font-weight: 700; margin-right: 0.3rem;">
                                    <i class="fa-solid fa-pen"></i> Modifier
                                </a>
                                @if(Auth::id() !== $u->id)
                                    <form action="{{ route('utilisateurs.destroy', $u->id) }}" method="POST"
                                        style="display: inline;"
                                        onsubmit="return confirm('Supprimer le compte de {{ $u->nom_complet }} ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            style="background-color: #fff1f2; color: #be123c; border: 1px solid #fecdd3; padding: 0.4rem 0.75rem; border-radius: 0.5rem; font-size: 0.85rem; font-weight: 700; cursor: pointer;">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: #94a3b8; padding: 2rem;">Aucun compte d'accès
                                trouvé.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 1.5rem;">
            {{ $users->appends(request()->query())->links('vendor.pagination.custom') }}
        </div>
    </div>
@endsection