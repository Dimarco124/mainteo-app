@extends('layouts.app')

@section('title', 'Planifier une Intervention')

@section('content')
<div class="header">
    <div class="page-title">
        <h1>Planifier une Intervention</h1>
        <p>Créez une planification future pour une opération technique</p>
    </div>
    <a href="{{ route('planning.index') }}" class="btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Retour au Planning
    </a>
</div>

<!-- Info Card -->
<div class="info-banner">
    <i class="fa-solid fa-info-circle"></i>
    <div>
        <strong>Workflow de planification</strong>
        <p>
            1. Sélectionnez une <strong>opération validée</strong> dans la liste<br>
            2. Choisissez les <strong>dates</strong> de l'intervention<br>
            3. Affectez une <strong>équipe</strong> ou un <strong>technicien</strong><br>
            4. L'intervention apparaîtra dans le calendrier
        </p>
    </div>
</div>

@if($operations->count() === 0)
<div class="card" style="text-align: center; padding: 3rem 2rem;">
    <div style="font-size: 4rem; color: #cbd5e1; margin-bottom: 1rem;">
        <i class="fa-solid fa-inbox"></i>
    </div>
    <h3 style="font-size: 1.1rem; font-weight: 700; color: #475569; margin-bottom: 0.5rem;">
        Aucune opération disponible
    </h3>
    <p style="color: #64748b; margin-bottom: 1.5rem;">
        Toutes les opérations ont déjà été planifiées.<br>
        Les nouvelles opérations apparaîtront ici après validation.
    </p>
    <a href="{{ route('operations.index') }}" class="btn-primary">
        <i class="fa-solid fa-wrench"></i> Voir les Opérations
    </a>
