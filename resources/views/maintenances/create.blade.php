@extends('layouts.app')

@section('title', 'Planifier une Maintenance')

@section('content')
<style>
.section-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 1rem;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}
.section-title {
    font-size: 0.85rem;
    font-weight: 800;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-bottom: 1.25rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.section-title-left {
    display: flex;
    align-items: center;
    gap: 0.6rem;
}
.form-label {
    display: block;
    font-size: 0.82rem;
    color: #475569;
    margin-bottom: 0.4rem;
    font-weight: 700;
}
.form-grid-2 {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.25rem;
}
/* Sélection des équipes en cartes modernes */
.equipes-container {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
}
.equipe-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 0.85rem 1.1rem;
    border: 1.5px solid #e2e8f0;
    border-radius: 0.75rem;
    background: #ffffff;
    cursor: pointer;
    transition: all 0.18s ease;
    user-select: none;
}
.equipe-card:hover {
    border-color: #cbd5e1;
    background: #f8fafc;
}
.equipe-card.selected {
    border-color: #10b981;
    background: #f0fdf4;
    box-shadow: 0 2px 6px rgba(16, 185, 129, 0.1);
}
.equipe-card-left {
    display: flex;
    align-items: center;
    gap: 0.9rem;
    flex: 1;
}
.equipe-checkbox {
    width: 20px;
    height: 20px;
    cursor: pointer;
    accent-color: #10b981;
    flex-shrink: 0;
}
.equipe-avatar {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: #eff6ff;
    color: #2563eb;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
    flex-shrink: 0;
    transition: all 0.18s ease;
}
.equipe-card.selected .equipe-avatar {
    background: #d1fae5;
    color: #059669;
}
.equipe-info-title {
    font-weight: 700;
    color: #1e293b;
    font-size: 0.92rem;
    line-height: 1.3;
}
.equipe-info-sub {
    font-size: 0.78rem;
    color: #64748b;
    margin-top: 0.15rem;
}
.equipe-card-right {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.badge-members {
    font-size: 0.78rem;
    color: #475569;
    background: #f1f5f9;
    padding: 0.25rem 0.65rem;
    border-radius: 9999px;
    font-weight: 600;
    white-space: nowrap;
}
.badge-selected-tag {
    display: none;
    font-size: 0.72rem;
    color: #ffffff;
    background: #10b981;
    padding: 0.2rem 0.55rem;
    border-radius: 9999px;
    font-weight: 700;
}
.equipe-card.selected .badge-selected-tag {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}
@media (max-width: 640px) {
    .form-grid-2 { grid-template-columns: 1fr; }
    .equipe-card-right { flex-direction: column; align-items: flex-end; gap: 0.35rem; }
}
</style>

<div class="header">
    <div>
        <a href="{{ route('planning.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.4rem; margin-bottom: 0.5rem;">
            <i class="fa-solid fa-arrow-left"></i> Retour au Planning
        </a>
        <h1>Planifier une Maintenance</h1>
        <p style="color: #64748b; font-size: 0.9rem; margin-top: 0.25rem;">Planifiez et affectez des équipes à une maintenance préventive ou corrective</p>
    </div>
</div>

<div style="max-width: 900px;">

@if($errors->any())
<div style="background: #fee2e2; border: 1px solid #ef4444; border-radius: 0.75rem; padding: 1rem 1.25rem; margin-bottom: 1.5rem; color: #991b1b;">
    <div style="font-weight: 700; display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <span>Veuillez corriger les erreurs suivantes :</span>
    </div>
    <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.88rem; line-height: 1.5;">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form id="maintenanceForm" action="{{ route('maintenances.store') }}" method="POST" onsubmit="return validateMaintenanceForm(event)">
    @csrf

    {{-- ══════════════════════════════════════════════════
         SECTION 1 — Type & Localisation
    ══════════════════════════════════════════════════ --}}
    <div class="section-card">
        <div class="section-title">
            <i class="fa-solid fa-clipboard-list" style="color: #3b82f6;"></i>
            Informations générales
        </div>

        <div style="margin-bottom: 1.25rem;">
            <label class="form-label">Type de Maintenance *</label>
            <select name="type_maintenance" required style="width: 100%; padding: 0.75rem;">
                <option value="">-- Sélectionner le type --</option>
                <option value="préventive" {{ old('type_maintenance') == 'préventive' ? 'selected' : '' }}>
                    Maintenance Préventive (Planifiée)
                </option>
                <option value="corrective programmée" {{ old('type_maintenance') == 'corrective programmée' ? 'selected' : '' }}>
                    Maintenance Corrective Programmée
                </option>
            </select>
            @error('type_maintenance')
            <span style="color: #be123c; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-grid-2">
            <div>
                <label class="form-label">Client *</label>
                <select name="client_id" id="client_select" required style="width: 100%; padding: 0.75rem;" {{ $clients->count() === 1 ? 'disabled' : '' }}>
                    <option value="">-- Sélectionner un client --</option>
                    @foreach($clients as $client)
                    <option value="{{ $client->id }}" {{ old('client_id', $clients->count() === 1 ? $client->id : '') == $client->id ? 'selected' : '' }}>
                        {{ $client->nom }}
                    </option>
                    @endforeach
                </select>
                @if($clients->count() === 1)
                <input type="hidden" name="client_id" value="{{ $clients->first()->id }}">
                @endif
                @error('client_id')
                <span style="color: #be123c; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>

            <div id="base_container" style="{{ isset($baseAssignee) ? '' : 'display: none;' }}">
                <label class="form-label">Base</label>
                <select name="base_id" id="base_select" style="width: 100%; padding: 0.75rem;" {{ isset($baseAssignee) ? 'disabled' : '' }}>
                    <option value="">Aucune base</option>
                    @if(isset($baseAssignee))
                    <option value="{{ $baseAssignee->id }}" selected>{{ $baseAssignee->nom_base }}</option>
                    @endif
                </select>
                @if(isset($baseAssignee))
                <input type="hidden" name="base_id" value="{{ $baseAssignee->id }}">
                @endif
            </div>
        </div>

        <div id="site_container" style="display: none; margin-top: 1.25rem;">
            <label class="form-label">Site</label>
            <select name="site_id" id="site_select" style="width: 100%; padding: 0.75rem;">
                <option value="">Chargement...</option>
            </select>
        </div>

        {{-- Compteur équipements --}}
        <div style="margin-top: 1rem; display: flex; align-items: center; justify-content: space-between; background: #f8fafc; border-radius: 0.6rem; padding: 0.65rem 1rem;">
            <span style="font-size: 0.85rem; color: #475569; font-weight: 600;">
                <i class="fa-solid fa-gears" style="color: #059669;"></i> Équipements sur ce périmètre :
            </span>
            <span id="equipement_count_badge" style="font-weight: 800; font-size: 0.9rem; color: #059669;">--</span>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════
         SECTION 2 — Description & Tâches
    ══════════════════════════════════════════════════ --}}
    <div class="section-card">
        <div class="section-title">
            <i class="fa-solid fa-file-lines" style="color: #8b5cf6;"></i>
            Description & tâches
        </div>

        <div style="margin-bottom: 1.25rem;">
            <label class="form-label">Description générale *</label>
            <textarea name="description" required rows="3" style="width: 100%; padding: 0.75rem;" placeholder="Décrivez l'objectif de cette maintenance...">{{ old('description') }}</textarea>
            @error('description')
            <span style="color: #be123c; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-grid-2">
            <div>
                <label class="form-label">Tâches prévues</label>
                <textarea name="taches_prevues" rows="3" style="width: 100%; padding: 0.75rem;" placeholder="Ex: Vérification filtres, Test pression...">{{ old('taches_prevues') }}</textarea>
            </div>
            <div>
                <label class="form-label">Pièces prévues</label>
                <textarea name="pieces_prevues" rows="3" style="width: 100%; padding: 0.75rem;" placeholder="Ex: Filtres, Joints...">{{ old('pieces_prevues') }}</textarea>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════
         SECTION 3 — Planification & Durée estimée
    ══════════════════════════════════════════════════ --}}
    <div class="section-card">
        <div class="section-title">
            <div class="section-title-left">
                <i class="fa-solid fa-calendar-days" style="color: #f59e0b;"></i>
                <span>Planification & Durée estimée</span>
            </div>
        </div>

        <div class="form-grid-2" style="margin-bottom: 1.25rem;">
            <div>
                <label class="form-label">Date et Heure de Début *</label>
                <input type="datetime-local" name="date_debut_prevue" id="date_debut_input"
                    value="{{ old('date_debut_prevue') }}" required
                    style="width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 0.5rem;"
                    onchange="calculateEndDate()">
                @error('date_debut_prevue')
                <span style="color: #be123c; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
                <small style="color: #64748b; font-size: 0.78rem; display: block; margin-top: 0.35rem;">
                    <i class="fa-solid fa-info-circle"></i> Date et heure prévues pour le démarrage des travaux.
                </small>
            </div>

            <div>
                <label class="form-label">Cadence Journalière (Splits / jour) *</label>
                <div style="position: relative;">
                    <input type="number" name="equipements_par_jour" id="equipements_par_jour_input"
                        value="{{ old('equipements_par_jour', 8) }}" min="1" max="100" required
                        style="width: 100%; padding: 0.75rem 4rem 0.75rem 0.75rem; border: 1.5px solid #059669; border-radius: 0.5rem; font-weight: 800; font-size: 1rem; color: #065f46; background: #f0fdf4;"
                        oninput="calculateEndDate()" onchange="calculateEndDate()">
                    <span style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); font-size: 0.78rem; font-weight: 700; color: #059669;">
                        éq. / jour
                    </span>
                </div>
                <small style="color: #64748b; font-size: 0.78rem; display: block; margin-top: 0.35rem;">
                    <i class="fa-solid fa-sliders"></i> Nombre de splits/équipements que l'équipe traite chaque jour (défaut : 8).
                </small>
            </div>
        </div>

        {{-- Champ caché pour stocker la date de fin calculée --}}
        <input type="hidden" name="date_fin_prevue" id="date_fin_input" value="{{ old('date_fin_prevue') }}">
        {{-- Champ caché pour les jours non ouvrables (JSON) --}}
        <input type="hidden" name="jours_non_ouvrables" id="jours_non_ouvrables_input" value="{{ old('jours_non_ouvrables', '[]') }}">

        {{-- Calendrier interactif des Jours Ouvrables / Non Ouvrables --}}
        <div id="working_days_section" style="margin-top: 1.25rem; display: none; background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 0.75rem; padding: 1.2rem;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 0.85rem;">
                <div>
                    <div style="font-weight: 800; font-size: 0.92rem; color: #1e293b; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fa-solid fa-calendar-check" style="color: #0284c7;"></i>
                        <span>Calendrier des jours ouvrables & repos</span>
                    </div>
                    <p style="font-size: 0.78rem; color: #64748b; margin: 0.2rem 0 0 0;">
                        Cochez/décochez les jours non ouvrables (dimanches, samedis, fériés). La maintenance s'étalera automatiquement en sautant ces jours pour respecter le nombre exact de jours ouvrables nécessaires.
                    </p>
                </div>
                {{-- Boutons d'actions rapides --}}
                <div style="display: flex; gap: 0.4rem; flex-wrap: wrap;">
                    <button type="button" onclick="setWorkingDaysPreset('sundays')" style="padding: 0.3rem 0.65rem; border-radius: 6px; border: 1px solid #cbd5e1; background: #f8fafc; font-size: 0.75rem; font-weight: 700; color: #475569; cursor: pointer;">
                        🚫 Exclure dimanches
                    </button>
                    <button type="button" onclick="setWorkingDaysPreset('weekends')" style="padding: 0.3rem 0.65rem; border-radius: 6px; border: 1px solid #cbd5e1; background: #f8fafc; font-size: 0.75rem; font-weight: 700; color: #475569; cursor: pointer;">
                        🚫 Exclure weekends (Sam & Dim)
                    </button>
                    <button type="button" onclick="setWorkingDaysPreset('all_working')" style="padding: 0.3rem 0.65rem; border-radius: 6px; border: 1px solid #cbd5e1; background: #f8fafc; font-size: 0.75rem; font-weight: 700; color: #0284c7; cursor: pointer;">
                        ✅ Tous ouvrables
                    </button>
                </div>
            </div>

            {{-- Grille des jours générés dynamiquement --}}
            <div id="working_days_grid" style="display: flex; flex-wrap: wrap; gap: 0.5rem; max-height: 280px; overflow-y: auto; padding: 0.3rem; background: #f8fafc; border-radius: 0.5rem; border: 1px dashed #cbd5e1;">
                {{-- Alimenté dynamiquement par JavaScript --}}
            </div>

            <div style="margin-top: 0.75rem; display: flex; justify-content: space-between; align-items: center; font-size: 0.78rem; color: #64748b;">
                <span id="working_days_summary">0 jour ouvrable requis</span>
                <div style="display: flex; gap: 0.75rem; align-items: center;">
                    <span style="display: inline-flex; align-items: center; gap: 0.3rem;">
                        <span style="width: 10px; height: 10px; background: #059669; border-radius: 2px;"></span> Ouvrable
                    </span>
                    <span style="display: inline-flex; align-items: center; gap: 0.3rem;">
                        <span style="width: 10px; height: 10px; background: #fee2e2; border: 1px solid #fca5a5; border-radius: 2px;"></span> Sauté (Repos/Férié)
                    </span>
                </div>
            </div>
        </div>

        {{-- Encart informatif de calcul automatique de fin --}}
        <div id="auto_calculation_banner" style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 0.75rem; padding: 1rem 1.25rem; margin-top: 0.75rem; transition: all 0.2s ease;">
            <div style="display: flex; align-items: flex-start; gap: 0.9rem;">
                <div id="calc_icon_wrapper" style="width: 38px; height: 38px; border-radius: 8px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; transition: all 0.2s ease;">
                    <i class="fa-solid fa-calculator"></i>
                </div>
                <div style="flex: 1;">
                    <div id="calc_title" style="font-weight: 700; color: #1e293b; font-size: 0.88rem; margin-bottom: 0.25rem;">
                        Date et Heure de Fin calculées automatiquement
                    </div>
                    <p id="calculation_details" style="color: #64748b; font-size: 0.83rem; margin: 0; line-height: 1.5;">
                        Sélectionnez un périmètre (client, base ou site) et la date de début. La date de fin sera automatiquement déterminée sur la base de <strong>8 équipements par jour</strong>.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════
         SECTION 3.1 — Périodicité & Répétition sur l'année
    ══════════════════════════════════════════════════ --}}
    <div class="section-card" id="periodicite_card" style="display: none;">
        <div class="section-title">
            <div class="section-title-left">
                <i class="fa-solid fa-repeat" style="color: #6366f1;"></i>
                <span>Périodicité & Répétition (Année en cours)</span>
            </div>
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <span id="repetition_count_badge" style="font-size: 0.78rem; font-weight: 700; background: #ede9fe; color: #6d28d9; padding: 0.25rem 0.65rem; border-radius: 9999px;">
                    0 mois sélectionné
                </span>
                <div style="display: flex; gap: 0.4rem;">
                    <button type="button" onclick="toggleAllRepetitionMonths(true)" style="background: none; border: none; color: #6366f1; font-size: 0.75rem; font-weight: 700; cursor: pointer; text-decoration: underline; padding: 0;">
                        Tout cocher
                    </button>
                    <span style="color: #cbd5e1; font-size: 0.75rem;">|</span>
                    <button type="button" onclick="toggleAllRepetitionMonths(false)" style="background: none; border: none; color: #64748b; font-size: 0.75rem; font-weight: 600; cursor: pointer; text-decoration: underline; padding: 0;">
                        Tout décocher
                    </button>
                </div>
            </div>
        </div>

        <p style="color: #64748b; font-size: 0.83rem; margin-top: -0.25rem; margin-bottom: 1rem; line-height: 1.45;">
            Vous pouvez répéter automatiquement cette maintenance sur les mois suivants de l'année. 
            <strong>Chaque mois sélectionné générera sa propre maintenance indépendante</strong> (avec son propre numéro et son suivi d'avancement dédié) sur la même période du mois.
        </p>

        {{-- Grille des mois suivants générée en JS --}}
        <div id="repetition_months_container" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 0.6rem;">
            {{-- Alimenté dynamiquement par JavaScript selon la date de début --}}
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════
         SECTION 4 — Affectation des Équipes
    ══════════════════════════════════════════════════ --}}
    <div class="section-card" id="section_equipes">
        <div class="section-title">
            <div class="section-title-left">
                <i class="fa-solid fa-users" style="color: #10b981;"></i>
                <span>Équipes participantes à la maintenance *</span>
            </div>
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <span id="selected_teams_badge" style="font-size: 0.78rem; font-weight: 700; background: #e2e8f0; color: #475569; padding: 0.25rem 0.65rem; border-radius: 9999px; transition: all 0.2s ease;">
                    0 sélectionnée(s)
                </span>
                <div style="display: flex; gap: 0.4rem;">
                    <button type="button" onclick="selectAllEquipes(true)" style="background: none; border: none; color: #059669; font-size: 0.75rem; font-weight: 700; cursor: pointer; text-decoration: underline; padding: 0;">Tout cocher</button>
                    <span style="color: #cbd5e1; font-size: 0.75rem;">|</span>
                    <button type="button" onclick="selectAllEquipes(false)" style="background: none; border: none; color: #64748b; font-size: 0.75rem; font-weight: 600; cursor: pointer; text-decoration: underline; padding: 0;">Tout décocher</button>
                </div>
            </div>
        </div>

        {{-- Alerte dynamique immédiate si aucune équipe n'est cochée à la soumission --}}
        <div id="equipe_validation_error" style="display: none; background: #fee2e2; border: 1.5px solid #ef4444; color: #991b1b; padding: 0.85rem 1.1rem; border-radius: 0.6rem; margin-bottom: 1.25rem; font-size: 0.88rem; font-weight: 700; align-items: center; gap: 0.6rem; box-shadow: 0 2px 4px rgba(239, 68, 68, 0.1);">
            <i class="fa-solid fa-triangle-exclamation" style="font-size: 1.1rem; color: #dc2626;"></i>
            <span>Veuillez cocher au moins une équipe participant à cette maintenance pour continuer.</span>
        </div>

        {{-- Champ caché alimenté par JS avec la 1ère équipe sélectionnée pour la compatibilité avec la BD --}}
        <input type="hidden" name="equipe_id" id="equipe_id_hidden" value="{{ old('equipe_id') }}">

        @error('equipe_id')
        <div style="background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; padding: 0.75rem 1rem; border-radius: 0.6rem; margin-bottom: 1rem; font-size: 0.85rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span>{{ $message }}</span>
        </div>
        @enderror

        <div class="equipes-container">
            @php
                $hasOldSelection = old('equipes_ids') !== null || old('equipe_id') !== null;
            @endphp
            @forelse($equipes as $eq)
            @php
                $oldEquipes = array_merge(
                    old('equipes_ids', []),
                    old('equipe_id') ? [old('equipe_id')] : [],
                    old('equipes_support', [])
                );
                // Si aucune soumission préalable et qu'il n'y a qu'une seule équipe, on la pré-sélectionne
                $isChecked = in_array($eq->id, $oldEquipes) || (!$hasOldSelection && $equipes->count() === 1);
            @endphp
            <label
                id="row_eq_{{ $eq->id }}"
                class="equipe-card {{ $isChecked ? 'selected' : '' }}"
            >
                <div class="equipe-card-left">
                    <input
                        type="checkbox"
                        name="equipes_ids[]"
                        value="{{ $eq->id }}"
                        id="eq_{{ $eq->id }}"
                        class="equipe-checkbox"
                        {{ $isChecked ? 'checked' : '' }}
                        onchange="onEquipeToggle()"
                    >
                    <div class="equipe-avatar">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div>
                        <div class="equipe-info-title">{{ $eq->nom_equipe }}</div>
                        <div class="equipe-info-sub">
                            @if($eq->chef)
                                <i class="fa-solid fa-user-tie" style="color: #94a3b8; font-size: 0.75rem;"></i> Chef : <span style="font-weight: 600; color: #334155;">{{ $eq->chef->nom_complet ?? $eq->chef->nom }}</span>
                            @else
                                <span style="color: #94a3b8;">Aucun chef assigné</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="equipe-card-right">
                    <span class="badge-selected-tag">
                        <i class="fa-solid fa-check"></i> Retenue
                    </span>
                    <span class="badge-members">
                        <i class="fa-solid fa-user" style="font-size: 0.7rem; color: #94a3b8;"></i> {{ $eq->membres->count() }} membre(s)
                    </span>
                </div>
            </label>
            @empty
            <div style="padding: 1.5rem; text-align: center; color: #b91c1c; background: #fef2f2; border: 1.5px dashed #fca5a5; border-radius: 0.75rem;">
                <i class="fa-solid fa-triangle-exclamation" style="font-size: 1.6rem; margin-bottom: 0.5rem; display: block; color: #ef4444;"></i>
                <strong style="font-size: 0.95rem;">Aucune équipe active trouvée</strong>
                <p style="font-size: 0.85rem; color: #64748b; margin-top: 0.25rem; margin-bottom: 0;">
                    Veuillez d'abord créer au moins une équipe dans le module Équipes avant de pouvoir planifier une maintenance.
                </p>
            </div>
            @endforelse
        </div>

        <p style="font-size: 0.8rem; color: #64748b; margin-top: 0.85rem; margin-bottom: 0; display: flex; align-items: center; gap: 0.4rem;">
            <i class="fa-solid fa-circle-info" style="color: #3b82f6;"></i>
            Cochez toutes les équipes qui interviennent sur cette maintenance. Chaque chef d'équipe soumettra son rapport journalier.
        </p>
    </div>


    {{-- Boutons --}}
    <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 0.5rem; margin-bottom: 2rem;">
        <a href="{{ route('planning.index') }}" style="padding: 0.75rem 1.5rem; border-radius: 0.75rem; background-color: #f1f5f9; color: #64748b; border: none; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-xmark"></i> Annuler
        </a>
        <button type="submit" class="btn-primary" style="padding: 0.75rem 1.75rem;">
            <i class="fa-solid fa-calendar-check"></i> Planifier la Maintenance
        </button>
    </div>

</form>
</div>

<script>
// Variable globale pour stocker le nombre d'équipements
let currentEquipmentCount = 0;

// ═══════════════════════════════════════════════════════════════════
// GESTION DES ÉQUIPES : Sélection avec cases à cocher
// ═══════════════════════════════════════════════════════════════════
function onEquipeToggle() {
    const checkboxes = document.querySelectorAll('.equipe-checkbox');
    const checked = Array.from(checkboxes).filter(cb => cb.checked);
    const hiddenEquipeId = document.getElementById('equipe_id_hidden');
    const badge = document.getElementById('selected_teams_badge');
    const errBox = document.getElementById('equipe_validation_error');
    
    // Remplir le champ caché avec le premier ID sélectionné
    if (hiddenEquipeId) {
        hiddenEquipeId.value = checked.length > 0 ? checked[0].value : '';
    }

    // Masquer l'erreur si l'utilisateur coche une équipe
    if (checked.length > 0 && errBox) {
        errBox.style.display = 'none';
    }
    
    // Mettre à jour le badge du compteur
    if (badge) {
        if (checked.length === 0) {
            badge.textContent = '0 sélectionnée(s)';
            badge.style.background = '#e2e8f0';
            badge.style.color = '#475569';
        } else if (checked.length === 1) {
            badge.textContent = '1 équipe sélectionnée';
            badge.style.background = '#d1fae5';
            badge.style.color = '#065f46';
        } else {
            badge.textContent = checked.length + ' équipes sélectionnées';
            badge.style.background = '#d1fae5';
            badge.style.color = '#065f46';
        }
    }
    
    // Mettre à jour la classe selected des cartes
    checkboxes.forEach(cb => {
        const row = document.getElementById('row_eq_' + cb.value);
        if (row) {
            if (cb.checked) {
                row.classList.add('selected');
            } else {
                row.classList.remove('selected');
            }
        }
    });
}

function selectAllEquipes(select) {
    document.querySelectorAll('.equipe-checkbox').forEach(cb => {
        cb.checked = select;
    });
    onEquipeToggle();
}

function validateMaintenanceForm(event) {
    const checked = document.querySelectorAll('.equipe-checkbox:checked');
    const errBox = document.getElementById('equipe_validation_error');
    if (checked.length === 0) {
        if (event) {
            event.preventDefault();
        }
        if (errBox) {
            errBox.style.display = 'flex';
        }
        const section = document.getElementById('section_equipes');
        if (section) {
            section.scrollIntoView({ behavior: 'smooth', block: 'center' });
            section.style.transition = 'box-shadow 0.3s ease, border-color 0.3s ease';
            section.style.borderColor = '#ef4444';
            section.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.25)';
            setTimeout(() => {
                section.style.boxShadow = '';
                section.style.borderColor = '';
            }, 3500);
        }
        alert("⚠️ Attention : Veuillez cocher au moins une équipe participant à la maintenance avant de valider.");
        return false;
    }
    if (errBox) {
        errBox.style.display = 'none';
    }
    const hiddenEquipeId = document.getElementById('equipe_id_hidden');
    if (hiddenEquipeId && checked.length > 0) {
        hiddenEquipeId.value = checked[0].value;
    }
    return true;
}

// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    onEquipeToggle();
});

