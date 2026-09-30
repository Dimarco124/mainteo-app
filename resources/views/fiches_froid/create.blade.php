@extends('layouts.app')

@section('title', 'Saisie Fiche Froid F-GAS')

@section('content')
<div class="header">
    <div>
        <a href="{{ route('fiches-froid.index') }}" style="color: #94a3b8; text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.4rem; margin-bottom: 0.5rem;">
            <i class="fa-solid fa-arrow-left"></i> Annuler
        </a>
        <h1>Nouvelle Fiche de Contrôle Froid & Fluides (F-GAS)</h1>
    </div>
</div>

<div class="card" style="max-width: 800px;">
    <form action="{{ route('fiches-froid.store') }}" method="POST">
        @csrf

        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.25rem; margin-bottom: 1.25rem;">
            <div>
                <label style="display: block; font-size: 0.85rem; color: #94a3b8; margin-bottom: 0.4rem;">Équipement Frigorifique *</label>
                <select name="equipement_id" required style="width: 100%; padding: 0.75rem; background-color: #0f172a; border: 1px solid #334155; border-radius: 0.5rem; color: #fff; outline: none;">
                    <option value="">Sélectionner un équipement</option>
                    @foreach($equipements as $eq)
                    <option value="{{ $eq->id }}" {{ $selectedEquipementId == $eq->id ? 'selected' : '' }}>
                        {{ $eq->equipement_code ?? 'EQ-'.$eq->id }} - {{ $eq->equipement_nom }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; color: #94a3b8; margin-bottom: 0.4rem;">Type de Fluide (Fréon) *</label>
                <select name="freon" required style="width: 100%; padding: 0.75rem; background-color: #0f172a; border: 1px solid #334155; border-radius: 0.5rem; color: #fff; outline: none;">
                    <option value="R410A">R410A</option>
                    <option value="R32">R32</option>
                    <option value="R134a">R134a</option>
                    <option value="R404A">R404A</option>
                    <option value="R407C">R407C</option>
                    <option value="Autre">Autre</option>
                </select>
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; color: #94a3b8; margin-bottom: 0.4rem;">État des Filtres</label>
                <input type="text" name="etat_filtres" value="Propres / Lavés" style="width: 100%; padding: 0.75rem; background-color: #0f172a; border: 1px solid #334155; border-radius: 0.5rem; color: #fff; outline: none;">
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; color: #94a3b8; margin-bottom: 0.4rem;">Disjoncteur / Dismatic</label>
                <input type="text" name="dismatic" value="Conforme" style="width: 100%; padding: 0.75rem; background-color: #0f172a; border: 1px solid #334155; border-radius: 0.5rem; color: #fff; outline: none;">
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; color: #94a3b8; margin-bottom: 0.4rem;">Liaison Cuivre</label>
                <input type="text" name="cuivre" value="Bon état" style="width: 100%; padding: 0.75rem; background-color: #0f172a; border: 1px solid #334155; border-radius: 0.5rem; color: #fff; outline: none;">
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; color: #94a3b8; margin-bottom: 0.4rem;">Isolant Armaflex</label>
                <input type="text" name="armaflex" value="Bon état" style="width: 100%; padding: 0.75rem; background-color: #0f172a; border: 1px solid #334155; border-radius: 0.5rem; color: #fff; outline: none;">
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; color: #94a3b8; margin-bottom: 0.4rem;">Test d'Étanchéité / Pression</label>
                <input type="text" name="test" value="Étanchéité OK (Aucune fuite)" style="width: 100%; padding: 0.75rem; background-color: #0f172a; border: 1px solid #334155; border-radius: 0.5rem; color: #fff; outline: none;">
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; color: #94a3b8; margin-bottom: 0.4rem;">État Général</label>
                <select name="etat_general" style="width: 100%; padding: 0.75rem; background-color: #0f172a; border: 1px solid #334155; border-radius: 0.5rem; color: #fff; outline: none;">
                    <option value="Conforme">Conforme</option>
                    <option value="Avertissement">Avertissement</option>
                    <option value="Non conforme">Non conforme</option>
                </select>
            </div>
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-size: 0.85rem; color: #94a3b8; margin-bottom: 0.4rem;">Observations & Mesures Techniques</label>
            <textarea name="observations" rows="4" placeholder="Précisez la pression d'aspiration, la température de soufflage ou toute anomalie constatée..." style="width: 100%; padding: 0.75rem; background-color: #0f172a; border: 1px solid #334155; border-radius: 0.5rem; color: #fff; outline: none;"></textarea>
        </div>

        <button type="submit" style="background: linear-gradient(135deg, #06b6d4, #38bdf8); color: #0f172a; border: none; padding: 0.85rem 1.5rem; border-radius: 0.5rem; font-weight: 700; font-size: 1rem; cursor: pointer;">
            <i class="fa-solid fa-snowflake"></i> Valider et Enregistrer la Fiche Froid
        </button>
    </form>
</div>
@endsection