</div>
@else
<form action="{{ route('planning.store') }}" method="POST">
    @csrf

    <!-- ÉTAPE 1 : Sélection de l'opération -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fa-solid fa-wrench"></i> Étape 1 : Sélectionner l'Opération</h3>
        </div>

        <div class="form-group">
            <label for="operation_select">Opération à planifier <span class="required">*</span></label>
            <select name="depannage_id" id="operation_select" required onchange="afficherDetailsOperation()">
                <option value="">-- Sélectionner une opération --</option>
                @foreach($operations as $op)
                <option value="{{ $op->id }}" 
                    {{ old('depannage_id') == $op->id ? 'selected' : '' }}
                    data-demande="{{ $op->demande ? $op->demande->numero_demande : 'N/A' }}"
                    data-client="{{ $op->demande && $op->demande->client ? $op->demande->client->nom : ($op->client_nom ?? 'N/A') }}"
                    data-base="{{ $op->demande && $op->demande->base ? $op->demande->base->nom_base : 'N/A' }}"
                    data-site="{{ $op->demande && $op->demande->site ? $op->demande->site->nom_site : 'N/A' }}"
                    data-site-id="{{ $op->demande && $op->demande->site ? $op->demande->site->id : '' }}"
                    data-equipement="{{ $op->demande && $op->demande->equipement ? $op->demande->equipement->equipement_nom : $op->equipement_reference }}"
                    data-description="{{ $op->description_panne }}"
                    data-urgence="{{ strtolower($op->urgence) }}"
                    data-type="{{ $op->type_intervention }}"
                    data-statut="{{ $op->statut }}"
                    data-date="{{ $op->demande && $op->demande->date_debut_souhaitee ? $op->demande->date_debut_souhaitee->format('Y-m-d') : ($op->date_fin_prevue ? \Carbon\Carbon::parse($op->date_fin_prevue)->format('Y-m-d') : ($op->date_prevue ? \Carbon\Carbon::parse($op->date_prevue)->format('Y-m-d') : '')) }}"
                    data-date-debut="{{ $op->demande && $op->demande->date_debut_souhaitee ? $op->demande->date_debut_souhaitee->format('Y-m-d') : ($op->date_debut_prevue ? \Carbon\Carbon::parse($op->date_debut_prevue)->format('Y-m-d') : '') }}"
                    data-date-fin="{{ $op->demande && $op->demande->date_debut_souhaitee ? $op->demande->date_debut_souhaitee->format('Y-m-d') : ($op->date_fin_prevue ? \Carbon\Carbon::parse($op->date_fin_prevue)->format('Y-m-d') : '') }}">
                    #{{ $op->id }} - {{ $op->type_intervention }} - {{ $op->demande && $op->demande->client ? $op->demande->client->nom : $op->client_nom }}
                </option>
                @endforeach
            </select>
            @error('depannage_id')
            <span class="error-message">{{ $message }}</span>
            @enderror
        </div>

        <!-- Détails de l'opération sélectionnée -->
        <div id="detailsOperation" class="details-box" style="display: none;">
            <h5><i class="fa-solid fa-file-lines"></i> Détails de l'opération</h5>
            <div class="details-grid">
                <div class="detail-item">
                    <strong>Demande d'origine:</strong>
                    <span id="detail_demande">—</span>
                </div>
                <div class="detail-item">
                    <strong>Type:</strong>
                    <span id="detail_type">—</span>
                </div>
                <div class="detail-item">
                    <strong>Client:</strong>
                    <span id="detail_client">—</span>
                </div>
                <div class="detail-item">
                    <strong>Base:</strong>
                    <span id="detail_base">—</span>
                </div>
                <div class="detail-item">
                    <strong>Site:</strong>
                    <span id="detail_site">—</span>
                </div>
                <div class="detail-item">
                    <strong>Équipement:</strong>
                    <span id="detail_equipement">—</span>
                </div>
                <div class="detail-item">
                    <strong>Niveau d'urgence:</strong>
                    <span id="detail_urgence">—</span>
                </div>
                <div class="detail-item">
                    <strong>Statut:</strong>
                    <span id="detail_statut">—</span>
                </div>
            </div>
            <div class="detail-description">
                <strong>Description de la panne:</strong>
                <p id="detail_description">—</p>
            </div>
        </div>
    </div>

    <!-- ÉTAPE 2 : Dates -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fa-solid fa-calendar-days"></i> Étape 2 : Dates de l'Intervention</h3>
        </div>

        <!-- Bannière date automatique -->
        <div id="date_auto_banner" class="auto-date-banner" style="display: none;">
            <i class="fa-solid fa-circle-check"></i>
            <span>
                Date automatique depuis la demande : <strong id="date_auto_label">—</strong>
            </span>
            <small>(Modifiable si nécessaire)</small>
        </div>

        <!-- Info maintenance : calcul automatique -->
        <div id="maintenance_info_banner" class="maintenance-banner" style="display: none;">
            <i class="fa-solid fa-calculator"></i>
            <div>
                <strong>Calcul automatique pour maintenance</strong>
                <p id="maintenance_calculation">—</p>
            </div>
        </div>

        <!-- Cadence pour les maintenances -->
        <div id="cadence_maintenance_container" class="form-group" style="display: none; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 0.75rem; padding: 1rem; margin-bottom: 1.25rem;">
            <label for="equipements_par_jour_input" style="font-weight: 700; color: #1e293b; display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                <span><i class="fa-solid fa-sliders" style="color: #0284c7;"></i> Cadence journalière de maintenance</span>
                <span style="font-size: 0.75rem; color: #64748b; font-weight: normal;">Défaut : 8 splits/jour</span>
            </label>
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <input type="number" 
                    id="equipements_par_jour_input" 
                    name="equipements_par_jour" 
                    value="{{ old('equipements_par_jour', 8) }}" 
                    min="1" 
                    max="100" 
                    oninput="calculateMaintenanceEndDate()"
                    style="width: 120px; padding: 0.6rem; border: 1.5px solid #cbd5e1; border-radius: 0.5rem; font-weight: 700; text-align: center; font-size: 1rem;">
                <span style="color: #475569; font-size: 0.85rem; font-weight: 600;">splits / jour / équipe</span>
            </div>
            <small style="color: #64748b; font-size: 0.78rem; display: block; margin-top: 0.4rem;">
                Nombre d'équipements que l'équipe traite par jour. Modifiez ce chiffre pour recalculer automatiquement la date de fin.
            </small>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="date_debut_input">Date de Début <small style="color: #6b7280;">(pré-remplie)</small></label>
                <input type="date" id="date_debut_input" name="date_debut" value="{{ old('date_debut') }}" onchange="calculateMaintenanceEndDate()">
                @error('date_debut')
                <span class="error-message">{{ $message }}</span>
                @enderror
            </div>
            <div class="form-group">
                <label for="date_fin_input">
                    Date de Fin 
                    <small id="date_fin_status" style="color: #6b7280;">(optionnelle)</small>
                </label>
                <input type="date" id="date_fin_input" name="date_fin" value="{{ old('date_fin') }}">
                <small id="date_fin_readonly_msg" style="display: none; color: #3b82f6; margin-top: 0.25rem; display: block;">
                    <i class="fa-solid fa-lock"></i> Date calculée automatiquement basée sur 8 maintenances/jour
                </small>
                @error('date_fin')
                <span class="error-message">{{ $message }}</span>
                @enderror
            </div>
        </div>

        {{-- Champ caché pour les jours non ouvrables (JSON) --}}
        <input type="hidden" name="jours_non_ouvrables" id="jours_non_ouvrables_input" value="{{ old('jours_non_ouvrables', '[]') }}">

        {{-- Calendrier interactif des Jours Ouvrables / Non Ouvrables pour Maintenance --}}
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
    </div>

    <!-- ÉTAPE 3 : Affectation -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fa-solid fa-users-gear"></i> Étape 3 : Affectation Équipe/Technicien</h3>
        </div>

        <div class="warning-banner">
            <i class="fa-solid fa-info-circle"></i>
            <strong>Important :</strong> Sélectionnez au moins une équipe OU un technicien
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="equipe_id"><i class="fa-solid fa-users"></i> Équipe</label>
                <select name="equipe_id" id="equipe_id">
                    <option value="">Aucune équipe</option>
                    @foreach($equipes as $eq)
                    <option value="{{ $eq->id }}" {{ old('equipe_id') == $eq->id ? 'selected' : '' }}>
                        {{ $eq->nom_equipe }} ({{ $eq->membres->count() }} membre(s))
                    </option>
                    @endforeach
                </select>
                @error('equipe_id')
                <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="technicien_id"><i class="fa-solid fa-user"></i> Technicien Individuel</label>
                <select name="technicien_id" id="technicien_id">
                    <option value="">Aucun technicien</option>
                    @foreach($techniciens as $t)
                    <option value="{{ $t->id }}" {{ old('technicien_id') == $t->id ? 'selected' : '' }}>
                        {{ $t->nom_complet }}
                    </option>
                    @endforeach
                </select>
                @error('technicien_id')
                <span class="error-message">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>

    <!-- ÉTAPE 4 : Commentaire -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fa-solid fa-comment"></i> Étape 4 : Commentaire <small style="font-weight: 400; color: #64748b;">(optionnel)</small></h3>
        </div>

        <div class="form-group">
            <label for="commentaire">Instructions pour l'équipe</label>
            <textarea name="commentaire" id="commentaire" rows="4" placeholder="Ajoutez des instructions spécifiques, remarques ou consignes pour l'équipe...">{{ old('commentaire') }}</textarea>
            <small style="color: #64748b; display: block; margin-top: 0.5rem;">
                Ce commentaire sera visible par les techniciens dans leur planning.
            </small>
        </div>
    </div>

    <!-- Boutons d'action -->
    <div class="form-actions">
        <a href="{{ route('planning.index') }}" class="btn-secondary">
            <i class="fa-solid fa-times"></i> Annuler
        </a>
        <button type="submit" class="btn-primary">
            <i class="fa-solid fa-calendar-check"></i> Planifier l'Intervention
        </button>
    </div>
