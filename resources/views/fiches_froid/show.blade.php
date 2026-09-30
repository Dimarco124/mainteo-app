@extends('layouts.app')

@section('title', 'Fiche Froid #' . $ficheFroid->id)

@section('content')
<div class="header">
    <div>
        <a href="{{ route('fiches-froid.index') }}" style="color: #94a3b8; text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.4rem; margin-bottom: 0.5rem;">
            <i class="fa-solid fa-arrow-left"></i> Retour à la liste
        </a>
        <h1>Contrôle Froid & Fluide #{{ $ficheFroid->id }} <span style="color: #06b6d4; font-size: 1.1rem;">({{ $ficheFroid->freon }})</span></h1>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <a href="{{ route('fiches-froid.edit', $ficheFroid->id) }}" style="background-color: rgba(245, 158, 11, 0.15); color: #f59e0b; padding: 0.75rem 1.25rem; border-radius: 0.5rem; text-decoration: none; font-weight: 700;">
            <i class="fa-solid fa-pen"></i> Modifier
        </a>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
    <!-- Grille des contrôles frigorifiques -->
    <div class="card">
        <h3 style="font-size: 1.1rem; font-weight: 700; color: #06b6d4; margin-bottom: 1.25rem;">
            <i class="fa-solid fa-snowflake"></i> Grille de Contrôle Technique Frigorifique
        </h3>
        
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.25rem;">
            <div>
                <p style="color: #94a3b8; font-size: 0.85rem;">Fluide Frigorigène (Fréon)</p>
                <p style="font-weight: 700; font-size: 1.1rem; color: #38bdf8;">{{ $ficheFroid->freon }}</p>
            </div>
            <div>
                <p style="color: #94a3b8; font-size: 0.85rem;">État des Filtres</p>
                <p style="font-weight: 600;">{{ $ficheFroid->etat_filtres ?? 'Conforme' }}</p>
            </div>
            <div>
                <p style="color: #94a3b8; font-size: 0.85rem;">Disjoncteur / Dismatic</p>
                <p style="font-weight: 600;">{{ $ficheFroid->dismatic ?? 'OK' }}</p>
            </div>
            <div>
                <p style="color: #94a3b8; font-size: 0.85rem;">Protection DPN</p>
                <p style="font-weight: 600;">{{ $ficheFroid->dpn ?? 'OK' }}</p>
            </div>
            <div>
                <p style="color: #94a3b8; font-size: 0.85rem;">État du Support Unit</p>
                <p style="font-weight: 600;">{{ $ficheFroid->support ?? 'Bon état' }}</p>
            </div>
            <div>
                <p style="color: #94a3b8; font-size: 0.85rem;">Télécommande / Régulation</p>
                <p style="font-weight: 600;">{{ $ficheFroid->telecommande ?? 'Testé OK' }}</p>
            </div>
            <div>
                <p style="color: #94a3b8; font-size: 0.85rem;">Liaison Cuivre</p>
                <p style="font-weight: 600;">{{ $ficheFroid->cuivre ?? 'OK' }}</p>
            </div>
            <div>
                <p style="color: #94a3b8; font-size: 0.85rem;">Isolant Armaflex</p>
                <p style="font-weight: 600;">{{ $ficheFroid->armaflex ?? 'Intact' }}</p>
            </div>
            <div>
                <p style="color: #94a3b8; font-size: 0.85rem;">Test Étanchéité / Pression</p>
                <p style="font-weight: 600;"><span class="badge badge-success">{{ $ficheFroid->test ?? 'Conforme' }}</span></p>
            </div>
            <div>
                <p style="color: #94a3b8; font-size: 0.85rem;">État Général</p>
                <p style="font-weight: 600;"><span class="badge badge-info">{{ $ficheFroid->etat_general ?? 'Bon' }}</span></p>
            </div>
        </div>

        @if($ficheFroid->observations)
        <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #334155;">
            <p style="color: #94a3b8; font-size: 0.85rem; margin-bottom: 0.4rem;">Observations du Technicien</p>
            <p style="font-size: 0.95rem; line-height: 1.5;">{{ $ficheFroid->observations }}</p>
        </div>
        @endif
    </div>

    <!-- Équipement & Technicien -->
    <div>
        <div class="card" style="margin-bottom: 1.5rem;">
            <h3 style="font-size: 1.1rem; font-weight: 700; color: #38bdf8; margin-bottom: 1rem;">
                <i class="fa-solid fa-boxes-stacked"></i> Équipement Contrôlé
            </h3>
            <p style="color: #94a3b8; font-size: 0.85rem;">Code Équipement</p>
            <p style="font-weight: 700; font-size: 1.1rem; margin-bottom: 0.75rem;">
                <a href="{{ route('equipements.show', $ficheFroid->equipement_id ?? 1) }}" style="color: #38bdf8; text-decoration: none;">
                    {{ $ficheFroid->equipement->equipement_code ?? 'EQ-'.$ficheFroid->equipement_id }} <i class="fa-solid fa-external-link" style="font-size: 0.75rem;"></i>
                </a>
            </p>
            <p style="color: #94a3b8; font-size: 0.85rem;">Nom & Marque</p>
            <p style="font-weight: 600;">{{ $ficheFroid->equipement->equipement_nom ?? 'N/A' }} ({{ $ficheFroid->equipement->marque ?? '' }})</p>
        </div>

        <div class="card">
            <h3 style="font-size: 1.1rem; font-weight: 700; color: #10b981; margin-bottom: 1rem;">
                <i class="fa-solid fa-user-check"></i> Intervenant & Date
            </h3>
            <p style="color: #94a3b8; font-size: 0.85rem;">Technicien habilité</p>
            <p style="font-weight: 700; font-size: 1rem; margin-bottom: 0.75rem;">{{ $ficheFroid->technicien->nom_complet ?? 'N/A' }}</p>

            <p style="color: #94a3b8; font-size: 0.85rem;">Date et heure du contrôle</p>
            <p style="font-weight: 600;">{{ $ficheFroid->date_saisie }}</p>
        </div>
    </div>
</div>
@endsection
