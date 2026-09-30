@extends('layouts.app')

@section('title', 'Opérations & Affectations')

@section('content')
<div class="header">
    <div class="page-title">
        <h1>Opérations & Affectations</h1>
        <p>Lancer des opérations : Affecter équipes/techniciens aux interventions en attente</p>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div style="flex: 1;">
            <div class="stat-val">{{ $stats['enAttente'] }}</div>
            <div class="stat-label">En attente d'affectation</div>
        </div>
        <div class="stat-icon" style="background-color: #fef3c7; color: #b45309;">
            <i class="fa-solid fa-clock"></i>
        </div>
    </div>

    <div class="stat-card">
        <div style="flex: 1;">
            <div class="stat-val">{{ $stats['urgent'] }}</div>
            <div class="stat-label">Urgents</div>
        </div>
        <div class="stat-icon" style="background-color: #fee2e2; color: #dc2626;">
            <i class="fa-solid fa-exclamation-triangle"></i>
        </div>
    </div>

    <div class="stat-card">
        <div style="flex: 1;">
            <div class="stat-val">{{ $stats['enCours'] }}</div>
            <div class="stat-label">En cours</div>
        </div>
        <div class="stat-icon" style="background-color: #dbeafe; color: #1d4ed8;">
            <i class="fa-solid fa-screwdriver-wrench"></i>
        </div>
    </div>

    <div class="stat-card">
        <div style="flex: 1;">
            <div class="stat-val">{{ $stats['terminees'] }}</div>
            <div class="stat-label">Terminées</div>
        </div>
        <div class="stat-icon" style="background-color: #d1fae5; color: #047857;">
            <i class="fa-solid fa-check-circle"></i>
        </div>
    </div>
</div>

