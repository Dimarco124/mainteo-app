@extends('layouts.app')

@section('title', 'Mes Demandes & Maintenances')

@section('content')
<div class="header">
    <div>
        <h1>Espace Demandeur — Suivi des Demandes & Maintenances</h1>
        <p style="color: #64748b; font-size: 0.9rem;">
            <i class="fa-solid fa-map-pin"></i> 
            @php
                $sitesAssignes = Auth::user()->sitesAssignes;
                $siteUnique = Auth::user()->site_id ? \App\Models\Site::find(Auth::user()->site_id) : null;
            @endphp
            @if($sitesAssignes && $sitesAssignes->count() > 0)
                @if($sitesAssignes->count() === 1)
                    Site : <strong style="color: #059669;">{{ $sitesAssignes->first()->nom_site }}</strong>
                @else
                    Sites : <strong style="color: #059669;">{{ $sitesAssignes->pluck('nom_site')->join(', ') }}</strong>
                @endif
            @elseif($siteUnique)
                Site : <strong style="color: #059669;">{{ $siteUnique->nom_site }}</strong>
            @else
                Sites : <strong style="color: #dc2626;">Aucun site assigné</strong>
            @endif
        </p>
    </div>
    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
        <a href="{{ route('planning.index') }}" class="btn-secondary" style="background-color: #f1f5f9; color: #334155; padding: 0.65rem 1.1rem; border-radius: 0.75rem; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-calendar-days" style="color: #059669;"></i> Planning
        </a>
        <a href="{{ route('demandes.create') }}" class="btn-primary">
            <i class="fa-solid fa-plus-circle"></i> Nouvelle Demande
        </a>
    </div>
</div>

<!-- Statistiques -->
<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
    <div class="stat-card">
        <div style="flex: 1;">
            <div class="stat-val">{{ $stats['total'] }}</div>
            <div class="stat-label">Total Demandes</div>
        </div>
        <div class="stat-icon" style="background-color: #dbeafe; color: #1d4ed8;">
            <i class="fa-solid fa-clipboard-list"></i>
        </div>
    </div>

    <div class="stat-card">
        <div style="flex: 1;">
            <div class="stat-val">{{ $stats['en_attente'] }}</div>
            <div class="stat-label">En Attente Validation</div>
        </div>
        <div class="stat-icon" style="background-color: #fef3c7; color: #b45309;">
            <i class="fa-solid fa-hourglass-half"></i>
        </div>
    </div>

    <div class="stat-card">
        <div style="flex: 1;">
            <div class="stat-val">{{ $stats['en_cours'] }}</div>
            <div class="stat-label">Demandes En Cours</div>
        </div>
        <div class="stat-icon" style="background-color: #eff6ff; color: #2563eb;">
            <i class="fa-solid fa-wrench"></i>
        </div>
    </div>

    <div class="stat-card" style="border-left: 4px solid #059669;">
        <div style="flex: 1;">
            <div class="stat-val" style="color: #059669;">{{ $stats['maintenances_total'] }}</div>
            <div class="stat-label">Maintenances Site</div>
            <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;">
                <span style="color: #059669; font-weight: 700;">{{ $stats['maintenances_en_cours'] }}</span> planifiée(s)/en cours
            </div>
        </div>
        <div class="stat-icon" style="background-color: #ecfdf5; color: #059669;">
            <i class="fa-solid fa-shield-halved"></i>
        </div>
    </div>

    <div class="stat-card">
        <div style="flex: 1;">
            <div class="stat-val">{{ $stats['a_confirmer'] }}</div>
            <div class="stat-label">À Confirmer</div>
        </div>
        <div class="stat-icon" style="background-color: #fffbeb; color: #b45309;">
            <i class="fa-solid fa-exclamation-triangle"></i>
        </div>
    </div>

    <div class="stat-card">
        <div style="flex: 1;">
            <div class="stat-val">{{ $stats['closees'] }}</div>
            <div class="stat-label">Clôturées</div>
        </div>
        <div class="stat-icon" style="background-color: #d1fae5; color: #047857;">
            <i class="fa-solid fa-check-double"></i>
        </div>
    </div>
