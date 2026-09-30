@extends('layouts.app')

@section('title', 'Modifier la Maintenance #' . $maintenance->numero_maintenance)

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
        <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
            <h1 style="margin: 0;">Modifier la Maintenance</h1>
            <span style="background: #e0f2fe; color: #0284c7; font-weight: 800; font-size: 0.9rem; padding: 0.3rem 0.75rem; border-radius: 0.5rem;">
                {{ $maintenance->numero_maintenance }}
            </span>
        </div>
        <p style="color: #64748b; font-size: 0.9rem; margin-top: 0.25rem;">Modifiez les paramètres, la planification, le statut ou les équipes affectées</p>
    </div>
    <div>
        <form action="{{ route('maintenances.destroy', $maintenance->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette maintenance ? Cette action est irréversible.');">
            @csrf
            @method('DELETE')
            <button type="submit" style="background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; padding: 0.6rem 1.1rem; border-radius: 0.5rem; font-weight: 700; font-size: 0.85rem; cursor: pointer; display: inline-flex; align-items: center; gap: 0.4rem;">
                <i class="fa-solid fa-trash"></i> Supprimer
            </button>
        </form>
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

<form id="maintenanceEditForm" action="{{ route('maintenances.update', $maintenance->id) }}" method="POST" onsubmit="return validateMaintenanceEditForm(event)">
    @csrf
    @method('PUT')

    {{-- ══════════════════════════════════════════════════
         SECTION 1 — Type, Statut & Localisation
    ══════════════════════════════════════════════════ --}}
    <div class="section-card">
        <div class="section-title">
            <div class="section-title-left">
                <i class="fa-solid fa-clipboard-list" style="color: #3b82f6;"></i>
                <span>Informations générales & Statut</span>
            </div>
        </div>

        <div class="form-grid-2" style="margin-bottom: 1.25rem;">
            <div>
                <label class="form-label">Type de Maintenance *</label>
                <select name="type_maintenance" required style="width: 100%; padding: 0.75rem;">
                    <option value="préventive" {{ old('type_maintenance', $maintenance->type_maintenance) == 'préventive' ? 'selected' : '' }}>
                        🛡️ Maintenance Préventive (Planifiée)
                    </option>
                    <option value="corrective programmée" {{ old('type_maintenance', $maintenance->type_maintenance) == 'corrective programmée' ? 'selected' : '' }}>
                        🔧 Maintenance Corrective Programmée
                    </option>
                </select>
                @error('type_maintenance')
                <span style="color: #be123c; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="form-label">Statut Actuel *</label>
                <select name="statut" required style="width: 100%; padding: 0.75rem;">
                    <option value="planifiée" {{ old('statut', $maintenance->statut) == 'planifiée' ? 'selected' : '' }}>
                        ⏳ Planifiée
                    </option>
                    <option value="confirmée_client" {{ old('statut', $maintenance->statut) == 'confirmée_client' ? 'selected' : '' }}>
                        ✔️ Confirmée Client
                    </option>
                    <option value="en_cours" {{ old('statut', $maintenance->statut) == 'en_cours' ? 'selected' : '' }}>
                        🔄 En Cours
                    </option>
                    <option value="terminée" {{ old('statut', $maintenance->statut) == 'terminée' ? 'selected' : '' }}>
                        ✅ Terminée
                    </option>
                    <option value="annulée" {{ old('statut', $maintenance->statut) == 'annulée' ? 'selected' : '' }}>
                        ❌ Annulée
                    </option>
                </select>
                @error('statut')
                <span style="color: #be123c; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="form-grid-2">
            <div>
                <label class="form-label">Client *</label>
                <select name="client_id" id="client_select" required style="width: 100%; padding: 0.75rem;" {{ $clients->count() === 1 ? 'disabled' : '' }}>
                    <option value="">-- Sélectionner un client --</option>
                    @foreach($clients as $client)
                    <option value="{{ $client->id }}" {{ old('client_id', $maintenance->client_id) == $client->id ? 'selected' : '' }}>
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

            <div id="base_container" style="{{ (isset($baseAssignee) || $bases->count() > 0) ? '' : 'display: none;' }}">
                <label class="form-label">Base</label>
                <select name="base_id" id="base_select" style="width: 100%; padding: 0.75rem;" {{ isset($baseAssignee) ? 'disabled' : '' }}>
                    <option value="">Aucune base</option>
                    @foreach($bases as $b)
                    <option value="{{ $b->id }}" {{ old('base_id', $maintenance->base_id) == $b->id ? 'selected' : '' }}>
                        {{ $b->nom_base }}
                    </option>
                    @endforeach
                </select>
                @if(isset($baseAssignee))
                <input type="hidden" name="base_id" value="{{ $baseAssignee->id }}">
                @endif
            </div>
        </div>

        <div id="site_container" style="{{ ($sites->count() > 0 || $maintenance->site_id) ? '' : 'display: none;' }} margin-top: 1.25rem;">
            <label class="form-label">Site</label>
            <select name="site_id" id="site_select" style="width: 100%; padding: 0.75rem;">
                <option value="">Aucun site particulier (tous les sites)</option>
                @foreach($sites as $s)
                <option value="{{ $s->id }}" {{ old('site_id', $maintenance->site_id) == $s->id ? 'selected' : '' }}>
                    {{ $s->nom_site }}
                </option>
                @endforeach
            </select>
        </div>

        {{-- Compteur équipements --}}
        <div style="margin-top: 1rem; display: flex; align-items: center; justify-content: space-between; background: #f8fafc; border-radius: 0.6rem; padding: 0.65rem 1rem;">
            <span style="font-size: 0.85rem; color: #475569; font-weight: 600;">
                <i class="fa-solid fa-gears" style="color: #059669;"></i> Équipements sur ce périmètre :
            </span>
            <span id="equipement_count_badge" style="font-weight: 800; font-size: 0.9rem; color: #059669;">
                {{ $maintenance->nombre_equipements_prevus ?? '--' }} équipement(s)
            </span>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════
         SECTION 2 — Description & Tâches
    ══════════════════════════════════════════════════ --}}
    <div class="section-card">
        <div class="section-title">
            <div class="section-title-left">
                <i class="fa-solid fa-file-lines" style="color: #8b5cf6;"></i>
                <span>Description & tâches</span>
            </div>
        </div>

        <div style="margin-bottom: 1.25rem;">
            <label class="form-label">Description générale *</label>
            <textarea name="description" required rows="3" style="width: 100%; padding: 0.75rem;" placeholder="Décrivez l'objectif de cette maintenance...">{{ old('description', $maintenance->description) }}</textarea>
            @error('description')
            <span style="color: #be123c; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-grid-2">
            <div>
                <label class="form-label">Tâches prévues</label>
                <textarea name="taches_prevues" rows="3" style="width: 100%; padding: 0.75rem;" placeholder="Ex: Vérification filtres, Test pression...">{{ old('taches_prevues', $maintenance->taches_prevues) }}</textarea>
            </div>
            <div>
                <label class="form-label">Pièces prévues</label>
                <textarea name="pieces_prevues" rows="3" style="width: 100%; padding: 0.75rem;" placeholder="Ex: Filtres, Joints...">{{ old('pieces_prevues', $maintenance->pieces_prevues) }}</textarea>
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

        @php
            $formatDebut = $maintenance->date_debut_prevue ? \Carbon\Carbon::parse($maintenance->date_debut_prevue)->format('Y-m-d\TH:i') : '';
            $formatFin = $maintenance->date_fin_prevue ? \Carbon\Carbon::parse($maintenance->date_fin_prevue)->format('Y-m-d\TH:i') : '';
        @endphp

        <div class="form-grid-2" style="margin-bottom: 1.25rem;">
            <div>
                <label class="form-label">Date et Heure de Début *</label>
                <input type="datetime-local" name="date_debut_prevue" id="date_debut_input"
                    value="{{ old('date_debut_prevue', $formatDebut) }}" required
                    style="width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 0.5rem;"
                    onchange="calculateEndDate()">
                @error('date_debut_prevue')
                <span style="color: #be123c; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="form-label" style="display: flex; align-items: center; justify-content: space-between;">
                    <span>Cadence Journalière (Splits / jour) *</span>
                    <span style="font-size: 0.72rem; color: #0284c7; font-weight: 600;">Défaut : 8</span>
                </label>
                <div style="position: relative;">
                    <input type="number" name="equipements_par_jour" id="equipements_par_jour_input"
                        value="{{ old('equipements_par_jour', $maintenance->equipements_par_jour ?? 8) }}" min="1" max="100" required
                        style="width: 100%; padding: 0.75rem; padding-right: 7.5rem; border: 1.5px solid #cbd5e1; border-radius: 0.5rem; font-weight: 700; color: #0f172a;"
                        oninput="calculateEndDate()"
                        placeholder="8">
                    <span style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); font-size: 0.78rem; font-weight: 700; color: #64748b; background: #f1f5f9; padding: 0.25rem 0.5rem; border-radius: 0.35rem; pointer-events: none;">
                        splits / jour
                    </span>
                </div>
                <small style="color: #64748b; font-size: 0.75rem; display: block; margin-top: 0.25rem;">
                    Nombre d'équipements/splits réalisés par jour par l'équipe.
                </small>
                @error('equipements_par_jour')
                <span style="color: #be123c; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div style="margin-bottom: 1.25rem;">
            <label class="form-label">Date et Heure de Fin</label>
            <input type="datetime-local" name="date_fin_prevue" id="date_fin_input"
                value="{{ old('date_fin_prevue', $formatFin) }}"
                style="width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 0.5rem;">
            @error('date_fin_prevue')
            <span style="color: #be123c; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
            @enderror
        </div>

        {{-- Champ caché pour les jours non ouvrables (JSON) --}}
        <input type="hidden" name="jours_non_ouvrables" id="jours_non_ouvrables_input" 
            value="{{ old('jours_non_ouvrables', json_encode($maintenance->jours_non_ouvrables ?? [])) }}">

        {{-- Calendrier interactif des Jours Ouvrables / Non Ouvrables --}}
        <div id="working_days_section" style="margin-top: 1.25rem; display: none; background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 0.75rem; padding: 1.2rem;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 0.85rem;">
                <div>
                    <div style="font-weight: 800; font-size: 0.92rem; color: #1e293b; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fa-solid fa-calendar-check" style="color: #0284c7;"></i>
                        <span>Calendrier des jours ouvrables & repos</span>
                    </div>
                    <p style="font-size: 0.78rem; color: #64748b; margin: 0.2rem 0 0 0;">
                        Cochez/décochez les jours non ouvrables (dimanches, samedis, fériés). La date de fin sera recalculée en sautant les jours exclus.
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
                        Date et Heure de Fin calculées selon la cadence choisie
                    </div>
                    <p id="calculation_details" style="color: #64748b; font-size: 0.83rem; margin: 0; line-height: 1.5;">
                        {{ $maintenance->nombre_equipements_prevus ?? 0 }} équipement(s) prévu(s). Vous pouvez modifier la cadence ci-dessus pour recalculer la durée.
                    </p>
                </div>
            </div>
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

        {{-- Champ caché alimenté par JS avec la 1ère équipe sélectionnée --}}
        <input type="hidden" name="equipe_id" id="equipe_id_hidden" value="{{ old('equipe_id', $maintenance->equipe_id) }}">

        @error('equipe_id')
        <div style="background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; padding: 0.75rem 1rem; border-radius: 0.6rem; margin-bottom: 1rem; font-size: 0.85rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span>{{ $message }}</span>
        </div>
        @enderror

        <div class="equipes-container">
            @foreach($equipes as $eq)
            @php
                $oldEquipes = old('equipes_ids', $equipesIdsSelected);
                $isChecked = in_array($eq->id, $oldEquipes);
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
            @endforeach
        </div>

        <p style="font-size: 0.8rem; color: #64748b; margin-top: 0.85rem; margin-bottom: 0; display: flex; align-items: center; gap: 0.4rem;">
            <i class="fa-solid fa-circle-info" style="color: #3b82f6;"></i>
            Cochez toutes les équipes qui interviennent sur cette maintenance.
        </p>
    </div>

    {{-- Boutons --}}
    <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 0.5rem; margin-bottom: 2rem;">
        <a href="{{ route('planning.index') }}" style="padding: 0.75rem 1.5rem; border-radius: 0.75rem; background-color: #f1f5f9; color: #64748b; border: none; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-xmark"></i> Annuler
        </a>
        <button type="submit" class="btn-primary" style="padding: 0.75rem 1.75rem;">
            <i class="fa-solid fa-check"></i> Mettre à jour la Maintenance
        </button>
    </div>