<!-- Section 1: Interventions en attente d'affectation -->
<div class="card">
    <div class="card-header">
        <div>
            <h2 class="card-title">🚨 Interventions à affecter</h2>
            <p style="margin-top: 0.35rem; color: #64748b; font-size: 0.85rem;">Statut : <code style="background: #fffbeb; color: #b45309; padding: 0.2rem 0.5rem; border-radius: 0.5rem;">En attente</code> • Non assignées</p>
        </div>
        <span class="badge badge-warning" style="font-size: 1.1rem; padding: 0.5rem 0.9rem;">{{ $demandesEnAttente->count() }}</span>
    </div>

    @if($demandesEnAttente->count() > 0)
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Type</th>
                        <th>Équipement / Site</th>
                        <th>Description</th>
                        <th>Urgence</th>
                        <th>Demandeur</th>
                        <th>📞 Téléphone</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($demandesEnAttente as $demande)
                        @php
                            $typeStyles = [
                                'Installation' => ['bg' => '#ddd6fe', 'color' => '#7c3aed', 'border' => '#c4b5fd'],
                                'Maintenance' => ['bg' => '#d1fae5', 'color' => '#047857', 'border' => '#a7f3d0'],
                                'Dépannage' => ['bg' => '#fef3c7', 'color' => '#b45309', 'border' => '#fde68a']
                            ];
                            $typeStyle = $typeStyles[$demande->type_intervention] ?? $typeStyles['Dépannage'];
                            
                            $urgStyles = [
                                'Urgent' => ['bg' => '#fff1f2', 'color' => '#be123c', 'border' => '#fecdd3'],
                                'Normal' => ['bg' => '#f0f9ff', 'color' => '#0369a1', 'border' => '#bae6fd'],
                                'Faible' => ['bg' => '#f1f5f9', 'color' => '#64748b', 'border' => '#cbd5e1']
                            ];
                            $urgStyle = $urgStyles[$demande->urgence] ?? $urgStyles['Normal'];
                        @endphp
                        <tr style="{{ $demande->urgence === 'Urgent' ? 'background-color: #fff1f233;' : '' }}">
                            <td>
                                <code style="color: #0f172a; font-weight: 700;">#{{ $demande->id }}</code>
                                <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;">
                                    {{ $demande->date_demande ? \Carbon\Carbon::parse($demande->date_demande)->format('d/m/Y') : '—' }}
                                </div>
                            </td>
                            <td>
                                <span class="badge" style="background-color: {{ $typeStyle['bg'] }}; color: {{ $typeStyle['color'] }}; border: 1px solid {{ $typeStyle['border'] }}; font-size: 0.78rem; padding: 0.3rem 0.55rem;">
                                    {{ $demande->type_intervention }}
                                </span>
                            </td>
                            <td>
                                <div style="font-weight: 700; color: #0f172a;">{{ $demande->equipement_reference }}</div>
                                @if($demande->equipement && $demande->equipement->site)
                                <div style="font-size: 0.82rem; color: #475569; margin-top: 0.25rem;">
                                    <i class="fa-solid fa-location-dot"></i> {{ $demande->equipement->site->nom_site }}
                                </div>
                                @endif
                            </td>
                            <td style="max-width: 350px;">
                                <div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: #334155;">
                                    {{ Str::limit($demande->description_panne, 80) }}
                                </div>
                            </td>
                            <td>
                                <span class="badge" style="background-color: {{ $urgStyle['bg'] }}; color: {{ $urgStyle['color'] }}; border: 1px solid {{ $urgStyle['border'] }}; font-size: 0.78rem; padding: 0.3rem 0.55rem;">
                                    @if($demande->urgence === 'Urgent')
                                        <i class="fa-solid fa-bolt"></i>
                                    @endif
                                    {{ $demande->urgence }}
                                </span>
                            </td>
                            <td>
                                <div style="font-weight: 700; color: #0f172a;">{{ $demande->demandeur_nom }}</div>
                                @if($demande->client)
                                <div style="font-size: 0.78rem; color: #64748b;">{{ $demande->client->nom }}</div>
                                @endif
                            </td>
                            <td>
                                @if($demande->demandeur && $demande->demandeur->telephone)
                                <a href="tel:{{ $demande->demandeur->telephone }}" style="display: inline-flex; align-items: center; gap: 0.5rem; color: #047857; text-decoration: none; font-weight: 700; padding: 0.4rem 0.75rem; background-color: #ecfdf5; border-radius: 0.5rem; border: 1px solid #a7f3d0;">
                                    <i class="fa-solid fa-phone"></i>
                                    <span>{{ $demande->demandeur->telephone }}</span>
                                </a>
                                @else
                                <span style="color: #cbd5e1;">—</span>
                                @endif
                            </td>
                            <td style="text-align:right;">
                                @php
                                    $dStart = !empty($demande->date_debut_prevue)
                                        ? \Carbon\Carbon::parse($demande->date_debut_prevue)->format('Y-m-d')
                                        : (!empty($demande->date_debut_souhaitee)
                                            ? \Carbon\Carbon::parse($demande->date_debut_souhaitee)->format('Y-m-d')
                                            : (!empty($demande->date_demande)
                                                ? \Carbon\Carbon::parse($demande->date_demande)->format('Y-m-d')
                                                : date('Y-m-d')));

                                    $dEnd = !empty($demande->date_fin_prevue)
                                        ? \Carbon\Carbon::parse($demande->date_fin_prevue)->format('Y-m-d')
                                        : $dStart;
                                @endphp
                                <button onclick="openAssignModal({{ $demande->id }}, '{{ addslashes($demande->equipement_reference ?? 'Équipement') }}', '{{ $dEnd }}', '{{ $dStart }}', '{{ $dEnd }}', '{{ addslashes($demande->ri_soutarah ?? $demande->demande?->numero_reference_externe ?? '') }}')" class="btn-primary" style="padding: 0.55rem 0.9rem;">
                                    <i class="fa-solid fa-user-plus"></i> Affecter
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div style="text-align: center; padding: 2.5rem 1rem; color: #64748b;">
            <i class="fa-solid fa-square-check" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.35;"></i>
            <p style="font-weight: 700; font-size: 0.95rem;">Aucune intervention en attente d'affectation</p>
            <p style="font-size: 0.85rem;">Toutes les demandes ont été traitées.</p>
        </div>
    @endif
</div>