</div>

<!-- SECTION 1 : Maintenances Préventives & Programmées sur mon site -->
<div class="card" id="section-maintenances" style="margin-bottom: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
        <div>
            <h3 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-shield-halved" style="color: #059669;"></i> Maintenances Programmées sur mon site ({{ $maintenancesSite->count() }})
            </h3>
            <p style="margin: 0.25rem 0 0 0; font-size: 0.85rem; color: #64748b;">
                Sessions de maintenance préventive et interventions planifiées par l'équipe technique
            </p>
        </div>
        <a href="{{ route('planning.index') }}" style="color: #059669; text-decoration: none; font-size: 0.85rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.35rem;">
            <i class="fa-solid fa-calendar-days"></i> Ouvrir le planning complet <i class="fa-solid fa-arrow-right"></i>
        </a>
    </div>

    @if($maintenancesSite->isNotEmpty())
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>N° Maintenance</th>
                        <th>Type</th>
                        <th>Site</th>
                        <th>Équipe(s) / Technicien</th>
                        <th>Période d'intervention</th>
                        <th>Progression Équipements</th>
                        <th>Statut</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($maintenancesSite as $m)
                    <tr>
                        <td>
                            <strong style="color: #059669; font-size: 0.95rem;">#{{ $m->numero_maintenance }}</strong>
                        </td>
                        <td>
                            <span class="badge" style="background-color: #f0fdf4; color: #166534; border: 1px solid #bbf7d0;">
                                {{ ucfirst($m->type_maintenance ?? 'Préventive') }}
                            </span>
                        </td>
                        <td>
                            <strong>{{ $m->site->nom_site ?? 'N/A' }}</strong><br>
                            <small style="color: #64748b;">{{ $m->base->nom_base ?? ($m->client->nom ?? '') }}</small>
                        </td>
                        <td>
                            @php
                                $nomEquipes = ($m->equipes && $m->equipes->isNotEmpty())
                                    ? $m->equipes->pluck('nom_equipe')->join(', ')
                                    : ($m->equipe ? $m->equipe->nom_equipe : ($m->technicien ? $m->technicien->nom_complet : 'Non assigné'));
                            @endphp
                            <div style="display: flex; align-items: center; gap: 0.4rem;">
                                <i class="fa-solid fa-hard-hat" style="color: #0284c7;"></i>
                                <strong style="color: #0f172a;">{{ $nomEquipes }}</strong>
                            </div>
                        </td>
                        <td>
                            <div style="font-size: 0.85rem; color: #1e293b; font-weight: 600;">
                                <i class="fa-solid fa-calendar-day" style="color: #64748b;"></i>
                                {{ $m->date_debut_prevue ? $m->date_debut_prevue->format('d/m/Y') : 'N/A' }}
                            </div>
                            @if($m->date_fin_prevue)
                            <div style="font-size: 0.75rem; color: #64748b;">
                                au {{ $m->date_fin_prevue->format('d/m/Y') }}
                            </div>
                            @endif
                        </td>
                        <td style="min-width: 150px;">
                            @php
                                $prevus = $m->nombre_equipements_prevus ?? 0;
                                $traites = $m->nombre_equipements_traites ?? 0;
                                $progression = $prevus > 0 ? min(100, round(($traites / $prevus) * 100)) : ($m->pourcentage_avancement ?? 0);
                            @endphp
                            <div style="display: flex; justify-content: space-between; font-size: 0.75rem; margin-bottom: 0.25rem;">
                                <span style="color: #64748b;">{{ $traites }} / {{ $prevus }} équip.</span>
                                <strong style="color: #059669;">{{ $progression }}%</strong>
                            </div>
                            <div style="background: #e2e8f0; border-radius: 9999px; height: 7px; overflow: hidden;">
                                <div style="background: linear-gradient(90deg, #10b981, #059669); height: 100%; width: {{ $progression }}%;"></div>
                            </div>
                        </td>
                        <td>
                            @if($m->statut === 'planifiée')
                                <span class="badge" style="background: #fffbeb; color: #b45309; border: 1px solid #fde68a;">⏳ Planifiée</span>
                            @elseif($m->statut === 'confirmée_client')
                                <span class="badge" style="background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0;">✔️ Confirmée</span>
                            @elseif($m->statut === 'en_cours')
                                <span class="badge" style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;">🔄 En cours</span>
                            @elseif($m->statut === 'terminée')
                                <span class="badge" style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;">✅ Terminée</span>
                            @else
                                <span class="badge">{{ ucfirst($m->statut) }}</span>
                            @endif
                        </td>
                        <td style="text-align: right;">
                            <a href="{{ route('maintenances.show', $m) }}" class="btn-primary" style="font-size: 0.8rem; padding: 0.45rem 0.85rem;">
                                <i class="fa-solid fa-eye"></i> Détails
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div style="text-align: center; padding: 2.5rem; color: #94a3b8;">
            <i class="fa-solid fa-shield-halved" style="font-size: 3.5rem; margin-bottom: 0.75rem; opacity: 0.3;"></i>
            <p style="font-size: 1rem; font-weight: 700; margin-bottom: 0.25rem;">Aucune maintenance programmée</p>
            <p style="font-size: 0.85rem;">Les sessions de maintenance préventive planifiées sur votre site apparaîtront ici.</p>
        </div>
    @endif
