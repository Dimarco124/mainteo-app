@extends('layouts.app')

@section('title', 'Terminer la Maintenance')

@section('content')
<div class="header">
    <div>
        <a href="{{ route('maintenances.show', $maintenance->id) }}" style="color: #64748b; text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.4rem; margin-bottom: 0.5rem;">
            <i class="fa-solid fa-arrow-left"></i> Retour
        </a>
        <h1>Terminer la Maintenance {{ $maintenance->numero_maintenance }}</h1>
    </div>
</div>

<!-- Informations de la Maintenance -->
<div class="card" style="max-width: 900px; margin-bottom: 1.5rem; background: #eff6ff; border: 2px solid #bfdbfe;">
    <div class="card-header" style="background: #dbeafe; border-bottom: 1px solid #bfdbfe;">
        <h3 class="card-title" style="color: #1d4ed8;"><i class="fa-solid fa-info-circle"></i> Récapitulatif</h3>
    </div>
    
    <div style="padding: 1.5rem;">
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
            <div>
                <label style="font-size: 0.8rem; color: #1d4ed8; font-weight: 600;">Client</label>
                <p style="font-size: 0.9rem; color: #0f172a; font-weight: 700;">{{ $maintenance->client->nom ?? 'N/A' }}</p>
            </div>
            
            <div>
                <label style="font-size: 0.8rem; color: #1d4ed8; font-weight: 600;">Site</label>
                <p style="font-size: 0.9rem; color: #0f172a;">{{ $maintenance->site->nom_site ?? 'N/A' }}</p>
            </div>
            
            <div style="grid-column: 1 / -1;">
                <label style="font-size: 0.8rem; color: #1d4ed8; font-weight: 600;">Équipement</label>
                <p style="font-size: 0.9rem; color: #0f172a; font-weight: 700;">
                    {{ $maintenance->equipement->equipement_nom ?? 'N/A' }} 
                    <small style="color: #64748b;">({{ $maintenance->equipement->equipement_code ?? 'N/A' }})</small>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Formulaire de Rapport -->
<div class="card" style="max-width: 900px;">
<form action="{{ route('maintenances.terminer', $maintenance->id) }}" method="POST">
    @csrf

    <div style="padding: 1.5rem;">
        <div style="margin-bottom: 1.25rem;">
            <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">
                Compte-rendu de la Maintenance * <span style="color: #be123c;">(Obligatoire)</span>
            </label>
            <textarea name="rapport_technicien" required rows="8" style="width: 100%; padding: 0.75rem;" placeholder="Décrivez les opérations effectuées, l'état de l'équipement, les anomalies constatées...">{{ old('rapport_technicien') }}</textarea>
            <small style="color: #64748b; font-size: 0.75rem; display: block; margin-top: 0.25rem;">
                Soyez précis : décrivez ce qui a été fait, testé, vérifié, etc.
            </small>
            @error('rapport_technicien')
            <span style="color: #be123c; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
            @enderror
        </div>

        <div style="margin-bottom: 1.25rem;">
            <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">
                Pièces Utilisées (optionnel)
            </label>
            <textarea name="pieces_utilisees" rows="4" style="width: 100%; padding: 0.75rem;" placeholder="Ex: Filtre à air, Joint de compresseur, Huile...">{{ old('pieces_utilisees') }}</textarea>
            <small style="color: #64748b; font-size: 0.75rem; display: block; margin-top: 0.25rem;">
                Listez les pièces remplacées ou ajoutées
            </small>
        </div>

        <p style="background: #fffbeb; border: 1px solid #fde68a; color: #92400e; padding: 1rem; border-radius: 0.5rem; font-size: 0.85rem; margin-bottom: 1.5rem;">
            <i class="fa-solid fa-info-circle"></i> <strong>Information :</strong> 
            Une fois le rapport soumis, la maintenance sera marquée comme terminée et ne pourra plus être modifiée.
        </p>
    </div>

    <!-- Boutons d'action -->
    <div style="border-top: 1px solid #e2e8f0; padding: 1rem 1.5rem; background: #f8fafc; display: flex; gap: 1rem; justify-content: flex-end;">
        <a href="{{ route('maintenances.show', $maintenance->id) }}" style="padding: 0.75rem 1.25rem; border-radius: 0.75rem; background-color: #f1f5f9; color: #64748b; border: none; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem;">
            Annuler
        </a>
        <button type="submit" class="btn-primary">
            <i class="fa-solid fa-flag-checkered"></i> Terminer la Maintenance
        </button>
    </div>
</form>
</div>

@endsection
