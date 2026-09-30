@extends('layouts.app')

@section('title', 'Détail Maintenance')

@section('content')
<div class="header">
    <div>
        <a href="{{ route('planning.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.4rem; margin-bottom: 0.5rem;">
            <i class="fa-solid fa-arrow-left"></i> Retour au Planning
        </a>
        <h1>Maintenance {{ $maintenance->numero_maintenance }}</h1>
    </div>
    
    {{-- Actions selon le rôle et le statut --}}
    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
        {{-- CHEF D'ÉQUIPE : Saisir Compte Rendu Journalier (SEULEMENT si maintenance démarrée) --}}
        @php
            $isChefEquipe = Auth::user()->isChefTechnicien() 
                && (
                    ($maintenance->equipe && $maintenance->equipe->chef_equipe == Auth::id())
                    || ($maintenance->equipes && $maintenance->equipes->contains(fn($e) => $e->chef_equipe == Auth::id()))
                );
        @endphp
        
        @if($isChefEquipe && $maintenance->statut === 'en_cours')
        <a href="{{ route('comptes-rendus.create', $maintenance->id) }}" class="btn-primary" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%); font-weight: 700; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);">
            <i class="fa-solid fa-file-pen"></i> + Saisir Compte Rendu Journalier
        </a>
        @endif

        {{-- SUPERVISEUR CLIENT : Accuser Réception --}}
        @if(Auth::user()->isSuperviseurClient() && $maintenance->statut === 'planifiée')
        <form action="{{ route('maintenances.confirmer', $maintenance->id) }}" method="POST">
            @csrf
            <button type="submit" class="btn-primary" style="background: #10b981;">
                <i class="fa-solid fa-check"></i> Accuser Réception (Optionnel)
            </button>
        </form>
        @endif
        
        {{-- CHEF D'ÉQUIPE : Démarrer la Maintenance (seulement si date atteinte) --}}
        @php
            $dateDebutPrevue = \Carbon\Carbon::parse($maintenance->date_debut_prevue);
            $aujourdhui = \Carbon\Carbon::now();
            $peutDemarrer = $aujourdhui->greaterThanOrEqualTo($dateDebutPrevue);
        @endphp
        
        @if($isChefEquipe && in_array($maintenance->statut, ['planifiée', 'confirmée_client']))
            @if($peutDemarrer)
            <form action="{{ route('maintenances.demarrer', $maintenance->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-play"></i> Démarrer la Maintenance
                </button>
            </form>
            @else
            <button type="button" class="btn-primary" disabled style="opacity: 0.5; cursor: not-allowed;" title="Disponible le {{ $dateDebutPrevue->format('d/m/Y à H:i') }}">
                <i class="fa-solid fa-clock"></i> Démarrage prévu le {{ $dateDebutPrevue->format('d/m/Y') }}
            </button>
            @endif
        @elseif($isChefEquipe && $maintenance->statut === 'en_cours')
            @php
                $isTermine = ($maintenance->nombre_equipements_prevus > 0 && $maintenance->nombre_equipements_restants == 0);
            @endphp
            @if($isTermine)
            <a href="{{ route('maintenances.rapportForm', $maintenance->id) }}" class="btn-primary">
                <i class="fa-solid fa-flag-checkered"></i> Terminer et Soumettre Rapport Finale
            </a>
            @else
            <button type="button" disabled style="background: #f1f5f9; color: #94a3b8; border: 1px solid #e2e8f0; padding: 0.6rem 1rem; border-radius: 0.5rem; font-weight: 600; cursor: not-allowed; display: inline-flex; align-items: center; gap: 0.5rem;" title="{{ $maintenance->nombre_equipements_restants }} équipement(s) restant(s) à traiter">
                <i class="fa-solid fa-lock"></i> Terminer ({{ $maintenance->nombre_equipements_restants }} éq. restants)
            </button>
            @endif
        @endif

        {{-- ADMIN & SUPERVISEUR SOUTARAH : Modifier / Supprimer --}}
        @if(Auth::user()->isAdmin() || Auth::user()->isSuperviseurSoutarah())
        <a href="{{ route('maintenances.edit', $maintenance->id) }}" style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; padding: 0.6rem 1rem; border-radius: 0.5rem; font-weight: 700; font-size: 0.9rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.4rem;">
            <i class="fa-solid fa-pen-to-square"></i> Modifier
        </a>
        <form action="{{ route('maintenances.destroy', $maintenance->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette maintenance ? Cette action est irréversible.');">
            @csrf
            @method('DELETE')
            <button type="submit" style="background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; padding: 0.6rem 1rem; border-radius: 0.5rem; font-weight: 700; font-size: 0.9rem; cursor: pointer; display: inline-flex; align-items: center; gap: 0.4rem;">
                <i class="fa-solid fa-trash"></i> Supprimer
            </button>
        </form>
        @endif
    </div>
</div>


<!-- Statut et Informations Générales -->
<div class="card" style="max-width: 1200px; margin-bottom: 1.5rem;">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3 class="card-title"><i class="fa-solid fa-info-circle"></i> Informations Générales</h3>
        
        @if($maintenance->statut === 'planifiée')
            <span class="badge badge-warning" style="font-size: 0.9rem; padding: 0.5rem 1rem;">⏳ Planifiée</span>
        @elseif($maintenance->statut === 'confirmée_client')
            <span class="badge badge-info" style="font-size: 0.9rem; padding: 0.5rem 1rem;">✔️ Confirmée Client</span>
        @elseif($maintenance->statut === 'en_cours')
            <span class="badge" style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; font-size: 0.9rem; padding: 0.5rem 1rem;">🔄 En Cours</span>
        @elseif($maintenance->statut === 'terminée')
            <span class="badge badge-success" style="font-size: 0.9rem; padding: 0.5rem 1rem;">✅ Terminée</span>
        @endif
    </div>
    
    <div style="padding: 1.5rem;">
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
            <div>
                <label style="font-size: 0.8rem; color: #64748b; font-weight: 600; display: block; margin-bottom: 0.25rem;">Type de Maintenance</label>
                <p style="font-size: 0.95rem; color: #0f172a; font-weight: 600;">
                    @if($maintenance->type_maintenance === 'préventive')
                    🛡️ Maintenance Préventive
                    @else
                    🔧 Maintenance Corrective Programmée
                    @endif
                </p>
            </div>
            
            <div>
                <label style="font-size: 0.8rem; color: #64748b; font-weight: 600; display: block; margin-bottom: 0.25rem;">Créée par</label>
                <p style="font-size: 0.95rem; color: #0f172a;">{{ $maintenance->createdBy->nom_complet ?? 'N/A' }} ({{ ucfirst($maintenance->created_by_role) }})</p>
            </div>
            
            <div>
                <label style="font-size: 0.8rem; color: #64748b; font-weight: 600; display: block; margin-bottom: 0.25rem;">Date de Création</label>
                <p style="font-size: 0.95rem; color: #0f172a;">{{ $maintenance->created_at->format('d/m/Y à H:i') }}</p>
            </div>
            
            <div>
                <label style="font-size: 0.8rem; color: #64748b; font-weight: 600; display: block; margin-bottom: 0.25rem;">Date Prévue de Début</label>
                <p style="font-size: 0.95rem; color: #0f172a; font-weight: 700;">{{ \Carbon\Carbon::parse($maintenance->date_debut_prevue)->format('d/m/Y à H:i') }}</p>
            </div>
            
            <div>
                <label style="font-size: 0.8rem; color: #64748b; font-weight: 600; display: block; margin-bottom: 0.25rem;">Date Prévue de Fin</label>
                <p style="font-size: 0.95rem; color: #0f172a; font-weight: 700;">
                    {{ $maintenance->date_fin_prevue ? \Carbon\Carbon::parse($maintenance->date_fin_prevue)->format('d/m/Y à H:i') : 'Non définie' }}
                </p>
            </div>

            <div>
                <label style="font-size: 0.8rem; color: #64748b; font-weight: 600; display: block; margin-bottom: 0.25rem;">Cadence Planifiée</label>
                <p style="font-size: 0.95rem; color: #0284c7; font-weight: 700;">
                    ⚡ {{ $maintenance->equipements_par_jour ?? 8 }} splits / jour
                </p>
            </div>

            @php
                $skippedDays = $maintenance->jours_non_ouvrables ?? [];
                $workingDaysCount = $maintenance->jours_ouvrables_count;
            @endphp
            @if($workingDaysCount || !empty($skippedDays))
            <div style="grid-column: span 2; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.5rem; padding: 0.75rem 1rem; margin-top: 0.25rem;">
                <label style="font-size: 0.8rem; color: #475569; font-weight: 700; display: flex; align-items: center; gap: 0.4rem; margin-bottom: 0.35rem;">
                    <i class="fa-solid fa-calendar-check" style="color: #059669;"></i>
                    Jours ouvrables & Période effective
                </label>
                <div style="display: flex; align-items: center; flex-wrap: wrap; gap: 0.5rem; font-size: 0.85rem;">
                    @if($workingDaysCount)
                    <span style="background: #d1fae5; color: #065f46; font-weight: 700; padding: 0.2rem 0.6rem; border-radius: 9999px;">
                        {{ $workingDaysCount }} jour(s) ouvrable(s) planifié(s)
                    </span>
                    @endif
                    @if(!empty($skippedDays))
                    <span style="background: #fee2e2; color: #991b1b; font-weight: 700; padding: 0.2rem 0.6rem; border-radius: 9999px;">
                        {{ count($skippedDays) }} jour(s) non ouvrable(s) sauté(s)
                    </span>
                    <span style="font-size: 0.78rem; color: #64748b;">
                        ({{ implode(', ', array_map(function($d) { return \Carbon\Carbon::parse($d)->format('d/m'); }, $skippedDays)) }})
                    </span>
                    @else
                    <span style="color: #64748b; font-size: 0.78rem;">Tous les jours de la période sont travaillés.</span>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- BARRE DE PROGRESSION ET AVANCEMENT ÉQUIPEMENTS -->
<div class="card" style="max-width: 1200px; margin-bottom: 1.5rem; border: 1.5px solid #cbd5e1; background: #ffffff;">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
        <h3 class="card-title" style="color: #0f172a; font-size: 1.05rem; font-weight: 800; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-bars-progress" style="color: #059669;"></i> Avancement & Progression des Travaux
        </h3>
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <span style="font-weight: 800; font-size: 0.95rem; color: #059669; background: #ecfdf5; padding: 0.35rem 0.85rem; border-radius: 9999px; border: 1px solid #a7f3d0;">
                {{ $maintenance->pourcentage_avancement }}% Réalisé
            </span>
            @if($isChefEquipe && $maintenance->statut === 'en_cours')
            <a href="{{ route('comptes-rendus.create', $maintenance->id) }}" class="btn-primary" style="background: #059669; font-size: 0.8rem; padding: 0.45rem 0.9rem;">
                <i class="fa-solid fa-plus"></i> Saisir Compte Rendu
            </a>
            @endif
        </div>
    </div>
    
    <div style="padding: 1.5rem;">
        {{-- Grille des 3 indicateurs clés --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.25rem;">
            {{-- 1. Objectif du Site --}}
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 1rem 1.25rem;">
                <div style="font-size: 0.78rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.3rem;">
                    Objectif Prévu (Site)
                </div>
                <div style="font-size: 1.5rem; font-weight: 800; color: #0f172a;">
                    {{ $maintenance->nombre_equipements_prevus > 0 ? $maintenance->nombre_equipements_prevus : '0' }} <span style="font-size: 0.9rem; font-weight: 600; color: #64748b;">éq.</span>
                </div>
                <div style="font-size: 0.75rem; color: #94a3b8; margin-top: 0.2rem;">
                    Périmètre complet de l'intervention
                </div>
            </div>

            {{-- 2. Traités au total (cumul) --}}
            <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 0.75rem; padding: 1rem 1.25rem;">
                <div style="font-size: 0.78rem; font-weight: 700; color: #047857; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.3rem;">
                    Traités au Total (Cumul)
                </div>
                <div style="font-size: 1.5rem; font-weight: 800; color: #059669;">
                    {{ $maintenance->nombre_equipements_traites }} <span style="font-size: 0.9rem; font-weight: 600; color: #047857;">éq.</span>
                </div>
                <div style="font-size: 0.75rem; color: #059669; margin-top: 0.2rem;">
                    Somme de tous les rapports des équipes
                </div>
            </div>

            {{-- 3. Restants sur le site --}}
            @php
                $restants = $maintenance->nombre_equipements_restants;
                $isTermine = ($maintenance->nombre_equipements_prevus > 0 && $restants == 0);
            @endphp
            <div style="background: {{ $isTermine ? '#f0fdf4' : '#fff7ed' }}; border: 1px solid {{ $isTermine ? '#86efac' : '#fdba74' }}; border-radius: 0.75rem; padding: 1rem 1.25rem;">
                <div style="font-size: 0.78rem; font-weight: 700; color: {{ $isTermine ? '#047857' : '#c2410c' }}; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.3rem;">
                    Reste sur le Site
                </div>
                <div style="font-size: 1.5rem; font-weight: 800; color: {{ $isTermine ? '#059669' : '#ea580c' }};">
                    {{ $restants }} <span style="font-size: 0.9rem; font-weight: 600; color: {{ $isTermine ? '#047857' : '#c2410c' }};">éq.</span>
                </div>
                <div style="font-size: 0.75rem; color: {{ $isTermine ? '#059669' : '#9a3412' }}; margin-top: 0.2rem;">
                    {{ $isTermine ? '✅ Tous les équipements sont traités' : 'Encore à traiter sur le terrain' }}
                </div>
            </div>
        </div>

        {{-- Barre de progression visuelle --}}
        <div style="margin-bottom: 0.5rem;">
            <div style="display: flex; justify-content: space-between; font-size: 0.8rem; font-weight: 700; color: #475569; margin-bottom: 0.35rem;">
                <span>Progression globale</span>
                <span>{{ $maintenance->nombre_equipements_traites }} / {{ $maintenance->nombre_equipements_prevus }} éq. ({{ $maintenance->pourcentage_avancement }}%)</span>
            </div>
            <div style="width: 100%; height: 20px; background: #f1f5f9; border-radius: 10px; overflow: hidden; border: 1px solid #e2e8f0; position: relative;">
                <div style="width: {{ $maintenance->pourcentage_avancement }}%; height: 100%; background: linear-gradient(135deg, #059669 0%, #10b981 100%); transition: width 0.5s ease; border-radius: 10px;"></div>
            </div>
        </div>

        {{-- Répartition des réalisations par équipe participante --}}
        @php
            $reportsByEquipe = $maintenance->comptesRendusJournaliers->groupBy('equipe_id');
        @endphp
        @if($reportsByEquipe->count() > 0)
        <div style="margin-top: 1.25rem; padding-top: 1.25rem; border-top: 1px solid #f1f5f9;">
            <div style="font-size: 0.78rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.4rem;">
                <i class="fa-solid fa-users-gear" style="color: #059669;"></i> Répartition du cumul par équipe
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 0.75rem;">
                @foreach($reportsByEquipe as $eqId => $eqReports)
                @php
                    $eqObj = $maintenance->equipes->firstWhere('id', $eqId) ?? ($maintenance->equipe && $maintenance->equipe->id == $eqId ? $maintenance->equipe : \App\Models\Equipe::find($eqId));
                    $eqTotalTraites = $eqReports->sum('nombre_equipements_traites');
                    $eqShare = $maintenance->nombre_equipements_traites > 0 ? round(($eqTotalTraites / $maintenance->nombre_equipements_traites) * 100, 1) : 0;
                @endphp
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.6rem; padding: 0.75rem 1rem; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-weight: 700; font-size: 0.88rem; color: #1e293b;">
                            <i class="fa-solid fa-users" style="color: #059669; font-size: 0.8rem; margin-right: 0.25rem;"></i>
                            {{ $eqObj->nom_equipe ?? 'Équipe #'.$eqId }}
                        </div>
                        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.15rem;">
                            {{ $eqReports->count() }} rapport(s) journalier(s)
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-weight: 800; font-size: 1rem; color: #059669;">
                            +{{ $eqTotalTraites }} éq.
                        </div>
                        <span style="font-size: 0.72rem; font-weight: 700; color: #047857; background: #ecfdf5; padding: 0.1rem 0.45rem; border-radius: 9999px; border: 1px solid #a7f3d0;">
                            {{ $eqShare }}% du cumul
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

<!-- COMPTES RENDUS JOURNALIERS D'ÉQUIPE -->
<div class="card" style="max-width: 1200px; margin-bottom: 1.5rem;">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3 class="card-title"><i class="fa-solid fa-clipboard-list"></i> Comptes Rendus Journaliers – Équipe</h3>
        @if($isChefEquipe && $maintenance->statut === 'en_cours')
        <a href="{{ route('comptes-rendus.create', $maintenance->id) }}" class="btn-primary" style="background: linear-gradient(135deg, #059669, #10b981); font-size: 0.85rem;">
            <i class="fa-solid fa-plus"></i> Nouveau Compte Rendu Journalier
        </a>
        @endif
    </div>

    <div style="padding: 1rem;">
        @if($maintenance->comptesRendusJournaliers->count() === 0)
            <div style="text-align: center; padding: 2rem; color: #64748b;">
                <i class="fa-solid fa-clipboard" style="font-size: 2.5rem; margin-bottom: 0.75rem; color: #cbd5e1;"></i>
                <p style="font-weight: 600; margin-bottom: 0.5rem;">Aucun compte rendu journalier saisi pour le moment.</p>
                <p style="font-size: 0.85rem;">Le chef d'équipe peut enregistrer ici le compte rendu chaque jour d'intervention.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="min-width: 100px;">DATE</th>
                            <th style="min-width: 180px;">ÉQUIPE / INTERVENANTS</th>
                            <th style="min-width: 200px;">ACTIVITÉS RÉALISÉES</th>
                            <th style="width: 100px; text-align: center;">ÉQ. TRAITÉS</th>
                            <th style="width: 120px;">ANOMALIES</th>
                            <th style="min-width: 150px;">RESPONSABLE</th>
                            <th style="width: 100px; text-align: center;">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($maintenance->comptesRendusJournaliers as $cr)
                        <tr>
                            <td style="white-space: nowrap;">
                                📅 {{ \Carbon\Carbon::parse($cr->date_rapport)->format('d/m/Y') }}
                            </td>
                            <td>
                                <strong>👥 {{ $cr->noms_intervenants ?? 'N/A' }}</strong>
                                @if($cr->equipe)
                                <div style="font-size: 0.75rem; color: #059669; font-weight: 600; margin-top: 0.2rem;">
                                    <i class="fa-solid fa-users"></i> {{ $cr->equipe->nom_equipe }}
                                </div>
                                @endif
                            </td>
                            <td>
                                {{ Str::limit($cr->activites_realisees, 100) }}
                            </td>
                            <td style="text-align: center; background: #ecfdf5; font-weight: 800; color: #059669;">
                                +{{ $cr->nombre_equipements_traites }}
                            </td>
                            <td>
                                @if(empty($cr->anomalies_constatees) || in_array(strtolower(trim($cr->anomalies_constatees)), ['ras', 'ras.', 'pas d\'anomalie', 'aucune', 'aucun']))
                                    <span class="badge badge-success">Aucune</span>
                                @else
                                    <span class="badge badge-warning" title="{{ $cr->anomalies_constatees }}">⚠️ Constatée</span>
                                @endif
                            </td>
                            <td>
                                <strong style="color: #0369a1;">{{ $cr->nom_responsable ?? 'N/A' }}</strong>
                            </td>
                            <td style="text-align: center;">
                                <a href="{{ route('comptes-rendus.show', $cr->id) }}" class="btn-secondary" style="font-size: 0.8rem; padding: 0.4rem 0.7rem;">
                                    <i class="fa-solid fa-eye"></i> Voir
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<!-- Localisation et Équipement -->
<div class="card" style="max-width: 1200px; margin-bottom: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title"><i class="fa-solid fa-location-dot"></i> Localisation & Équipement</h3>
    </div>
    
    <div style="padding: 1.5rem;">
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
            <div>
                <label style="font-size: 0.8rem; color: #64748b; font-weight: 600; display: block; margin-bottom: 0.25rem;">Client</label>
                <p style="font-size: 0.95rem; color: #0f172a; font-weight: 700;">{{ $maintenance->client->nom ?? 'N/A' }}</p>
            </div>
            
            @if($maintenance->base)
            <div>
                <label style="font-size: 0.8rem; color: #64748b; font-weight: 600; display: block; margin-bottom: 0.25rem;">Base</label>
                <p style="font-size: 0.95rem; color: #0f172a;">{{ $maintenance->base->nom_base }}</p>
            </div>
            @endif
            
            @if($maintenance->site)
            <div>
                <label style="font-size: 0.8rem; color: #64748b; font-weight: 600; display: block; margin-bottom: 0.25rem;">Site</label>
                <p style="font-size: 0.95rem; color: #0f172a;">{{ $maintenance->site->nom_site }}</p>
            </div>
            @endif
            
            <div>
                <label style="font-size: 0.8rem; color: #64748b; font-weight: 600; display: block; margin-bottom: 0.25rem;">Équipement</label>
                <p style="font-size: 0.95rem; color: #0f172a; font-weight: 700;">{{ $maintenance->equipement->equipement_nom ?? 'N/A' }}</p>
                <small style="color: #64748b;">Code: {{ $maintenance->equipement->equipement_code ?? 'N/A' }}</small>
            </div>
        </div>
    </div>
</div>

<!-- Description et Tâches -->
<div class="card" style="max-width: 1200px; margin-bottom: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title"><i class="fa-solid fa-file-lines"></i> Description & Tâches Prévues</h3>
    </div>
    
    <div style="padding: 1.5rem;">
        <div style="margin-bottom: 1.5rem;">
            <label style="font-size: 0.8rem; color: #64748b; font-weight: 600; display: block; margin-bottom: 0.5rem;">Description</label>
            <p style="font-size: 0.9rem; color: #0f172a; line-height: 1.6; background: #f8fafc; padding: 1rem; border-radius: 0.5rem;">
                {{ $maintenance->description }}
            </p>
        </div>
        
        @if($maintenance->taches_prevues)
        <div style="margin-bottom: 1.5rem;">
            <label style="font-size: 0.8rem; color: #64748b; font-weight: 600; display: block; margin-bottom: 0.5rem;">Tâches Prévues</label>
            <p style="font-size: 0.9rem; color: #0f172a; line-height: 1.6; background: #f8fafc; padding: 1rem; border-radius: 0.5rem; white-space: pre-wrap;">{{ $maintenance->taches_prevues }}</p>
        </div>
        @endif
        
        @if($maintenance->pieces_prevues)
        <div>
            <label style="font-size: 0.8rem; color: #64748b; font-weight: 600; display: block; margin-bottom: 0.5rem;">Pièces Prévues</label>
            <p style="font-size: 0.9rem; color: #0f172a; line-height: 1.6; background: #f8fafc; padding: 1rem; border-radius: 0.5rem; white-space: pre-wrap;">{{ $maintenance->pieces_prevues }}</p>
        </div>
        @endif
    </div>
</div>

<!-- Affectation -->
<div class="card" style="max-width: 1200px; margin-bottom: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title"><i class="fa-solid fa-users-gear"></i> Affectation</h3>
    </div>
    
    <div style="padding: 1.5rem;">
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
            @if($maintenance->equipes && $maintenance->equipes->count() > 0)
            <div style="grid-column: 1 / -1;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                    <label style="font-size: 0.8rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                        <i class="fa-solid fa-users"></i> Équipes affectées ({{ $maintenance->equipes->count() }})
                    </label>
                </div>
                <div style="display: flex; flex-wrap: wrap; gap: 0.75rem;">
                    @foreach($maintenance->equipes as $eq)
                    <div style="background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 0.6rem; padding: 0.5rem 0.85rem; display: inline-flex; align-items: center; gap: 0.6rem;">
                        <i class="fa-solid fa-users" style="color: #059669;"></i>
                        <div>
                            <span style="font-weight: 700; color: #065f46; font-size: 0.9rem;">{{ $eq->nom_equipe }}</span>
                            @if($eq->chef)
                                <small style="color: #047857; display: block; font-size: 0.75rem;">Chef : {{ $eq->chef->nom_complet ?? $eq->chef->nom }}</small>
                            @endif
                        </div>
                        <span style="background: #ffffff; color: #047857; font-size: 0.72rem; font-weight: 700; padding: 0.15rem 0.5rem; border-radius: 9999px; border: 1px solid #a7f3d0;">
                            {{ $eq->membres->count() }} membre(s)
                        </span>
                        @if((auth()->user()->isAdmin() || auth()->user()->isSuperviseurSoutarah()) && $maintenance->statut !== 'terminée' && $maintenance->equipes->count() > 1)
                        <form action="{{ route('maintenances.equipes.remove', ['maintenance' => $maintenance->id, 'equipe' => $eq->id]) }}" method="POST" onsubmit="return confirm('Retirer l\'équipe {{ $eq->nom_equipe }} de cette maintenance ?');" style="display:inline; margin-left: 0.25rem;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" title="Retirer cette équipe" style="background: none; border: none; color: #dc2626; cursor: pointer; padding: 0 0.2rem; font-size: 0.9rem; font-weight: bold;">&times;</button>
                        </form>
                        @endif
                    </div>
                    @endforeach
                </div>

                @if((auth()->user()->isAdmin() || auth()->user()->isSuperviseurSoutarah()) && $maintenance->statut !== 'terminée')
                    @php
                        $affecteesIds = $maintenance->equipes->pluck('id')->toArray();
                        $disponibles = ($allEquipes ?? collect())->whereNotIn('id', $affecteesIds);
                    @endphp
                    @if($disponibles->count() > 0)
                    <form action="{{ route('maintenances.equipes.add', $maintenance->id) }}" method="POST" style="margin-top: 1rem; display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; background: #f8fafc; padding: 0.75rem; border-radius: 0.5rem; border: 1px dashed #cbd5e1;">
                        @csrf
                        <span style="font-size: 0.8rem; color: #475569; font-weight: 600;">Ajouter une équipe en renfort :</span>
                        <select name="equipe_id" required style="padding: 0.4rem 0.75rem; border-radius: 0.5rem; border: 1px solid #cbd5e1; font-size: 0.82rem; background: #fff;">
                            <option value="">-- Sélectionner une équipe --</option>
                            @foreach($disponibles as $disp)
                                <option value="{{ $disp->id }}">{{ $disp->nom_equipe }} ({{ $disp->membres->count() }} membres)</option>
                            @endforeach
                        </select>
                        <button type="submit" style="background: #059669; color: white; border: none; padding: 0.4rem 0.85rem; border-radius: 0.5rem; font-size: 0.82rem; font-weight: 700; cursor: pointer;">
                            <i class="fa-solid fa-plus"></i> Affecter l'équipe
                        </button>
                    </form>
                    @endif
                @endif
            </div>
            @elseif($maintenance->equipe)
            <div>
                <label style="font-size: 0.8rem; color: #64748b; font-weight: 600; display: block; margin-bottom: 0.25rem;">Équipe</label>
                <p style="font-size: 0.95rem; color: #059669; font-weight: 700;">
                    <i class="fa-solid fa-users"></i> {{ $maintenance->equipe->nom_equipe }}
                </p>
            </div>
            @endif
            
            @if($maintenance->technicien)
            <div>
                <label style="font-size: 0.8rem; color: #64748b; font-weight: 600; display: block; margin-bottom: 0.25rem;">Technicien</label>
                <p style="font-size: 0.95rem; color: #0369a1; font-weight: 700;">
                    <i class="fa-solid fa-user"></i> {{ $maintenance->technicien->nom_complet }}
                </p>
            </div>
            @endif
            
            @if(!$maintenance->equipe && !$maintenance->technicien)
            <div style="grid-column: 1 / -1;">
                <p style="color: #94a3b8; text-align: center; padding: 2rem;">
                    <i class="fa-solid fa-user-slash" style="font-size: 2rem; opacity: 0.3; display: block; margin-bottom: 0.5rem;"></i>
                    Aucune affectation pour le moment
                </p>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Rapport Technicien (si terminée) -->
@if($maintenance->statut === 'terminée' && $maintenance->rapport_technicien)
<div class="card" style="max-width: 1200px; margin-bottom: 1.5rem; background: #f0fdf4; border: 2px solid #a7f3d0;">
    <div class="card-header" style="background: #ecfdf5; border-bottom: 1px solid #a7f3d0;">
        <h3 class="card-title" style="color: #047857;"><i class="fa-solid fa-clipboard-check"></i> Rapport de Maintenance</h3>
        @if($maintenance->date_fin_reelle)
        <p style="margin-top: 0.5rem; color: #047857; font-size: 0.85rem;">
            Terminée le {{ \Carbon\Carbon::parse($maintenance->date_fin_reelle)->format('d/m/Y à H:i') }}
            @if($maintenance->technicien)
            par {{ $maintenance->technicien->nom_complet }}
            @endif
        </p>
        @endif
    </div>
    
    <div style="padding: 1.5rem;">
        <div style="margin-bottom: 1.5rem;">
            <label style="font-size: 0.8rem; color: #047857; font-weight: 600; display: block; margin-bottom: 0.5rem;">Compte-rendu du Technicien</label>
            <p style="font-size: 0.9rem; color: #0f172a; line-height: 1.6; background: #ffffff; padding: 1rem; border-radius: 0.5rem; white-space: pre-wrap;">{{ $maintenance->rapport_technicien }}</p>
        </div>
        
        @if($maintenance->pieces_utilisees)
        <div style="margin-bottom: 1.5rem;">
            <label style="font-size: 0.8rem; color: #047857; font-weight: 600; display: block; margin-bottom: 0.5rem;">Pièces Utilisées</label>
            <p style="font-size: 0.9rem; color: #0f172a; line-height: 1.6; background: #ffffff; padding: 1rem; border-radius: 0.5rem; white-space: pre-wrap;">{{ $maintenance->pieces_utilisees }}</p>
        </div>
        @endif
        
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
            <div>
                <label style="font-size: 0.8rem; color: #047857; font-weight: 600; display: block; margin-bottom: 0.25rem;">Date Début Réelle</label>
                <p style="font-size: 0.9rem; color: #0f172a;">{{ $maintenance->date_debut_reelle ? \Carbon\Carbon::parse($maintenance->date_debut_reelle)->format('d/m/Y à H:i') : 'N/A' }}</p>
            </div>
            
            <div>
                <label style="font-size: 0.8rem; color: #047857; font-weight: 600; display: block; margin-bottom: 0.25rem;">Date Fin Réelle</label>
                <p style="font-size: 0.9rem; color: #0f172a;">{{ $maintenance->date_fin_reelle ? \Carbon\Carbon::parse($maintenance->date_fin_reelle)->format('d/m/Y à H:i') : 'N/A' }}</p>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Section informative pour le technicien selon le statut --}}
@if($isChefEquipe)
    {{-- Maintenance Planifiée ou Confirmée - Prêt à démarrer --}}
    @if(in_array($maintenance->statut, ['planifiée', 'confirmée_client']))
    @php
        $dateDebutPrevue = \Carbon\Carbon::parse($maintenance->date_debut_prevue);
        $aujourdhui = \Carbon\Carbon::now();
        $peutDemarrer = $aujourdhui->greaterThanOrEqualTo($dateDebutPrevue);
    @endphp
    
    @if($peutDemarrer)
    <div class="card" style="max-width: 1200px; margin-bottom: 1.5rem; background: linear-gradient(135deg, #dbeafe, #bfdbfe); border: 2px solid #3b82f6;">
        <div style="padding: 2rem; text-align: center;">
            <div style="background: #3b82f6; color: #fff; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; margin: 0 auto 1.5rem;">
                <i class="fa-solid fa-rocket"></i>
            </div>
            <h3 style="color: #1e40af; font-size: 1.25rem; font-weight: 700; margin-bottom: 0.75rem;">
                Maintenance Prête à Démarrer
            </h3>
            <p style="color: #475569; font-size: 0.95rem; line-height: 1.6; margin-bottom: 1.5rem; max-width: 600px; margin-left: auto; margin-right: auto;">
                Cette maintenance a été affectée à votre équipe. En tant que chef d'équipe, vous pouvez démarrer les travaux dès maintenant.
                @if($maintenance->statut === 'confirmée_client')
                <br><span style="color: #059669; font-weight: 600;"><i class="fa-solid fa-check-circle"></i> Le client a accusé réception</span>
                @endif
            </p>
            <form action="{{ route('maintenances.demarrer', $maintenance->id) }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="btn-primary" style="font-size: 1rem; padding: 0.85rem 2rem;">
                    <i class="fa-solid fa-play"></i> Démarrer la Maintenance
                </button>
            </form>
        </div>
    </div>
    @else
    <div class="card" style="max-width: 1200px; margin-bottom: 1.5rem; background: linear-gradient(135deg, #fef3c7, #fde68a); border: 2px solid #f59e0b;">
        <div style="padding: 2rem; text-align: center;">
            <div style="background: #f59e0b; color: #fff; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; margin: 0 auto 1.5rem;">
                <i class="fa-solid fa-clock"></i>
            </div>
            <h3 style="color: #92400e; font-size: 1.25rem; font-weight: 700; margin-bottom: 0.75rem;">
                Maintenance Programmée
            </h3>
            <p style="color: #78716c; font-size: 0.95rem; line-height: 1.6; margin-bottom: 1rem; max-width: 600px; margin-left: auto; margin-right: auto;">
                Cette maintenance est programmée pour le <strong>{{ $dateDebutPrevue->format('d/m/Y à H:i') }}</strong>.
                <br>Vous pourrez la démarrer à partir de cette date.
            </p>
            <div style="background: white; padding: 1rem; border-radius: 0.5rem; display: inline-block; margin-top: 0.5rem;">
                <i class="fa-solid fa-calendar-days" style="color: #d97706; margin-right: 0.5rem;"></i>
                <span style="font-weight: 700; color: #92400e;">Date de début prévue : {{ $dateDebutPrevue->format('d/m/Y à H:i') }}</span>
            </div>
        </div>
    </div>
    @endif
    @endif
    
    {{-- Maintenance En Cours - Prêt à soumettre le rapport --}}
    @if($maintenance->statut === 'en_cours')
    @php
        $isTermine = ($maintenance->nombre_equipements_prevus > 0 && $maintenance->nombre_equipements_restants == 0);
    @endphp
    @if($isTermine)
    <div class="card" style="max-width: 1200px; margin-bottom: 1.5rem; background: linear-gradient(135deg, #f0fdf4, #dcfce7); border: 2px solid #22c55e;">
        <div style="padding: 2rem; text-align: center;">
            <div style="background: linear-gradient(135deg, #059669, #10b981); color: #fff; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; margin: 0 auto 1.5rem; box-shadow: 0 8px 24px rgba(16, 185, 129, 0.35);">
                <i class="fa-solid fa-clipboard-check"></i>
            </div>
            <h3 style="color: #047857; font-size: 1.25rem; font-weight: 700; margin-bottom: 0.75rem;">
                ✅ Tous les équipements sont traités !
            </h3>
            <p style="color: #475569; font-size: 0.95rem; line-height: 1.6; margin-bottom: 1.5rem; max-width: 600px; margin-left: auto; margin-right: auto;">
                L'ensemble des {{ $maintenance->nombre_equipements_prevus }} équipements prévus ont été traités. Vous pouvez maintenant soumettre votre rapport final.
            </p>
            <a href="{{ route('maintenances.rapportForm', $maintenance->id) }}" class="btn-primary" style="font-size: 1rem; padding: 0.85rem 2rem; display: inline-flex; align-items: center; gap: 0.5rem; background: linear-gradient(135deg, #059669, #10b981); box-shadow: 0 4px 14px rgba(16, 185, 129, 0.4);">
                <i class="fa-solid fa-flag-checkered"></i> Terminer et Soumettre le Rapport
            </a>
        </div>
    </div>
    @else
    <div class="card" style="max-width: 1200px; margin-bottom: 1.5rem; background: linear-gradient(135deg, #fff7ed, #fef3c7); border: 2px solid #f59e0b;">
        <div style="padding: 2rem; text-align: center;">
            <div style="background: linear-gradient(135deg, #d97706, #f59e0b); color: #fff; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; margin: 0 auto 1.5rem; box-shadow: 0 8px 24px rgba(245, 158, 11, 0.35);">
                <i class="fa-solid fa-hourglass-half"></i>
            </div>
            <h3 style="color: #92400e; font-size: 1.25rem; font-weight: 700; margin-bottom: 0.75rem;">
                ⏳ Travaux en cours — encore {{ $maintenance->nombre_equipements_restants }} équipement(s) à traiter
            </h3>
            <p style="color: #78716c; font-size: 0.95rem; line-height: 1.6; margin-bottom: 1rem; max-width: 600px; margin-left: auto; margin-right: auto;">
                Le rapport final ne peut être soumis qu'une fois <strong>tous les {{ $maintenance->nombre_equipements_prevus }} équipements prévus traités</strong>.<br>
                <span style="color: #059669; font-weight: 600;">{{ $maintenance->nombre_equipements_traites }} traité(s)</span> sur {{ $maintenance->nombre_equipements_prevus }} — encore <span style="color: #dc2626; font-weight: 700;">{{ $maintenance->nombre_equipements_restants }} restant(s)</span>.
            </p>
            <button type="button" disabled style="font-size: 1rem; padding: 0.85rem 2rem; display: inline-flex; align-items: center; gap: 0.5rem; background: #e2e8f0; color: #94a3b8; border: none; border-radius: 0.5rem; font-weight: 700; cursor: not-allowed;">
                <i class="fa-solid fa-lock"></i> Rapport non disponible (travaux incomplets)
            </button>
        </div>
    </div>
    @endif
    @endif
@endif

{{-- ═══════════════════════════════════════════════════════════════════
     VALIDATION SUPERVISEUR SOUTARAH / ADMIN
     Examen et transmission du rapport au client
     ═══════════════════════════════════════════════════════════════════ --}}
@if((Auth::user()->isAdmin() || Auth::user()->isSuperviseurSoutarah()) && $maintenance->statut_rapport_technicien === 'soumis')
<div class="card" style="max-width: 1200px; margin-bottom: 1.5rem; border: 2px solid #059669;">
    <div class="card-header" style="background: #f0fdf4;">
        <h3 class="card-title" style="color: #047857;">
            <i class="fa-solid fa-user-check"></i> Examen du Rapport Technicien (Soutarah / Admin)
        </h3>
        <p style="margin-top: 0.5rem; color: #047857; font-size: 0.85rem;">
            Vérifiez le compte-rendu. Validez pour transmettre au client ou rejetez si des corrections sont requises.
        </p>
    </div>

    <div style="display: flex; gap: 1rem; padding: 1.25rem; flex-wrap: wrap;">
        <form action="{{ route('maintenances.transmettreRapportClient', $maintenance->id) }}" method="POST" style="flex: 1; min-width: 220px;">
            @csrf
            <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; padding: 0.75rem; font-size: 0.9rem; font-weight: 600;">
                <i class="fa-solid fa-check"></i> Valider et Transmettre au Client
            </button>
        </form>

        <button type="button" onclick="openRejectRapportModal()" style="flex: 1; min-width: 220px; background-color: #dc2626; color: white; border: none; border-radius: 0.375rem; font-weight: 600; cursor: pointer; padding: 0.75rem; font-size: 0.9rem; display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;">
            <i class="fa-solid fa-rotate-left"></i> Rejeter au Technicien (Correction)
        </button>
    </div>
</div>

<!-- Modal Rejet du Rapport Technicien -->
<div id="rejectRapportModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.5); backdrop-filter: blur(4px); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #ffffff; border-radius: 0.75rem; max-width: 500px; width: 100%; padding: 1.5rem; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
        <h3 style="font-size: 1.05rem; font-weight: 700; color: #9f1239; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-rotate-left"></i> Rejeter le Rapport au Technicien
        </h3>
        <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 1.25rem;">
            Indiquez le motif du rejet et les instructions pour le technicien.
        </p>

        <form action="{{ route('maintenances.rejeterRapportTechnicien', $maintenance->id) }}" method="POST">
            @csrf
            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-size: 0.85rem; color: #334155; margin-bottom: 0.4rem; font-weight: 600;">
                    Motif du rejet / Instructions pour le technicien <span style="color: #dc2626;">*</span>
                </label>
                <textarea name="raison_rejet" rows="4" required class="form-control" style="width: 100%; padding: 0.6rem; border: 1px solid #cbd5e1; border-radius: 0.375rem; font-size: 0.85rem;" placeholder="Ex: Merci de compléter le détail des pièces remplacées."></textarea>
            </div>

            <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                <button type="button" onclick="closeRejectRapportModal()" style="padding: 0.6rem 1.1rem; border-radius: 0.375rem; background-color: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; font-weight: 600; cursor: pointer;">
                    Annuler
                </button>
                <button type="submit" style="background-color: #dc2626; color: #ffffff; padding: 0.6rem 1.1rem; border-radius: 0.375rem; font-weight: 600; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 0.4rem;">
                    <i class="fa-solid fa-paper-plane"></i> Transmettre le Rejet
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openRejectRapportModal() {
    document.getElementById('rejectRapportModal').style.display = 'flex';
}
function closeRejectRapportModal() {
    document.getElementById('rejectRapportModal').style.display = 'none';
}
</script>
@endif

{{-- ═══════════════════════════════════════════════════════════════════
     RAPPORT REJETÉ - Message pour le Technicien
     ═══════════════════════════════════════════════════════════════════ --}}
@if($maintenance->statut_rapport_technicien === 'rejete')
<div class="card" style="max-width: 1200px; margin-bottom: 1.5rem; background-color: #fef2f2; border: 2px solid #fecdd3;">
    <div style="padding: 1.25rem;">
        <h4 style="color: #9f1239; margin-top: 0; font-size: 1rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-circle-xmark" style="color: #e11d48;"></i> Rapport de Maintenance Refusé / À Corriger
        </h4>
        <div style="background: #ffffff; padding: 0.85rem 1rem; border-radius: 0.5rem; border: 1px solid #fecdd3; margin-top: 0.75rem;">
            <strong style="color: #9f1239; font-size: 0.85rem; display: block; margin-bottom: 0.25rem;">Motif du refus :</strong>
            <p style="color: #334155; margin: 0; font-size: 0.9rem; line-height: 1.6;">
                {{ $maintenance->raison_rejet_rapport }}
            </p>
        </div>
        @if($maintenance->date_rejet_rapport)
            <p style="color: #9f1239; font-size: 0.8rem; margin-top: 0.6rem; margin-bottom: 0;">
                <i class="fa-solid fa-clock"></i> Refusé le {{ \Carbon\Carbon::parse($maintenance->date_rejet_rapport)->format('d/m/Y à H:i') }}
            </p>
        @endif
        
        @if(Auth::user()->isTechnicien())
        <a href="{{ route('maintenances.rapportForm', $maintenance->id) }}" class="btn-primary" style="margin-top: 1rem; display: inline-flex;">
            <i class="fa-solid fa-pen-to-square"></i> Corriger et Resoumettre le Rapport
        </a>
        @endif
    </div>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════════════════
     VALIDATION FINALE PAR LE SUPERVISEUR CLIENT
     ═══════════════════════════════════════════════════════════════════ --}}
