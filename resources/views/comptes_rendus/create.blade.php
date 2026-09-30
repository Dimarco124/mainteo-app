@extends('layouts.app')

@section('title', 'Compte Rendu Journalier – Équipe')

@section('content')
<div class="header">
    <div>
        <a href="{{ route('maintenances.show', $maintenance->id) }}" style="color: #64748b; text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.4rem; margin-bottom: 0.5rem;">
            <i class="fa-solid fa-arrow-left"></i> Retour à la Maintenance {{ $maintenance->numero_maintenance }}
        </a>
        <h1>📝 Compte Rendu Journalier – Équipe</h1>
        <p style="color: #64748b; font-size: 0.85rem;">
            Saisie quotidienne de l'avancement de la maintenance {{ $maintenance->numero_maintenance }}
        </p>
    </div>
</div>

<div class="card" style="max-width: 900px; margin: 0 auto;">
    <div style="background: linear-gradient(135deg, #059669 0%, #10b981 100%); color: white; padding: 1.25rem 1.5rem; border-radius: 0.75rem 0.75rem 0 0;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h3 style="font-size: 1.1rem; font-weight: 700; margin: 0;">{{ $maintenance->numero_maintenance }} — {{ $maintenance->site->nom_site ?? ($maintenance->client->nom ?? 'N/A') }}</h3>
                <div style="display: flex; flex-wrap: wrap; gap: 1.25rem; margin-top: 0.4rem; font-size: 0.82rem;">
                    <span>Objectif Site : <strong>{{ $maintenance->nombre_equipements_prevus }}</strong> éq.</span>
                    <span>Déjà Traités : <strong>{{ $maintenance->nombre_equipements_traites }}</strong> éq.</span>
                    <span>Reste sur Site : <strong style="background: rgba(255,255,255,0.25); padding: 0.15rem 0.5rem; border-radius: 6px;">{{ $maintenance->nombre_equipements_restants }}</strong> éq.</span>
                </div>
            </div>
            <div style="background: rgba(255,255,255,0.2); padding: 0.5rem 1rem; border-radius: 0.5rem; font-weight: 700; font-size: 0.9rem;">
                {{ $maintenance->pourcentage_avancement }}% Complété
            </div>
        </div>
    </div>

    <form action="{{ route('comptes-rendus.store', $maintenance->id) }}" method="POST" style="padding: 1.75rem;">
        @csrf

        {{-- Section 1 : Informations de base --}}
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.25rem; margin-bottom: 1.5rem;">
            
            {{-- Site --}}
            <div>
                <label style="display: block; font-size: 0.85rem; color: #475569; margin-bottom: 0.4rem; font-weight: 700;">
                    Site *
                </label>
                <input type="text" value="{{ $maintenance->site->nom_site ?? ($maintenance->base->nom_base ?? $maintenance->client->nom) }}" readonly style="width: 100%; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid #cbd5e1; background: #f8fafc; font-weight: 600; color: #1e293b;">
            </div>

            {{-- Date du rapport --}}
            <div>
                <label style="display: block; font-size: 0.85rem; color: #475569; margin-bottom: 0.4rem; font-weight: 700;">
                    Date *
                </label>
                <input type="date" name="date_rapport" value="{{ old('date_rapport', date('Y-m-d')) }}" required style="width: 100%; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid #cbd5e1; font-weight: 600; color: #1e293b;">
                @error('date_rapport')
                <span style="color: #dc2626; font-size: 0.75rem;">{{ $message }}</span>
                @enderror
            </div>
        </div>

        {{-- Équipe / Intervenants --}}
        <div style="margin-bottom: 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                <label style="font-size: 0.85rem; color: #475569; font-weight: 700; margin: 0;">
                    Intervenants de l'équipe *
                </label>
                @if(isset($userEquipe))
                <span style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; font-size: 0.75rem; font-weight: 700; padding: 0.2rem 0.6rem; border-radius: 9999px;">
                    <i class="fa-solid fa-users"></i> {{ $userEquipe->nom_equipe }}
                </span>
                @endif
            </div>
            <input type="text" name="noms_intervenants" value="{{ old('noms_intervenants', $nomsIntervenantsDefault) }}" placeholder="Ex: N'guessan, Moussa et Yaya" required style="width: 100%; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid #cbd5e1; font-size: 0.9rem;">
            <small style="color: #64748b; font-size: 0.75rem;">Membres de votre équipe ayant participé aux travaux aujourd'hui.</small>
        </div>

        <hr style="border: none; border-top: 1px solid #e2e8f0; margin-bottom: 1.5rem;">

        {{-- Section 2 : Activités et Équipements --}}
        
        {{-- Activités réalisées --}}
        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-size: 0.85rem; color: #475569; margin-bottom: 0.4rem; font-weight: 700;">
                Activités réalisées *
            </label>
            <textarea name="activites_realisees" rows="3" required placeholder="Ex: Entretien des climatiseurs du laboratoire, nettoyage des filtres..." style="width: 100%; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid #cbd5e1; font-size: 0.9rem;">{{ old('activites_realisees') }}</textarea>
            @error('activites_realisees')
            <span style="color: #dc2626; font-size: 0.75rem;">{{ $message }}</span>
            @enderror
        </div>

        {{-- Nombre d'équipements traités aujourd'hui --}}
        <div style="background-color: #ecfdf5; padding: 1.25rem; border-radius: 0.75rem; border: 2px solid #a7f3d0; margin-bottom: 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; flex-wrap: wrap; gap: 0.5rem;">
                <label style="font-size: 0.9rem; color: #065f46; font-weight: 800; margin: 0;">
                    <i class="fa-solid fa-list-check"></i> Nombre d'équipements traités par votre équipe aujourd'hui *
                </label>
                <span style="font-size: 0.8rem; font-weight: 700; color: #065f46; background: #ffffff; padding: 0.2rem 0.65rem; border-radius: 9999px; border: 1px solid #6ee7b7;">
                    Reste sur le site : <strong>{{ $maintenance->nombre_equipements_restants }}</strong> éq.
                </span>
            </div>
            <input type="number" name="nombre_equipements_traites" min="0" value="{{ old('nombre_equipements_traites', 0) }}" required style="width: 100%; padding: 0.85rem; border-radius: 0.5rem; border: 2px solid #10b981; font-size: 1.1rem; font-weight: 800; color: #065f46; background: white;">
            <small style="color: #047857; font-size: 0.78rem; display: block; margin-top: 0.4rem;">
                Ce chiffre sera automatiquement additionné au cumul global de la maintenance et réduira le nombre d'équipements restants sur le site.
            </small>
            @error('nombre_equipements_traites')
            <span style="color: #dc2626; font-size: 0.75rem;">{{ $message }}</span>
            @enderror
        </div>

        {{-- Section 3 : Constats et suivi --}}
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.25rem; margin-bottom: 1.5rem;">
            
            {{-- Anomalies / pannes constatées --}}
            <div>
                <label style="display: block; font-size: 0.85rem; color: #475569; margin-bottom: 0.4rem; font-weight: 700;">
                    Anomalies / pannes constatées
                </label>
                <textarea name="anomalies_constatees" rows="2" placeholder="Ex: pas d'anomalie ou Fuite de gaz sur unité 3..." style="width: 100%; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid #cbd5e1; font-size: 0.85rem;">{{ old('anomalies_constatees', "pas d'anomalie") }}</textarea>
            </div>

            {{-- Difficultés rencontrées --}}
            <div>
                <label style="display: block; font-size: 0.85rem; color: #475569; margin-bottom: 0.4rem; font-weight: 700;">
                    Difficultés rencontrées
                </label>
                <textarea name="difficultes_rencontrees" rows="2" placeholder="Ex: pas de difficulté ou Accès difficile à la toiture..." style="width: 100%; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid #cbd5e1; font-size: 0.85rem;">{{ old('difficultes_rencontrees', "pas de difficulté") }}</textarea>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.25rem; margin-bottom: 1.5rem;">
            
            {{-- Matériel utilisé --}}
            <div>
                <label style="display: block; font-size: 0.85rem; color: #475569; margin-bottom: 0.4rem; font-weight: 700;">
                    Matériel utilisé
                </label>
                <textarea name="materiel_utilise" rows="2" placeholder="Ex: caisse à outils, escabeau, manomètre..." style="width: 100%; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid #cbd5e1; font-size: 0.85rem;">{{ old('materiel_utilise', "caisse à outils, escabeau") }}</textarea>
            </div>

            {{-- Travaux non terminés / à reprendre --}}
            <div>
                <label style="display: block; font-size: 0.85rem; color: #475569; margin-bottom: 0.4rem; font-weight: 700;">
                    Travaux non terminés / à reprendre
                </label>
                <textarea name="travaux_non_termines" rows="2" placeholder="Ex: ras ou 5 unités à finir demain..." style="width: 100%; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid #cbd5e1; font-size: 0.85rem;">{{ old('travaux_non_termines', "ras") }}</textarea>
            </div>
        </div>

        {{-- Section 4 : Horaires et Responsable --}}
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.25rem; margin-bottom: 2rem;">
            
            {{-- Heure de fin --}}
            <div>
                <label style="display: block; font-size: 0.85rem; color: #475569; margin-bottom: 0.4rem; font-weight: 700;">
                    Heure de fin *
                </label>
                <input type="text" name="heure_fin" value="{{ old('heure_fin', '17h30') }}" placeholder="Ex: 17h30" required style="width: 100%; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid #cbd5e1; font-size: 0.9rem;">
            </div>

            {{-- Responsable d'équipe --}}
            <div>
                <label style="display: block; font-size: 0.85rem; color: #475569; margin-bottom: 0.4rem; font-weight: 700;">
                    Responsable d'équipe *
                </label>
                <input type="text" name="nom_responsable" value="{{ old('nom_responsable', $responsableDefault) }}" placeholder="Ex: Mr N'guessan" required style="width: 100%; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid #cbd5e1; font-weight: 700; font-size: 0.9rem;">
            </div>
        </div>

        {{-- Submit Buttons --}}
        <div style="display: flex; justify-content: flex-end; gap: 1rem; padding-top: 1rem; border-top: 1px solid #e2e8f0;">
            <a href="{{ route('maintenances.show', $maintenance->id) }}" class="btn-secondary" style="padding: 0.75rem 1.5rem; text-decoration: none; border-radius: 0.5rem; background: #e2e8f0; color: #475569; font-weight: 700;">
                Annuler
            </a>
            <button type="submit" class="btn-primary" style="padding: 0.75rem 2rem; background: linear-gradient(135deg, #059669 0%, #10b981 100%); border: none; border-radius: 0.5rem; color: white; font-weight: 700; cursor: pointer; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);">
                <i class="fa-solid fa-paper-plane"></i> Soumettre le Compte Rendu
            </button>
        </div>
    </form>
</div>
@endsection