</form>
</div>

<script>
// Variable globale pour stocker le nombre d'équipements
let currentEquipmentCount = {{ $maintenance->nombre_equipements_prevus ?? 0 }};

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

function validateMaintenanceEditForm(event) {
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

    function refreshEquipementCount() {
        const siteId = siteSelect ? siteSelect.value : null;
        const baseId = baseSelect ? baseSelect.value : null;
        const clientId = clientSelect ? clientSelect.value : null;

        if (!siteId && !baseId && !clientId) {
            if (countBadge) countBadge.innerHTML = '--';
            currentEquipmentCount = 0;
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
            if (baseSelect && !baseSelect.disabled) {
                baseSelect.innerHTML = '<option value="">-- Sélectionner une base --</option>';
            }
            if (siteSelect) {
                siteSelect.innerHTML = '<option value="">-- Sélectionner un site --</option>';
            }

            if (!clientId) return;

            // Charger les bases du client
            fetch(`/api/clients/${clientId}/bases`)
                .then(r => r.json())
                .then(bases => {
                    if (bases.length > 0) {
                        if (baseSelect && !baseSelect.disabled) {
                            bases.forEach(b => {
                                baseSelect.innerHTML += `<option value="${b.id}">${b.nom_base}</option>`;
                            });
                        }
                        if (baseContainer) baseContainer.style.display = 'block';
                    } else {
                        if (baseContainer) baseContainer.style.display = 'none';
                        loadClientSitesDirects(clientId);
                    }
                    refreshEquipementCount();
                });
        });
    }

    function loadClientSitesDirects(clientId) {
        fetch(`/api/clients/${clientId}/sites`)
            .then(r => r.json())
            .then(sites => {
                if (sites.length > 0) {
                    if (siteSelect) {
                        siteSelect.innerHTML = '<option value="">Aucun site particulier (tous les sites)</option>';
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
    }

    // 2. Changement de Base
    if (baseSelect) {
        baseSelect.addEventListener('change', function() {
            const baseId = this.value;
            if (!baseId) {
                refreshEquipementCount();
                return;
            }

            fetch(`/api/bases/${baseId}/sites`)
                .then(r => r.json())
                .then(sites => {
                    if (sites.length > 0) {
                        if (siteSelect) {
                            siteSelect.innerHTML = '<option value="">Aucun site particulier (tous les sites de la base)</option>';
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
});

// ═══════════════════════════════════════════════════════════════════
// GESTION DES JOURS NON OUVRABLES & CALCUL INTELLIGENT DE LA FIN
// ═══════════════════════════════════════════════════════════════════
let excludedNonWorkingDays = new Set();
try {
    const rawExcluded = document.getElementById('jours_non_ouvrables_input')?.value || '[]';
    const initialExcluded = JSON.parse(rawExcluded);
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
    const calcDetails = document.getElementById('calculation_details');
    const calcTitle = document.getElementById('calc_title');
    const equipementsParJourInput = document.getElementById('equipements_par_jour_input');
    const banner = document.getElementById('auto_calculation_banner');
    const calcIconWrapper = document.getElementById('calc_icon_wrapper');
    const workingDaysSection = document.getElementById('working_days_section');
    const workingDaysGrid = document.getElementById('working_days_grid');
    const workingDaysSummary = document.getElementById('working_days_summary');
    
    if (!dateDebutInput || !dateFinInput) return;
    const dateDebut = dateDebutInput.value;
    const equipmentCount = currentEquipmentCount;
    
    const cadence = (equipementsParJourInput && parseInt(equipementsParJourInput.value) > 0) 
        ? parseInt(equipementsParJourInput.value) 
        : 8;
    
    if (!dateDebut || equipmentCount === 0) {
        if (workingDaysSection) workingDaysSection.style.display = 'none';
        if (joursInput) joursInput.value = '[]';
        if (calcTitle) {
            calcTitle.textContent = `Date et Heure de Fin calculées sur la base de ${cadence} équipement(s)/jour`;
        }
        if (calcDetails) {
            calcDetails.innerHTML = `${equipmentCount} équipement(s) prévu(s). Cadence : <strong>${cadence} équipements/jour</strong>. Indiquez la date de début pour calculer la date de fin.`;
        }
        return;
    }
    
    // Nombre de jours ouvrables nécessaires
    const workingDaysNeeded = Math.ceil(equipmentCount / cadence);
    
    // Parcours jour par jour depuis dateDebut en sautant les jours non ouvrables
    const startDateObj = new Date(dateDebut);
    let cursor = new Date(startDateObj);
    let workingDaysFound = 0;
    let skippedDaysList = [];
    let timelineDays = [];

    const pad = n => String(n).padStart(2, '0');
    const dayNames = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];
    const monthNames = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];

    let maxSafety = 365;
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

    if (joursInput) {
        joursInput.value = JSON.stringify(skippedDaysList);
    }

    const finalDateFinObj = new Date(lastActiveDate);
    finalDateFinObj.setHours(startDateObj.getHours(), startDateObj.getMinutes(), 0, 0);

    const dateFinFormatted = `${finalDateFinObj.getFullYear()}-${pad(finalDateFinObj.getMonth() + 1)}-${pad(finalDateFinObj.getDate())}T${pad(finalDateFinObj.getHours())}:${pad(finalDateFinObj.getMinutes())}`;
    dateFinInput.value = dateFinFormatted;

    if (workingDaysSection && workingDaysGrid) {
        workingDaysSection.style.display = 'block';
        if (workingDaysSummary) {
            workingDaysSummary.innerHTML = `<strong>${workingDaysNeeded} jour(s) ouvrable(s)</strong> requis pour <strong>${equipmentCount} équipement(s)</strong> (${cadence} éq./jour) · <strong>${skippedDaysList.length} jour(s) sauté(s)</strong>`;
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

    if (banner) {
        banner.style.background = '#f0fdf4';
        banner.style.borderColor = '#86efac';
    }
    if (calcIconWrapper) {
        calcIconWrapper.style.background = '#d1fae5';
        calcIconWrapper.style.color = '#059669';
    }
    if (calcTitle) {
        calcTitle.textContent = `⚡ Durée et date de fin calculées avec jours ouvrables`;
        calcTitle.style.color = '#065f46';
    }
    if (calcDetails) {
        calcDetails.innerHTML = `
            <strong>${equipmentCount} équipement(s)</strong> ÷ <strong>${cadence} équipements/jour</strong> = 
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
}

document.addEventListener('DOMContentLoaded', function() {
    calculateEndDate();
});
</script>
@endsection