// Logique de cascade Client → Base → Site & Comptage Équipements
document.addEventListener('DOMContentLoaded', function() {
    const clientSelect = document.getElementById('client_select');
    const baseContainer = document.getElementById('base_container');
    const baseSelect = document.getElementById('base_select');
    const siteContainer = document.getElementById('site_container');
    const siteSelect = document.getElementById('site_select');
    const countBadge = document.getElementById('equipement_count_badge');

    // Réinitialise totalement le sous-niveau (Base, Site et Compteur)
    function resetSubLevels() {
        if (baseSelect && !baseSelect.disabled) {
            baseSelect.innerHTML = '<option value="">-- Sélectionner une base --</option>';
            baseSelect.value = '';
        }
        if (baseContainer) baseContainer.style.display = 'none';

        if (siteSelect) {
            siteSelect.innerHTML = '<option value="">-- Sélectionner un site --</option>';
            siteSelect.value = '';
        }
        if (siteContainer) siteContainer.style.display = 'none';

        if (countBadge) countBadge.innerHTML = '--';
        currentEquipmentCount = 0;
        calculateEndDate();
    }

    // Réinitialise uniquement le site et son compteur
    function resetSiteLevel() {
        if (siteSelect) {
            siteSelect.innerHTML = '<option value="">-- Sélectionner un site --</option>';
            siteSelect.value = '';
        }
        if (siteContainer) siteContainer.style.display = 'none';
        
        if (countBadge) countBadge.innerHTML = '--';
        currentEquipmentCount = 0;
        calculateEndDate();
    }

    // Interroge l'API de comptage basée EXCLUSIVEMENT sur la sélection actuelle active
    function refreshEquipementCount() {
        const siteId = siteSelect ? siteSelect.value : null;
        const baseId = baseSelect ? baseSelect.value : null;
        const clientId = clientSelect ? clientSelect.value : null;

        if (!siteId && !baseId && !clientId) {
            if (countBadge) countBadge.innerHTML = '--';
            currentEquipmentCount = 0;
            calculateEndDate();
            return;
        }

        let params = new URLSearchParams();
        if (siteId) {
            params.append('site_id', siteId);
        } else if (baseId) {
            params.append('base_id', baseId);
        } else if (clientId) {
            params.append('client_id', clientId);
        }

        fetch(`/api/equipements/count?${params.toString()}`)
            .then(r => r.json())
            .then(data => {
                const count = data.count || 0;
                currentEquipmentCount = count;
                
                if (countBadge) {
                    if (count > 0) {
                        countBadge.innerHTML = `<span style="font-size: 0.85rem; padding: 0.25rem 0.65rem; background: #d1fae5; color: #065f46; border-radius: 12px; font-weight: 800;">${count} équipement(s)</span>`;
                    } else {
                        countBadge.innerHTML = '<span style="color: #94a3b8; font-size: 0.85rem; font-weight: 600;">0 équipement</span>';
                    }
                }
                
                // Recalculer la date de fin
                calculateEndDate();
            })
            .catch(() => {
                if (countBadge) countBadge.innerHTML = '--';
                currentEquipmentCount = 0;
                calculateEndDate();
            });
    }

    // 1. Changement de Client
    if (clientSelect) {
        clientSelect.addEventListener('change', function() {
            const clientId = this.value;
            resetSubLevels();

            if (!clientId) return;

            // Charger les bases du client
            fetch(`/api/clients/${clientId}/bases`)
                .then(r => r.json())
                .then(bases => {
                    if (bases.length > 0) {
                        if (!baseSelect.disabled) {
                            baseSelect.innerHTML = '<option value="">-- Sélectionner une base --</option>';
                            bases.forEach(b => {
                                baseSelect.innerHTML += `<option value="${b.id}">${b.nom_base}</option>`;
                            });
                        }
                        if (baseContainer) baseContainer.style.display = 'block';
                        refreshEquipementCount();
                    } else {
                        // Entreprise sans bases
                        if (baseContainer) baseContainer.style.display = 'none';
                        loadClientSitesDirects(clientId);
                    }
                });
        });
    }

    // Charger les sites d'un client sans bases
    function loadClientSitesDirects(clientId) {
        fetch(`/api/clients/${clientId}/sites`)
            .then(r => r.json())
            .then(sites => {
                if (sites.length > 0) {
                    if (siteSelect) {
                        siteSelect.innerHTML = '<option value="">-- Sélectionner un site --</option>';
                        sites.forEach(s => {
                            siteSelect.innerHTML += `<option value="${s.id}">${s.nom_site}</option>`;
                        });
                    }
                    if (siteContainer) siteContainer.style.display = 'block';
                    refreshEquipementCount();
                } else {
                    if (siteContainer) siteContainer.style.display = 'none';
                    refreshEquipementCount();
                }
            });
    }

    // 2. Changement de Base
    if (baseSelect) {
        baseSelect.addEventListener('change', function() {
            const baseId = this.value;
            resetSiteLevel();

            if (!baseId) {
                refreshEquipementCount();
                return;
            }

            // Charger les sites de la base sélectionnée
            fetch(`/api/bases/${baseId}/sites`)
                .then(r => r.json())
                .then(sites => {
                    if (sites.length > 0) {
                        if (siteSelect) {
                            siteSelect.innerHTML = '<option value="">-- Sélectionner un site --</option>';
                            sites.forEach(s => {
                                siteSelect.innerHTML += `<option value="${s.id}">${s.nom_site}</option>`;
                            });
                        }
                        if (siteContainer) siteContainer.style.display = 'block';
                    } else {
                        if (siteContainer) siteContainer.style.display = 'none';
                    }
                    refreshEquipementCount();
                });
        });
    }

    // 3. Changement de Site
    if (siteSelect) {
        siteSelect.addEventListener('change', function() {
            refreshEquipementCount();
        });
    }

    // Initialisation au chargement
    if (clientSelect && clientSelect.value) {
        refreshEquipementCount();
    }
});

