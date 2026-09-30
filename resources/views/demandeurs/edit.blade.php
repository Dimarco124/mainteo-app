@extends('layouts.app')

@section('title', 'Modifier un Demandeur')

@section('content')
<div class="header">
    <div>
        <a href="{{ route('demandeurs.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.4rem; margin-bottom: 0.5rem;">
            <i class="fa-solid fa-arrow-left"></i> Retour à la liste
        </a>
        <h1>Modifier le Demandeur : {{ $demandeur->nom_complet }}</h1>
    </div>
</div>

<div class="card" style="max-width: 900px;">
    <form action="{{ route('demandeurs.update', $demandeur) }}" method="POST">
        @csrf
        @method('PUT')

        <h3 style="margin-bottom: 1rem; color: #1e40af; border-bottom: 2px solid #bfdbfe; padding-bottom: 0.5rem;">
            <i class="fa-solid fa-user"></i> Informations Personnelles
        </h3>

        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.25rem; margin-bottom: 1.25rem;">
            <div>
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">
                    Nom *
                </label>
                <input type="text" name="nom" value="{{ old('nom', $demandeur->nom) }}" required style="width: 100%; padding: 0.75rem;">
                @error('nom')
                    <span style="color: #be123c; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">
                    Prénom
                </label>
                <input type="text" name="prenom" value="{{ old('prenom', $demandeur->prenom) }}" style="width: 100%; padding: 0.75rem;">
                @error('prenom')
                    <span style="color: #be123c; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.25rem; margin-bottom: 1.25rem;">
            <div>
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">
                    Email *
                </label>
                <input type="email" name="email" value="{{ old('email', $demandeur->email) }}" required style="width: 100%; padding: 0.75rem;">
                @error('email')
                    <span style="color: #be123c; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">
                    Téléphone *
                </label>
                <input type="text" name="telephone" value="{{ old('telephone', $demandeur->telephone) }}" required style="width: 100%; padding: 0.75rem;">
                @error('telephone')
                    <span style="color: #be123c; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.25rem; margin-bottom: 2rem;">
            <div>
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">
                    Statut *
                </label>
                <select name="statut" required style="width: 100%; padding: 0.75rem;">
                    <option value="actif" {{ old('statut', $demandeur->statut) === 'actif' ? 'selected' : '' }}>Actif</option>
                    <option value="inactif" {{ old('statut', $demandeur->statut) === 'inactif' ? 'selected' : '' }}>Inactif</option>
                </select>
                @error('statut')
                    <span style="color: #be123c; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">
                    Nouveau Mot de passe <small style="color: #94a3b8;">(Laisser vide pour ne pas changer)</small>
                </label>
                <input type="password" name="mot_de_passe" style="width: 100%; padding: 0.75rem;">
                @error('mot_de_passe')
                    <span style="color: #be123c; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <h3 style="margin-bottom: 1rem; color: #1e40af; border-bottom: 2px solid #bfdbfe; padding-bottom: 0.5rem;">
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

            @php
                $sitesAssignesIds = old('sites', $demandeur->sitesAssignes->pluck('id')->toArray());
            @endphp

            @if($sites->count() > 0)
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem; max-height: 400px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 0.5rem; padding: 1rem; background-color: #f8fafc;">
                @foreach($sites as $site)
                <label style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; background-color: white; border: 2px solid #e2e8f0; border-radius: 0.5rem; cursor: pointer; transition: all 0.2s;" class="site-checkbox-label">
                    <input type="checkbox" name="sites[]" value="{{ $site->id }}" 
                           {{ in_array($site->id, $sitesAssignesIds) ? 'checked' : '' }}
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
            </div>
            @endif
        </div>

        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
            <button type="submit" class="btn-primary">
                <i class="fa-solid fa-save"></i> Enregistrer les Modifications
            </button>
            <a href="{{ route('demandeurs.index') }}" style="padding: 0.75rem 1.5rem; border-radius: 0.75rem; background-color: #f1f5f9; color: #64748b; text-decoration: none; font-weight: 700;">
                <i class="fa-solid fa-times"></i> Annuler
            </a>
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