<!-- Section 2: Opérations en cours -->
<div class="card">
    <div class="card-header" style="align-items: flex-start; gap: 1rem; flex-wrap: wrap;">
        <div>
            <h2 class="card-title">Opérations affectées & en cours</h2>
            <p style="margin-top: 0.35rem; color: #64748b; font-size: 0.85rem;">Interventions déjà assignées à des équipes/techniciens</p>
        </div>
        <form action="{{ route('operations.index') }}" method="GET" style="display: inline-flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
            <select name="statut_operation" onchange="this.form.submit()" style="min-width: 170px;">
                <option value="">Tous les statuts</option>
                <option value="en_cours" {{ request('statut_operation') == 'en_cours' ? 'selected' : '' }}>🔧 En cours</option>
                <option value="resolu" {{ request('statut_operation') == 'resolu' ? 'selected' : '' }}>✅ Terminées</option>
            </select>
            @if(request('statut_operation'))
                <a href="{{ route('operations.index') }}" style="padding: 0.65rem 0.95rem; border-radius: 0.75rem; background-color: #f1f5f9; color: #475569; text-decoration: none; font-weight: 700; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 0.35rem;">
                    <i class="fa-solid fa-redo"></i>
                </a>
            @endif
        </form>
    </div>

    @if($operationsEnCours->count() > 0)
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Type</th>
                        <th>Équipement / Site</th>
                        <th>Équipe / Technicien</th>
                        <th>📅 Date prévue</th>
                        <th>Statut</th>
                        <th style="text-align: right;">Détails</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($operationsEnCours as $op)
                        <tr>
                            <td>
                                <code style="color: #0284c7; font-weight: 700;">#{{ $op->id }}</code>
                                <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;">{{ $op->type_intervention }}</div>
                            </td>
                            <td>
                                @php
                                    $typeStyles = [
                                        'Installation' => ['bg' => '#ddd6fe', 'color' => '#7c3aed'],
                                        'Maintenance' => ['bg' => '#d1fae5', 'color' => '#047857'],
                                        'Dépannage' => ['bg' => '#fef3c7', 'color' => '#b45309']
                                    ];
                                    $typeStyle = $typeStyles[$op->type_intervention] ?? ['bg' => '#f1f5f9', 'color' => '#64748b'];
                                @endphp
                                <span class="badge" style="background-color: {{ $typeStyle['bg'] }}; color: {{ $typeStyle['color'] }};">
                                    {{ $op->type_intervention }}
                                </span>
                            </td>
                            <td>
                                <div style="font-weight: 700; color: #0f172a;">{{ $op->equipement_reference }}</div>
                                @if($op->equipement && $op->equipement->site)
                                <div style="font-size: 0.78rem; color: #64748b; margin-top: 0.25rem;">
                                    <i class="fa-solid fa-location-dot"></i> {{ $op->equipement->site->nom_site }}
                                </div>
                                @endif
                            </td>
                            <td>
                                @if($op->equipe)
                                    <div style="font-weight: 700; color: #1e40af;"><i class="fa-solid fa-people-group"></i> {{ $op->equipe->nom_equipe }}</div>
                                    @if($op->equipe->chef)
                                        <div style="font-size: 0.78rem; color: #475569; margin-top: 0.25rem;">👑 {{ $op->equipe->chef->nom_complet }}</div>
                                    @endif
                                @elseif($op->technicien)
                                    <div style="font-weight: 700; color: #0f766e;"><i class="fa-solid fa-user-gear"></i> {{ $op->technicien->nom_complet }}</div>
                                @else
                                    <span style="color: #cbd5e1;">—</span>
                                @endif
                            </td>
                            <td>
                                @if($op->date_prevue)
                                    <div style="font-weight: 700; color: #1e40af;">
                                        <i class="fa-solid fa-calendar"></i>
                                        {{ \Carbon\Carbon::parse($op->date_prevue)->format('d/m/Y') }}
                                    </div>
                                @else
                                    <span style="color: #cbd5e1;">Non définie</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $sMap = [
                                        'resolu'               => ['bg'=>'#ecfdf5','color'=>'#047857','icon'=>'fa-check-circle',   'label'=>'✅ Terminée'],
                                        'résolu'               => ['bg'=>'#ecfdf5','color'=>'#047857','icon'=>'fa-check-circle',   'label'=>'✅ Terminée'],
                                        'en_attente_piece'     => ['bg'=>'#eff6ff','color'=>'#1e40af','icon'=>'fa-boxes-stacked',  'label'=>'📦 Att. pièce'],
                                        'partiellement_resolu' => ['bg'=>'#fffbeb','color'=>'#92400e','icon'=>'fa-circle-half-stroke','label'=>'⚠️ Partiel'],
                                        'non_resolu'           => ['bg'=>'#fef2f2','color'=>'#b91c1c','icon'=>'fa-ban',           'label'=>'⛔ Bloqué'],
                                    ];
                                    $s = $sMap[$op->statut] ?? ['bg'=>'#eff6ff','color'=>'#1d4ed8','icon'=>'fa-screwdriver-wrench','label'=>'En cours'];
                                @endphp
                                <span class="badge" style="background-color: {{ $s['bg'] }}; color: {{ $s['color'] }}; font-size: 0.78rem;">
                                    <i class="fa-solid {{ $s['icon'] }}"></i> {{ $s['label'] }}
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <a href="{{ route('depannages.show', $op->id) }}" class="btn-primary" style="padding: 0.55rem 0.9rem;">
                                    <i class="fa-solid fa-eye"></i> Voir
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="margin-top: 1.25rem;">
            {{ $operationsEnCours->appends(request()->query())->links('vendor.pagination.custom') }}
        </div>
    @else
        <div style="text-align: center; padding: 2.5rem 1rem; color: #64748b;">
            <i class="fa-solid fa-inbox" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.35;"></i>
            <p style="font-weight: 700; font-size: 0.95rem;">Aucune opération en cours</p>
        </div>
    @endif
