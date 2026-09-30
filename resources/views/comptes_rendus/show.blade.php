@extends('layouts.app')

@section('title', 'Compte Rendu Journalier')

@section('content')
<div class="header">
    <div>
        <a href="{{ route('maintenances.show', $rapport->maintenance_id) }}" style="color: #64748b; text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.4rem; margin-bottom: 0.5rem;">
            <i class="fa-solid fa-arrow-left"></i> Retour à la Maintenance {{ $rapport->maintenance->numero_maintenance ?? '' }}
        </a>
        <h1><i class="fa-solid fa-clipboard-list"></i> Compte Rendu Journalier</h1>
        <p style="color: #64748b; font-size: 0.9rem;">
            Rapport d'activité du {{ \Carbon\Carbon::parse($rapport->date_rapport)->format('d/m/Y') }}
        </p>
    </div>
    <div>
        <button onclick="window.print()" class="btn-primary" style="background: linear-gradient(135deg, #059669, #10b981);">
            <i class="fa-solid fa-print"></i> Imprimer
        </button>
    </div>
</div>

<div class="card" style="max-width: 900px; margin: 0 auto;">
    
    {{-- En-tête du Rapport --}}
    <div style="background: linear-gradient(135deg, #059669 0%, #10b981 100%); color: white; padding: 1.5rem; border-radius: 0.75rem; margin-bottom: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <div style="display: grid; grid-template-columns: 1fr auto; gap: 1rem; align-items: center;">
            <div>
                <div style="font-size: 0.75rem; font-weight: 700; opacity: 0.9; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">
                    Compte Rendu Journalier – Équipe
                </div>
                <h2 style="font-size: 1.35rem; font-weight: 800; margin: 0; line-height: 1.3;">
                    {{ $rapport->site->nom_site ?? ($rapport->maintenance->site->nom_site ?? 'N/A') }}
                </h2>
                <p style="font-size: 0.85rem; opacity: 0.95; margin-top: 0.3rem; margin-bottom: 0;">
                    {{ $rapport->maintenance->numero_maintenance ?? '' }} · {{ $rapport->maintenance->client->nom ?? '' }}
                </p>
            </div>
            <div style="text-align: right;">
                <div style="font-size: 1rem; font-weight: 800; background: rgba(255,255,255,0.25); padding: 0.6rem 1.1rem; border-radius: 0.5rem; border: 1px solid rgba(255,255,255,0.3); box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    {{ \Carbon\Carbon::parse($rapport->date_rapport)->format('d/m/Y') }}
                </div>
                <div style="font-size: 0.75rem; opacity: 0.9; margin-top: 0.5rem;">
                    Fin : {{ $rapport->heure_fin ?? 'N/A' }}
                </div>
            </div>
        </div>
    </div>

    {{-- Section Équipe & Avancement --}}
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
        
        {{-- Équipe --}}
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 1.25rem;">
            <div style="margin-bottom: 1rem;">
                <div style="font-size: 0.7rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.4rem;">
                    Équipe (Intervenants)
                </div>
                <div style="font-size: 0.95rem; font-weight: 700; color: #0f172a;">
                    {{ $rapport->noms_intervenants ?? 'N/A' }}
                </div>
            </div>
            <div style="padding-top: 1rem; border-top: 1px solid #e2e8f0;">
                <div style="font-size: 0.7rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.4rem;">
                    Responsable d'équipe
                </div>
                <div style="font-size: 0.95rem; font-weight: 700; color: #059669;">
                    {{ $rapport->nom_responsable ?? 'N/A' }}
                </div>
            </div>
        </div>

        {{-- Avancement --}}
        <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 0.75rem; padding: 1.25rem;">
            <div style="margin-bottom: 1rem;">
                <div style="font-size: 0.7rem; font-weight: 700; color: #047857; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.4rem;">
                    Équipements traités ce jour
                </div>
                <div style="font-size: 2rem; font-weight: 800; color: #065f46;">
                    {{ $rapport->nombre_equipements_traites }}
                </div>
            </div>
            <div style="padding-top: 1rem; border-top: 1px solid #a7f3d0;">
                <div style="font-size: 0.7rem; font-weight: 700; color: #047857; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.4rem;">
                    Avancement Global Maintenance
                </div>
                <div style="font-size: 1.1rem; font-weight: 800; color: #059669;">
                    {{ $rapport->maintenance->nombre_equipements_traites ?? 0 }} / {{ $rapport->maintenance->nombre_equipements_prevus ?? 0 }} éq. <span style="color: #10b981;">({{ $rapport->maintenance->pourcentage_avancement ?? 0 }}%)</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Détails des Activités --}}
    <div style="display: flex; flex-direction: column; gap: 1rem;">
        
        {{-- Activités réalisées --}}
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 1.25rem;">
            <div style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.6rem;">
                Activités réalisées
            </div>
            <div style="font-size: 0.9rem; color: #1e293b; line-height: 1.6; white-space: pre-wrap;">{{ $rapport->activites_realisees }}</div>
        </div>

        {{-- Anomalies / pannes constatées --}}
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 1.25rem;">
            <div style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.6rem;">
                Anomalies / Pannes constatées
            </div>
            <div style="font-size: 0.9rem; color: #1e293b; line-height: 1.6; white-space: pre-wrap;">{{ $rapport->anomalies_constatees ?? "Aucune anomalie" }}</div>
        </div>

        {{-- Difficultés rencontrées --}}
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 1.25rem;">
            <div style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.6rem;">
                Difficultés rencontrées
            </div>
            <div style="font-size: 0.9rem; color: #1e293b; line-height: 1.6; white-space: pre-wrap;">{{ $rapport->difficultes_rencontrees ?? "Aucune difficulté" }}</div>
        </div>

        {{-- Matériel utilisé --}}
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 1.25rem;">
            <div style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.6rem;">
                Matériel utilisé
            </div>
            <div style="font-size: 0.9rem; color: #1e293b; line-height: 1.6; white-space: pre-wrap;">{{ $rapport->materiel_utilise ?? "Caisse à outils, escabeau" }}</div>
        </div>

        {{-- Travaux non terminés / à reprendre --}}
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 1.25rem;">
            <div style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.6rem;">
                Travaux non terminés / À reprendre
            </div>
            <div style="font-size: 0.9rem; color: #1e293b; line-height: 1.6; white-space: pre-wrap;">{{ $rapport->travaux_non_termines ?? "RAS" }}</div>
        </div>
    </div>

    {{-- Footer signature info --}}
    <div style="margin-top: 2rem; padding-top: 1.25rem; border-top: 2px solid #e2e8f0; text-align: center; font-size: 0.8rem; color: #64748b;">
        <div style="background: #f8fafc; padding: 0.85rem; border-radius: 0.5rem; display: inline-block;">
            Saisi par <strong style="color: #059669;">{{ $rapport->createdBy->nom_complet ?? 'N/A' }}</strong> le <strong>{{ $rapport->created_at->format('d/m/Y à H:i') }}</strong>
        </div>
    </div>
</div>
@endsection