</div>

<!-- SECTION 2 : Liste des Demandes d'Intervention -->
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
        <div>
            <h3 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-list-check"></i> Mes Demandes d'Intervention ({{ $mesDemandes->count() }})
            </h3>
            <p style="margin: 0.25rem 0 0 0; font-size: 0.85rem; color: #64748b;">
                Suivi des signalements de pannes, dépannages et installations
            </p>
        </div>
        <a href="{{ route('demandes.index') }}" style="color: #059669; text-decoration: none; font-size: 0.85rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.35rem;">
            Toutes les demandes <i class="fa-solid fa-arrow-right"></i>
        </a>
    </div>

    @if($mesDemandes->isNotEmpty())
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>N° Demande</th>
                        <th>Date</th>
                        <th>Équipement</th>
                        <th>Urgence</th>
                        <th>Intervention / Affectation</th>
                        <th>Statut</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($mesDemandes as $d)
                    <tr>
                        <td><strong style="color: #059669;">#{{ $d->numero_demande }}</strong></td>
                        <td>{{ $d->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            @if($d->equipement)
                                <strong>{{ $d->equipement->equipement_code }}</strong><br>
                                <small style="color: #64748b;">{{ $d->equipement->equipement_nom }}</small>
                            @else
                                <span style="color: #94a3b8;">Équipement non renseigné</span>
                            @endif
                        </td>
                        <td>
                            @php
                            $urgenceStyles = [
                                'faible' => ['bg' => '#f1f5f9', 'color' => '#64748b', 'border' => '#cbd5e1'],
                                'moyen' => ['bg' => '#f0f9ff', 'color' => '#0369a1', 'border' => '#bae6fd'],
                                'urgent' => ['bg' => '#fffbeb', 'color' => '#b45309', 'border' => '#fde68a'],
                                'critique' => ['bg' => '#fff1f2', 'color' => '#be123c', 'border' => '#fecdd3']
                            ];
                            $urgStyle = $urgenceStyles[$d->niveau_urgence] ?? $urgenceStyles['moyen'];
                            @endphp
                            <span class="badge" style="background-color: {{ $urgStyle['bg'] }}; color: {{ $urgStyle['color'] }}; border: 1px solid {{ $urgStyle['border'] }};">
                                {{ ucfirst($d->niveau_urgence) }}
                            </span>
                        </td>
                        <td>
                            @if($d->technicalOperation)
                                @php
                                    $top = $d->technicalOperation;
                                @endphp
                                @if($top->equipe)
                                    <div style="font-weight: 700; color: #1e40af; font-size: 0.85rem; display: flex; align-items: center; gap: 0.35rem;">
                                        <i class="fa-solid fa-hard-hat"></i> {{ $top->equipe->nom_equipe }}
                                    </div>
                                @elseif($top->technicien)
                                    <div style="font-weight: 700; color: #1e40af; font-size: 0.85rem; display: flex; align-items: center; gap: 0.35rem;">
                                        <i class="fa-solid fa-user-gear"></i> {{ $top->technicien->nom_complet }}
                                    </div>
                                @else
                                    <span style="color: #b45309; font-size: 0.8rem; font-weight: 600;">
                                        <i class="fa-solid fa-hourglass-half"></i> Opération en attente d'affectation
                                    </span>
                                @endif

                                @if($top->date_prevue)
                                    <div style="font-size: 0.75rem; color: #047857; margin-top: 0.15rem;">
                                        <i class="fa-solid fa-calendar"></i> Prévue le {{ \Carbon\Carbon::parse($top->date_prevue)->format('d/m/Y') }}
                                    </div>
                                @endif
                            @else
                                <span style="color: #94a3b8; font-size: 0.8rem;">
                                    <i class="fa-solid fa-clock"></i> En cours d'instruction
                                </span>
                            @endif
                        </td>
                        <td>
                            @php
                            $statutStyles = [
                                'pending_client_validation' => ['bg' => '#fffbeb', 'color' => '#b45309', 'border' => '#fde68a', 'label' => 'En attente validation'],
                                'validated_by_client' => ['bg' => '#f0f9ff', 'color' => '#0369a1', 'border' => '#bae6fd', 'label' => 'Validée par superviseur'],
                                'rejected_by_client' => ['bg' => '#fff1f2', 'color' => '#be123c', 'border' => '#fecdd3', 'label' => 'Rejetée'],
                                'needs_technical_operation' => ['bg' => '#eff6ff', 'color' => '#1e40af', 'border' => '#bfdbfe', 'label' => 'En cours traitement'],
                                'confirmed_by_demandeur' => ['bg' => '#ecfdf5', 'color' => '#047857', 'border' => '#a7f3d0', 'label' => 'Confirmée'],
                                'closed' => ['bg' => '#f8fafc', 'color' => '#475569', 'border' => '#cbd5e1', 'label' => 'Clôturée']
                            ];
                            $statStyle = $statutStyles[$d->statut] ?? ['bg' => '#f1f5f9', 'color' => '#64748b', 'border' => '#cbd5e1', 'label' => $d->statut];
                            @endphp
                            <span class="badge" style="background-color: {{ $statStyle['bg'] }}; color: {{ $statStyle['color'] }}; border: 1px solid {{ $statStyle['border'] }}; font-size: 0.75rem;">
                                {{ $statStyle['label'] }}
                            </span>

                            {{-- Badge "À CONFIRMER" si rapport soumis --}}
                            @if($d->technicalOperation && $d->technicalOperation->rapport_technique && !$d->date_confirmation_demandeur)
                                <br>
                                <span class="badge" style="background-color: #fff1f2; color: #be123c; border: 1px solid #fecdd3; font-size: 0.75rem; margin-top: 0.25rem; animation: pulse 2s infinite;">
                                    <i class="fa-solid fa-exclamation-circle"></i> ACTION REQUISE
                                </span>
                            @endif
                        </td>
                        <td style="text-align: right;">
                            <a href="{{ route('demandes.show', $d) }}" class="btn-primary" style="font-size: 0.8rem; padding: 0.4rem 0.8rem;">
                                <i class="fa-solid fa-eye"></i> Détails
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div style="text-align: center; padding: 3rem; color: #94a3b8;">
            <i class="fa-solid fa-clipboard" style="font-size: 4rem; margin-bottom: 1rem; opacity: 0.3;"></i>
            <p style="font-size: 1.1rem; font-weight: 600; margin-bottom: 0.5rem;">Aucune demande pour le moment</p>
            <p style="font-size: 0.9rem;">Créez votre première demande d'intervention en cliquant sur le bouton ci-dessus.</p>
        </div>
    @endif
</div>

<style>
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}
</style>
@endsection
