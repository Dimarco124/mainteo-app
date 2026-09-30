@extends('layouts.app')

@section('title', 'Compléter l\'Équipement et Planifier l\'Installation')

@section('content')
<div class="header">
    <div>
        <a href="{{ route('demandes.show', $demande->id) }}" style="color: #64748b; text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.4rem; margin-bottom: 0.5rem;">
            <i class="fa-solid fa-arrow-left"></i> Retour à la Demande
        </a>
        <h1>Compléter l'Équipement et Planifier l'Installation</h1>
        <p style="color: #64748b; font-size: 0.85rem;">Demande #{{ $demande->numero_demande }} - {{ $demande->temp_equipement_nom }}</p>
    </div>
</div>

{{-- Informations de la demande --}}
<div class="card" style="max-width: 900px; margin-bottom: 1.5rem; background: #f0fdf4; border: 1px solid #a7f3d0;">
    <h3 style="font-size: 0.95rem; font-weight: 800; color: #065f46; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
        <i class="fa-solid fa-info-circle"></i> Informations de la Demande
    </h3>
    
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
        <div>
            <span style="font-size: 0.8rem; color: #047857; font-weight: 600;">Client :</span>
            <div style="font-weight: 700; color: #065f46;">{{ $demande->client->nom ?? 'N/A' }}</div>
        </div>
        <div>
            <span style="font-size: 0.8rem; color: #047857; font-weight: 600;">Base :</span>
            <div style="font-weight: 700; color: #065f46;">{{ $demande->base->nom_base ?? 'N/A' }}</div>
        </div>
        <div>
            <span style="font-size: 0.8rem; color: #047857; font-weight: 600;">Site :</span>
            <div style="font-weight: 700; color: #065f46;">{{ $demande->site->nom_site ?? 'N/A' }}</div>
        </div>
        <div>
            <span style="font-size: 0.8rem; color: #047857; font-weight: 600;">Date Souhaitée :</span>
            <div style="font-weight: 700; color: #065f46;">{{ $demande->date_debut_souhaitee ? \Carbon\Carbon::parse($demande->date_debut_souhaitee)->format('d/m/Y') : 'N/A' }}</div>
        </div>
    </div>
    
    <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #a7f3d0;">
        <span style="font-size: 0.8rem; color: #047857; font-weight: 600;">Informations Fournies par le Client :</span>
        <div style="background: #fff; padding: 0.75rem; border-radius: 0.5rem; margin-top: 0.5rem;">
            <div><strong>Nom :</strong> {{ $demande->temp_equipement_nom }}</div>
            <div><strong>Type :</strong> {{ $demande->temp_equipement_type }}</div>
            <div><strong>Emplacement :</strong> {{ $demande->temp_equipement_emplacement === 'interne' ? 'Interne/Intérieur' : 'Externe/Extérieur' }}</div>
            @if($demande->description)
            <div><strong>Description :</strong> {{ $demande->description }}</div>
            @endif
        </div>
    </div>
</div>

