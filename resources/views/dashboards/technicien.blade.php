@extends('layouts.app')

@section('title', 'Espace Technicien')

@section('content')
<div class="header">
    <div class="page-title">
        <h1>Espace Technicien Terrain</h1>
        <p>Bienvenue {{ Auth::user()->nom_complet }} — Vos interventions et maintenances attribuées.</p>
    </div>
</div>

<!-- KPIs Technicien -->
<div class="stats-grid" style="grid-template-columns: repeat(3, 1fr); gap: 1.25rem; margin-bottom: 2rem;">
    <!-- Dépannages Attribués -->
    <div class="stat-card">
        <div class="stat-icon" style="background-color: #fef2f2; color: #dc2626;"><i class="fa-solid fa-wrench"></i></div>
        <div>
            <div class="stat-val">{{ $stats['total_depannages'] ?? $mesDepannages->count() }}</div>
            <div class="stat-label">Dépannages Attribués</div>
            <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;">
                En cours : <strong style="color: #dc2626;">{{ $stats['depannages_en_cours'] ?? $mesDepannages->where('statut', 'en cours')->count() }}</strong>
            </div>
        </div>
    </div>

    <!-- Installations Attribuées -->
    <div class="stat-card">
        <div class="stat-icon" style="background-color: #fffbeb; color: #d97706;"><i class="fa-solid fa-screwdriver"></i></div>
        <div>
            <div class="stat-val">{{ $stats['total_installations'] ?? $mesInstallations->count() }}</div>
            <div class="stat-label">Installations Attribuées</div>
            <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;">
                En cours : <strong style="color: #d97706;">{{ $stats['installations_en_cours'] ?? $mesInstallations->where('statut', 'en cours')->count() }}</strong>
            </div>
        </div>
    </div>

    <!-- Maintenances Actives -->
    <div class="stat-card">
        <div class="stat-icon" style="background-color: #faf5ff; color: #8b5cf6;"><i class="fa-solid fa-screwdriver-wrench"></i></div>
        <div>
            <div class="stat-val">{{ $stats['maintenances_actives'] }}</div>
            <div class="stat-label">Maintenances Actives</div>
            <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;">
                En cours : <strong style="color: #8b5cf6;">{{ $stats['maintenances_en_cours'] }}</strong> | Planifiées : {{ $stats['maintenances_planifiees'] }}
            </div>
        </div>
    </div>
</div>