</div>

<!-- Section 3: Opérations terminées -->
<div class="card">
    <div class="card-header">
        <div>
            <h2 class="card-title">✅ Opérations Terminées</h2>
            <p style="margin-top: 0.35rem; color: #64748b; font-size: 0.85rem;">Interventions complétées avec succès</p>
        </div>
        <span class="badge badge-success" style="font-size: 1.1rem; padding: 0.5rem 0.9rem;">{{ $operationsTerminees->total() }}</span>
    </div>

    @if($operationsTerminees->count() > 0)
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Type</th>
                        <th>Équipement / Site</th>
                        <th>Équipe / Technicien</th>
                        <th>📅 Date intervention</th>
                        <th>Date terminée</th>
                        <th>Validation Client</th>
                        <th style="text-align: right;">Détails</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($operationsTerminees as $op)
                        <tr>
                            <td>
                                <code style="color: #047857; font-weight: 700;">#{{ $op->id }}</code>
                                <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;">{{ $op->type_intervention }}</div>
                            </td>
                            <td>
                                @php
                                    $typeStyles = [
                                        'Installation' => ['bg' => '#ddd6fe', 'color' => '#7c3aed'],
                                        'Maintenance' => ['bg' => '#d1fae5', 'color' => '#047857'],
                                        'Dépannage' => ['bg' => '#fef3c7', 'color' => '#b45309']
                                    ];
                                    $typeStyle = $typeStyles[$op->type_intervention] ?? ['bg' => '#f1f5f9', 'color' => '#64748b'];
                                @endphp
                                <span class="badge" style="background-color: {{ $typeStyle['bg'] }}; color: {{ $typeStyle['color'] }};">
                                    {{ $op->type_intervention }}
                                </span>
                            </td>
                            <td>
                                <div style="font-weight: 700; color: #0f172a;">{{ $op->equipement_reference }}</div>
                                @if($op->equipement && $op->equipement->site)
                                <div style="font-size: 0.78rem; color: #64748b; margin-top: 0.25rem;">
                                    <i class="fa-solid fa-location-dot"></i> {{ $op->equipement->site->nom_site }}
                                </div>
                                @endif
                            </td>
                            <td>
                                @if($op->equipe)
                                    <div style="font-weight: 700; color: #1e40af;"><i class="fa-solid fa-people-group"></i> {{ $op->equipe->nom_equipe }}</div>
                                    @if($op->equipe->chef)
                                        <div style="font-size: 0.78rem; color: #475569; margin-top: 0.25rem;">👑 {{ $op->equipe->chef->nom_complet }}</div>
                                    @endif
                                @elseif($op->technicien)
                                    <div style="font-weight: 700; color: #0f766e;"><i class="fa-solid fa-user-gear"></i> {{ $op->technicien->nom_complet }}</div>
                                @else
                                    <span style="color: #cbd5e1;">—</span>
                                @endif
                            </td>
                            <td>
                                @if($op->date_prevue)
                                    <div style="font-weight: 700; color: #1e40af;">
                                        <i class="fa-solid fa-calendar"></i>
                                        {{ \Carbon\Carbon::parse($op->date_prevue)->format('d/m/Y') }}
                                    </div>
                                @else
                                    <span style="color: #cbd5e1;">Non définie</span>
                                @endif
                            </td>
                            <td>
                                @if($op->date_intervention_reelle)
                                    <div style="font-weight: 700; color: #047857;">
                                        <i class="fa-solid fa-check-circle"></i>
                                        {{ \Carbon\Carbon::parse($op->date_intervention_reelle)->format('d/m/Y') }}
                                    </div>
                                @elseif($op->date_prevue)
                                    <div style="font-weight: 600; color: #64748b;">
                                        <i class="fa-solid fa-calendar"></i>
                                        {{ \Carbon\Carbon::parse($op->date_prevue)->format('d/m/Y') }}
                                    </div>
                                @else
                                    <span style="color: #cbd5e1;">—</span>
                                @endif
                            </td>
                            <td>
                                @if($op->statut_validation_finale_client === 'validé')
                                    <span class="badge" style="background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;">
                                        <i class="fa-solid fa-check-double"></i> Validé
                                    </span>
                                @elseif($op->statut_validation_finale_client === 'rejeté')
                                    <span class="badge" style="background-color: #fff1f2; color: #be123c; border: 1px solid #fecdd3;">
                                        <i class="fa-solid fa-times-circle"></i> Rejeté
                                    </span>
                                @else
                                    <span class="badge" style="background-color: #fffbeb; color: #92400e; border: 1px solid #fde68a;">
                                        <i class="fa-solid fa-clock"></i> En attente
                                    </span>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                <a href="{{ route('depannages.show', $op->id) }}" class="btn-primary" style="padding: 0.55rem 0.9rem;">
                                    <i class="fa-solid fa-eye"></i> Voir
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="margin-top: 1.25rem;">
            {{ $operationsTerminees->links('vendor.pagination.custom') }}
        </div>
    @else
        <div style="text-align: center; padding: 2.5rem 1rem; color: #64748b;">
            <i class="fa-solid fa-box-open" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.35;"></i>
            <p style="font-weight: 700; font-size: 0.95rem;">Aucune opération terminée</p>
        </div>
    @endif