</form>
@endif

<style>
.info-banner {
    background-color: #dbeafe;
    border: 1px solid #93c5fd;
    border-radius: 0.75rem;
    padding: 1.25rem;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: flex-start;
    gap: 1rem;
}

.info-banner i {
    color: #1e40af;
    font-size: 1.5rem;
    flex-shrink: 0;
    margin-top: 0.2rem;
}

.info-banner strong {
    color: #1e40af;
    font-size: 0.95rem;
    display: block;
    margin-bottom: 0.5rem;
}

.info-banner p {
    color: #1e3a8a;
    font-size: 0.875rem;
    line-height: 1.6;
    margin: 0;
}

.card {
    background: white;
    border-radius: 0.75rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    margin-bottom: 1.5rem;
}

.card-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #e2e8f0;
}

.card-header h3 {
    font-size: 1rem;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.card-header h3 i {
    color: #3b82f6;
}

.form-group {
    padding: 1.5rem;
}

.form-row {
    padding: 1.5rem;
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
}

.form-row .form-group {
    padding: 0;
}

.form-group label {
    display: block;
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
}

.form-group label i {
    color: #6b7280;
    margin-right: 0.25rem;
}

.required {
    color: #dc2626;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    transition: all 0.2s;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-group textarea {
    resize: vertical;
}

.error-message {
    color: #dc2626;
    font-size: 0.75rem;
    margin-top: 0.25rem;
    display: block;
}

.details-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    padding: 1.25rem;
    margin: 0 1.5rem 1.5rem;
}

