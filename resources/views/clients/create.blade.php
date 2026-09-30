@extends('layouts.app')

@section('title', 'Nouvelle Entreprise')

@section('content')
<div class="header">
    <div class="page-title">
        <h1>Nouvelle Entreprise Cliente</h1>
        <p>Enregistrer une nouvelle entreprise dans le système.</p>
    </div>
</div>

<div class="card" style="max-width: 700px;">
    <form action="{{ route('clients.store') }}" method="POST">
        @csrf

        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div>
                <label style="display: block; font-size: 0.85rem; color: #0f172a; margin-bottom: 0.5rem; font-weight: 700;">
                    Code <span style="color: #dc2626;">*</span>
                </label>
                <input type="text" name="code" required value="{{ old('code', $nextCode) }}" 
                       placeholder="Ex: CLI-001, SOCIDA, F1"
                       style="width: 100%; padding: 0.75rem;">
                <small style="color: #64748b; font-size: 0.7rem;">Code unique de l'entreprise (texte libre)</small>
                @error('code')
                    <span style="color: #dc2626; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>
            <div>
                <label style="display: block; font-size: 0.85rem; color: #0f172a; margin-bottom: 0.5rem; font-weight: 700;">
                    Nom / Raison Sociale <span style="color: #dc2626;">*</span>
                </label>
                <input type="text" name="nom" required value="{{ old('nom') }}" 
                       placeholder="Ex: SOCIDA, SITARAIL, PETROCI" 
                       style="width: 100%; padding: 0.75rem;">
                @error('nom')
                    <span style="color: #dc2626; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div>
                <label style="display: block; font-size: 0.85rem; color: #0f172a; margin-bottom: 0.5rem; font-weight: 700;">
                    Téléphone Principal
                </label>
                <input type="text" name="telephone" value="{{ old('telephone') }}" 
                       placeholder="+225 27 22 00 00 00" 
                       style="width: 100%; padding: 0.75rem;">
            </div>
            <div>
                <label style="display: block; font-size: 0.85rem; color: #0f172a; margin-bottom: 0.5rem; font-weight: 700;">
                    Email de Contact
                </label>
                <input type="email" name="email" value="{{ old('email') }}" 
                       placeholder="contact@entreprise.com" 
                       style="width: 100%; padding: 0.75rem;">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div>
                <label style="display: block; font-size: 0.85rem; color: #0f172a; margin-bottom: 0.5rem; font-weight: 700;">
                    Ville du Siège
                </label>
                <input type="text" name="ville" value="{{ old('ville') }}" 
                       placeholder="Abidjan, Bouaké, San-Pedro..." 
                       style="width: 100%; padding: 0.75rem;">
            </div>
            <div>
                <label style="display: block; font-size: 0.85rem; color: #0f172a; margin-bottom: 0.5rem; font-weight: 700;">
                    Pays
                </label>
                <input type="text" name="pays" value="{{ old('pays', 'Côte d\'Ivoire') }}" 
                       style="width: 100%; padding: 0.75rem;">
            </div>
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-size: 0.85rem; color: #0f172a; margin-bottom: 0.5rem; font-weight: 700;">
                Adresse Complète du Siège
            </label>
            <textarea name="adresse" rows="2" 
                      placeholder="Ex: Rue des Jardins, Plateau, Abidjan" 
                      style="width: 100%; padding: 0.75rem;">{{ old('adresse') }}</textarea>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 1rem; border-top: 1px solid #e2e8f0;">
            <a href="{{ route('clients.combined', ['view' => 'entreprises']) }}" 
               style="background-color: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; padding: 0.65rem 1.1rem; border-radius: 0.75rem; text-decoration: none; font-size: 0.8rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-arrow-left"></i> Annuler
            </a>
            <button type="submit" class="btn-primary">
                <i class="fa-solid fa-check"></i> Enregistrer l'Entreprise
            </button>
        </div>
    </form>
</div>
@endsection