@if(Auth::user()->isSuperviseurClient() && $maintenance->statut_rapport_technicien === 'transmis_client' && $maintenance->statut_validation_client === 'en_attente')
<div class="card" style="max-width: 1200px; margin-bottom: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fa-solid fa-clipboard-check"></i> Décision du Superviseur Client sur le Rapport
        </h3>
    </div>
    
    <div style="padding: 1.5rem;">
        <p style="color: #475569; font-size: 0.9rem; line-height: 1.6; margin-top: 0; margin-bottom: 1.25rem;">
            Veuillez examiner les détails du rapport. Vous pouvez soit <strong>valider et clôturer</strong> la maintenance, soit la <strong>refuser</strong> afin de demander des corrections.
        </p>

        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
            {{-- OPTION 1 : VALIDER ET CLÔTURER --}}
            <div style="background: #f8fafc; padding: 1.25rem; border-radius: 0.5rem; border: 1px solid #e2e8f0;">
                <h4 style="color: #059669; font-size: 0.95rem; margin-top: 0; margin-bottom: 1rem; font-weight: 700;">
                    <i class="fa-solid fa-circle-check"></i> Valider et Clôturer la Maintenance
                </h4>
                <form action="{{ route('maintenances.validerParClient', $maintenance->id) }}" method="POST">
                    @csrf
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; font-size: 0.85rem; color: #334155; margin-bottom: 0.4rem; font-weight: 600;">
                            Commentaire de validation (Optionnel)
                        </label>
                        <textarea name="commentaire_validation" rows="3" class="form-control"
                                  placeholder="Remarques éventuelles sur la qualité des travaux..."
                                  style="width: 100%; padding: 0.6rem; border: 1px solid #cbd5e1; border-radius: 0.375rem; font-size: 0.85rem;"></textarea>
                    </div>
                    <button type="submit" class="btn-primary" 
                            style="width: 100%; justify-content: center; padding: 0.75rem; font-size: 0.9rem; font-weight: 600;">
                        <i class="fa-solid fa-check"></i> Valider et Clôturer
                    </button>
                </form>
            </div>

            {{-- OPTION 2 : REFUSER LE RAPPORT --}}
            <div style="background: #f8fafc; padding: 1.25rem; border-radius: 0.5rem; border: 1px solid #e2e8f0;">
                <h4 style="color: #dc2626; font-size: 0.95rem; margin-top: 0; margin-bottom: 1rem; font-weight: 700;">
                    <i class="fa-solid fa-circle-xmark"></i> Refuser et Demander Correction
                </h4>
                <form action="{{ route('maintenances.rejeterParClient', $maintenance->id) }}" method="POST">
                    @csrf
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; font-size: 0.85rem; color: #334155; margin-bottom: 0.4rem; font-weight: 600;">
                            Motif du refus <span style="color: #dc2626;">*</span>
                        </label>
                        <textarea name="raison_rejet" rows="3" required minlength="5" class="form-control"
                                  placeholder="Motif détaillé du refus (ex: rapport incomplet, maintenance non conforme...)"
                                  style="width: 100%; padding: 0.6rem; border: 1px solid #cbd5e1; border-radius: 0.375rem; font-size: 0.85rem;"></textarea>
                    </div>
                    <button type="submit" onclick="return confirm('Êtes-vous sûr de vouloir refuser ce rapport ?')"
                            style="background-color: #dc2626; color: white; border: none; width: 100%; padding: 0.75rem; border-radius: 0.375rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;">
                        <i class="fa-solid fa-rotate-left"></i> Refuser et Renvoyer
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Message si déjà validé et clôturé --}}
@if($maintenance->statut_validation_client === 'validé')
<div class="card" style="max-width: 1200px; margin-bottom: 1.5rem; background: linear-gradient(135deg, #f0fdf4, #dcfce7); border: 2px solid #10b981;">
    <div class="card-header" style="background: #ecfdf5;">
        <h3 class="card-title" style="color: #047857;">
            <i class="fa-solid fa-check-circle"></i> Maintenance Validée et Clôturée
        </h3>
    </div>
    
    <div style="padding: 1.25rem;">
        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
            <div style="background: #10b981; color: #fff; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                <i class="fa-solid fa-check"></i>
            </div>
            <div>
                <p style="color: #047857; font-size: 1rem; font-weight: 700; margin: 0;">
                    Maintenance clôturée avec succès
                </p>
                <p style="color: #065f46; font-size: 0.85rem; margin: 0;">
                    Validée le {{ $maintenance->date_validation_client ? \Carbon\Carbon::parse($maintenance->date_validation_client)->format('d/m/Y à H:i') : 'N/A' }}
                    @if($maintenance->validatedByClient)
                        par {{ $maintenance->validatedByClient->nom_complet }}
                    @endif
                </p>
            </div>
        </div>

        @if($maintenance->commentaire_validation_client)
        <div style="background: #f0fdf4; padding: 1rem; border-radius: 0.5rem; border: 1px solid #a7f3d0;">
            <p style="font-size: 0.85rem; color: #047857; font-weight: 700; margin: 0 0 0.5rem 0;">
                <i class="fa-solid fa-comment"></i> Commentaire de validation :
            </p>
            <p style="color: #065f46; line-height: 1.6; margin: 0;">
                {{ $maintenance->commentaire_validation_client }}
            </p>
        </div>
        @endif
    </div>
</div>
@endif

@endsection
