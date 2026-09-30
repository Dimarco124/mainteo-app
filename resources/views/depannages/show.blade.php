@extends('layouts.app')

@section('title', 'Fiche Intervention #' . $depannage->id)

@section('content')
<style>
    .ticket-wrapper {
        max-width: 1380px;
        margin: 0 auto;
    }

    .ticket-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.85fr) minmax(0, 1.15fr);
        gap: 1.5rem;
        align-items: start;
    }

    .ticket-card {
        background: #ffffff;
        border-radius: 0.85rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 4px rgba(15, 23, 42, 0.03);
        margin-bottom: 1.5rem;
        overflow: hidden;
    }

    .ticket-card-header {
        padding: 1rem 1.25rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid #f1f5f9;
        background: #ffffff;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .ticket-card-body {
        padding: 1.25rem;
    }

    .pill-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.4rem 0.8rem;
        border-radius: 9999px;
        font-size: 0.82rem;
        font-weight: 700;
        letter-spacing: 0.01em;
        line-height: 1.2;
    }

    .meta-row {
        display: flex;
        align-items: flex-start;
        gap: 0.85rem;
        padding: 0.85rem 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .meta-row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .meta-icon {
        width: 36px;
        height: 36px;
        border-radius: 0.6rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.95rem;
        flex-shrink: 0;
    }

    .meta-content {
        flex: 1;
        min-width: 0;
    }

    .meta-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
        font-weight: 700;
        margin-bottom: 0.2rem;
    }

    .meta-value {
        font-size: 0.92rem;
        font-weight: 700;
        color: #0f172a;
        word-break: break-word;
    }

    .desc-box {
        font-size: 0.92rem;
        line-height: 1.65;
        color: #334155;
        background: #f8fafc;
        padding: 1.1rem 1.25rem;
        border-radius: 0.75rem;
        border: 1px solid #e2e8f0;
        white-space: pre-wrap;
        word-break: break-word;
        max-height: 350px;
        overflow-y: auto;
    }

    @media (max-width: 900px) {
        .ticket-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

@php
    $userAuth = Auth::user();
    $roleAuth = $userAuth->type_utilisateur ?? '';
    // STRICTEMENT réservé à Admin et Superviseur Soutarah
    $peutEditerRi = in_array($roleAuth, ['admin', 'superviseur_soutarah']);

    // Statut terrain styles
    $statutStyles = [
        'en cours' => ['bg' => '#eff6ff', 'color' => '#1d4ed8', 'border' => '#bfdbfe', 'icon' => 'fa-spinner fa-spin', 'label' => 'En cours'],
        'en attente' => ['bg' => '#fffbeb', 'color' => '#b45309', 'border' => '#fde68a', 'icon' => 'fa-hourglass-half', 'label' => 'En attente'],
        'resolu' => ['bg' => '#ecfdf5', 'color' => '#047857', 'border' => '#a7f3d0', 'icon' => 'fa-circle-check', 'label' => 'Résolu'],
        'résolu' => ['bg' => '#ecfdf5', 'color' => '#047857', 'border' => '#a7f3d0', 'icon' => 'fa-circle-check', 'label' => 'Résolu'],
        'en_attente_piece' => ['bg' => '#f0f9ff', 'color' => '#0369a1', 'border' => '#bae6fd', 'icon' => 'fa-boxes-stacked', 'label' => 'En attente de pièce'],
        'partiellement_resolu' => ['bg' => '#fffbeb', 'color' => '#b45309', 'border' => '#fde68a', 'icon' => 'fa-triangle-exclamation', 'label' => 'Partiellement résolu'],
        'non_resolu' => ['bg' => '#fef2f2', 'color' => '#b91c1c', 'border' => '#fca5a5', 'icon' => 'fa-ban', 'label' => 'Non résolu / Bloqué'],
    ];
    $currentStatus = $statutStyles[$depannage->statut] ?? ['bg' => '#f8fafc', 'color' => '#475569', 'border' => '#cbd5e1', 'icon' => 'fa-circle-info', 'label' => ucfirst($depannage->statut)];
@endphp

<div class="ticket-wrapper">

    {{-- ════════════════════════════════════════════════════════════════════
         EN-TÊTE PRINCIPAL : BREADCRUMB, TITRE, BADGES & RI SOUTARAH
         ════════════════════════════════════════════════════════════════════ --}}
    <div style="margin-bottom: 1.5rem;">
        <div style="margin-bottom: 0.6rem;">
            @if(Auth::user()->isTechnicien())
            <a href="{{ route('technicien.interventions') }}" style="color: #64748b; text-decoration: none; font-size: 0.88rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.45rem;">
                <i class="fa-solid fa-arrow-left"></i> Mes Interventions
            </a>
            @else
            <a href="{{ route('demandes.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.88rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.45rem;">
                <i class="fa-solid fa-arrow-left"></i> Toutes les Demandes
            </a>
            @endif
        </div>

        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap;">
            <div>
                <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 0.4rem;">
                    <span style="background: #0f172a; color: #fff; font-size: 0.82rem; font-weight: 800; padding: 0.35rem 0.75rem; border-radius: 0.5rem; letter-spacing: 0.03em;">
                        Ticket #{{ $depannage->id }}
                    </span>
                    <h1 style="font-size: 1.45rem; font-weight: 800; color: #0f172a; margin: 0; display: inline-flex; align-items: center; gap: 0.5rem;">
                        {{ $depannage->equipement_reference ?? 'Intervention' }}
                    </h1>
                </div>

                {{-- Rangée des Badges (Type, Urgence, Statut, Approuvé, RI) --}}
                <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; margin-top: 0.5rem;">
                    {{-- Type Intervention --}}
                    @if($depannage->type_intervention === 'Dépannage')
                    <span class="pill-badge" style="background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5;">
                        <i class="fa-solid fa-wrench"></i> Dépannage
                    </span>
                    @elseif($depannage->type_intervention === 'Maintenance')
                    <span class="pill-badge" style="background: #fef3c7; color: #b45309; border: 1px solid #fde68a;">
                        <i class="fa-solid fa-screwdriver-wrench"></i> Maintenance
                    </span>
                    @elseif($depannage->type_intervention === 'Installation')
                    <span class="pill-badge" style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;">
                        <i class="fa-solid fa-gears"></i> Installation
                    </span>
                    @endif

                    {{-- Urgence --}}
                    @if(($depannage->urgence ?? '') === 'Urgent')
                    <span class="pill-badge" style="background: #fef2f2; color: #dc2626; border: 1px solid #fecaca;">
                        <i class="fa-solid fa-fire"></i> Urgent
                    </span>
                    @elseif(($depannage->urgence ?? '') === 'Faible')
                    <span class="pill-badge" style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;">
                        <i class="fa-solid fa-info-circle"></i> Faible
                    </span>
                    @else
                    <span class="pill-badge" style="background: #fffbeb; color: #d97706; border: 1px solid #fde68a;">
                        <i class="fa-solid fa-circle-exclamation"></i> Normal
                    </span>
                    @endif

                    {{-- Statut global --}}
                    <span class="pill-badge" style="background: {{ $currentStatus['bg'] }}; color: {{ $currentStatus['color'] }}; border: 1px solid {{ $currentStatus['border'] }};">
                        <i class="fa-solid {{ $currentStatus['icon'] }}"></i> {{ $currentStatus['label'] }}
                    </span>

                    {{-- Badges d'approbation --}}
                    @if($depannage->statut_validation_admin === 'approuvé')
                    <span class="pill-badge" style="background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd;">
                        <i class="fa-solid fa-shield-check"></i> Validé Admin
                    </span>
                    @endif

                    @if($depannage->statut_validation_superviseur === 'approuvé')
                    <span class="pill-badge" style="background: #fef3c7; color: #d97706; border: 1px solid #fde68a;">
                        <i class="fa-solid fa-shield-check"></i> Validé Superviseur
                    </span>
                    @endif

                    {{-- BADGE RI SOUTARAH : Pur & Pro --}}
                    @if(!empty($depannage->ri_soutarah))
                    <span class="pill-badge" style="background: #eff6ff; color: #1d4ed8; border: 1.5px solid #93c5fd; padding: 0.4rem 0.85rem;">
                        <i class="fa-solid fa-barcode"></i> RI&nbsp;Soutarah&nbsp;:&nbsp;<strong style="letter-spacing: 0.02em;">{{ $depannage->ri_soutarah }}</strong>
                    </span>
                    @if($peutEditerRi)
                    <button type="button" onclick="document.getElementById('editRiBlock').style.display='block';document.getElementById('editRiInput').focus();"
                        title="Modifier le RI Soutarah"
                        style="background: #ffffff; color: #2563eb; border: 1.5px solid #93c5fd; padding: 0.35rem 0.65rem; border-radius: 9999px; font-weight: 700; font-size: 0.78rem; cursor: pointer; display: inline-flex; align-items: center; gap: 0.3rem;">
                        <i class="fa-solid fa-pen"></i> Modifier
                    </button>
                    @endif
                    @else
                    <span class="pill-badge" style="background: #fff7ed; color: #c2410c; border: 1.5px dashed #fdba74; padding: 0.4rem 0.85rem;">
                        <i class="fa-solid fa-circle-exclamation"></i> RI Soutarah :&nbsp;
                        @if($peutEditerRi)
                        <span>Non renseigné</span>
                        @else
                        <span>En attente d'attribution</span>
                        @endif
                    </span>
                    @if($peutEditerRi)
                    <button type="button" onclick="document.getElementById('editRiBlock').style.display='block';document.getElementById('editRiInput').focus();"
                        style="background: #d97706; color: #ffffff; border: none; padding: 0.38rem 0.8rem; border-radius: 9999px; font-weight: 700; font-size: 0.78rem; cursor: pointer; display: inline-flex; align-items: center; gap: 0.35rem;">
                        <i class="fa-solid fa-plus"></i> Définir le RI
                    </button>
                    @endif
                    @endif
                </div>

                {{-- Bloc inline de modification RI Soutarah (Admin / Superviseur Soutarah ONLY) --}}
                @if($peutEditerRi)
                <div id="editRiBlock" style="display: none; margin-top: 0.85rem; padding: 1rem; background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 0.75rem; max-width: 520px; box-shadow: 0 4px 12px rgba(0,0,0,0.06);">
                    <div style="font-size: 0.82rem; font-weight: 800; color: #0f172a; margin-bottom: 0.5rem; display: flex; align-items: center; justify-content: space-between;">
                        <span><i class="fa-solid fa-barcode" style="color: #2563eb;"></i> Définir / Modifier le RI Soutarah</span>
                        <button type="button" onclick="document.getElementById('editRiBlock').style.display='none';" style="border: none; background: transparent; color: #64748b; cursor: pointer; font-size: 1rem;">&times;</button>
                    </div>
                    <form action="{{ route('depannages.updateRiSoutarah', $depannage->id) }}" method="POST" style="margin: 0; display: flex; gap: 0.5rem; flex-wrap: wrap;">
                        @csrf
                        @method('PATCH')
                        <input type="text" name="ri_soutarah" id="editRiInput"
                            value="{{ old('ri_soutarah', $depannage->ri_soutarah ?? $depannage->demande?->numero_reference_externe) }}"
                            maxlength="50" required
                            placeholder="Ex: RI-2026-001"
                            style="flex: 1 1 240px; min-width: 0; padding: 0.6rem 0.8rem; border: 1.5px solid #3b82f6; border-radius: 0.5rem; font-size: 0.9rem; font-weight: 700; color: #1e3a8a; background: #fff;">
                        <button type="submit" style="background: #059669; color: #fff; border: none; padding: 0.6rem 1rem; border-radius: 0.5rem; font-weight: 700; font-size: 0.85rem; cursor: pointer; display: inline-flex; align-items: center; gap: 0.35rem;">
                            <i class="fa-solid fa-check"></i> Enregistrer
                        </button>
                        <button type="button" onclick="document.getElementById('editRiBlock').style.display='none';"
                            style="background: #e2e8f0; color: #475569; border: none; padding: 0.6rem 0.8rem; border-radius: 0.5rem; font-weight: 600; font-size: 0.85rem; cursor: pointer;">
                            Annuler
                        </button>
                    </form>
                    <small style="display: block; margin-top: 0.4rem; color: #64748b; font-size: 0.75rem;">
                        Ce numéro identifie l'intervention pour Soutarah et les rapports clients.
                    </small>
                </div>
                @endif
            </div>

            {{-- Statut clôture final / Bouton rapport rapide --}}
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                @if(in_array($depannage->statut, ['resolu','résolu']) && $depannage->statut_validation_finale_client === 'validé')
                <div style="background: #ecfdf5; border: 1.5px solid #10b981; color: #047857; padding: 0.55rem 1rem; border-radius: 0.65rem; font-size: 0.88rem; font-weight: 800; display: inline-flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-circle-check"></i> Résolu & Clôturé
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════════
         GRILLE PRINCIPALE (COLONNE GAUCHE: CONTENU | COLONNE DROITE: SIDEBAR)
         ════════════════════════════════════════════════════════════════════ --}}
    <div class="ticket-grid">

        {{-- ── COLONNE PRINCIPALE (GAUCHE) ────────────────────────────────── --}}
        <div>

            {{-- 1. CONSTAT & DESCRIPTION DU PROBLÈME --}}
            <div class="ticket-card">
                <div class="ticket-card-header">
                    <div style="display: flex; align-items: center; gap: 0.5rem; font-weight: 800; font-size: 0.95rem; color: #0f172a;">
                        <i class="fa-solid fa-file-lines" style="color: #059669;"></i> Constat & Description du Problème
                    </div>
                    @if($depannage->date_demande)
                    <span style="font-size: 0.78rem; color: #64748b; font-weight: 600;">
                        Signalé le {{ \Carbon\Carbon::parse($depannage->date_demande)->format('d/m/Y') }}
                    </span>
                    @endif
                </div>
                <div class="ticket-card-body">
                    <div class="desc-box">
                        {{ $depannage->description_panne }}
                    </div>

                    {{-- Dates convenue / prévues si renseignées --}}
                    @if($depannage->date_debut_prevue || $depannage->date_fin_prevue || $depannage->date_prevue)
                    <div style="margin-top: 1rem; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 0.65rem; padding: 0.75rem 1rem; display: flex; align-items: center; gap: 0.65rem; font-size: 0.85rem; color: #166534; font-weight: 700;">
                        <i class="fa-solid fa-calendar-check" style="font-size: 1rem; color: #10b981;"></i>
                        <span>
                            @php
                                $dDeb = $depannage->date_debut_prevue ?? $depannage->date_prevue;
                                $dFin = $depannage->date_fin_prevue ?? $depannage->date_prevue;
                            @endphp
                            @if($dDeb && $dFin && $dDeb !== $dFin)
                                Période convenue : Du {{ \Carbon\Carbon::parse($dDeb)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($dFin)->format('d/m/Y') }}
                            @elseif($dDeb)
                                Passage prévu : {{ \Carbon\Carbon::parse($dDeb)->format('d/m/Y') }}
                            @endif
                        </span>
                    </div>
                    @endif

                    {{-- Pièces jointes (Photos & Documents) --}}
                    @if($depannage->photo_panne || $depannage->fichier_joint)
                    <div style="margin-top: 1.25rem; pt-3; border-top: 1px solid #f1f5f9; padding-top: 1rem;">
                        <div style="font-size: 0.8rem; font-weight: 800; text-transform: uppercase; color: #64748b; letter-spacing: 0.04em; margin-bottom: 0.75rem;">
                            <i class="fa-solid fa-paperclip"></i> Pièces Jointes Initiales
                        </div>
                        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                            @if($depannage->photo_panne)
                            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 0.65rem; padding: 0.65rem; max-width: 260px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                                <a href="{{ asset($depannage->photo_panne) }}" target="_blank" style="display: block; overflow: hidden; border-radius: 0.45rem;">
                                    <img src="{{ asset($depannage->photo_panne) }}" alt="Photo panne" style="width: 100%; height: 150px; object-fit: cover; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.03)'" onmouseout="this.style.transform='scale(1)'">
                                </a>
                                <a href="{{ asset($depannage->photo_panne) }}" target="_blank" style="display: block; text-align: center; margin-top: 0.5rem; font-size: 0.78rem; color: #059669; font-weight: 700; text-decoration: none;">
                                    <i class="fa-solid fa-magnifying-glass-plus"></i> Agrandir la Photo
                                </a>
                            </div>
                            @endif

                            @if($depannage->fichier_joint)
                            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 0.65rem; padding: 0.85rem 1rem; display: flex; align-items: center; gap: 0.85rem; max-width: 320px;">
                                <div style="width: 40px; height: 40px; background: #ecfdf5; color: #059669; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                                    <i class="fa-solid fa-file-lines"></i>
                                </div>
                                <div style="flex: 1; min-width: 0;">
                                    <div style="font-size: 0.82rem; font-weight: 700; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Document Joint</div>
                                    <a href="{{ asset($depannage->fichier_joint) }}" target="_blank" style="display: inline-flex; align-items: center; gap: 0.35rem; color: #059669; font-size: 0.78rem; font-weight: 700; text-decoration: none; margin-top: 0.2rem;">
                                        <i class="fa-solid fa-download"></i> Télécharger
                                    </a>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- 2. ESPACE TECHNICIEN : PRISE EN CHARGE & RAPPORT D'INTERVENTION --}}
            @if($peutSoumettreCR)

                {{-- Action A: Confirmer la Réception (si pas encore confirmée) --}}
                @if($depannage->confirmation_reception !== 'confirmé' && in_array($depannage->statut, ['en cours', 'approuvé - en attente assignation']))
                <div class="ticket-card" style="border: 1.5px solid #60a5fa; background: linear-gradient(135deg, #f0f7ff, #ffffff);">
                    <div class="ticket-card-header" style="background: transparent; border-bottom: 1px solid #dbeafe;">
                        <div style="display: flex; align-items: center; gap: 0.5rem; font-weight: 800; font-size: 0.95rem; color: #1e40af;">
                            <i class="fa-solid fa-hand-holding-hand" style="color: #2563eb;"></i> 1. Prise en Charge de l'Intervention
                        </div>
                        <span style="font-size: 0.78rem; background: #dbeafe; color: #1e40af; padding: 0.25rem 0.55rem; border-radius: 9999px; font-weight: 700;">Action Requise</span>
                    </div>
                    <div class="ticket-card-body">
                        <p style="font-size: 0.88rem; color: #1e3a8a; margin: 0 0 1rem 0; line-height: 1.6;">
                            Veuillez confirmer que vous avez bien pris connaissance de cette intervention et que vous vous rendez sur le terrain.
                        </p>
                        <form action="{{ route('depannages.confirmReception', $depannage->id) }}" method="POST">
                            @csrf
                            <button type="submit" style="width: 100%; padding: 0.85rem 1.25rem; background: #059669; color: #ffffff; border: none; border-radius: 0.65rem; font-weight: 800; font-size: 0.95rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.5rem; box-shadow: 0 2px 6px rgba(5, 150, 105, 0.25); transition: background 0.2s;" onmouseover="this.style.background='#047857'" onmouseout="this.style.background='#059669'">
                                <i class="fa-solid fa-circle-check"></i> Confirmer la Réception
                            </button>
                        </form>
                    </div>
                </div>
                @elseif($depannage->confirmation_reception === 'confirmé')
                <div style="background: #f0fdf4; border: 1px solid #86efac; border-radius: 0.65rem; padding: 0.75rem 1rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.65rem; font-size: 0.85rem; color: #15803d; font-weight: 700;">
                    <i class="fa-solid fa-circle-check" style="font-size: 1.1rem; color: #10b981;"></i>
                    <span>Prise en charge confirmée le {{ \Carbon\Carbon::parse($depannage->date_confirmation_reception)->format('d/m/Y à H:i') }}</span>
                </div>
                @endif

                {{-- Action B: Rapport d'Intervention / Formulaire --}}
                @if($depannage->statut_rapport_technicien === 'rejete')
                <div class="ticket-card" style="border: 2px solid #f43f5e; background: #fff1f2;">
                    <div class="ticket-card-header" style="background: #ffe4e6; border-bottom: 1px solid #fecdd3;">
                        <div style="font-weight: 800; color: #be123c; font-size: 0.95rem; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fa-solid fa-triangle-exclamation"></i> Rapport Rejeté — Correction Requise
                        </div>
                    </div>
                    <div class="ticket-card-body">
                        <p style="color: #9f1239; font-size: 0.85rem; font-weight: 700; margin-bottom: 0.4rem;">Motif du rejet :</p>
                        <div style="background: #ffffff; padding: 0.85rem 1rem; border-radius: 0.5rem; border: 1px solid #fecdd3; color: #be123c; font-size: 0.88rem; font-weight: 600; margin-bottom: 1rem; line-height: 1.5;">
                            {{ $depannage->raison_rejet_rapport ?? 'Le compte-rendu ou les justificatifs doivent être complétés.' }}
                        </div>
                        <a href="{{ route('depannages.rapportForm', $depannage->id) }}" class="btn-primary" style="width: 100%; justify-content: center; background: #be123c; padding: 0.85rem 1.25rem; font-size: 0.95rem; text-decoration: none;">
                            <i class="fa-solid fa-pen-to-square"></i> Corriger &amp; Renvoyer le Rapport
                        </a>
                    </div>
                </div>
                @elseif(!in_array($depannage->statut, ['resolu', 'résolu']))
                <div class="ticket-card" style="border: 1.5px solid #93c5fd; background: linear-gradient(135deg, #f8fafc, #eff6ff);">
                    <div class="ticket-card-header" style="background: transparent; border-bottom: 1px solid #e2e8f0;">
                        <div style="font-weight: 800; font-size: 0.95rem; color: #1e3a8a; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fa-solid fa-clipboard-check" style="color: #2563eb;"></i> 2. Rapport d'Intervention Terrain
                        </div>
                        @if($depannage->statut_rapport_technicien === 'soumis')
                        <span style="font-size: 0.78rem; background: #e0e7ff; color: #3730a3; padding: 0.25rem 0.55rem; border-radius: 9999px; font-weight: 700;">Rapport Intermédiaire Soumis</span>
                        @endif
                    </div>
                    <div class="ticket-card-body">
                        @if($depannage->statut_rapport_technicien === 'soumis')
                        <p style="font-size: 0.85rem; color: #1e3a8a; margin-bottom: 1rem; line-height: 1.6;">
                            Un compte-rendu intermédiaire est déjà enregistré. Lors d'un passage suivant ou après pose des pièces, finalisez et clôturez l'intervention.
                        </p>
                        <a href="{{ route('depannages.rapportForm', $depannage->id) }}" class="btn-primary" style="width: 100%; justify-content: center; background: #2563eb; padding: 0.85rem 1.25rem; font-size: 0.95rem; text-decoration: none; display: inline-flex; gap: 0.5rem;">
                            <i class="fa-solid fa-rotate"></i> 2ème Passage / Modifier le Rapport & Clôturer
                        </a>
                        @else
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.75rem; margin-bottom: 1rem; font-size: 0.82rem; color: #475569;">
                            <div style="background: #ffffff; padding: 0.65rem 0.85rem; border-radius: 0.5rem; border: 1px solid #e2e8f0;">
                                <i class="fa-solid fa-circle-check" style="color: #10b981;"></i> Statut terrain (Résolu, Pièce, etc.)
                            </div>
                            <div style="background: #ffffff; padding: 0.65rem 0.85rem; border-radius: 0.5rem; border: 1px solid #e2e8f0;">
                                <i class="fa-solid fa-circle-check" style="color: #10b981;"></i> Compte-rendu détaillé des travaux
                            </div>
                            <div style="background: #ffffff; padding: 0.65rem 0.85rem; border-radius: 0.5rem; border: 1px solid #e2e8f0;">
                                <i class="fa-solid fa-circle-check" style="color: #10b981;"></i> Photos d'intervention justificatives
                            </div>
                        </div>
                        <a href="{{ route('depannages.rapportForm', $depannage->id) }}" class="btn-primary" style="width: 100%; justify-content: center; background: #059669; padding: 0.85rem 1.25rem; font-size: 0.95rem; text-decoration: none; display: inline-flex; gap: 0.5rem; box-shadow: 0 2px 6px rgba(5, 150, 105, 0.25);">
                            <i class="fa-solid fa-pen-to-square"></i> Remplir le Rapport d'Intervention
                        </a>
                        @endif
                    </div>
                </div>
                @else
                <div class="ticket-card" style="border: 1.5px solid #86efac; background: #f0fdf4;">
                    <div class="ticket-card-header" style="background: transparent; border-bottom: 1px solid #bbf7d0;">
                        <div style="font-weight: 800; color: #166534; font-size: 0.95rem; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fa-solid fa-circle-check" style="color: #10b981;"></i> Rapport Enregistré
                        </div>
                        @if($depannage->date_rapport_technicien)
                        <span style="font-size: 0.78rem; color: #15803d; font-weight: 600;">
                            Soumis le {{ \Carbon\Carbon::parse($depannage->date_rapport_technicien)->format('d/m/Y à H:i') }}
                        </span>
                        @endif
                    </div>
                    @if($depannage->rapport)
                    <div class="ticket-card-body">
                        <div style="background: #ffffff; border: 1px solid #bbf7d0; border-radius: 0.5rem; padding: 1rem; color: #1f2937; font-size: 0.9rem; line-height: 1.6;">
                            {{ $depannage->rapport }}
                        </div>
                    </div>
                    @endif
                </div>
                @endif

            @elseif(in_array(Auth::user()->type_utilisateur, ['admin', 'superviseur']))
                {{-- Vue Lecture seule pour Admin / Superviseur --}}
                @if($depannage->rapport)
                <div class="ticket-card">
                    <div class="ticket-card-header">
                        <div style="font-weight: 800; font-size: 0.95rem; color: #0f172a; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fa-solid fa-file-signature" style="color: #059669;"></i> Compte-Rendu du Technicien
                        </div>
                        <span style="font-size: 0.75rem; color: #64748b; background: #f1f5f9; padding: 0.25rem 0.55rem; border-radius: 0.4rem; font-weight: 600;">Lecture seule</span>
                    </div>
                    <div class="ticket-card-body">
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.65rem; padding: 1.1rem; color: #0f172a; font-size: 0.92rem; line-height: 1.65; white-space: pre-wrap;">{{ $depannage->rapport }}</div>
                    </div>
                </div>
                @endif
            @endif

            {{-- 3. RAPPORT FINAL AVEC PHOTOS & WORKFLOW DE CLÔTURE --}}
            @include('depannages._rapport_final_technicien')

        </div>

        {{-- ── COLONNE LATÉRALE (DROITE) ─────────────────────────────────── --}}
        <div>

            {{-- ═══════════════════════════════════════════════════════════════
                 VALIDATIONS WORKFLOW (SI ENCORE NÉCESSAIRES)
                 ═══════════════════════════════════════════════════════════════ --}}

            {{-- Étape 1 : Validation Superviseur --}}
            @if(Auth::user()->type_utilisateur === 'superviseur' && $depannage->created_by_role === 'admin' && $depannage->statut_validation_superviseur === 'en attente')
            <div class="ticket-card" style="border: 2px solid #f59e0b;">
                <div class="ticket-card-header" style="background: #fffbeb;">
                    <h3 style="font-size: 0.92rem; font-weight: 800; color: #b45309; margin: 0;">
                        <i class="fa-solid fa-clipboard-check"></i> Validation Superviseur Requise
                    </h3>
                </div>
                <div class="ticket-card-body">
                    <p style="font-size: 0.85rem; color: #92400e; margin-bottom: 0.85rem;">
                        Veuillez approuver ou rejeter cette intervention planifiée par l'administration.
                    </p>
                    <form action="{{ route('depannages.approveSuperviseur', $depannage->id) }}" method="POST" style="margin-bottom: 0.6rem;">
                        @csrf
                        <textarea name="message" rows="2" placeholder="Message optionnel..." style="width: 100%; padding: 0.65rem; font-size: 0.85rem; border: 1.5px solid #fde68a; border-radius: 0.5rem; margin-bottom: 0.5rem;"></textarea>
                        <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; background: #059669; padding: 0.75rem; font-size: 0.9rem;">
                            <i class="fa-solid fa-check"></i> Approuver
                        </button>
                    </form>
                    <form action="{{ route('depannages.rejectSuperviseur', $depannage->id) }}" method="POST">
                        @csrf
                        <textarea name="message" rows="2" placeholder="Motif du rejet (obligatoire)..." required style="width: 100%; padding: 0.65rem; font-size: 0.85rem; border: 1.5px solid #fca5a5; border-radius: 0.5rem; margin-bottom: 0.5rem;"></textarea>
                        <button type="submit" style="width: 100%; padding: 0.75rem; background: #be123c; color: #fff; border: none; border-radius: 0.5rem; font-weight: 700; cursor: pointer; font-size: 0.88rem;">
                            <i class="fa-solid fa-times"></i> Rejeter
                        </button>
                    </form>
                </div>
            </div>
            @elseif(Auth::user()->type_utilisateur === 'admin' && $depannage->created_by_role === 'superviseur' && $depannage->statut_validation_admin === 'en attente')
            <div class="ticket-card" style="border: 2px solid #0284c7;">
                <div class="ticket-card-header" style="background: #f0f9ff;">
                    <h3 style="font-size: 0.92rem; font-weight: 800; color: #0369a1; margin: 0;">
                        <i class="fa-solid fa-clipboard-check"></i> Validation Admin Requise
                    </h3>
                </div>
                <div class="ticket-card-body">
                    <p style="font-size: 0.85rem; color: #0c4a6e; margin-bottom: 0.85rem;">
                        Demande soumise par le superviseur <strong>{{ $depannage->demandeur->nom_complet ?? 'N/A' }}</strong>.
                    </p>
                    <form action="{{ route('depannages.approveAdmin', $depannage->id) }}" method="POST" style="margin-bottom: 0.6rem;">
                        @csrf
                        <textarea name="message" rows="2" placeholder="Message de validation optionnel..." style="width: 100%; padding: 0.65rem; font-size: 0.85rem; border: 1.5px solid #bae6fd; border-radius: 0.5rem; margin-bottom: 0.5rem;"></textarea>
                        <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; background: #059669; padding: 0.75rem; font-size: 0.9rem;">
                            <i class="fa-solid fa-check"></i> Approuver
                        </button>
                    </form>
                    <form action="{{ route('depannages.rejectAdmin', $depannage->id) }}" method="POST">
                        @csrf
                        <textarea name="message" rows="2" placeholder="Motif du rejet..." required style="width: 100%; padding: 0.65rem; font-size: 0.85rem; border: 1.5px solid #fca5a5; border-radius: 0.5rem; margin-bottom: 0.5rem;"></textarea>
                        <button type="submit" style="width: 100%; padding: 0.75rem; background: #be123c; color: #fff; border: none; border-radius: 0.5rem; font-weight: 700; cursor: pointer; font-size: 0.88rem;">
                            <i class="fa-solid fa-times"></i> Rejeter
                        </button>
                    </form>
                </div>
            </div>
            @endif

            {{-- Étape 2 : Assignation Technicien (Admin) --}}
            @if(Auth::user()->type_utilisateur === 'admin' && $depannage->statut === 'approuvé - en attente assignation')
            <div class="ticket-card" style="border: 2px solid #10b981;">
                <div class="ticket-card-header" style="background: #ecfdf5;">
                    <h3 style="font-size: 0.95rem; font-weight: 800; color: #065f46; margin: 0;">
                        <i class="fa-solid fa-user-gear"></i> Affecter l'Intervention
                    </h3>
                </div>
                <div class="ticket-card-body">
                    <form action="{{ route('depannages.assign', $depannage->id) }}" method="POST">
                        @csrf
                        @method('PATCH')

                        {{-- Mode --}}
                        <div style="display: flex; margin-bottom: 0.85rem; border: 1.5px solid #10b981; border-radius: 0.5rem; overflow: hidden;">
                            <label style="flex: 1; text-align: center; cursor: pointer;">
                                <input type="radio" name="mode_affectation" value="technicien" id="modeTech" checked style="display: none;">
                                <span id="lblTech" style="display: block; padding: 0.55rem; font-size: 0.82rem; font-weight: 700; background: #059669; color: #fff;">
                                    🔧 Technicien
                                </span>
                            </label>
                            <label style="flex: 1; text-align: center; cursor: pointer;">
                                <input type="radio" name="mode_affectation" value="equipe" id="modeEquipe" style="display: none;">
                                <span id="lblEquipe" style="display: block; padding: 0.55rem; font-size: 0.82rem; font-weight: 700; background: #ecfdf5; color: #059669;">
                                    👷 Équipe
                                </span>
                            </label>
                        </div>

                        <div id="fieldTechnicien" style="margin-bottom: 0.85rem;">
                            <label style="display: block; font-size: 0.8rem; color: #065f46; font-weight: 700; margin-bottom: 0.3rem;">Technicien Individuel</label>
                            <select name="technicien_id" style="width: 100%; padding: 0.65rem; border: 1.5px solid #a7f3d0; border-radius: 0.5rem; font-size: 0.85rem;">
                                <option value="">Choisir un technicien...</option>
                                @foreach($techniciens as $t)
                                <option value="{{ $t->id }}">{{ $t->nom_complet }} ({{ $t->type_utilisateur }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="fieldEquipe" style="display:none; margin-bottom: 0.85rem;">
                            <label style="display: block; font-size: 0.8rem; color: #065f46; font-weight: 700; margin-bottom: 0.3rem;">Équipe Terrain</label>
                            <select name="equipe_id" style="width: 100%; padding: 0.65rem; border: 1.5px solid #a7f3d0; border-radius: 0.5rem; font-size: 0.85rem;">
                                <option value="">Choisir une équipe...</option>
                                @foreach($equipes as $eq)
                                <option value="{{ $eq->id }}">{{ $eq->nom_equipe }} @if($eq->chef)(Chef: {{ $eq->chef->nom_complet }})@endif</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- RI Soutarah --}}
                        <div style="margin-bottom: 0.85rem;">
                            <label style="display: block; font-size: 0.8rem; color: #065f46; font-weight: 700; margin-bottom: 0.3rem;">
                                <i class="fa-solid fa-barcode"></i> N° RI Soutarah
                            </label>
                            <input type="text" name="ri_soutarah" value="{{ old('ri_soutarah', $depannage->ri_soutarah ?? $depannage->demande?->numero_reference_externe) }}" placeholder="Ex: RI-2026-001" style="width: 100%; padding: 0.65rem; border: 1.5px solid #a7f3d0; border-radius: 0.5rem; font-size: 0.85rem; font-weight: 700; color: #0f172a;">
                        </div>

                        <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; background: #059669; padding: 0.85rem; font-size: 0.95rem;">
                            <i class="fa-solid fa-check"></i> Confirmer l'Affectation
                        </button>
                    </form>
                </div>
            </div>
            @endif

            {{-- ═══════════════════════════════════════════════════════════════
                 FICHE RÉSUMÉ DU TICKET (CLEAN & PROFESSIONNELLE)
                 ═══════════════════════════════════════════════════════════════ --}}
            <div class="ticket-card">
                <div class="ticket-card-header">
                    <div style="display: flex; align-items: center; gap: 0.5rem; font-weight: 800; font-size: 0.95rem; color: #0f172a;">
                        <i class="fa-solid fa-circle-info" style="color: #059669;"></i> Résumé de l'Intervention
                    </div>
                </div>
                <div class="ticket-card-body" style="padding-top: 0.5rem; padding-bottom: 0.5rem;">

                    {{-- Entreprise Cliente --}}
                    @if($depannage->client_nom || ($depannage->client && $depannage->client->nom))
                    <div class="meta-row">
                        <div class="meta-icon" style="background: #f0fdf4; color: #059669;">
                            <i class="fa-solid fa-building"></i>
                        </div>
                        <div class="meta-content">
                            <div class="meta-label">Entreprise Cliente</div>
                            <div class="meta-value" style="color: #059669;">{{ $depannage->client_nom ?? $depannage->client->nom }}</div>
                        </div>
                    </div>
                    @endif

                    {{-- Équipement --}}
                    <div class="meta-row">
                        <div class="meta-icon" style="background: #f1f5f9; color: #475569;">
                            <i class="fa-solid fa-gear"></i>
                        </div>
                        <div class="meta-content">
                            <div class="meta-label">Équipement Concerné</div>
                            <div class="meta-value">{{ $depannage->equipement_reference ?? 'N/A' }}</div>
                            @if($depannage->equipement && $depannage->equipement->site)
                            <div style="font-size: 0.78rem; color: #64748b; margin-top: 0.15rem;">
                                <i class="fa-solid fa-location-dot"></i> {{ $depannage->equipement->site->nom_site }}
                                @if($depannage->equipement->site->baseSite)
                                ({{ $depannage->equipement->site->baseSite->nom_base }})
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- RI Soutarah --}}
                    <div class="meta-row">
                        <div class="meta-icon" style="background: {{ !empty($depannage->ri_soutarah) ? '#eff6ff' : '#fff7ed' }}; color: {{ !empty($depannage->ri_soutarah) ? '#2563eb' : '#c2410c' }};">
                            <i class="fa-solid fa-barcode"></i>
                        </div>
                        <div class="meta-content">
                            <div class="meta-label">N° RI Soutarah</div>
                            <div class="meta-value">
                                @if(!empty($depannage->ri_soutarah))
                                <span style="color: #1d4ed8; font-family: monospace; font-size: 1rem; letter-spacing: 0.03em;">
                                    {{ $depannage->ri_soutarah }}
                                </span>
                                @else
                                <span style="color: #9a3412; font-size: 0.85rem; font-weight: 600;">
                                    @if($peutEditerRi)
                                    Non renseigné
                                    @else
                                    En attente d'attribution
                                    @endif
                                </span>
                                @endif

                                @if($peutEditerRi)
                                <button type="button" onclick="document.getElementById('editRiBlock').style.display='block';document.getElementById('editRiInput').focus();window.scrollTo({top:0,behavior:'smooth'});"
                                    style="margin-left: 0.5rem; background: #f8fafc; color: #2563eb; border: 1px solid #cbd5e1; padding: 0.2rem 0.5rem; border-radius: 0.35rem; font-size: 0.72rem; font-weight: 700; cursor: pointer;">
                                    <i class="fa-solid fa-pen"></i> Modifier
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Demandeur --}}
                    <div class="meta-row">
                        <div class="meta-icon" style="background: #f8fafc; color: #64748b;">
                            <i class="fa-solid fa-user"></i>
                        </div>
                        <div class="meta-content">
                            <div class="meta-label">Demandeur</div>
                            <div class="meta-value">
                                {{ $depannage->demandeur->nom_complet ?? $depannage->demandeur_nom ?? 'Client' }}
                            </div>
                            @if(optional($depannage->demandeur)->telephone)
                            <div style="margin-top: 0.25rem;">
                                <a href="tel:{{ $depannage->demandeur->telephone }}" style="display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.78rem; color: #059669; font-weight: 700; text-decoration: none; background: #ecfdf5; padding: 0.2rem 0.5rem; border-radius: 0.4rem; border: 1px solid #a7f3d0;">
                                    <i class="fa-solid fa-phone"></i> {{ $depannage->demandeur->telephone }}
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Affectation --}}
                    <div class="meta-row">
                        <div class="meta-icon" style="background: #eff6ff; color: #2563eb;">
                            <i class="fa-solid fa-user-check"></i>
                        </div>
                        <div class="meta-content">
                            <div class="meta-label">Affecté à</div>
                            <div class="meta-value">
                                @if($depannage->technicien)
                                <span>🔧 {{ $depannage->technicien->nom_complet }}</span>
                                <div style="font-size: 0.75rem; color: #64748b; font-weight: 600;">Technicien assigné</div>
                                @elseif($depannage->equipe)
                                <span>👷 Équipe : {{ $depannage->equipe->nom_equipe }}</span>
                                @if($depannage->equipe->chef)
                                <div style="font-size: 0.78rem; color: #64748b; font-weight: 600;">Chef : {{ $depannage->equipe->chef->nom_complet }}</div>
                                @endif
                                <div style="font-size: 0.75rem; color: #64748b;">{{ $depannage->equipe->membres->count() }} intervenant(s)</div>
                                @else
                                <span style="color: #d97706; font-size: 0.85rem;">⚠️ Non encore assigné</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Dates Clés --}}
                    <div class="meta-row">
                        <div class="meta-icon" style="background: #f8fafc; color: #64748b;">
                            <i class="fa-solid fa-calendar-days"></i>
                        </div>
                        <div class="meta-content">
                            <div class="meta-label">Dates Clés</div>
                            <div style="font-size: 0.82rem; color: #334155; line-height: 1.6;">
                                @if($depannage->date_demande)
                                <div>Signalé : <strong>{{ \Carbon\Carbon::parse($depannage->date_demande)->format('d/m/Y') }}</strong></div>
                                @endif
                                @if($depannage->date_prevue || $depannage->date_debut_prevue)
                                <div style="color: #059669;">Prévu : <strong>{{ \Carbon\Carbon::parse($depannage->date_prevue ?? $depannage->date_debut_prevue)->format('d/m/Y') }}</strong></div>
                                @endif
                                @if($depannage->date_realisation)
                                <div style="color: #166534;">Réalisé : <strong>{{ \Carbon\Carbon::parse($depannage->date_realisation)->format('d/m/Y') }}</strong></div>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════════════════
                 MODIFIER L'AFFECTATION (ADMIN UNIQUEMENT)
                 ═══════════════════════════════════════════════════════════════ --}}
            @if(Auth::user()->type_utilisateur === 'admin' && in_array($depannage->statut, ['en cours', 'résolu', 'resolu', 'en_attente_piece', 'partiellement_resolu', 'non_resolu']))
            <div class="ticket-card">
                <div class="ticket-card-header">
                    <div style="font-weight: 800; font-size: 0.92rem; color: #0f172a; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fa-solid fa-user-gear" style="color: #059669;"></i> Réassigner l'Intervention
                    </div>
                </div>
                <div class="ticket-card-body">
                    <form action="{{ route('depannages.assign', $depannage->id) }}" method="POST">
                        @csrf
                        @method('PATCH')

                        {{-- Toggle Technicien / Équipe --}}
                        <div style="display: flex; margin-bottom: 0.85rem; border: 1px solid #cbd5e1; border-radius: 0.5rem; overflow: hidden;">
                            <label style="flex: 1; text-align: center; cursor: pointer;">
                                <input type="radio" name="mode_affectation" value="technicien" id="modeTech2"
                                    {{ !$depannage->equipe_id ? 'checked' : '' }} style="display: none;">
                                <span id="lblTech2" style="display: block; padding: 0.5rem; font-size: 0.8rem; font-weight: 700; background: {{ !$depannage->equipe_id ? '#059669' : '#f8fafc' }}; color: {{ !$depannage->equipe_id ? '#fff' : '#64748b' }};">
                                    🔧 Technicien
                                </span>
                            </label>
                            <label style="flex: 1; text-align: center; cursor: pointer;">
                                <input type="radio" name="mode_affectation" value="equipe" id="modeEquipe2"
                                    {{ $depannage->equipe_id ? 'checked' : '' }} style="display: none;">
                                <span id="lblEquipe2" style="display: block; padding: 0.5rem; font-size: 0.8rem; font-weight: 700; background: {{ $depannage->equipe_id ? '#059669' : '#f8fafc' }}; color: {{ $depannage->equipe_id ? '#fff' : '#64748b' }};">
                                    👷 Équipe
                                </span>
                            </label>
                        </div>

                        {{-- Technicien --}}
                        <div id="fieldTechnicien2" style="{{ $depannage->equipe_id ? 'display:none' : '' }}; margin-bottom: 0.85rem;">
                            <label style="display: block; font-size: 0.78rem; color: #64748b; margin-bottom: 0.25rem; font-weight: 700;">Technicien</label>
                            <select name="technicien_id" style="width: 100%; padding: 0.6rem; border: 1px solid #cbd5e1; border-radius: 0.45rem; font-size: 0.85rem;">
                                <option value="">Choisir un technicien...</option>
                                @foreach($techniciens as $t)
                                <option value="{{ $t->id }}" {{ $depannage->technicien_id == $t->id ? 'selected' : '' }}>
                                    {{ $t->nom_complet }} ({{ $t->type_utilisateur }})
                                </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Équipe --}}
                        <div id="fieldEquipe2" style="{{ !$depannage->equipe_id ? 'display:none' : '' }}; margin-bottom: 0.85rem;">
                            <label style="display: block; font-size: 0.78rem; color: #64748b; margin-bottom: 0.25rem; font-weight: 700;">Équipe Terrain</label>
                            <select name="equipe_id" style="width: 100%; padding: 0.6rem; border: 1px solid #cbd5e1; border-radius: 0.45rem; font-size: 0.85rem;">
                                <option value="">Choisir une équipe...</option>
                                @foreach($equipes as $eq)
                                <option value="{{ $eq->id }}" {{ $depannage->equipe_id == $eq->id ? 'selected' : '' }}>
                                    {{ $eq->nom_equipe }} @if($eq->chef)(Chef: {{ $eq->chef->nom_complet }})@endif
                                </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- RI Soutarah --}}
                        <div style="margin-bottom: 0.85rem;">
                            <label style="display: block; font-size: 0.78rem; color: #64748b; margin-bottom: 0.25rem; font-weight: 700;">
                                <i class="fa-solid fa-barcode"></i> N° RI Soutarah
                            </label>
                            <input type="text" name="ri_soutarah" value="{{ old('ri_soutarah', $depannage->ri_soutarah ?? $depannage->demande?->numero_reference_externe) }}" placeholder="Ex: RI-2026-001" style="width: 100%; padding: 0.6rem; border: 1px solid #cbd5e1; border-radius: 0.45rem; font-size: 0.85rem; font-weight: 700; color: #0f172a;">
                        </div>

                        <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; padding: 0.75rem; font-size: 0.88rem;">
                            <i class="fa-solid fa-arrows-rotate"></i> Mettre à jour l'Affectation
                        </button>
                    </form>
                </div>
            </div>
            @endif

        </div>

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle pour formulaire d'assignation initiale
    const radTech = document.getElementById('modeTech');
    const radEq = document.getElementById('modeEquipe');
    if (radTech && radEq) {
        function updateToggle1() {
            const isTech = radTech.checked;
            const fTech = document.getElementById('fieldTechnicien');
            const fEq = document.getElementById('fieldEquipe');
            const lblT = document.getElementById('lblTech');
            const lblE = document.getElementById('lblEquipe');
            if (fTech) fTech.style.display = isTech ? 'block' : 'none';
            if (fEq) fEq.style.display = isTech ? 'none' : 'block';
            if (lblT) {
                lblT.style.background = isTech ? '#059669' : '#ecfdf5';
                lblT.style.color = isTech ? '#ffffff' : '#059669';
            }
            if (lblE) {
                lblE.style.background = !isTech ? '#059669' : '#ecfdf5';
                lblE.style.color = !isTech ? '#ffffff' : '#059669';
            }
        }
        radTech.addEventListener('change', updateToggle1);
        radEq.addEventListener('change', updateToggle1);
    }

    // Toggle pour formulaire de réassignation
    const radTech2 = document.getElementById('modeTech2');
    const radEq2 = document.getElementById('modeEquipe2');
    if (radTech2 && radEq2) {
        function updateToggle2() {
            const isTech = radTech2.checked;
            const fTech2 = document.getElementById('fieldTechnicien2');
            const fEq2 = document.getElementById('fieldEquipe2');
            const lblT2 = document.getElementById('lblTech2');
            const lblE2 = document.getElementById('lblEquipe2');
            if (fTech2) fTech2.style.display = isTech ? 'block' : 'none';
            if (fEq2) fEq2.style.display = isTech ? 'none' : 'block';
            if (lblT2) {
                lblT2.style.background = isTech ? '#059669' : '#f8fafc';
                lblT2.style.color = isTech ? '#ffffff' : '#64748b';
            }
            if (lblE2) {
                lblE2.style.background = !isTech ? '#059669' : '#f8fafc';
                lblE2.style.color = !isTech ? '#ffffff' : '#64748b';
            }
        }
        radTech2.addEventListener('change', updateToggle2);
        radEq2.addEventListener('change', updateToggle2);
    }
});
</script>
@endsection