<div class="card" style="max-width: 900px;">
    @if ($errors->any())
        <div style="background-color: #fee; border: 1px solid #fcc; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
            <h4 style="color: #c00; margin-bottom: 0.5rem;">⚠️ Erreurs de validation :</h4>
            <ul style="color: #c00; margin: 0; padding-left: 1.5rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    <form action="{{ route('equipements.completeStore', $demande->id) }}" method="POST">
        @csrf

        {{-- CARTE DYNAMIQUE DU CODE ÉQUIPEMENT --}}
        <div style="background: linear-gradient(135deg, #065f46, #047857); color: #ffffff; padding: 1.25rem 1.5rem; border-radius: 1rem; margin-bottom: 1.5rem; box-shadow: 0 10px 25px -5px rgba(6, 95, 70, 0.3); display: flex; align-items: center; justify-content: space-between; gap: 1rem; border: 2px solid #34d399;">
            <div>
                <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; display: flex; align-items: center; gap: 0.4rem; margin-bottom: 0.35rem; color: #a7f3d0;">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> Code Équipement (Généré automatiquement)
                </div>
                <div id="codePreviewText" style="font-family: monospace; font-size: 1.5rem; font-weight: 900; letter-spacing: 0.05em; color: #ffffff;">
                    Calcul en cours...
                </div>
            </div>
            <div style="background: rgba(255,255,255,0.15); backdrop-filter: blur(4px); padding: 0.5rem 0.85rem; border-radius: 0.6rem; font-size: 0.8rem; font-weight: 600; text-align: right; border: 1px solid rgba(255,255,255,0.2);">
                <i class="fa-solid fa-sync" style="font-size: 0.75rem; margin-right: 0.3rem;"></i> Calcul Automatique
            </div>
        </div>
        <input type="hidden" name="equipement_code" id="equipement_code" value="{{ old('equipement_code') }}">

        {{-- CARACTÉRISTIQUES TECHNIQUES --}}
        <h3 style="font-size: 1rem; font-weight: 800; color: #059669; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-cogs"></i> Caractéristiques Techniques
        </h3>

        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.25rem; margin-bottom: 1.5rem;">
            <div>
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Marque *</label>
                <input type="text" name="marque" value="{{ old('marque') }}" required placeholder="ex: Daikin, Carrier, Trane" style="width: 100%; padding: 0.75rem;">
                @error('marque')
                <span style="color: #be123c; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Modèle *</label>
                <input type="text" name="modele" value="{{ old('modele') }}" required placeholder="ex: RXS-F 2.5CV" style="width: 100%; padding: 0.75rem;">
                @error('modele')
                <span style="color: #be123c; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">N° de Série *</label>
                <input type="text" name="num_serie" value="{{ old('num_serie') }}" required placeholder="ex: DKN-2024-00123" style="width: 100%; padding: 0.75rem;">
                @error('num_serie')
                <span style="color: #be123c; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Puissance *</label>
                <input type="text" name="puissance" value="{{ old('puissance') }}" required placeholder="ex: 7.5 kW ou 9000 BTU" style="width: 100%; padding: 0.75rem;">
                @error('puissance')
                <span style="color: #be123c; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Réfrigérant</label>
                <input type="text" name="refrigerant" value="{{ old('refrigerant') }}" placeholder="ex: R410A, R134a, R407C" style="width: 100%; padding: 0.75rem;">
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">N° d'Équipement sur Site</label>
                <input type="text" name="num_sur_site" id="num_sur_site" value="{{ old('num_sur_site', '32') }}" placeholder="ex: 32" style="width: 100%; padding: 0.75rem;">
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Type d'Unité</label>
                <select name="type_unite" style="width: 100%; padding: 0.75rem;">
                    <option value="Exterieure" {{ old('type_unite', 'Exterieure') == 'Exterieure' ? 'selected' : '' }}>Extérieure</option>
                    <option value="Interieure" {{ old('type_unite') == 'Interieure' ? 'selected' : '' }}>Intérieure</option>
                    <option value="Inconnue" {{ old('type_unite') == 'Inconnue' ? 'selected' : '' }}>Inconnue</option>
                </select>
            </div>
        </div>

        {{-- AFFECTATION --}}
        <h3 style="font-size: 1rem; font-weight: 800; color: #059669; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; margin-top: 1.5rem;">
            <i class="fa-solid fa-users"></i> Affectation Équipe / Technicien
        </h3>

        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.25rem; margin-bottom: 1.5rem;">
            <div>
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Équipe</label>
                <select name="equipe_id" style="width: 100%; padding: 0.75rem;">
                    <option value="">-- Sélectionner une équipe --</option>
                    @foreach($equipes as $eq)
                    <option value="{{ $eq->id }}" {{ old('equipe_id') == $eq->id ? 'selected' : '' }}>
                        {{ $eq->nom_equipe }}
                    </option>
                    @endforeach
                </select>
                @error('equipe_id')
                <span style="color: #be123c; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Technicien</label>
                <select name="technicien_id" style="width: 100%; padding: 0.75rem;">
                    <option value="">-- Sélectionner un technicien --</option>
                    @foreach($techniciens as $tech)
                    <option value="{{ $tech->id }}" {{ old('technicien_id') == $tech->id ? 'selected' : '' }}>
                        {{ $tech->nom_complet }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div style="background: #eff6ff; border: 1px solid #bfdbfe; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
            <p style="font-size: 0.85rem; color: #1e40af; margin: 0;">
                <i class="fa-solid fa-info-circle"></i> Vous devez sélectionner au moins une équipe OU un technicien
            </p>
        </div>

        {{-- DATE D'INSTALLATION --}}
        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 700;">
                Date d'Installation Planifiée * <span style="color: #be123c;">(Obligatoire)</span>
            </label>
            <input type="date" name="date_installation_souhaitee" id="date_installation_souhaitee" 
                   value="{{ old('date_installation_souhaitee', $demande->date_debut_souhaitee ? $demande->date_debut_souhaitee->format('Y-m-d') : date('Y-m-d')) }}" 
                   required style="width: 100%; padding: 0.75rem; border: 2px solid #a7f3d0; border-radius: 0.5rem;">
            @error('date_installation_souhaitee')
            <span style="color: #be123c; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
            @enderror
        </div>

        {{-- OBSERVATIONS --}}
        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Observations / Remarques Techniques</label>
            <textarea name="observations" rows="3" placeholder="Notes techniques, précautions, informations complémentaires..." style="width: 100%; padding: 0.75rem;">{{ old('observations') }}</textarea>
        </div>

        <button type="submit" class="btn-primary" style="width: 100%; justify-content: center;">
            <i class="fa-solid fa-check-double"></i> Valider, Créer l'Équipement et Planifier l'Installation
        </button>
    </form>
</div>

{{-- JavaScript pour générer le code équipement --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    function generateEquipmentCode() {
        try {
            const codeInput = document.getElementById('equipement_code');
            const previewText = document.getElementById('codePreviewText');

            // 1. Code Base (depuis la demande)
            const baseCode = '{{ $demande->base->code_base ?? "F1" }}';

            // 2. Code Site (depuis la demande)
            const siteCode = '{{ $demande->site->code_site ?? "SITE" }}';

            // 3. Type (depuis temp_equipement_type) + Numéro sur site
            const typeVal = '{{ $demande->temp_equipement_type }}';
            let typeCode = 'S';
            if (typeVal.includes('Groupe')) typeCode = 'GF';
            else if (typeVal.includes('CVC')) typeCode = 'CVC';
            else if (typeVal.includes('Chambre')) typeCode = 'CF';
            else if (typeVal.includes('Armoire')) typeCode = 'AR';
            else if (typeVal.includes('Split')) typeCode = 'S';

            const numOnSiteInput = document.querySelector('[name="num_sur_site"]');
            const numOnSiteVal = (numOnSiteInput && numOnSiteInput.value) ? numOnSiteInput.value : '32';
            const numOnSite = numOnSiteVal.toString().replace(/[^0-9]/g, '') || '32';
            const typeNum = typeCode + numOnSite;

            // 4. Emplacement (depuis temp_equipement_emplacement) + Puissance
            const emplVal = '{{ $demande->temp_equipement_emplacement }}';
            const emplChar = (emplVal === 'interne') ? 'I' : 'E';

            const puisInput = document.querySelector('[name="puissance"]');
            const puisValRaw = puisInput ? puisInput.value : '1.5';
            const puisValNum = puisValRaw.replace(/[^0-9.]/g, '') || '1.5';
            const emplPuis = emplChar + puisValNum;

            // 5. Marque + Réfrigérant (ex: NAS56 si réfrigérant = R56)
            const marqueInput = document.querySelector('[name="marque"]');
            const marqueRaw = marqueInput ? marqueInput.value : 'NAS';
            const marqueCode = (marqueRaw.replace(/[^A-Za-z0-9]/g, '') || 'NAS').substring(0, 3).toUpperCase();

            const refrigerantInput = document.querySelector('[name="refrigerant"]');
            const refrigerantRaw = refrigerantInput ? refrigerantInput.value : 'R56';
            // Extraire les chiffres après le "R" dans le réfrigérant (ex: R410A -> 410, R56 -> 56, R407C -> 407)
            const refrigerantMatch = refrigerantRaw.match(/R?(\d+)/i);
            const refrigerantNum = refrigerantMatch ? refrigerantMatch[1] : '56';
            const serieCode = marqueCode + refrigerantNum;

            // 6. Mois + Année
            const dateInstInput = document.getElementById('date_installation_souhaitee');
            let mmaa = '0826';
            if (dateInstInput && dateInstInput.value) {
                const parts = dateInstInput.value.split('-');
                if (parts.length === 3) {
                    const mm = parts[1];
                    const aa = parts[0].slice(-2);
                    mmaa = mm + aa;
                }
            }

            const finalCode = `${baseCode}${siteCode} ${typeNum}${emplPuis}${serieCode} ${mmaa}`;

            if (codeInput) codeInput.value = finalCode;
            if (previewText) previewText.textContent = finalCode;

        } catch (e) {
            console.error('Erreur génération code:', e);
        }
    }

    // Écouter les changements
    document.querySelectorAll('form input, form select, form textarea').forEach(el => {
        el.addEventListener('input', generateEquipmentCode);
        el.addEventListener('change', generateEquipmentCode);
        el.addEventListener('keyup', generateEquipmentCode);
    });

    // Générer immédiatement
    generateEquipmentCode();
});
</script>
@endsection