// ═══════════════════════════════════════════════════════════════════
// GESTION DES JOURS NON OUVRABLES & CALCUL INTELLIGENT DE LA FIN
// ═══════════════════════════════════════════════════════════════════
let excludedNonWorkingDays = new Set();
try {
    const initialExcluded = JSON.parse(document.getElementById('jours_non_ouvrables_input')?.value || '[]');
    if (Array.isArray(initialExcluded)) {
        initialExcluded.forEach(d => excludedNonWorkingDays.add(d));
    }
} catch(e) {
    excludedNonWorkingDays = new Set();
}

function setWorkingDaysPreset(preset) {
    const dateDebutInput = document.getElementById('date_debut_input');
    if (!dateDebutInput || !dateDebutInput.value) {
        alert('Veuillez d\'abord choisir une date de début.');
        return;
    }

    const equipementsParJourInput = document.getElementById('equipements_par_jour_input');
    const ratePerDay = (equipementsParJourInput && parseInt(equipementsParJourInput.value) > 0) ? parseInt(equipementsParJourInput.value) : 8;
    const workingDaysNeeded = Math.ceil((currentEquipmentCount || 1) / ratePerDay);

    // Explorer sur une plage raisonnable (ex: 120 jours max)
    const start = new Date(dateDebutInput.value);
    excludedNonWorkingDays.clear();

    if (preset === 'sundays') {
        let cursor = new Date(start);
        for (let i = 0; i < 180; i++) {
            if (cursor.getDay() === 0) { // 0 = Dimanche
                const y = cursor.getFullYear();
                const m = String(cursor.getMonth() + 1).padStart(2, '0');
                const d = String(cursor.getDate()).padStart(2, '0');
                excludedNonWorkingDays.add(`${y}-${m}-${d}`);
            }
            cursor.setDate(cursor.getDate() + 1);
        }
    } else if (preset === 'weekends') {
        let cursor = new Date(start);
        for (let i = 0; i < 180; i++) {
            const dayOfWeek = cursor.getDay();
            if (dayOfWeek === 0 || dayOfWeek === 6) { // Samedi ou Dimanche
                const y = cursor.getFullYear();
                const m = String(cursor.getMonth() + 1).padStart(2, '0');
                const d = String(cursor.getDate()).padStart(2, '0');
                excludedNonWorkingDays.add(`${y}-${m}-${d}`);
            }
            cursor.setDate(cursor.getDate() + 1);
        }
    } else if (preset === 'all_working') {
        // Déjà cleared
    }

    calculateEndDate();
}

