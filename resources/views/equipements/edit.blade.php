@extends('layouts.app')

@section('title', 'Modifier Équipement - ' . $equipement->equipement_nom)

@section('content')
<div class="header">
    <div>
        <a href="{{ route('equipements.show', $equipement->id) }}" style="color: #94a3b8; text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.4rem; margin-bottom: 0.5rem;">
            <i class="fa-solid fa-arrow-left"></i> Annuler
        </a>
        <h1>Modifier l'Équipement {{ $equipement->equipement_code }}</h1>
    </div>
</div>

<div class="card" style="max-width: 800px;">
    <form action="{{ route('equipements.update', $equipement->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.25rem; margin-bottom: 1.25rem;">
            <div>
                <label style="display: block; font-size: 0.85rem; color: #94a3b8; margin-bottom: 0.4rem;">Code Équipement *</label>
                <input type="text" name="equipement_code" required value="{{ old('equipement_code', $equipement->equipement_code) }}" style="width: 100%; padding: 0.75rem; background-color: #0f172a; border: 1px solid #334155; border-radius: 0.5rem; color: #fff; outline: none;">
            </div>
            <div>
                <label style="display: block; font-size: 0.85rem; color: #94a3b8; margin-bottom: 0.4rem;">Nom de l'Équipement *</label>
                <input type="text" name="equipement_nom" required value="{{ old('equipement_nom', $equipement->equipement_nom) }}" style="width: 100%; padding: 0.75rem; background-color: #0f172a; border: 1px solid #334155; border-radius: 0.5rem; color: #fff; outline: none;">
            </div>
            <div>
                <label style="display: block; font-size: 0.85rem; color: #94a3b8; margin-bottom: 0.4rem;">Marque</label>
                <input type="text" name="marque" value="{{ old('marque', $equipement->marque) }}" style="width: 100%; padding: 0.75rem; background-color: #0f172a; border: 1px solid #334155; border-radius: 0.5rem; color: #fff; outline: none;">
            </div>
            <div>
                <label style="display: block; font-size: 0.85rem; color: #94a3b8; margin-bottom: 0.4rem;">Puissance</label>
                <input type="text" name="puissance" value="{{ old('puissance', $equipement->puissance) }}" placeholder="Ex: 18000 BTU, 3CV, 15 kW" style="width: 100%; padding: 0.75rem; background-color: #0f172a; border: 1px solid #334155; border-radius: 0.5rem; color: #fff; outline: none;">
                <small style="color: #64748b; font-size: 0.7rem; display: block; margin-top: 0.25rem;">Exemple : 18000 BTU, 3CV, 1,5CV, 15 kW</small>
            </div>
            <div>
                <label style="display: block; font-size: 0.85rem; color: #94a3b8; margin-bottom: 0.4rem;">
                    <i class="fa-solid fa-cube" style="color: #8b5cf6;"></i> Type d'Unité (Split)
                </label>
                <select name="type_unite" style="width: 100%; padding: 0.75rem; background-color: #0f172a; border: 1px solid #334155; border-radius: 0.5rem; color: #fff; outline: none;">
                    <option value="Exterieure" {{ $equipement->type_unite == 'Exterieure' ? 'selected' : '' }}>Unité Extérieure</option>
                    <option value="Interieure" {{ $equipement->type_unite == 'Interieure' ? 'selected' : '' }}>Unité Intérieure</option>
                    <option value="Inconnue" {{ $equipement->type_unite == 'Inconnue' ? 'selected' : '' }}>Inconnue</option>
                </select>
                <small style="color: #64748b; font-size: 0.7rem; display: block; margin-top: 0.25rem;">Pour les climatiseurs split : unité intérieure ou extérieure</small>
            </div>
            <div>
                <label style="display: block; font-size: 0.85rem; color: #94a3b8; margin-bottom: 0.4rem;">
                    <i class="fa-solid fa-location-dot" style="color: #10b981;"></i> Position Physique *
                </label>
                <select name="emplacement" required style="width: 100%; padding: 0.75rem; background-color: #0f172a; border: 1px solid #334155; border-radius: 0.5rem; color: #fff; outline: none;">
                    <option value="interne" {{ old('emplacement', $equipement->emplacement) == 'interne' ? 'selected' : '' }}>🏢 Interne (Intérieur bâtiment)</option>
                    <option value="externe" {{ old('emplacement', $equipement->emplacement) == 'externe' ? 'selected' : '' }}>🌳 Externe (Extérieur bâtiment)</option>
                </select>
                <small style="color: #64748b; font-size: 0.7rem; display: block; margin-top: 0.25rem;">Interne = à l'intérieur du bâtiment, Externe = à l'extérieur</small>
            </div>
            <div>
                <label style="display: block; font-size: 0.85rem; color: #94a3b8; margin-bottom: 0.4rem;">État actuel</label>
                <select name="etat" style="width: 100%; padding: 0.75rem; background-color: #0f172a; border: 1px solid #334155; border-radius: 0.5rem; color: #fff; outline: none;">
                    <option value="Bon état" {{ $equipement->etat == 'Bon état' ? 'selected' : '' }}>Bon état</option>
                    <option value="En panne" {{ $equipement->etat == 'En panne' ? 'selected' : '' }}>En panne</option>
                    <option value="Maintenance requise" {{ $equipement->etat == 'Maintenance requise' ? 'selected' : '' }}>Maintenance requise</option>
                </select>
            </div>
            <div>
                <label style="display: block; font-size: 0.85rem; color: #94a3b8; margin-bottom: 0.4rem;">Zone / Emplacement (Sous-site)</label>
                <select name="zone_id" style="width: 100%; padding: 0.75rem; background-color: #0f172a; border: 1px solid #334155; border-radius: 0.5rem; color: #fff; outline: none;">
                    <option value="">-- Aucun sous-site / Emplacement --</option>
                    @if($equipement->site_id)
                        @foreach(\App\Models\ZoneSite::where('site_id', $equipement->site_id)->orderBy('nom_zone')->get() as $z)
                            <option value="{{ $z->id }}" {{ old('zone_id', $equipement->zone_id) == $z->id ? 'selected' : '' }}>
                                {{ $z->nom_zone }} ({{ $z->code_zone }})
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-size: 0.85rem; color: #94a3b8; margin-bottom: 0.4rem;">Observations & Notes Techniques</label>
            <textarea name="observations" rows="4" style="width: 100%; padding: 0.75rem; background-color: #0f172a; border: 1px solid #334155; border-radius: 0.5rem; color: #fff; outline: none;">{{ old('observations', $equipement->observations) }}</textarea>
        </div>

        <button type="submit" style="background: linear-gradient(135deg, #f59e0b, #d97706); color: #0f172a; border: none; padding: 0.85rem 1.5rem; border-radius: 0.5rem; font-weight: 700; font-size: 1rem; cursor: pointer;">
            <i class="fa-solid fa-save"></i> Mettre à jour l'Équipement
        </button>
    </form>
</div>
@endsection