</div>

<!-- Modal d'affectation -->
<div id="modalAssign" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 1rem; padding: 2rem; max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2 style="font-size: 1.25rem; font-weight: 800; color: #0f172a;">Affecter une équipe/technicien</h2>
            <button onclick="closeAssignModal()" style="background: none; border: none; font-size: 1.5rem; color: #94a3b8; cursor: pointer;">&times;</button>
        </div>

        <form id="formAssign" method="POST" action="">
            @csrf
            @method('PATCH')

            <div style="margin-bottom: 1rem; padding: 1rem; background-color: #f8fafc; border-radius: 0.75rem;">
                <div style="font-size: 0.85rem; color: #64748b;">Intervention #<span id="assignInterventionId"></span></div>
                <div style="font-weight: 700; color: #0f172a; margin-top: 0.25rem;" id="assignEquipement"></div>
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 700; color: #334155;">Mode d'affectation</label>
                <select name="mode_affectation" id="modeAffectation" onchange="toggleAffectationMode()" required style="width: 100%; padding: 0.75rem 1rem;">
                    <option value="equipe">Affecter une équipe</option>
                    <option value="technicien">Affecter un technicien individuel</option>
                </select>
            </div>

            <div id="divEquipe" style="margin-bottom: 1.25rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 700; color: #334155;">Équipe</label>
                <select name="equipe_id" id="equipe_id" style="width: 100%; padding: 0.75rem 1rem;">
                    <option value="">-- Sélectionner une équipe --</option>
                    @foreach($equipes as $equipe)
                        <option value="{{ $equipe->id }}">{{ $equipe->nom_equipe }} @if($equipe->chef)(Chef: {{ $equipe->chef->nom_complet }})@endif</option>
                    @endforeach
                </select>
            </div>

            <div id="divTechnicien" style="display: none; margin-bottom: 1.25rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 700; color: #334155;">Technicien</label>
                <select name="technicien_id" id="technicien_id" style="width: 100%; padding: 0.75rem 1rem;">
                    <option value="">-- Sélectionner un technicien --</option>
                    @foreach($techniciens as $tech)
                        <option value="{{ $tech->id }}">{{ $tech->nom_complet }} ({{ $tech->type_utilisateur }})</option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 700; color: #334155;">
                    <i class="fa-solid fa-calendar-day"></i> Date d'Intervention Souhaitée
                </label>
                <div id="date_auto_info" style="display:none; background:#dcfce7; border:1px solid #86efac; border-radius:0.5rem; padding:0.5rem 0.75rem; margin-bottom:0.5rem; font-size:0.8rem; color:#15803d; font-weight:700;">
                    <i class="fa-solid fa-calendar-check"></i> Date définie dans la demande
                </div>
                <input type="date" name="date_prevue" id="datePrevueModal" required style="width: 100%; padding: 0.75rem 0.85rem; border: 2px solid #cbd5e1; border-radius: 0.5rem; font-size: 0.95rem;">
                <small style="display: block; margin-top: 0.5rem; color: #64748b; font-size: 0.8rem;">
                    <i class="fa-solid fa-info-circle"></i> Vous pouvez modifier cette date selon vos disponibilités
                </small>
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 700; color: #334155;">
                    <i class="fa-solid fa-barcode" style="color: #2563eb;"></i> N° RI Soutarah / Référence
                </label>
                <input type="text" name="ri_soutarah" id="riSoutarahModal" maxlength="50" placeholder="Ex: RI-2026-001" style="width: 100%; padding: 0.75rem 0.85rem; border: 2px solid #cbd5e1; border-radius: 0.5rem; font-size: 0.95rem; font-weight: 700; color: #1e3a8a;">
                <small style="display: block; margin-top: 0.35rem; color: #64748b; font-size: 0.8rem;">
                    Numéro de référence Soutarah pour le suivi
                </small>
            </div>

            <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                <button type="button" onclick="closeAssignModal()" style="padding: 0.75rem 1.25rem; background-color: #f1f5f9; color: #475569; border: none; border-radius: 0.75rem; font-weight: 700; cursor: pointer;">
                    Annuler
                </button>
                <button type="submit" class="btn-primary" style="padding: 0.75rem 1.5rem;">
                    <i class="fa-solid fa-check"></i> Affecter
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openAssignModal(id, equipement, datePrevue, dateDebut, dateFin, riSoutarah) {
    document.getElementById('modalAssign').style.display = 'flex';
    document.getElementById('assignInterventionId').textContent = id;
    document.getElementById('assignEquipement').textContent = equipement;
    document.getElementById('formAssign').action = `/depannages/${id}/assign`;
    document.getElementById('riSoutarahModal').value = riSoutarah || '';
    
    const inputDate = document.getElementById('datePrevueModal');
    const dateInfo  = document.getElementById('date_auto_info');
    
    // Prioriser dateDebut, puis datePrevue
    const dateValue = dateDebut || datePrevue || dateFin;
    
    if (dateValue) {
        inputDate.value = dateValue;
        dateInfo.style.display = 'block';
    } else {
        inputDate.value = '';
        dateInfo.style.display = 'none';
    }
}

function closeAssignModal() {
    document.getElementById('modalAssign').style.display = 'none';
}

function toggleAffectationMode() {
    const mode = document.getElementById('modeAffectation').value;
    const divEquipe = document.getElementById('divEquipe');
    const divTechnicien = document.getElementById('divTechnicien');
    const equipeSelect = document.getElementById('equipe_id');
    const technicienSelect = document.getElementById('technicien_id');

    if (mode === 'equipe') {
        divEquipe.style.display = 'block';
        divTechnicien.style.display = 'none';
        equipeSelect.required = true;
        technicienSelect.required = false;
        technicienSelect.value = '';
    } else {
        divEquipe.style.display = 'none';
        divTechnicien.style.display = 'block';
        technicienSelect.required = true;
        equipeSelect.required = false;
        equipeSelect.value = '';
    }
}
</script>
@endsection