function toggleNonWorkingDay(dateStr) {
    if (excludedNonWorkingDays.has(dateStr)) {
        excludedNonWorkingDays.delete(dateStr);
    } else {
        excludedNonWorkingDays.add(dateStr);
    }
    calculateEndDate();
}

function calculateEndDate() {
    const dateDebutInput = document.getElementById('date_debut_input');
    const dateFinInput = document.getElementById('date_fin_input');
    const joursInput = document.getElementById('jours_non_ouvrables_input');
    const equipementsParJourInput = document.getElementById('equipements_par_jour_input');
    const banner = document.getElementById('auto_calculation_banner');
    const calcDetails = document.getElementById('calculation_details');
    const calcTitle = document.getElementById('calc_title');
    const calcIconWrapper = document.getElementById('calc_icon_wrapper');
    const workingDaysSection = document.getElementById('working_days_section');
    const workingDaysGrid = document.getElementById('working_days_grid');
    const workingDaysSummary = document.getElementById('working_days_summary');
    
    if (!dateDebutInput || !dateFinInput) return;
    
    const dateDebut = dateDebutInput.value;
    const equipmentCount = currentEquipmentCount;
    let ratePerDay = equipementsParJourInput ? parseInt(equipementsParJourInput.value, 10) : 8;
    if (isNaN(ratePerDay) || ratePerDay < 1) {
        ratePerDay = 8;
    }
    
    // Si pas de date de début ou pas d'équipements
    if (!dateDebut || equipmentCount === 0) {
        dateFinInput.value = '';
        if (workingDaysSection) workingDaysSection.style.display = 'none';
        if (joursInput) joursInput.value = '[]';
        if (banner) {
            banner.style.background = '#f8fafc';
            banner.style.borderColor = '#e2e8f0';
        }
        if (calcIconWrapper) {
            calcIconWrapper.style.background = '#e0f2fe';
            calcIconWrapper.style.color = '#0284c7';
        }
        if (calcTitle) {
            calcTitle.textContent = 'Date et Heure de Fin calculées automatiquement';
            calcTitle.style.color = '#1e293b';
        }
        if (calcDetails) {
            if (equipmentCount > 0 && !dateDebut) {
                calcDetails.innerHTML = `<strong>${equipmentCount} équipement(s)</strong> détecté(s). Veuillez choisir la <strong>date de début</strong> ci-dessus pour calculer la date de fin (cadence : <strong>${ratePerDay} équipement(s)/jour</strong>).`;
            } else {
                calcDetails.innerHTML = `Sélectionnez un périmètre (client, base ou site) et la date de début. La date de fin sera automatiquement calculée sur la base de <strong>${ratePerDay} équipements par jour</strong>.`;
            }
        }
        return;
    }
    
    // Nombre de jours ouvrables nécessaires
    const workingDaysNeeded = Math.ceil(equipmentCount / ratePerDay);
    
    // Parcours jour par jour depuis dateDebut en sautant les jours non ouvrables
    const startDateObj = new Date(dateDebut);
    let cursor = new Date(startDateObj);
    let workingDaysFound = 0;
    let skippedDaysList = [];
    let timelineDays = [];

    const pad = n => String(n).padStart(2, '0');
    const dayNames = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];
    const monthNames = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];

    let maxSafety = 365; // Sécurité boucle
    let lastActiveDate = new Date(cursor);

    while (workingDaysFound < workingDaysNeeded && maxSafety > 0) {
        maxSafety--;
        const dateStr = `${cursor.getFullYear()}-${pad(cursor.getMonth() + 1)}-${pad(cursor.getDate())}`;
        const isNonWorking = excludedNonWorkingDays.has(dateStr);
        const dayOfWeek = cursor.getDay();
        const isWeekend = (dayOfWeek === 0 || dayOfWeek === 6);

        if (isNonWorking) {
            skippedDaysList.push(dateStr);
            timelineDays.push({
                dateStr: dateStr,
                dayName: dayNames[dayOfWeek],
                dayNum: cursor.getDate(),
                monthName: monthNames[cursor.getMonth()],
                isNonWorking: true,
                isWeekend: isWeekend,
                order: null
            });
        } else {
            workingDaysFound++;
            lastActiveDate = new Date(cursor);
            timelineDays.push({
                dateStr: dateStr,
                dayName: dayNames[dayOfWeek],
                dayNum: cursor.getDate(),
                monthName: monthNames[cursor.getMonth()],
                isNonWorking: false,
                isWeekend: isWeekend,
                order: workingDaysFound
            });
        }

        if (workingDaysFound < workingDaysNeeded) {
            cursor.setDate(cursor.getDate() + 1);
        }
    }

    // Sauvegarder la liste exacte des jours non ouvrables inclus dans cette plage
    if (joursInput) {
        joursInput.value = JSON.stringify(skippedDaysList);
    }

    // Calcul de l'heure et date de fin : même heure que la date de début sur le dernier jour
    const finalDateFinObj = new Date(lastActiveDate);
    finalDateFinObj.setHours(startDateObj.getHours(), startDateObj.getMinutes(), 0, 0);

    const dateFinFormatted = `${finalDateFinObj.getFullYear()}-${pad(finalDateFinObj.getMonth() + 1)}-${pad(finalDateFinObj.getDate())}T${pad(finalDateFinObj.getHours())}:${pad(finalDateFinObj.getMinutes())}`;
    dateFinInput.value = dateFinFormatted;

    // Rendre visible la section des jours ouvrables et générer les badges cliquables
    if (workingDaysSection && workingDaysGrid) {
        workingDaysSection.style.display = 'block';
        if (workingDaysSummary) {
            workingDaysSummary.innerHTML = `<strong>${workingDaysNeeded} jour(s) ouvrable(s)</strong> requis pour <strong>${equipmentCount} équipement(s)</strong> (${ratePerDay} éq./jour) · <strong>${skippedDaysList.length} jour(s) sauté(s)</strong>`;
        }

        let html = '';
        timelineDays.forEach(item => {
            const isExcluded = item.isNonWorking;
            const bg = isExcluded ? '#fee2e2' : '#ffffff';
            const border = isExcluded ? '1.5px solid #f87171' : '1.5px solid #059669';
            const textCol = isExcluded ? '#991b1b' : '#065f46';
            const badgeBg = isExcluded ? '#fca5a5' : '#d1fae5';
            const badgeText = isExcluded ? '#7f1d1d' : '#047857';
            const labelOrder = isExcluded ? '<i class="fa-solid fa-ban" style="color: #ef4444;"></i> Repos' : `<i class="fa-solid fa-check" style="color: #059669;"></i> J-${item.order}`;

            html += `
                <div onclick="toggleNonWorkingDay('${item.dateStr}')" 
                     title="Cliquer pour basculer : ${item.dayName} ${item.dayNum} ${item.monthName} (${isExcluded ? 'Non Ouvrable / Sauté' : 'Ouvrable'})"
                     style="flex: 1 0 auto; min-width: 82px; max-width: 105px; background: ${bg}; border: ${border}; border-radius: 8px; padding: 0.45rem 0.5rem; text-align: center; cursor: pointer; user-select: none; transition: all 0.15s ease; box-shadow: 0 1px 2px rgba(0,0,0,0.04);">
                    <div style="font-size: 0.7rem; font-weight: 700; color: ${item.isWeekend ? '#c2410c' : '#64748b'}; text-transform: uppercase;">
                        ${item.dayName}
                    </div>
                    <div style="font-size: 1.05rem; font-weight: 800; color: ${textCol}; line-height: 1.2; margin: 0.1rem 0;">
                        ${item.dayNum} ${item.monthName}
                    </div>
                    <div style="font-size: 0.68rem; font-weight: 700; background: ${badgeBg}; color: ${badgeText}; border-radius: 4px; padding: 0.1rem 0.3rem; margin-top: 0.2rem; display: inline-block;">
                        ${labelOrder}
                    </div>
                </div>
            `;
        });

        workingDaysGrid.innerHTML = html;
    }
    
    // Afficher la bannière de calcul en vert
    if (banner) {
        banner.style.background = '#f0fdf4';
        banner.style.borderColor = '#86efac';
    }
    if (calcIconWrapper) {
        calcIconWrapper.style.background = '#d1fae5';
        calcIconWrapper.style.color = '#059669';
    }
    if (calcTitle) {
        calcTitle.textContent = '⚡ Durée et date de fin calculées avec jours ouvrables';
        calcTitle.style.color = '#065f46';
    }
    if (calcDetails) {
        calcDetails.innerHTML = `
            <strong>${equipmentCount} équipement(s)</strong> sur ce site ÷ <strong>${ratePerDay} équipements/jour</strong> = 
            <strong style="color: #059669;">${workingDaysNeeded} jour(s) ouvrable(s)</strong> effectif(s).<br>
            <span style="font-size: 0.85rem; color: #047857; font-weight: 600; display: inline-block; margin-top: 0.35rem;">
                📅 Date de fin ajustée : <strong>${finalDateFinObj.toLocaleDateString('fr-FR', { 
                    weekday: 'long', 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                })}</strong>
                ${skippedDaysList.length > 0 ? ` · <span style="color: #b91c1c; font-weight: 700;">(${skippedDaysList.length} jour(s) non ouvrable(s) sauté(s))</span>` : ''}
            </span>
        `;
    }
    // Mise à jour de la section périodicité pour les mois suivants de l'année
    renderRepetitionMonths(startDateObj);
}