.details-box h5 {
    font-size: 0.95rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.details-box h5 i {
    color: #3b82f6;
}

.details-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    font-size: 0.875rem;
}

.detail-item strong {
    display: block;
    color: #475569;
    margin-bottom: 0.25rem;
}

.detail-item span {
    color: #0f172a;
}

.detail-description {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e2e8f0;
    font-size: 0.875rem;
}

.detail-description strong {
    display: block;
    color: #475569;
    margin-bottom: 0.5rem;
}

.detail-description p {
    color: #64748b;
    line-height: 1.6;
    margin: 0;
}

.auto-date-banner {
    background: #dcfce7;
    border: 1px solid #86efac;
    border-radius: 0.5rem;
    padding: 0.75rem 1rem;
    margin: 1.5rem 1.5rem 0;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.auto-date-banner i {
    color: #16a34a;
    font-size: 1.1rem;
}

.auto-date-banner span {
    font-size: 0.875rem;
    color: #15803d;
    font-weight: 600;
}

.auto-date-banner small {
    font-size: 0.8rem;
    color: #166534;
    margin-left: 0.5rem;
}

.maintenance-banner {
    background: #fef3c7;
    border: 1px solid #fbbf24;
    border-radius: 0.5rem;
    padding: 0.75rem 1rem;
    margin: 1.5rem 1.5rem 0;
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
}

.maintenance-banner i {
    color: #d97706;
    font-size: 1.1rem;
    margin-top: 0.1rem;
}

.maintenance-banner strong {
    display: block;
    color: #92400e;
    font-size: 0.875rem;
    margin-bottom: 0.25rem;
}

.maintenance-banner p {
    color: #78350f;
    font-size: 0.875rem;
    margin: 0;
    line-height: 1.5;
}

.warning-banner {
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 0.5rem;
    padding: 0.75rem 1rem;
    margin: 0 1.5rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: #78350f;
}

.warning-banner i {
    color: #f59e0b;
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 1.5rem;
}

.btn-primary,
.btn-secondary {
    padding: 0.75rem 1.5rem;
    border-radius: 0.75rem;
    font-weight: 700;
    font-size: 0.875rem;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-primary {
    background-color: #3b82f6;
    color: white;
}

.btn-primary:hover {
    background-color: #2563eb;
    transform: translateY(-1px);
    box-shadow: 0 4px 6px rgba(59, 130, 246, 0.2);
}

.btn-secondary {
    background-color: #f1f5f9;
    color: #64748b;
}

.btn-secondary:hover {
    background-color: #e2e8f0;
}
</style>

<script>
// Variable globale pour stocker les données de l'opération
let currentOperationData = {
    siteId: null,
    type: null,
    equipmentCount: 0
};

// Afficher les détails de l'opération sélectionnée
function afficherDetailsOperation() {
    const select = document.getElementById('operation_select');
    const option = select.options[select.selectedIndex];
    const detailsDiv = document.getElementById('detailsOperation');
    
    if (option.value) {
        document.getElementById('detail_demande').textContent = option.dataset.demande;
        document.getElementById('detail_type').textContent = option.dataset.type;
        document.getElementById('detail_client').textContent = option.dataset.client;
        document.getElementById('detail_base').textContent = option.dataset.base;
        document.getElementById('detail_site').textContent = option.dataset.site;
        document.getElementById('detail_equipement').textContent = option.dataset.equipement;
        document.getElementById('detail_description').textContent = option.dataset.description;
        document.getElementById('detail_statut').textContent = option.dataset.statut;
        
        // Stocker le type d'intervention et site_id pour calcul maintenance
        currentOperationData.type = option.dataset.type;
        currentOperationData.siteId = option.dataset.siteId || null;
        
        // Badge urgence avec couleur
        const urgence = option.dataset.urgence;
        let urgenceBadge = '';
        if (urgence === 'critique') {
            urgenceBadge = '<span class="badge" style="background: #dc2626; color: white; padding: 0.25rem 0.75rem; border-radius: 0.5rem; font-size: 0.75rem; font-weight: 700;">🔴 CRITIQUE</span>';
        } else if (urgence === 'urgent') {
            urgenceBadge = '<span class="badge" style="background: #f59e0b; color: white; padding: 0.25rem 0.75rem; border-radius: 0.5rem; font-size: 0.75rem; font-weight: 700;">🟠 URGENT</span>';
        } else if (urgence === 'moyen') {
            urgenceBadge = '<span class="badge" style="background: #eab308; color: white; padding: 0.25rem 0.75rem; border-radius: 0.5rem; font-size: 0.75rem; font-weight: 700;">🟡 MOYEN</span>';
        } else {
            urgenceBadge = '<span class="badge" style="background: #10b981; color: white; padding: 0.25rem 0.75rem; border-radius: 0.5rem; font-size: 0.75rem; font-weight: 700;">🟢 FAIBLE</span>';
        }
        document.getElementById('detail_urgence').innerHTML = urgenceBadge;
        
        // ─── Auto-remplissage des dates depuis la demande ───
        const dateDebut = option.dataset.dateDateDebut || option.dataset.date;
        const dateFin   = option.dataset.dateDateFin   || option.dataset.date;
        const banner = document.getElementById('date_auto_banner');
        const dateDebutInput = document.getElementById('date_debut_input');
        const dateFinInput   = document.getElementById('date_fin_input');
        if (dateDebut || dateFin) {
            if (dateDebut) dateDebutInput.value = dateDebut;
            if (dateFin)   dateFinInput.value   = dateFin;
            else if (dateDebut) dateFinInput.value = dateDebut;
            // Formatter la plage pour affichage
            const dD = dateDebut ? new Date(dateDebut) : null;
            const dF = dateFin   ? new Date(dateFin)   : null;
            const fmt = d => d ? d.toLocaleDateString('fr-FR', { day: '2-digit', month: 'long', year: 'numeric' }) : '';
            const label = (dD && dF && dateDebut !== dateFin)
                ? `Du ${fmt(dD)} au ${fmt(dF)}`
                : fmt(dD || dF);
            document.getElementById('date_auto_label').textContent = label;
            banner.style.display = 'flex';
        } else {
            banner.style.display = 'none';
        }
        
        detailsDiv.style.display = 'block';
        
        // Calculer la date de fin pour maintenance
        calculateMaintenanceEndDate();
    } else {
        detailsDiv.style.display = 'none';
        document.getElementById('date_auto_banner').style.display = 'none';
        document.getElementById('maintenance_info_banner').style.display = 'none';
        
        // Réinitialiser les champs
        currentOperationData = { siteId: null, type: null, equipmentCount: 0 };
    }
}

// ═══════════════════════════════════════════════════════════════════
// GESTION DES JOURS NON OUVRABLES POUR MAINTENANCE
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
        // Déjà vidé
    }

    calculateMaintenanceEndDate();
}

