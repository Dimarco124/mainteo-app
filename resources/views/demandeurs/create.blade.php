@extends('layouts.app')

@section('title', 'Créer un Demandeur')

@section('content')
<div class="header">
    <div class="page-title">
        <h1>Créer un Nouveau Demandeur</h1>
        <p>Ajouter un utilisateur demandeur et l'assigner aux sites de votre base.</p>
    </div>
</div>

<div class="card" style="max-width: 900px;">
    <form action="{{ route('demandeurs.store') }}" method="POST">
        @csrf

        <div style="background-color: #eff6ff; padding: 1rem; margin-bottom: 1.5rem; border-radius: 0.5rem;">
            <p style="margin: 0; color: #1e40af; font-size: 0.85rem;">
                <i class="fa-solid fa-info-circle"></i> <strong>Information :</strong> Ce demandeur sera assigné à votre base et pourra créer des demandes d'intervention pour les sites que vous lui attribuez.
            </p>
        </div>

        <h3 style="margin-bottom: 1rem; color: #0f172a; border-bottom: 2px solid #e2e8f0; padding-bottom: 0.5rem; font-size: 1rem; font-weight: 700;">
            <i class="fa-solid fa-user"></i> Informations Personnelles
        </h3>

        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem; margin-bottom: 1.5rem;">
            <div>
                <label for="nom" style="display: block; margin-bottom: 0.5rem; font-weight: 700; color: #0f172a; font-size: 0.85rem;">
                    Nom <span style="color: #dc2626;">*</span>
                </label>
                <input type="text" name="nom" id="nom" value="{{ old('nom') }}" required style="width: 100%; padding: 0.75rem;">
                @error('nom')
                    <span style="color: #dc2626; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="prenom" style="display: block; margin-bottom: 0.5rem; font-weight: 700; color: #0f172a; font-size: 0.85rem;">
                    Prénom
                </label>
                <input type="text" name="prenom" id="prenom" value="{{ old('prenom') }}" style="width: 100%; padding: 0.75rem;">
                @error('prenom')
                    <span style="color: #dc2626; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem; margin-bottom: 1.5rem;">
            <div>
                <label for="email" style="display: block; margin-bottom: 0.5rem; font-weight: 700; color: #0f172a; font-size: 0.85rem;">
                    Email <span style="color: #dc2626;">*</span>
                </label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required style="width: 100%; padding: 0.75rem;" placeholder="identifiant@exemple.com">
                @error('email')
                    <span style="color: #dc2626; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
                <span style="font-size: 0.75rem; color: #64748b; margin-top: 0.5rem; display: block;">
                    <i class="fa-solid fa-info-circle"></i> Identifiant de connexion
                </span>
            </div>

            <div>
                <label for="mot_de_passe" style="display: block; margin-bottom: 0.5rem; font-weight: 700; color: #0f172a; font-size: 0.85rem;">
                    Mot de passe <span style="color: #dc2626;">*</span>
                </label>
                <input type="password" name="mot_de_passe" id="mot_de_passe" required style="width: 100%; padding: 0.75rem;" placeholder="Minimum 4 caractères">
                @error('mot_de_passe')
                    <span style="color: #dc2626; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div style="margin-bottom: 2rem;">
            <label for="telephone" style="display: block; margin-bottom: 0.5rem; font-weight: 700; color: #0f172a; font-size: 0.85rem;">
                Numéro de Téléphone <span style="color: #dc2626;">*</span>
            </label>
            <input type="text" name="telephone" id="telephone" value="{{ old('telephone') }}" required style="width: 100%; padding: 0.75rem;" placeholder="+225 XX XX XX XX XX">
            @error('telephone')
                <span style="color: #dc2626; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
            @enderror
            <span style="font-size: 0.75rem; color: #64748b; margin-top: 0.5rem; display: block;">
                <i class="fa-solid fa-info-circle"></i> Sera visible par Superviseur Soutarah pour confirmation d'intervention
            </span>
        </div>

        <h3 style="margin-bottom: 1rem; color: #0f172a; border-bottom: 2px solid #e2e8f0; padding-bottom: 0.5rem; font-size: 1rem; font-weight: 700;">
            <i class="fa-solid fa-location-dot"></i> Assignation aux Sites
        </h3>

        <div style="background-color: #fef3c7; padding: 1rem; margin-bottom: 1rem; border-radius: 0.5rem;">
            <p style="margin: 0; color: #b45309; font-size: 0.85rem;">
                <i class="fa-solid fa-exclamation-triangle"></i> <strong>Important :</strong> Sélectionnez au moins un site. Le demandeur pourra créer des demandes uniquement pour les sites cochés ci-dessous.
            </p>
        </div>

        <div style="margin-bottom: 1.5rem;">
            @error('sites')
                <div style="background-color: #fee2e2; padding: 0.75rem; margin-bottom: 1rem; border-radius: 0.5rem;">
                    <span style="color: #be123c; font-size: 0.85rem;">{{ $message }}</span>
                </div>
            @enderror

            @if($sites->count() > 0)
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem; max-height: 400px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 0.5rem; padding: 1rem; background-color: #f8fafc;">
                @foreach($sites as $site)
                <label style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; background-color: white; border: 2px solid #e2e8f0; border-radius: 0.5rem; cursor: pointer; transition: all 0.2s;" class="site-checkbox-label">
                    <input type="checkbox" name="sites[]" value="{{ $site->id }}" 
                           {{ is_array(old('sites')) && in_array($site->id, old('sites')) ? 'checked' : '' }}
                           style="width: 18px; height: 18px; cursor: pointer;">
                    <span style="font-weight: 600; color: #1e40af; font-size: 0.9rem;">
                        <i class="fa-solid fa-building"></i> {{ $site->nom_site }}
                    </span>
                </label>
                @endforeach
            </div>

            <div style="margin-top: 0.75rem; display: flex; gap: 0.5rem;">
                <button type="button" onclick="selectAllSites()" style="padding: 0.5rem 1rem; background-color: #10b981; color: white; border: none; border-radius: 0.5rem; font-weight: 600; cursor: pointer; font-size: 0.85rem;">
                    <i class="fa-solid fa-check-double"></i> Tout sélectionner
                </button>
                <button type="button" onclick="deselectAllSites()" style="padding: 0.5rem 1rem; background-color: #64748b; color: white; border: none; border-radius: 0.5rem; font-weight: 600; cursor: pointer; font-size: 0.85rem;">
                    <i class="fa-solid fa-times"></i> Tout désélectionner
                </button>
            </div>
            @else
            <div style="text-align: center; padding: 2rem; background-color: #fef3c7; border-radius: 0.5rem;">
                <i class="fa-solid fa-exclamation-triangle" style="font-size: 2rem; color: #f59e0b; margin-bottom: 0.5rem;"></i>
                <p style="color: #b45309; font-weight: 600;">Aucun site disponible dans votre base.</p>
                <p style="color: #92400e; font-size: 0.85rem;">Contactez l'administrateur pour créer des sites.</p>
            </div>
            @endif
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 1rem; border-top: 1px solid #e2e8f0;">
            <a href="{{ route('demandeurs.index') }}" 
               style="background-color: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; padding: 0.65rem 1.1rem; border-radius: 0.75rem; text-decoration: none; font-size: 0.8rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-arrow-left"></i> Annuler
            </a>
            <button type="submit" class="btn-primary" {{ $sites->count() === 0 ? 'disabled' : '' }}>
                <i class="fa-solid fa-save"></i> Créer le Demandeur
            </button>
        </div>
    </form>
</div>

<style>
.site-checkbox-label:hover {
    border-color: #1d4ed8 !important;
    background-color: #eff6ff !important;
}

.site-checkbox-label:has(input:checked) {
    border-color: #1d4ed8 !important;
    background-color: #dbeafe !important;
}
</style>

<script>
function selectAllSites() {
    document.querySelectorAll('input[name="sites[]"]').forEach(checkbox => {
        checkbox.checked = true;
    });
}

function deselectAllSites() {
    document.querySelectorAll('input[name="sites[]"]').forEach(checkbox => {
        checkbox.checked = false;
    });
}
</script>
@endsection