// ═══════════════════════════════════════════════════════════════════
// PÉRIODICITÉ & RÉPÉTITION SUR LES MOIS SUIVANTS DE L'ANNÉE
// ═══════════════════════════════════════════════════════════════════
function renderRepetitionMonths(startDate) {
    const card = document.getElementById('periodicite_card');
    const container = document.getElementById('repetition_months_container');
    if (!card || !container) return;

    if (!startDate || isNaN(startDate.getTime())) {
        card.style.display = 'none';
        container.innerHTML = '';
        return;
    }

    const startMonth = startDate.getMonth(); // 0 = Janvier, 11 = Décembre
    const year = startDate.getFullYear();
    const monthNames = [
        'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
        'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
    ];

    // S'il n'y a plus de mois après dans l'année en cours (ex: maintenance en décembre)
    if (startMonth >= 11) {
        card.style.display = 'none';
        container.innerHTML = '';
        return;
    }

    // Récupérer les cases déjà cochées s'il y en avait
    const previouslyChecked = new Set();
    container.querySelectorAll('input[type="checkbox"]:checked').forEach(cb => {
        previouslyChecked.add(cb.value);
    });

    let html = '';
    for (let m = startMonth + 1; m < 12; m++) {
        const monthNum = String(m + 1).padStart(2, '0');
        const monthVal = `${year}-${monthNum}`;
        const isChecked = previouslyChecked.has(monthVal);

        html += `
            <label style="display: flex; align-items: center; gap: 0.6rem; background: #ffffff; border: 1.5px solid ${isChecked ? '#6366f1' : '#e2e8f0'}; border-radius: 8px; padding: 0.6rem 0.85rem; cursor: pointer; transition: all 0.15s ease; user-select: none;"
                   onmouseover="this.style.borderColor='#818cf8'"
                   onmouseout="this.style.borderColor=this.querySelector('input').checked ? '#6366f1' : '#e2e8f0'">
                <input type="checkbox" 
                       name="mois_repetition[]" 
                       value="${monthVal}" 
                       ${isChecked ? 'checked' : ''}
                       onchange="updateRepetitionStyle(this)"
                       style="width: 16px; height: 16px; accent-color: #6366f1; cursor: pointer;">
                <div>
                    <div style="font-weight: 800; font-size: 0.85rem; color: #1e293b;">
                        ${monthNames[m]}
                    </div>
                    <div style="font-size: 0.72rem; color: #64748b; font-weight: 600;">
                        ${year}
                    </div>
                </div>
            </label>
        `;
    }

    container.innerHTML = html;
    card.style.display = 'block';
    updateRepetitionBadge();
}

