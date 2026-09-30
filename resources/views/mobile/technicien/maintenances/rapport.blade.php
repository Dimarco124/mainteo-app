@extends('mobile.technicien.layout')

@section('title', 'Terminer Maintenance - MAINTEO Mobile')
@section('page-title', 'Terminer Maintenance')

@section('mobile-content')
<div style="padding: 1rem; padding-bottom: 120px;">
    
    {{-- En-tête --}}
    <div style="margin-bottom: 1.5rem;">
        <a href="{{ route('mobile.technicien.maintenances.details', $maintenance->id) }}" style="display: inline-flex; align-items: center; gap: 0.5rem; color: var(--text-muted); text-decoration: none; font-size: 0.9rem; margin-bottom: 1rem;">
            <i class="fa-solid fa-arrow-left"></i> Retour
        </a>
        
        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, #047857, #10b981); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">
                <i class="fa-solid fa-flag-checkered"></i>
            </div>
            <div>
                <div style="font-size: 0.8rem; color: #10b981; font-weight: 700;">{{ $maintenance->numero_maintenance }}</div>
                <h1 style="font-size: 1.3rem; font-weight: 800; color: var(--text-dark); margin: 0;">Terminer Maintenance</h1>
            </div>
        </div>
    </div>
    
    {{-- Informations de contexte --}}
    <div class="card" style="margin-bottom: 1rem; background: #f0fdf4; border: 1px solid #a7f3d0;">
        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
            <i class="fa-solid fa-building" style="color: #047857; font-size: 1.2rem;"></i>
            <div>
                <div style="font-size: 0.75rem; color: #065f46;">Client</div>
                <div style="font-size: 0.9rem; font-weight: 700; color: #047857;">{{ $maintenance->client->nom ?? 'N/A' }}</div>
            </div>
        </div>
        
        @if($maintenance->site)
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <i class="fa-solid fa-location-dot" style="color: #047857; font-size: 1.2rem;"></i>
            <div>
                <div style="font-size: 0.75rem; color: #065f46;">Site</div>
                <div style="font-size: 0.9rem; font-weight: 700; color: #047857;">{{ $maintenance->site->nom_site }}</div>
            </div>
        </div>
        @endif
    </div>
    
    {{-- Formulaire --}}
    <form action="{{ route('mobile.technicien.maintenances.terminer', $maintenance->id) }}" method="POST">
        @csrf
        
        <div class="card" style="margin-bottom: 1rem;">
            <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-dark); margin-bottom: 0.5rem;">
                <i class="fa-solid fa-clipboard-list"></i> Compte-rendu de la Maintenance *
            </label>
            <textarea 
                name="rapport_technicien" 
                required 
                rows="8" 
                placeholder="Décrivez les opérations effectuées, l'état des équipements, les anomalies constatées..."
                style="width: 100%; padding: 0.75rem; border: 1.5px solid var(--border-color); border-radius: 0.75rem; font-size: 0.9rem; font-family: inherit; resize: vertical;"
            >{{ old('rapport_technicien') }}</textarea>
            <small style="display: block; font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem;">
                Soyez précis : décrivez ce qui a été fait, testé, vérifié, etc.
            </small>
            @error('rapport_technicien')
            <div style="color: #dc2626; font-size: 0.8rem; margin-top: 0.5rem;">
                <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
            </div>
            @enderror
        </div>
        
        <div class="card" style="margin-bottom: 1rem;">
            <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-dark); margin-bottom: 0.5rem;">
                <i class="fa-solid fa-toolbox"></i> Pièces utilisées (optionnel)
            </label>
            <textarea 
                name="pieces_utilisees" 
                rows="4" 
                placeholder="Listez les pièces remplacées ou utilisées..."
                style="width: 100%; padding: 0.75rem; border: 1.5px solid var(--border-color); border-radius: 0.75rem; font-size: 0.9rem; font-family: inherit; resize: vertical;"
            >{{ old('pieces_utilisees') }}</textarea>
            @error('pieces_utilisees')
            <div style="color: #dc2626; font-size: 0.8rem; margin-top: 0.5rem;">
                <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
            </div>
            @enderror
        </div>
        
        {{-- Avertissement --}}
        <div style="background: #fef3c7; border: 1.5px solid #fbbf24; border-radius: 0.75rem; padding: 1rem; margin-bottom: 1rem;">
            <div style="display: flex; gap: 0.75rem;">
                <i class="fa-solid fa-info-circle" style="color: #d97706; font-size: 1.2rem; margin-top: 0.1rem;"></i>
                <div style="font-size: 0.85rem; color: #92400e; line-height: 1.5;">
                    <strong>Attention :</strong> Une fois soumis, votre rapport sera envoyé au superviseur pour validation. Assurez-vous que toutes les informations sont complètes.
                </div>
            </div>
        </div>
        
        {{-- Boutons d'action (fixes en bas) --}}
        <div style="position: fixed; bottom: 70px; left: 1rem; right: 1rem; z-index: 10; display: flex; gap: 0.75rem;">
            <a href="{{ route('mobile.technicien.maintenances.details', $maintenance->id) }}" style="flex: 1; background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; padding: 1rem; border-radius: 1rem; font-size: 0.95rem; font-weight: 700; text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center;">
                <i class="fa-solid fa-times"></i>&nbsp; Annuler
            </a>
            <button type="submit" style="flex: 2; background: linear-gradient(135deg, #047857, #10b981); color: white; border: none; padding: 1rem; border-radius: 1rem; font-size: 0.95rem; font-weight: 700; cursor: pointer; box-shadow: 0 4px 12px rgba(4, 120, 87, 0.3); display: flex; align-items: center; justify-content: center;">
                <i class="fa-solid fa-check"></i>&nbsp; Terminer et Soumettre
            </button>
        </div>
    </form>
</div>
@endsection