<!-- Maintenances à Traiter (Planifiées, Confirmées ou En Cours) -->
@if($mesMaintenances->whereIn('statut', ['planifiée', 'confirmée_client', 'en_cours'])->count() > 0)
<div class="card" style="margin-bottom: 1.5rem; border-left: none !important;">
    <div class="card-header">
        <h3 class="card-title" style="color: #8b5cf6;"><i class="fa-solid fa-screwdriver-wrench"></i> Maintenances à Traiter</h3>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>N° Maintenance</th>
                    <th>Type</th>
                    <th>Équipement</th>
                    <th>Client</th>
                    <th>Date Prévue</th>
                    <th>Statut</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($mesMaintenances->whereIn('statut', ['planifiée', 'confirmée_client', 'en_cours']) as $m)
                <tr>
                    <td><strong style="color: #8b5cf6;">{{ $m->numero_maintenance }}</strong></td>
                    <td>
                        @if($m->type_maintenance === 'préventive')
                        <span style="font-size: 0.8rem; color: #1d4ed8;">🛡️ Préventive</span>
                        @else
                        <span style="font-size: 0.8rem; color: #b45309;">🔧 Corrective</span>
                        @endif
                    </td>
                    <td><strong>{{ $m->equipement->equipement_nom ?? 'N/A' }}</strong></td>
                    <td>{{ $m->client->nom ?? 'N/A' }}</td>
                    <td><code>{{ \Carbon\Carbon::parse($m->date_debut_prevue)->format('d/m/Y H:i') }}</code></td>
                    <td>
                        @if($m->statut === 'planifiée')
                            <span class="badge badge-warning">⏳ Planifiée</span>
                        @elseif($m->statut === 'confirmée_client')
                            <span class="badge badge-info">✔️ Confirmée</span>
                        @else
                            <span class="badge" style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;">🔄 En Cours</span>
                        @endif
                    </td>
                    <td style="text-align: right;">
                        @if(in_array($m->statut, ['planifiée', 'confirmée_client']))
                            @php
                                $dateDebutPrevue = \Carbon\Carbon::parse($m->date_debut_prevue);
                                $maintenant = \Carbon\Carbon::now();
                                $peutDemarrer = $maintenant->gte($dateDebutPrevue);
                                $isChefDeCette = Auth::user()->isChefTechnicien()
                                    && (
                                        ($m->equipe && $m->equipe->chef_equipe == Auth::id())
                                        || ($m->equipes && $m->equipes->contains(fn($e) => $e->chef_equipe == Auth::id()))
                                    );
                            @endphp
                            @if($isChefDeCette)
                                @if($peutDemarrer)
                                <form action="{{ route('maintenances.demarrer', $m->id) }}" method="POST" style="display: inline; margin-right: 0.5rem;">
                                    @csrf
                                    <button type="submit" style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; padding: 0.5rem 0.75rem; border-radius: 0.5rem; font-size: 0.85rem; font-weight: 700; cursor: pointer;">
                                        <i class="fa-solid fa-play"></i> Démarrer
                                    </button>
                                </form>
                                @else
                                <button type="button" disabled style="background: #fef3c7; color: #92400e; border: 1px solid #fde68a; padding: 0.5rem 0.75rem; border-radius: 0.5rem; font-size: 0.85rem; font-weight: 700; cursor: not-allowed; opacity: 0.6; margin-right: 0.5rem;" title="Démarrage prévu le {{ $dateDebutPrevue->format('d/m/Y à H:i') }}">
                                    <i class="fa-solid fa-clock"></i> Programmé
                                </button>
                                @endif
                            @endif
                        @elseif($m->statut === 'en_cours')
                            @php
                                $isChefDeCette = Auth::user()->isChefTechnicien()
                                    && (
                                        ($m->equipe && $m->equipe->chef_equipe == Auth::id())
                                        || ($m->equipes && $m->equipes->contains(fn($e) => $e->chef_equipe == Auth::id()))
                                    );
                                $isTermineDeCette = ($m->nombre_equipements_prevus > 0 && $m->nombre_equipements_restants == 0);
                            @endphp
                            @if($isChefDeCette && $isTermineDeCette)
                            <a href="{{ route('maintenances.rapportForm', $m->id) }}" style="background: #f0fdf4; color: #047857; border: 1px solid #a7f3d0; padding: 0.5rem 0.75rem; border-radius: 0.5rem; font-size: 0.85rem; font-weight: 700; text-decoration: none; display: inline-block; margin-right: 0.5rem;">
                                <i class="fa-solid fa-flag-checkered"></i> Terminer
                            </a>
                            @endif
                        @endif
                        
                        <a href="{{ route('maintenances.show', $m->id) }}" style="background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; padding: 0.5rem 0.75rem; border-radius: 0.5rem; text-decoration: none; font-size: 0.85rem; font-weight: 700;">
                            <i class="fa-solid fa-eye"></i> Voir
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- 2. DÉPANNAGES ATTRIBUÉS -->
<div class="card" style="margin-bottom: 1.5rem; border-left: none !important;">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3 class="card-title" style="color: #dc2626;"><i class="fa-solid fa-wrench"></i> Dépannages Attribués ({{ $mesDepannages->count() }})</h3>
        <a href="{{ route('technicien.interventions', ['type' => 'Dépannage']) }}" style="font-size: 0.85rem; color: #dc2626; text-decoration: none; font-weight: 600;">
            Voir tous les dépannages ({{ $mesDepannages->count() }}) →
        </a>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Réf. / N°</th>
                    <th>Équipement</th>
                    <th>Client / Site</th>
                    <th>Description de la Panne</th>
                    <th>Urgence</th>
                    <th>Date Demande</th>
                    <th>Statut</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($mesDepannages as $dep)
                <tr>
                    <td><strong style="color: #dc2626;">{{ $dep->equipement_reference ?? ('DEP-' . str_pad($dep->id, 5, '0', STR_PAD_LEFT)) }}</strong></td>
                    <td>
                        <div style="font-weight: 600; color: #1e293b;">{{ $dep->equipement->equipement_nom ?? 'N/A' }}</div>
                        @if($dep->equipement && $dep->equipement->zone)
                            <div style="font-size: 0.75rem; color: #64748b;">Zone: {{ $dep->equipement->zone->nom_zone }}</div>
                        @endif
                    </td>
                    <td>
                        <div style="font-weight: 600; color: #334155;">{{ $dep->client_nom ?? ($dep->equipement->client->nom ?? 'N/A') }}</div>
                        @if($dep->equipement && $dep->equipement->site)
                            <div style="font-size: 0.75rem; color: #64748b;"><i class="fa-solid fa-location-dot"></i> {{ $dep->equipement->site->nom_site }}</div>
                        @endif
                    </td>
                    <td>{{ Str::limit($dep->description_panne, 50) }}</td>
                    <td>
                        @if($dep->urgence === 'Urgent')
                            <span class="badge badge-danger"><i class="fa-solid fa-fire"></i> Urgent</span>
                        @else
                            <span class="badge badge-warning">{{ $dep->urgence ?? 'Normal' }}</span>
                        @endif
                    </td>
                    <td><code>{{ \Carbon\Carbon::parse($dep->date_demande)->format('d/m/Y') }}</code></td>
                    <td>
                        @if(in_array($dep->statut, ['résolu', 'resolu']))
                            @if($dep->statut_validation_finale_client === 'validé')
                                <span class="badge" style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;">✅ Validé Client</span>
                            @else
                                <span class="badge badge-success">Résolu</span>
                            @endif
                        @elseif($dep->statut === 'en cours')
                            <span class="badge badge-info">En cours</span>
                        @else
                            <span class="badge badge-warning">{{ ucfirst($dep->statut) }}</span>
                        @endif
                    </td>
                    <td style="text-align: right;">
                        @if($dep->statut_rapport_technicien === 'rejete')
                            <a href="{{ route('depannages.show', $dep->id) }}" style="background-color: #fef2f2; color: #be123c; border: 1px solid #fecdd3; padding: 0.4rem 0.75rem; border-radius: 0.5rem; text-decoration: none; font-size: 0.85rem; font-weight: 700;">
                                <i class="fa-solid fa-pen-to-square"></i> Corriger
                            </a>
                        @elseif(in_array($dep->statut, ['résolu', 'resolu']) && $dep->statut_rapport_technicien !== 'rejete')
                            <a href="{{ route('depannages.show', $dep->id) }}" style="background-color: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; padding: 0.4rem 0.75rem; border-radius: 0.5rem; text-decoration: none; font-size: 0.85rem; font-weight: 700;">
                                <i class="fa-solid fa-eye"></i> Voir
                            </a>
                        @else
                            <a href="{{ route('depannages.show', $dep->id) }}" style="background-color: #fef2f2; color: #dc2626; border: 1px solid #fecaca; padding: 0.4rem 0.75rem; border-radius: 0.5rem; text-decoration: none; font-size: 0.85rem; font-weight: 700;">
                                <i class="fa-solid fa-wrench"></i> Traiter
                            </a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: #94a3b8; padding: 2rem;">
                        <i class="fa-solid fa-circle-check" style="font-size: 2rem; color: #10b981; margin-bottom: 0.5rem; display: block; opacity: 0.5;"></i>
                        Aucun dépannage attribué pour le moment.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- 3. INSTALLATIONS ATTRIBUÉES -->