function updateRepetitionStyle(checkbox) {
    const label = checkbox.closest('label');
    if (label) {
        label.style.borderColor = checkbox.checked ? '#6366f1' : '#e2e8f0';
        label.style.background = checkbox.checked ? '#f5f3ff' : '#ffffff';
    }
    updateRepetitionBadge();
}

function updateRepetitionBadge() {
    const container = document.getElementById('repetition_months_container');
    const badge = document.getElementById('repetition_count_badge');
    if (!container || !badge) return;

    const count = container.querySelectorAll('input[type="checkbox"]:checked').length;
    badge.textContent = `${count} mois sélectionné${count > 1 ? 's' : ''}`;
    if (count > 0) {
        badge.style.background = '#6366f1';
        badge.style.color = '#ffffff';
    } else {
        badge.style.background = '#ede9fe';
        badge.style.color = '#6d28d9';
    }
}

function toggleAllRepetitionMonths(checked) {
    const container = document.getElementById('repetition_months_container');
    if (!container) return;
    const checkboxes = container.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(cb => {
        cb.checked = checked;
        const label = cb.closest('label');
        if (label) {
            label.style.borderColor = checked ? '#6366f1' : '#e2e8f0';
            label.style.background = checked ? '#f5f3ff' : '#ffffff';
        }
    });
    updateRepetitionBadge();
}
</script>
@endsection