function toggleNonWorkingDay(dateStr) {
    if (excludedNonWorkingDays.has(dateStr)) {
        excludedNonWorkingDays.delete(dateStr);
    } else {
        excludedNonWorkingDays.add(dateStr);
    }
    calculateMaintenanceEndDate();
}

// ═══════════════════════════════════════════════════════════════════
// CALCUL AUTOMATIQUE DE LA DATE DE FIN POUR MAINTENANCE
// ═══════════════════════════════════════════════════════════════════
async function calculateMaintenanceEndDate() {
    const typeIntervention = currentOperationData.type;
    const dateDebutInput = document.getElementById('date_debut_input');
    const dateFinInput = document.getElementById('date_fin_input');
    const joursInput = document.getElementById('jours_non_ouvrables_input');
    const maintenanceBanner = document.getElementById('maintenance_info_banner');
    const maintenanceCalc = document.getElementById('maintenance_calculation');
    const dateFinStatus = document.getElementById('date_fin_status');
    const dateFinReadonlyMsg = document.getElementById('date_fin_readonly_msg');
    const cadenceContainer = document.getElementById('cadence_maintenance_container');
    const equipementsParJourInput = document.getElementById('equipements_par_jour_input');
    const workingDaysSection = document.getElementById('working_days_section');
    const workingDaysGrid = document.getElementById('working_days_grid');
    const workingDaysSummary = document.getElementById('working_days_summary');
    
    // Vérifier si c'est une maintenance
    if (!typeIntervention || typeIntervention.toLowerCase() !== 'maintenance') {
        // Pas une maintenance : réinitialiser
        if (cadenceContainer) cadenceContainer.style.display = 'none';
        if (workingDaysSection) workingDaysSection.style.display = 'none';
        if (joursInput) joursInput.value = '[]';
        dateFinInput.removeAttribute('readonly');
        dateFinInput.style.backgroundColor = '';
        dateFinInput.style.cursor = '';
        maintenanceBanner.style.display = 'none';
        dateFinStatus.textContent = '(optionnelle)';
        dateFinStatus.style.color = '#6b7280';
        dateFinReadonlyMsg.style.display = 'none';
        return;
    }
    
    // C'est une maintenance : afficher le conteneur de cadence
    if (cadenceContainer) cadenceContainer.style.display = 'block';
    
    const siteId = currentOperationData.siteId;
    const dateDebut = dateDebutInput.value;
    const cadence = (equipementsParJourInput && parseInt(equipementsParJourInput.value) > 0) 
        ? parseInt(equipementsParJourInput.value) 
        : 8;
    
    if (!dateDebut) {
        // Pas de date de début sélectionnée
        dateFinInput.value = '';
        if (workingDaysSection) workingDaysSection.style.display = 'none';
        maintenanceBanner.style.display = 'none';
        return;
    }
    
    if (!siteId) {
        // Pas de site ID disponible
        dateFinInput.value = dateDebut;
        if (workingDaysSection) workingDaysSection.style.display = 'none';
        maintenanceBanner.style.display = 'none';
        return;
    }
    
    try {
        // Appel AJAX pour obtenir le nombre d'équipements si pas déjà chargé
        let equipmentCount = currentOperationData.equipmentCount;
        if (!equipmentCount || currentOperationData.lastSiteId !== siteId) {
            const response = await fetch(`/api/sites/${siteId}/equipment-count`);
            const data = await response.json();
            if (data.success) {
                equipmentCount = data.equipment_count;
                currentOperationData.equipmentCount = equipmentCount;
                currentOperationData.lastSiteId = siteId;
            }
        }
        
        if (equipmentCount > 0) {
            // Calculer le nombre de jours ouvrables nécessaires selon la cadence définie
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

            // Formater en YYYY-MM-DD
            const dateFin = `${lastActiveDate.getFullYear()}-${pad(lastActiveDate.getMonth() + 1)}-${pad(lastActiveDate.getDate())}`;
            
            // Mettre à jour le champ date_fin
            dateFinInput.value = dateFin;
            
            // Rendre le champ readonly
            dateFinInput.setAttribute('readonly', 'readonly');
            dateFinInput.style.backgroundColor = '#f1f5f9';
            dateFinInput.style.cursor = 'not-allowed';
            
            // Rendre visible la section des jours ouvrables et générer les badges cliquables
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

            // Afficher le message d'information
            maintenanceCalc.innerHTML = `<strong>${equipmentCount} équipement(s)</strong> sur le site ÷ <strong>${cadence} splits/jour</strong> = <strong style="color: #059669;">${workingDaysNeeded} jour(s) ouvrable(s)</strong> requis ${skippedDaysList.length > 0 ? ` · <span style="color: #b91c1c;">(${skippedDaysList.length} sauté(s))</span>` : ''}`;
            maintenanceBanner.style.display = 'flex';
            
            // Mettre à jour le label
            dateFinStatus.textContent = '(calculée avec jours ouvrables)';
            dateFinStatus.style.color = '#3b82f6';
            dateFinReadonlyMsg.innerHTML = `<i class="fa-solid fa-lock"></i> Date calculée avec ${workingDaysNeeded} j ouvrables (${cadence} splits/jour)`;
            dateFinReadonlyMsg.style.display = 'block';
        } else {
            // Pas d'équipements : date fin = date début
            if (workingDaysSection) workingDaysSection.style.display = 'none';
            dateFinInput.value = dateDebut;
            dateFinInput.setAttribute('readonly', 'readonly');
            dateFinInput.style.backgroundColor = '#f1f5f9';
            dateFinInput.style.cursor = 'not-allowed';
            
            maintenanceCalc.textContent = `Aucun équipement sur ce site. Date de fin = Date de début.`;
            maintenanceBanner.style.display = 'flex';
            
            dateFinStatus.textContent = '(calculée automatiquement)';
            dateFinStatus.style.color = '#3b82f6';
            dateFinReadonlyMsg.innerHTML = `<i class="fa-solid fa-lock"></i> Date automatique`;
            dateFinReadonlyMsg.style.display = 'block';
        }
    } catch (error) {
        console.error('Erreur lors du calcul de la date de fin:', error);
        dateFinInput.value = dateDebut;
    }
}

// Afficher les détails si une opération était pré-sélectionnée (old() après erreur validation)
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('operation_select').value) {
        afficherDetailsOperation();
    }
});
</script>
@endsection
