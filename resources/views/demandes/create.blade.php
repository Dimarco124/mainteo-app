@extends('layouts.app')

@section('title', 'Nouvelle Demande')

@section('content')
<style>
    html, body {
        overflow-x: hidden;
    }
    .form-grid-3 {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.25rem;
        margin-bottom: 1.25rem;
    }
    .form-grid-2 {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.25rem;
        margin-bottom: 1.25rem;
    }
    .select2-container {
        width: 100% !important;
        max-width: 100% !important;
    }
    .select2-dropdown {
        box-sizing: border-box !important;
        max-width: 100% !important;
        overflow-x: hidden !important;
    }
    .select2-container .select2-selection--single {
        height: 44px !important;
        border-radius: 0.5rem !important;
        border: 1px solid #cbd5e1 !important;
        display: flex !important;
        align-items: center !important;
        padding-left: 0.25rem !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 42px !important;
    }

    @media (max-width: 900px) {
        .form-grid-3 {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    @media (max-width: 640px) {
        .form-grid-3, .form-grid-2 {
            grid-template-columns: 1fr;
        }
        .card {
            padding: 1.25rem !important;
        }
    }
</style>

<div class="header">
    <div>
        <a href="{{ route('demandes.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.4rem; margin-bottom: 0.5rem;">
            <i class="fa-solid fa-arrow-left"></i> Retour
        </a>
        <h1>Créer une Demande d'Intervention</h1>
    </div>
</div>

<div class="card" style="max-width: 900px; width: 100%;">
    <form action="{{ route('demandes.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- Numéro de Référence Externe (Conditionnellement Obligatoire) -->
        <div style="margin-bottom: 1.5rem; background-color: {{ isset($userClient) && strtoupper($userClient->nom) === 'SUCAF' ? '#fef3c7' : '#f0f9ff' }}; border: 2px solid {{ isset($userClient) && strtoupper($userClient->nom) === 'SUCAF' ? '#fbbf24' : '#bae6fd' }}; padding: 1.25rem; border-radius: 0.75rem;">
            <label style="display: block; font-size: 0.95rem; color: {{ isset($userClient) && strtoupper($userClient->nom) === 'SUCAF' ? '#92400e' : '#0369a1' }}; margin-bottom: 0.6rem; font-weight: 700;">
                <i class="fa-solid fa-barcode"></i> Numéro de Référence Externe 
                @if(isset($userClient) && strtoupper($userClient->nom) === 'SUCAF')
                    <span style="color: #dc2626; font-weight: 800;">*</span>
                    <span style="font-size: 0.8rem; color: #92400e; font-weight: 600;">(obligatoire pour SUCAF)</span>
                @else
                    <span style="font-size: 0.8rem; color: #64748b; font-weight: 400;">(optionnel)</span>
                @endif
            </label>
            <input 
                type="text" 
                name="numero_reference_externe" 
                id="numero_reference_externe" 
                value="{{ old('numero_reference_externe') }}" 
                maxlength="100"
                @if(isset($userClient) && strtoupper($userClient->nom) === 'SUCAF') required @endif
                placeholder="Ex: REQ-COCACOLA-2026-045, FERKE-DEM-123, CLI-2026-0789..."
                style="width: 100%; padding: 0.85rem; font-size: 1rem; font-weight: 600; font-family: 'Courier New', monospace; border: 2px solid {{ isset($userClient) && strtoupper($userClient->nom) === 'SUCAF' ? '#fbbf24' : '#bae6fd' }}; border-radius: 0.5rem;">
            <small style="color: #64748b; font-size: 0.8rem; display: block; margin-top: 0.5rem;">
                <i class="fa-solid fa-info-circle"></i> Numéro de référence de la demande faite manuellement (hors application). 
                @if(isset($userClient) && strtoupper($userClient->nom) === 'SUCAF')
                    <strong style="color: #92400e;">Ce champ est obligatoire pour SUCAF.</strong>
                @else
                    Si fourni, il doit être unique.
                @endif
            </small>
            @error('numero_reference_externe')
                <span style="color: #be123c; font-size: 0.85rem; margin-top: 0.5rem; display: block; font-weight: 600; background-color: #fff1f2; padding: 0.5rem; border-radius: 0.375rem;">
                    <i class="fa-solid fa-exclamation-triangle"></i> {{ $message }}
                </span>
            @enderror
        </div>

        <div class="form-grid-3">
            @if($sites->count() > 0)
            <div>
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Site *</label>
                @if($sites->count() === 1)
                    {{-- Un seul site : champ désactivé avec valeur pré-remplie --}}
                    <input 
                        type="text" 
                        value="{{ $sites->first()->baseSite ? $sites->first()->baseSite->nom_base . ' - ' : '' }}{{ $sites->first()->nom_site }}"
                        disabled
                        style="width: 100%; padding: 0.75rem; background-color: #f1f5f9; color: #475569; font-weight: 600; border: 2px solid #cbd5e1; border-radius: 0.5rem; cursor: not-allowed;">
                    <input type="hidden" name="site_id" id="site_id" value="{{ $sites->first()->id }}">
                @else
                    {{-- Plusieurs sites : select normal --}}
                    <select name="site_id" id="site_id" required style="width: 100%; padding: 0.75rem;">
                        <option value="">Sélectionner un site</option>
                        @foreach($sites as $site)
                            <option value="{{ $site->id }}" {{ old('site_id') == $site->id ? 'selected' : '' }}>
                                {{ $site->baseSite ? $site->baseSite->nom_base . ' - ' : '' }}{{ $site->nom_site }}
                            </option>
                        @endforeach
                    </select>
                @endif
                @error('site_id')
                    <span style="color: #be123c; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Emplacement (Sous-site / Bureau)</label>
                <select name="zone_id" id="zone_id" style="width: 100%; padding: 0.75rem;">
                    <option value="">-- Tous les emplacements du site --</option>
                </select>
                @error('zone_id')
                    <span style="color: #be123c; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>

            <div style="position: relative;">
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Équipement *</label>
                <select name="equipement_id" id="equipement_id" required style="width: 100%; padding: 0.75rem;">
                    <option value="">Sélectionner d'abord un site</option>
                </select>
                @error('equipement_id')
                    <span style="color: #be123c; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>
            @else
            <!-- Cas où le demandeur n'a AUCUN site assigné -->
            <div style="grid-column: 1 / -1; background: linear-gradient(135deg, #fee2e2, #fecaca); border: 3px solid #dc2626; border-radius: 1rem; padding: 2rem; text-align: center;">
                <div style="font-size: 4rem; color: #dc2626; margin-bottom: 1rem;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <h3 style="color: #7f1d1d; font-size: 1.5rem; font-weight: 800; margin-bottom: 1rem;">
                    Aucun Site Assigné
                </h3>
                <p style="color: #991b1b; font-size: 1.1rem; font-weight: 600; line-height: 1.6; margin-bottom: 1.5rem;">
                    Vous n'avez actuellement aucun site assigné à votre compte.<br>
                    Vous ne pouvez pas créer de demande d'intervention sans site assigné.
                </p>
                <div style="background-color: white; border: 2px solid #dc2626; border-radius: 0.75rem; padding: 1.25rem; margin-top: 1.5rem;">
                    <p style="color: #7f1d1d; font-weight: 700; margin-bottom: 0.75rem;">
                        <i class="fa-solid fa-info-circle"></i> Que faire ?
                    </p>
                    <p style="color: #991b1b; font-size: 0.95rem; line-height: 1.5;">
                        Veuillez contacter votre <strong>superviseur client</strong> ou l'<strong>administrateur</strong> pour qu'ils assignent un ou plusieurs sites à votre compte.
                    </p>
                </div>
            </div>
            @endif
        </div>

        <div style="margin-bottom: 1.25rem;">
            <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Description du Problème *</label>
            <textarea name="description" id="description" rows="4" required style="width: 100%; padding: 0.75rem;" placeholder="Décrivez le problème rencontré en détail...">{{ old('description') }}</textarea>
            @error('description')
                <span style="color: #be123c; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-grid-2">
            <div>
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Type d'Intervention</label>
                <div style="background-color: #fff7ed; border: 1px solid #ffedd5; color: #c2410c; padding: 0.75rem; border-radius: 0.5rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-wrench"></i> Dépannage
                </div>
                <input type="hidden" name="type_intervention" value="Dépannage">
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Niveau d'Urgence *</label>
                <select name="niveau_urgence" id="niveau_urgence" required style="width: 100%; padding: 0.75rem;">
                    <option value="faible" {{ old('niveau_urgence') == 'faible' ? 'selected' : '' }}>Faible</option>
                    <option value="moyen" {{ old('niveau_urgence', 'moyen') == 'moyen' ? 'selected' : '' }}>Moyen</option>
                    <option value="urgent" {{ old('niveau_urgence') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                    <option value="critique" {{ old('niveau_urgence') == 'critique' ? 'selected' : '' }}>Critique</option>
                </select>
                @error('niveau_urgence')
                    <span style="color: #be123c; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div style="margin-bottom: 1.25rem;">
            <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Date d'Intervention Souhaitée</label>
            <input type="date" name="date_debut_souhaitee" id="date_debut_souhaitee" value="{{ old('date_debut_souhaitee') }}" style="width: 100%; padding: 0.75rem;">
            <small style="color: #94a3b8; font-size: 0.75rem;">Indiquez la date à laquelle vous souhaitez que l'intervention ait lieu</small>
            @error('date_debut_souhaitee')
                <span style="color: #be123c; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
            @enderror
        </div>

        @if(Auth::user()->isSuperviseurClient() || Auth::user()->isAdmin())
        <div style="margin-bottom: 1.5rem; background: linear-gradient(135deg, #fef2f2, #fff); border: 2px solid #fecaca; border-radius: 0.75rem; padding: 1rem 1.25rem;">
            <label style="display: flex; align-items: flex-start; gap: 0.75rem; cursor: pointer; margin: 0;">
                <input type="checkbox" name="est_vip" value="1" {{ old('est_vip') ? 'checked' : '' }} style="margin-top: 0.2rem; width: 1.25rem; height: 1.25rem; cursor: pointer; accent-color: #dc2626;">
                <div>
                    <div style="font-weight: 800; color: #991b1b; font-size: 0.95rem; display: flex; align-items: center; gap: 0.4rem;">
                        <i class="fa-solid fa-crown" style="color: #dc2626;"></i> Définir comme Demande VIP (Priorité absolue)
                    </div>
                    <div style="font-size: 0.8rem; color: #7f1d1d; margin-top: 0.25rem; line-height: 1.35;">
                        Cochez cette case si cette demande concerne un haut cadre ou une urgence absolue nécessitant un traitement prioritaire immédiat par Soutarah.
                    </div>
                </div>
            </label>
        </div>
        @endif

        @if($sites->count() > 0)
        <button type="submit" class="btn-primary">
            <i class="fa-solid fa-check"></i> Créer la Demande
        </button>
        @endif
    </form>
</div>

<script>
@if($sites->count() > 0)
let allSiteEquipements = [];
let allSiteZones = [];

function renderEquipements(selectedZoneId) {
    const equipementSelect = document.getElementById('equipement_id');
    if (!equipementSelect) return;

    if ($(equipementSelect).data('select2')) {
        $(equipementSelect).select2('destroy');
    }

    equipementSelect.innerHTML = '<option value="">Sélectionner un équipement</option>';

    let filtered = allSiteEquipements;
    if (selectedZoneId) {
        filtered = allSiteEquipements.filter(e => e.zone_id == selectedZoneId);
    }

    if (filtered.length === 0) {
        equipementSelect.innerHTML = '<option value="">Aucun équipement dans cet emplacement</option>';
    } else {
        filtered.forEach(equipement => {
            const option = document.createElement('option');
            option.value = equipement.id;
            option.textContent = `${equipement.equipement_code} - ${equipement.equipement_nom}`;
            equipementSelect.appendChild(option);
        });
    }

    $(equipementSelect).select2({
        width: '100%',
        dropdownParent: $(equipementSelect).parent(),
        placeholder: "🔍 Rechercher un équipement...",
        allowClear: true,
        language: {
            noResults: function() {
                return "Aucun équipement trouvé";
            },
            searching: function() {
                return "Recherche en cours...";
            }
        }
    });
}

// Fonction pour charger zones et équipements d'un site
function loadSiteData(siteId) {
    const zoneSelect = document.getElementById('zone_id');
    const equipementSelect = document.getElementById('equipement_id');
    
    if ($(equipementSelect).data('select2')) {
        $(equipementSelect).select2('destroy');
    }
    
    zoneSelect.innerHTML = '<option value="">Chargement...</option>';
    equipementSelect.innerHTML = '<option value="">Chargement...</option>';
    allSiteEquipements = [];
    allSiteZones = [];
    
    if (siteId) {
        // 1. Charger les emplacements (zones) du site
        fetch(`/api/sites/${siteId}/zones`)
            .then(res => res.json())
            .then(zones => {
                allSiteZones = zones;
                zoneSelect.innerHTML = '<option value="">-- Tous les emplacements du site --</option>';
                zones.forEach(z => {
                    const opt = document.createElement('option');
                    opt.value = z.id;
                    opt.textContent = z.nom_zone + (z.code_zone ? ` (${z.code_zone})` : '');
                    zoneSelect.appendChild(opt);
                });
            })
            .catch(err => {
                console.error('Erreur chargement emplacements:', err);
                zoneSelect.innerHTML = '<option value="">-- Erreur emplacements --</option>';
            });

        // 2. Charger les équipements du site
        fetch(`/api/sites/${siteId}/equipements`)
            .then(response => response.json())
            .then(data => {
                allSiteEquipements = data;
                renderEquipements(zoneSelect.value);
            })
            .catch(error => {
                console.error('Erreur chargement équipements:', error);
                equipementSelect.innerHTML = '<option value="">Erreur de chargement</option>';
            });
    } else {
        zoneSelect.innerHTML = '<option value="">-- Choisir d\'abord un site --</option>';
        equipementSelect.innerHTML = '<option value="">Sélectionner d\'abord un site</option>';
        $(equipementSelect).select2({
            width: '100%',
            dropdownParent: $(equipementSelect).parent(),
            placeholder: "🔍 Rechercher un équipement...",
            allowClear: true
        });
    }
}

// Charger les zones et équipements lors du changement de site (si select multiple sites)
const siteSelectElement = document.getElementById('site_id');
if (siteSelectElement && siteSelectElement.tagName === 'SELECT') {
    siteSelectElement.addEventListener('change', function() {
        loadSiteData(this.value);
    });
}

// AUTO-LOAD : Charger automatiquement si un site est déjà sélectionné (input hidden ou select avec 1 seul site)
document.addEventListener('DOMContentLoaded', function() {
    const siteIdInput = document.getElementById('site_id');
    
    if (siteIdInput) {
        let siteId = siteIdInput.value;
        
        // Si c'est un select, vérifier le nombre d'options
        if (siteIdInput.tagName === 'SELECT') {
            const siteOptions = siteIdInput.querySelectorAll('option[value!=""]');
            if (siteOptions.length === 1 && !siteId) {
                // Un seul site : auto-sélectionner
                siteId = siteOptions[0].value;
                siteIdInput.value = siteId;
            }
        }
        
        // Si un site est sélectionné/prérempli, charger ses données
        if (siteId) {
            loadSiteData(siteId);
        }
    }
});

// Événement: Changement de zone → Re-render équipements
document.getElementById('zone_id').addEventListener('change', function() {
    renderEquipements(this.value);
});

@endif
</script>
@endsection