<div class="card" style="margin-bottom: 1.5rem; border-left: none !important;">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3 class="card-title" style="color: #d97706;"><i class="fa-solid fa-screwdriver"></i> Installations Attribuées ({{ $mesInstallations->count() }})</h3>
        <a href="{{ route('technicien.interventions', ['type' => 'Installation']) }}" style="font-size: 0.85rem; color: #d97706; text-decoration: none; font-weight: 600;">
            Voir toutes les installations ({{ $mesInstallations->count() }}) →
        </a>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Réf. / N°</th>
                    <th>Équipement</th>
                    <th>Client / Site</th>
                    <th>Description de l'Installation</th>
                    <th>Date Prévue</th>
                    <th>Date Demande</th>
                    <th>Statut</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($mesInstallations as $inst)
                <tr>
                    <td><strong style="color: #d97706;">{{ $inst->equipement_reference ?? ('INST-' . str_pad($inst->id, 5, '0', STR_PAD_LEFT)) }}</strong></td>
                    <td>
                        <div style="font-weight: 600; color: #1e293b;">{{ $inst->equipement->equipement_nom ?? 'N/A' }}</div>
                        @if($inst->equipement && $inst->equipement->zone)
                            <div style="font-size: 0.75rem; color: #64748b;">Zone: {{ $inst->equipement->zone->nom_zone }}</div>
                        @endif
                    </td>
                    <td>
                        <div style="font-weight: 600; color: #334155;">{{ $inst->client_nom ?? ($inst->equipement->client->nom ?? 'N/A') }}</div>
                        @if($inst->equipement && $inst->equipement->site)
                            <div style="font-size: 0.75rem; color: #64748b;"><i class="fa-solid fa-location-dot"></i> {{ $inst->equipement->site->nom_site }}</div>
                        @endif
                    </td>
                    <td>{{ Str::limit($inst->description_panne, 50) }}</td>
                    <td>
                        @if($inst->date_debut_prevue)
                            <span style="color: #d97706; font-weight: 600;"><i class="fa-solid fa-calendar-day"></i> {{ \Carbon\Carbon::parse($inst->date_debut_prevue)->format('d/m/Y') }}</span>
                        @elseif($inst->date_prevue)
                            <span style="color: #d97706; font-weight: 600;"><i class="fa-solid fa-calendar-day"></i> {{ \Carbon\Carbon::parse($inst->date_prevue)->format('d/m/Y') }}</span>
                        @else
                            <span style="color: #94a3b8;">Non définie</span>
                        @endif
                    </td>
                    <td><code>{{ \Carbon\Carbon::parse($inst->date_demande)->format('d/m/Y') }}</code></td>
                    <td>
                        @if(in_array($inst->statut, ['résolu', 'resolu']))
                            @if($inst->statut_validation_finale_client === 'validé')
                                <span class="badge" style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;">✅ Validé Client</span>
                            @else
                                <span class="badge badge-success">Terminée</span>
                            @endif
                        @elseif($inst->statut === 'en cours')
                            <span class="badge" style="background: #fffbeb; color: #b45309; border: 1px solid #fde68a;">En cours</span>
                        @else
                            <span class="badge badge-warning">{{ ucfirst($inst->statut) }}</span>
                        @endif
                    </td>
                    <td style="text-align: right;">
                        @if($inst->statut_rapport_technicien === 'rejete')
                            <a href="{{ route('depannages.show', $inst->id) }}" style="background-color: #fef2f2; color: #be123c; border: 1px solid #fecdd3; padding: 0.4rem 0.75rem; border-radius: 0.5rem; text-decoration: none; font-size: 0.85rem; font-weight: 700;">
                                <i class="fa-solid fa-pen-to-square"></i> Corriger
                            </a>
                        @elseif(in_array($inst->statut, ['résolu', 'resolu']) && $inst->statut_rapport_technicien !== 'rejete')
                            <a href="{{ route('depannages.show', $inst->id) }}" style="background-color: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; padding: 0.4rem 0.75rem; border-radius: 0.5rem; text-decoration: none; font-size: 0.85rem; font-weight: 700;">
                                <i class="fa-solid fa-eye"></i> Voir
                            </a>
                        @else
                            <a href="{{ route('depannages.show', $inst->id) }}" style="background-color: #fffbeb; color: #b45309; border: 1px solid #fde68a; padding: 0.4rem 0.75rem; border-radius: 0.5rem; text-decoration: none; font-size: 0.85rem; font-weight: 700;">
                                <i class="fa-solid fa-screwdriver"></i> Traiter
                            </a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: #94a3b8; padding: 2rem;">
                        <i class="fa-solid fa-circle-check" style="font-size: 2rem; color: #10b981; margin-bottom: 0.5rem; display: block; opacity: 0.5;"></i>
                        Aucune installation attribuée pour le moment.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
