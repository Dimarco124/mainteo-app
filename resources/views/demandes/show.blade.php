@extends('layouts.app')

@section('title', 'Détails Demande #' . $demande->numero_demande)

@section('content')
@php
$isClosed = $demande->statut === 'closed';
@endphp

@if($isClosed)
<div style="background: linear-gradient(135deg, #0f172a, #1e293b); color: #f1f5f9; padding: 1rem 1.5rem; border-radius: 0.75rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 1rem; border: 2px solid #475569;">
    <div style="background: #f59e0b; width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0;">
        <i class="fa-solid fa-lock"></i>
    </div>
    <div style="flex: 1;">
        <h4 style="margin: 0; font-size: 1rem; font-weight: 700; color: #fbbf24;">
            <i class="fa-solid fa-archive"></i> Demande Clôturée
        </h4>
        <p style="margin: 0.25rem 0 0 0; font-size: 0.85rem; color: #cbd5e1;">
            Cette demande est définitivement close. Aucune modification (affectation, validation, etc.) n'est plus autorisée.
            @if($demande->date_cloture_finale)
            Clôturée le {{ \Carbon\Carbon::parse($demande->date_cloture_finale)->format('d/m/Y à H:i') }}
            @endif
        </p>
    </div>
</div>
@endif

{{-- Alerte : Équipement en Attente de Complétion --}}
@if($demande->equipement_needs_completion && in_array(Auth::user()->type_utilisateur, ['admin', 'superviseur_soutarah']))
<div style="background: linear-gradient(135deg, #f59e0b, #d97706); color: #ffffff; padding: 1.5rem; border-radius: 1rem; margin-bottom: 1.5rem; box-shadow: 0 10px 25px -5px rgba(245, 158, 11, 0.3); border: 2px solid #fbbf24;">
    <div style="display: flex; align-items: center; justify-content: space-between; gap: 1.5rem; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 250px;">
            <h4 style="margin: 0 0 0.5rem 0; font-size: 1.1rem; font-weight: 800; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-exclamation-triangle"></i> Action Requise : Compléter l'Équipement
            </h4>
            <p style="margin: 0; font-size: 0.9rem; opacity: 0.95; line-height: 1.5;">
                Cette demande d'installation nécessite la complétion des informations techniques de l'équipement avant de pouvoir planifier l'intervention.
            </p>
            <div style="margin-top: 0.75rem; padding: 0.75rem; background: rgba(255,255,255,0.15); border-radius: 0.5rem; backdrop-filter: blur(4px);">
                <div style="font-size: 0.85rem; font-weight: 600; margin-bottom: 0.35rem;">Informations fournies par le client :</div>
                <div style="font-size: 0.85rem;">
                    <strong>Nom :</strong> {{ $demande->temp_equipement_nom }}<br>
                    <strong>Type :</strong> {{ $demande->temp_equipement_type }}<br>
                    <strong>Emplacement :</strong> {{ $demande->temp_equipement_emplacement === 'interne' ? 'Interne/Intérieur' : 'Externe/Extérieur' }}
                </div>
            </div>
        </div>
        <div style="flex-shrink: 0;">
            <a href="{{ route('equipements.completeForm', $demande->id) }}" style="display: inline-flex; align-items: center; gap: 0.75rem; background: #ffffff; color: #d97706; padding: 1rem 1.75rem; border-radius: 0.75rem; text-decoration: none; font-weight: 800; font-size: 1rem; box-shadow: 0 4px 12px rgba(0,0,0,0.15); transition: all 0.2s;">
                <i class="fa-solid fa-clipboard-check" style="font-size: 1.25rem;"></i>
                <span>Valider et Compléter les Informations</span>
            </a>
        </div>
    </div>
</div>
@endif

<div class="header">
    <div>
        <a href="{{ route('demandes.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.4rem; margin-bottom: 0.5rem;">
            <i class="fa-solid fa-arrow-left"></i> Retour
        </a>
        <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
            <div>
                <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 0.25rem;">N° Application</div>
                <h1 style="margin: 0; display: inline-block;">{{ $demande->numero_demande }}</h1>
            </div>
            <div style="padding: 0.75rem 1.25rem; background-color: #fffbeb; border: 2px solid #fbbf24; border-radius: 0.75rem;">
                <div style="font-size: 0.75rem; color: #92400e; margin-bottom: 0.25rem; font-weight: 600;">
                    <i class="fa-solid fa-barcode"></i> N° Référence Externe
                </div>
                <code style="font-size: 1.1rem; font-weight: 800; color: #92400e; font-family: 'Courier New', monospace;">
                    {{ $demande->numero_reference_externe }}
                </code>
            </div>
            @php
            $statutStyles = [
            'pending_client_validation' => ['bg' => '#fffbeb', 'color' => '#b45309', 'border' => '#fde68a', 'label' => 'Attente validation client'],
            'validated_by_client' => ['bg' => '#f0f9ff', 'color' => '#0369a1', 'border' => '#bae6fd', 'label' => 'Validée par client'],
            'rejected_by_client' => ['bg' => '#fff1f2', 'color' => '#be123c', 'border' => '#fecdd3', 'label' => 'Rejetée par client'],
            'needs_technical_operation' => ['bg' => '#eff6ff', 'color' => '#1e40af', 'border' => '#bfdbfe', 'label' => 'Opération requise'],
            'rejected_by_soutarah' => ['bg' => '#fff1f2', 'color' => '#be123c', 'border' => '#fecdd3', 'label' => 'Rejetée Soutarah'],
            'confirmed_by_demandeur' => ['bg' => '#ecfdf5', 'color' => '#047857', 'border' => '#a7f3d0', 'label' => 'Confirmée demandeur'],
            'closed' => ['bg' => '#f8fafc', 'color' => '#475569', 'border' => '#cbd5e1', 'label' => 'Clôturée']
            ];
            $style = $statutStyles[$demande->statut];
            @endphp
            <span class="badge" style="background-color: {{ $style['bg'] }}; color: {{ $style['color'] }}; border: 1px solid {{ $style['border'] }}; font-size: 0.85rem; padding: 0.35rem 0.85rem;">
                {{ $style['label'] }}
            </span>
            @if($demande->est_vip && in_array(Auth::user()->type_utilisateur, ['admin', 'superviseur_soutarah', 'technicien', 'superviseur_client']))
            <span class="badge" style="background: linear-gradient(135deg, #dc2626, #b91c1c); color: white; font-size: 0.85rem; padding: 0.35rem 0.85rem; font-weight: 800; display: inline-flex; align-items: center; gap: 0.35rem; box-shadow: 0 4px 10px rgba(220, 38, 38, 0.35); animation: pulse 2s infinite;">
                <i class="fa-solid fa-siren-on"></i> 🚨 DEMANDE VIP
            </span>
            @endif
        </div>
    </div>
</div>

<!-- Informations générales et Description -->
<div style="display: grid; grid-template-columns: 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
    <div class="card">
        <h5 style="font-size: 0.95rem; font-weight: 700; color: #059669; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-info-circle"></i> Informations Générales
        </h5>
        <table style="width: 100%; font-size: 0.85rem;">
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 0.75rem 0; color: #64748b; font-weight: 600; width: 200px;">N° Demande:</td>
                <td style="padding: 0.75rem 0;"><code style="color: #059669; font-weight: 700;">{{ $demande->numero_demande }}</code></td>
            </tr>
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 0.75rem 0; color: #64748b; font-weight: 600;">Date:</td>
                <td style="padding: 0.75rem 0;">{{ $demande->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 0.75rem 0; color: #64748b; font-weight: 600;">Créée par:</td>
                <td style="padding: 0.75rem 0;">
                    <strong>{{ optional($demande->createdBy)->nom_complet ?? 'N/A' }}</strong> ({{ ucfirst($demande->created_by_role) }})
                    @if(optional($demande->createdBy)->telephone)
                    <div style="margin-top: 0.5rem;">
                        <a href="tel:{{ $demande->createdBy->telephone }}" style="display: inline-flex; align-items: center; gap: 0.4rem; color: #047857; text-decoration: none; font-weight: 700; padding: 0.35rem 0.7rem; background-color: #ecfdf5; border-radius: 0.5rem; border: 1px solid #a7f3d0; font-size: 0.85rem;">
                            <i class="fa-solid fa-phone"></i>
                            <span>{{ $demande->createdBy->telephone }}</span>
                        </a>
                    </div>
                    @endif
                </td>
            </tr>
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 0.75rem 0; color: #64748b; font-weight: 600;">Client:</td>
                <td style="padding: 0.75rem 0;">{{ optional($demande->client)->nom ?? 'N/A' }}</td>
            </tr>
            @if($demande->base)
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 0.75rem 0; color: #64748b; font-weight: 600;">Base:</td>
                <td style="padding: 0.75rem 0;">{{ $demande->base->nom_base }}</td>
            </tr>
            @endif
            @if($demande->site)
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 0.75rem 0; color: #64748b; font-weight: 600;">Site:</td>
                <td style="padding: 0.75rem 0;">{{ $demande->site->nom_site }}</td>
            </tr>
            @endif
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 0.75rem 0; color: #64748b; font-weight: 600;">Équipement:</td>
                <td style="padding: 0.75rem 0;">
                    @if($demande->equipement)
                    <strong>{{ $demande->equipement->equipement_code }}</strong> - {{ $demande->equipement->equipement_nom }}
                    @else
                    <span style="color: #94a3b8;">N/A</span>
                    @endif
                </td>
            </tr>
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 0.75rem 0; color: #64748b; font-weight: 600;">Urgence:</td>
                <td style="padding: 0.75rem 0;">
                    @php
                    $urgenceStyles = [
                    'faible' => ['bg' => '#f1f5f9', 'color' => '#64748b', 'border' => '#cbd5e1'],
                    'moyen' => ['bg' => '#f0f9ff', 'color' => '#0369a1', 'border' => '#bae6fd'],
                    'urgent' => ['bg' => '#fffbeb', 'color' => '#b45309', 'border' => '#fde68a'],
                    'critique' => ['bg' => '#fff1f2', 'color' => '#be123c', 'border' => '#fecdd3']
                    ];
                    $urgStyle = $urgenceStyles[$demande->niveau_urgence];
                    @endphp
                    <span class="badge" style="background-color: {{ $urgStyle['bg'] }}; color: {{ $urgStyle['color'] }}; border: 1px solid {{ $urgStyle['border'] }};">
                        {{ ucfirst($demande->niveau_urgence) }}
                    </span>
                </td>
            </tr>
            @if($demande->date_debut_souhaitee)
            <tr>
                <td style="padding: 0.75rem 0; color: #64748b; font-weight: 600;">Date d'intervention souhaitée:</td>
                <td style="padding: 0.75rem 0;">
                    <code>{{ $demande->date_debut_souhaitee->format('d/m/Y') }}</code>
                </td>
            </tr>
            @endif
        </table>
    </div>
</div>

<!-- Description du problème - Section mise en évidence -->
<div class="card" style="margin-bottom: 1.5rem; background: linear-gradient(135deg, #f0f9ff, #e0f2fe); border: 2px solid #0ea5e9;">
    <h5 style="font-size: 1rem; font-weight: 700; color: #0369a1; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
        <i class="fa-solid fa-file-text"></i> Description du Problème
    </h5>
    <div style="background-color: #ffffff; padding: 1.25rem; border-radius: 0.75rem; border: 1px solid #bae6fd; line-height: 1.6; color: #0f172a; font-size: 0.95rem;">
        {{ $demande->description }}
    </div>

    @if($demande->photo_panne)
    <h6 style="font-size: 0.85rem; color: #64748b; margin-top: 1.25rem; margin-bottom: 0.5rem;">
        <i class="fa-solid fa-image"></i> Photo de la panne:
    </h6>
    <img src="{{ asset('storage/' . $demande->photo_panne) }}"
        alt="Photo panne"
        style="width: 100%; max-height: 400px; object-fit: cover; border-radius: 0.75rem; border: 2px solid #e2e8f0; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
    @endif
</div>

<!-- État du Workflow -->
<div class="card">
    <h5 style="font-size: 1rem; font-weight: 700; color: #0f172a; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
        <i class="fa-solid fa-clipboard-check"></i> État du Workflow
    </h5>

    <!-- Étape 1: Validation Superviseur Client -->
    {{-- Ne montrer cette section QUE si la demande est encore en attente de validation client OU rejetée par le client --}}
    @if(in_array($demande->statut, ['pending_client_validation', 'rejected_by_client']) || $demande->date_validation_client)
    <div style="background-color: {{ $demande->statut == 'pending_client_validation' ? '#fffbeb' : ($demande->date_validation_client ? '#f0fdf4' : '#f8fafc') }}; border: 1px solid {{ $demande->statut == 'pending_client_validation' ? '#fde68a' : ($demande->date_validation_client ? '#a7f3d0' : '#e2e8f0') }}; border-radius: 0.75rem; padding: 1.25rem; margin-bottom: 1.25rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
            <strong style="font-size: 0.9rem; color: #0f172a;">1. Validation Superviseur Client</strong>
            @if($demande->date_validation_client)
            <span class="badge badge-success">✓ Validée</span>
            @elseif($demande->statut == 'rejected_by_client')
            <span class="badge badge-danger">✗ Rejetée</span>
            @elseif($demande->statut == 'pending_client_validation')
            <span class="badge badge-warning">En attente</span>
            @endif
        </div>

        @if($demande->date_validation_client)
        <p style="font-size: 0.85rem; color: #334155; margin-bottom: 0.5rem;"><strong>Validée par:</strong> {{ optional($demande->validatedByClient)->nom_complet ?? 'N/A' }}</p>
        <p style="font-size: 0.85rem; color: #334155; margin-bottom: 0.5rem;"><strong>Date:</strong> {{ \Carbon\Carbon::parse($demande->date_validation_client)->format('d/m/Y H:i') }}</p>
        @if($demande->message_validation_client)
        <p style="font-size: 0.85rem; color: #334155;"><strong>Message:</strong> {{ $demande->message_validation_client }}</p>
        @endif
        @elseif($demande->statut == 'rejected_by_client')
        <p style="font-size: 0.85rem; color: #be123c; margin-bottom: 0.5rem;"><strong>Rejetée par:</strong> {{ optional($demande->validatedByClient)->nom_complet ?? 'N/A' }}</p>
        <p style="font-size: 0.85rem; color: #be123c;"><strong>Raison:</strong> {{ $demande->message_validation_client }}</p>
        @else
        <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 1rem;">En attente de validation par le superviseur client</p>

        @if(Auth::user()->isSuperviseurClient() &&
        $demande->statut == 'pending_client_validation' &&
        !$isClosed)
        <div style="display: flex; gap: 0.75rem;">
            <button onclick="openValidateClientModal()" class="btn-primary" style="background: linear-gradient(135deg, #059669, #10b981);">
                <i class="fa-solid fa-check"></i> Valider la {{ $demande->type_intervention ?? 'Demande' }}
            </button>
            <button onclick="openRejectClientModal()" style="background: linear-gradient(135deg, #be123c, #e11d48); color: #ffffff; padding: 0.65rem 1.1rem; border-radius: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.5rem; box-shadow: 0 4px 12px rgba(190, 18, 60, 0.2); border: none; cursor: pointer;">
                <i class="fa-solid fa-times"></i> Rejeter
            </button>
        </div>
        @endif
        @endif
    </div>
    @endif

    <!-- Étape 2: Validation Demande d'Installation par Soutarah/Admin -->
    @if($demande->type_intervention === 'Installation')
    <div style="background-color: {{ $demande->date_validation_soutarah ? '#f0fdf4' : ($demande->statut == 'rejected_by_soutarah' ? '#fff1f2' : '#f0f9ff') }}; border: 2px solid {{ $demande->date_validation_soutarah ? '#10b981' : ($demande->statut == 'rejected_by_soutarah' ? '#f43f5e' : '#38bdf8') }}; border-radius: 0.75rem; padding: 1.25rem; margin-bottom: 1.25rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
            <strong style="font-size: 1rem; color: #0f172a; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-screwdriver-wrench"></i> {{ $demande->date_validation_client ? '1' : '2' }}. Validation & Approbation Soutarah (Installation)
            </strong>
            @if($demande->date_validation_soutarah)
            <span class="badge badge-success">✓ Validée par Soutarah</span>
            @elseif($demande->statut == 'rejected_by_soutarah')
            <span class="badge badge-danger">✗ Rejetée par Soutarah</span>
            @else
            <span class="badge badge-info">En attente de validation Soutarah</span>
            @endif
        </div>

        <p style="font-size: 0.85rem; color: #334155; margin-bottom: 1rem;">
            Installation du nouvel équipement : <strong>{{ $demande->equipement ? $demande->equipement->equipement_code . ' (' . $demande->equipement->equipement_nom . ')' : 'N/A' }}</strong> sollicitée pour le <strong>{{ $demande->date_debut_souhaitee ? (is_string($demande->date_debut_souhaitee) ? \Carbon\Carbon::parse($demande->date_debut_souhaitee)->format('d/m/Y') : $demande->date_debut_souhaitee->format('d/m/Y')) : 'N/A' }}</strong>.
        </p>

        @if($demande->date_validation_soutarah)
        <p style="font-size: 0.85rem; color: #334155; margin-bottom: 0.5rem;"><strong>Validée par:</strong> {{ optional($demande->validatedBySoutarah)->nom_complet ?? 'N/A' }}</p>
        <p style="font-size: 0.85rem; color: #334155; margin-bottom: 0.5rem;"><strong>Date:</strong> {{ \Carbon\Carbon::parse($demande->date_validation_soutarah)->format('d/m/Y H:i') }}</p>
        @elseif($demande->statut == 'rejected_by_soutarah')
        <p style="font-size: 0.85rem; color: #be123c; margin-bottom: 0.5rem;"><strong>Rejetée par:</strong> {{ optional($demande->validatedBySoutarah)->nom_complet ?? 'N/A' }}</p>
        <p style="font-size: 0.85rem; color: #be123c;"><strong>Raison:</strong> {{ $demande->message_validation_soutarah }}</p>
        @elseif((Auth::user()->isAdmin() || Auth::user()->isSuperviseurSoutarah()) && !$isClosed)
        <div style="display: flex; gap: 0.75rem;">
            <form action="{{ route('demandes.validateInstallation', $demande) }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="btn-primary" style="background: linear-gradient(135deg, #059669, #10b981);">
                    <i class="fa-solid fa-check"></i> Valider & Créer l'Opération Technique
                </button>
            </form>
            <button onclick="openRejectInstallationModal()" style="background: linear-gradient(135deg, #be123c, #e11d48); color: #ffffff; padding: 0.65rem 1.1rem; border-radius: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.5rem; box-shadow: 0 4px 12px rgba(190, 18, 60, 0.2); border: none; cursor: pointer;">
                <i class="fa-solid fa-times"></i> Rejeter
            </button>
        </div>
        @endif
    </div>
    @endif

    <!-- Étape 2: Validation Admin/Superviseur Soutarah - Visible uniquement pour Admin et Superviseur Soutarah -->
    @if((Auth::user()->isAdmin() || Auth::user()->isSuperviseurSoutarah()) && $demande->statut != 'pending_client_validation' && $demande->statut != 'rejected_by_client' && $demande->type_intervention !== 'Installation')
    <div style="background-color: {{ $demande->statut == 'validated_by_client' ? '#f0f9ff' : '#f8fafc' }}; border: 1px solid {{ $demande->statut == 'validated_by_client' ? '#bae6fd' : '#e2e8f0' }}; border-radius: 0.75rem; padding: 1.25rem; margin-bottom: 1.25rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
            <strong style="font-size: 0.9rem; color: #0f172a;">1. Validation Admin/Superviseur Soutarah</strong>
            @if($demande->date_validation_soutarah)
            <span class="badge badge-success">✓ Validée</span>
            @elseif($demande->statut == 'rejected_by_soutarah')
            <span class="badge badge-danger">✗ Rejetée</span>
            @elseif($demande->statut == 'validated_by_client')
            <span class="badge badge-info">En attente</span>
            @endif
        </div>

        @if($demande->date_validation_soutarah)
        <p style="font-size: 0.85rem; color: #334155; margin-bottom: 0.5rem;"><strong>Validée par:</strong> {{ optional($demande->validatedBySoutarah)->nom_complet ?? 'N/A' }}</p>
        <p style="font-size: 0.85rem; color: #334155; margin-bottom: 0.5rem;"><strong>Date:</strong> {{ \Carbon\Carbon::parse($demande->date_validation_soutarah)->format('d/m/Y H:i') }}</p>
        @if($demande->message_validation_soutarah)
        <p style="font-size: 0.85rem; color: #334155;"><strong>Message:</strong> {{ $demande->message_validation_soutarah }}</p>
        @endif
        @elseif($demande->statut == 'rejected_by_soutarah')
        <p style="font-size: 0.85rem; color: #be123c; margin-bottom: 0.5rem;"><strong>Rejetée par:</strong> {{ optional($demande->validatedBySoutarah)->nom_complet ?? 'N/A' }}</p>
        <p style="font-size: 0.85rem; color: #be123c;"><strong>Raison:</strong> {{ $demande->message_validation_soutarah }}</p>
        @else
        <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 1rem;">En attente de validation Soutarah</p>

        @if((Auth::user()->isAdmin() || Auth::user()->isSuperviseurSoutarah()) && $demande->statut == 'validated_by_client' && !$isClosed)
        <div style="display: flex; gap: 0.75rem;">
            <form action="{{ route('demandes.validateSoutarah', $demande) }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-check"></i> Valider et Créer Opération Technique
                </button>
            </form>
            <button onclick="openRejectSoutarahModal()" style="background: linear-gradient(135deg, #be123c, #e11d48); color: #ffffff; padding: 0.65rem 1.1rem; border-radius: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.5rem; box-shadow: 0 4px 12px rgba(190, 18, 60, 0.2); border: none; cursor: pointer;">
                <i class="fa-solid fa-times"></i> Rejeter
            </button>
        </div>
        @endif
        @endif
    </div>
    @endif

    <!-- Étape 3: Opération Technique liée -->
    @if($demande->technical_operation_id && $demande->technicalOperation)
    @php
        $op = $demande->technicalOperation;
        $opStatut = $op->statut ?? 'en attente';
        $isInstallation = $demande->type_intervention === 'Installation';
        
        // Déterminer couleur et libellé selon statut opération
        if ($opStatut === 'résolu') {
            $opBg = '#ecfdf5'; $opBorder = '#a7f3d0'; $opBadgeBg = '#059669'; $opBadgeLabel = $isInstallation ? '✅ Installation Terminée' : '✅ Terminée'; 
        } elseif (in_array($opStatut, ['en cours'])) {
            $opBg = '#eff6ff'; $opBorder = '#bfdbfe'; $opBadgeBg = '#2563eb'; $opBadgeLabel = $isInstallation ? '🔧 Installation En Cours' : '🔧 En Cours';
        } else {
            $opBg = '#fffbeb'; $opBorder = '#fde68a'; $opBadgeBg = '#b45309'; $opBadgeLabel = $isInstallation ? '⏳ En Attente d\'Installation' : '⏳ En Attente';
        }
    @endphp
    <div style="background-color: {{ $opBg }}; border: 2px solid {{ $opBorder }}; border-radius: 0.75rem; padding: 1.25rem; margin-bottom: 1.25rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
            <strong style="font-size: 0.9rem; color: #0f172a;">2. {{ $isInstallation ? 'Suivi de l\'Installation' : 'Opération Technique' }}</strong>
            <span class="badge" style="background-color: {{ $opBadgeBg }}; color: #fff; padding: 0.35rem 0.75rem; border-radius: 0.5rem; font-size: 0.8rem;">{{ $opBadgeLabel }}</span>
        </div>

        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem; font-size: 0.85rem; margin-bottom: 1rem;">
            <div>
                <span style="color: #64748b; font-weight: 600;">Réf. Opération :</span><br>
                <code style="color: #1d4ed8;">#{{ $op->id }}</code>
            </div>
            <div>
                <span style="color: #64748b; font-weight: 600;">Date {{ $isInstallation ? 'd\'installation' : 'd\'intervention' }} prévue :</span><br>
                @if($op->date_prevue)
                    <strong style="color: #047857;">{{ \Carbon\Carbon::parse($op->date_prevue)->format('d/m/Y') }}</strong>
                @elseif($demande->date_debut_souhaitee)
                    <strong style="color: #047857;">{{ (is_string($demande->date_debut_souhaitee) ? \Carbon\Carbon::parse($demande->date_debut_souhaitee) : $demande->date_debut_souhaitee)->format('d/m/Y') }}</strong>
                    <small style="color: #94a3b8;">(date de la demande)</small>
                @else
                    <span style="color: #94a3b8;">Non définie</span>
                @endif
            </div>
            @if($op->technicien)
            <div>
                <span style="color: #64748b; font-weight: 600;">Technicien assigné :</span><br>
                <strong>{{ $op->technicien->nom_complet }}</strong>
            </div>
            @endif
            @if($op->equipe)
            <div>
                <span style="color: #64748b; font-weight: 600;">Équipe assignée :</span><br>
                <strong>{{ $op->equipe->nom_equipe }}</strong>
                @if($op->equipe->chef) <small>(Chef: {{ $op->equipe->chef->nom_complet }})</small> @endif
            </div>
            @endif
            @if(!$op->technicien && !$op->equipe)
            <div>
                <span style="color: #64748b; font-weight: 600;">Affectation :</span><br>
                <span style="color: #f59e0b; font-weight: 700;"><i class="fa-solid fa-hourglass-half"></i> En attente d'affectation</span>
            </div>
            @endif
        </div>

        @if($op->rapport_technique)
        <div style="background-color: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 0.75rem; padding: 1rem; margin-bottom: 1rem;">
            <strong style="color: #047857; font-size: 0.9rem; display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                <i class="fa-solid fa-file-check"></i> Rapport {{ $isInstallation ? 'd\'Installation' : 'Technique' }} Disponible
            </strong>
            <p style="color: #065f46; font-size: 0.85rem; margin: 0;">{{ Str::limit($op->rapport_technique, 200) }}</p>
        </div>
        @endif

        <a href="{{ route('depannages.show', $op) }}" class="btn-primary">
            <i class="fa-solid fa-eye"></i> Voir le détail {{ $isInstallation ? 'de l\'installation' : 'de l\'opération' }}
        </a>
    </div>

    <!-- Étape 4: Confirmation par le Demandeur (PHASE 5) -->
    @if($demande->technicalOperation && $demande->technicalOperation->rapport_technique && in_array($demande->statut, ['needs_technical_operation', 'validated_by_client', 'confirmed_by_demandeur']))
    <div style="background-color: {{ $demande->date_confirmation_demandeur ? '#ecfdf5' : '#fffbeb' }}; border: 1px solid {{ $demande->date_confirmation_demandeur ? '#a7f3d0' : '#fde68a' }}; border-radius: 0.75rem; padding: 1.25rem; margin-bottom: 1.25rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
            <strong style="font-size: 0.9rem; color: #0f172a;">3. Confirmation par le Demandeur</strong>
            @if($demande->date_confirmation_demandeur)
            <span class="badge badge-success">✓ Confirmée</span>
            @else
            <span class="badge badge-warning">En attente</span>
            @endif
        </div>

        @if($demande->date_confirmation_demandeur)
        <div style="background-color: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 0.5rem; padding: 1rem;">
            <p style="font-size: 0.85rem; color: #047857; margin-bottom: 0.5rem;">
                <strong><i class="fa-solid fa-check-circle"></i> Travaux confirmés par:</strong> {{ optional($demande->createdBy)->nom_complet ?? 'N/A' }}
            </p>
            <p style="font-size: 0.85rem; color: #047857; margin-bottom: 0.5rem;"><strong>Date:</strong> {{ \Carbon\Carbon::parse($demande->date_confirmation_demandeur)->format('d/m/Y H:i') }}</p>
            @if($demande->commentaire_demandeur)
            <p style="font-size: 0.85rem; color: #047857;"><strong>Commentaire:</strong> {{ $demande->commentaire_demandeur }}</p>
            @endif
        </div>
        @else
        <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 1rem;">
            <i class="fa-solid fa-info-circle"></i> Le demandeur doit confirmer que les travaux ont été correctement réalisés.
        </p>

        @if(Auth::user()->isDemandeur() && Auth::user()->id == $demande->created_by_user_id && !$isClosed)
        <div style="background-color: #fffbeb; border: 1px solid #fde68a; border-radius: 0.75rem; padding: 1rem; margin-bottom: 1rem;">
            <strong style="color: #b45309; font-size: 0.9rem; display: block; margin-bottom: 0.5rem;">
                <i class="fa-solid fa-hand-point-right"></i> Action requise !
            </strong>
            <p style="color: #b45309; font-size: 0.85rem; margin-bottom: 1rem;">Veuillez confirmer que les travaux ont été réalisés selon vos attentes.</p>
            <button onclick="openConfirmDemandeurModal()" style="background: linear-gradient(135deg, #059669, #10b981); color: #ffffff; padding: 0.75rem 1.25rem; border-radius: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.5rem; box-shadow: 0 6px 16px rgba(5, 150, 105, 0.25); border: none; cursor: pointer; font-size: 0.9rem;">
                <i class="fa-solid fa-check-double"></i> CONFIRMER LES TRAVAUX
            </button>
        </div>
        @endif
        @endif
    </div>
    @endif

    <!-- Étape 5: Validation Finale Superviseur Client (PHASE 5) -->
    @if($demande->statut == 'confirmed_by_demandeur' || $demande->statut == 'closed')
    <div style="background-color: {{ $demande->statut == 'closed' ? '#ecfdf5' : '#f0f9ff' }}; border: 1px solid {{ $demande->statut == 'closed' ? '#a7f3d0' : '#bae6fd' }}; border-radius: 0.75rem; padding: 1.25rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
            <strong style="font-size: 0.9rem; color: #0f172a;">5. Validation Finale - Superviseur Client</strong>
            @if($demande->statut == 'closed')
            <span class="badge badge-success">✓ CLÔTURÉE</span>
            @else
            <span class="badge badge-info">En attente validation finale</span>
            @endif
        </div>

        @if($demande->statut == 'closed')
        <div style="background-color: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 0.5rem; padding: 1rem;">
            <h6 style="color: #047857; font-size: 0.95rem; margin-bottom: 0.75rem;">
                <i class="fa-solid fa-check-circle"></i> Demande Clôturée avec Succès
            </h6>
            <hr style="border: none; border-top: 1px solid #a7f3d0; margin: 0.75rem 0;">
            @php
            $clotureUser = $demande->cloturePar ?? $demande->validatedFinaleBy;
            $clotureDate = $demande->date_cloture_finale ?? $demande->date_validation_finale;
            $clotureMsg = $demande->message_validation_finale;
            @endphp
            <p style="font-size: 0.85rem; color: #047857; margin-bottom: 0.5rem;">
                <strong>Validée par:</strong> {{ $clotureUser ? $clotureUser->nom_complet : 'N/A' }}
            </p>
            <p style="font-size: 0.85rem; color: #047857; margin-bottom: 0.5rem;">
                <strong>Date de clôture:</strong> {{ $clotureDate ? \Carbon\Carbon::parse($clotureDate)->format('d/m/Y H:i') : 'N/A' }}
            </p>
            @if($demande->statut_validation_finale_client && $demande->statut_validation_finale_client !== 'en_attente')
            <p style="font-size: 0.85rem; color: #047857; margin-bottom: 0.5rem;">
                <strong>Résultat:</strong>
                @if(in_array($demande->statut_validation_finale_client, ['conforme', 'validé']))
                <span style="color: #047857; font-weight: 700;"><i class="fa-solid fa-check"></i> Travaux conformes</span>
                @elseif($demande->statut_validation_finale_client === 'non_conforme')
                <span style="color: #be123c; font-weight: 700;"><i class="fa-solid fa-times"></i> Travaux non conformes</span>
                @endif
            </p>
            @endif
            @if($demande->message_non_conformite)
            <p style="font-size: 0.85rem; color: #be123c; margin-bottom: 0.5rem;">
                <strong>Motif non-conformité:</strong> {{ $demande->message_non_conformite }}
            </p>
            @endif
            @if($clotureMsg)
            <p style="font-size: 0.85rem; color: #047857;">
                <strong>Message:</strong> {{ $clotureMsg }}
            </p>
            @endif
        </div>
        @else
        <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 1rem;">
            <i class="fa-solid fa-info-circle"></i> Le superviseur client doit valider la clôture définitive de la demande.
        </p>

        @if(Auth::user()->isSuperviseurClient() &&
        ((Auth::user()->base_id && Auth::user()->base_id == $demande->base_id) ||
        (Auth::user()->client_id && Auth::user()->client_id == $demande->client_id)) &&
        !$isClosed)
        <div style="background-color: #f0f9ff; border: 1px solid #bae6fd; border-radius: 0.75rem; padding: 1rem;">
            <strong style="color: #0369a1; font-size: 0.9rem; display: block; margin-bottom: 0.5rem;">
                <i class="fa-solid fa-hand-point-right"></i> Validation finale requise !
            </strong>
            <p style="color: #0369a1; font-size: 0.85rem; margin-bottom: 1rem;">Le demandeur a confirmé que les travaux sont satisfaisants. Vous pouvez maintenant clôturer cette demande.</p>

            <div style="display: flex; gap: 0.75rem;">
                <button onclick="openValidateFinaleModal()" style="background: linear-gradient(135deg, #059669, #10b981); color: #ffffff; padding: 0.75rem 1.25rem; border-radius: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.5rem; box-shadow: 0 6px 16px rgba(5, 150, 105, 0.25); border: none; cursor: pointer; font-size: 0.9rem;">
                    <i class="fa-solid fa-check-circle"></i> CLÔTURER LA DEMANDE
                </button>
                <button onclick="openRejectFinaleModal()" style="background: linear-gradient(135deg, #be123c, #e11d48); color: #ffffff; padding: 0.75rem 1.25rem; border-radius: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.5rem; box-shadow: 0 6px 16px rgba(190, 18, 60, 0.25); border: none; cursor: pointer; font-size: 0.9rem;">
                    <i class="fa-solid fa-times-circle"></i> TRAVAUX NON CONFORMES
                </button>
            </div>
        </div>
        @endif
        @endif
    </div>
    @endif
    @endif
</div>

{{-- ═══════════════════════════════════════════════════════════════════
     SECTION: CONFIRMATION PASSAGE TECHNICIENS (Demandeur)
     ═══════════════════════════════════════════════════════════════════ --}}
@if($demande->technicalOperation && $demande->technicalOperation->statut_rapport_technicien === 'transmis_client' && !$isClosed)
@include('demandes._confirmation_passage_techniciens')
@endif

{{-- ═══════════════════════════════════════════════════════════════════
     SECTION: VALIDATION FINALE - COMPARAISON DES 2 RAPPORTS
     Visible pour le Superviseur Client uniquement
     ═══════════════════════════════════════════════════════════════════ --}}
@if(Auth::user()->isSuperviseurClient() &&
((Auth::user()->base_id && Auth::user()->base_id == $demande->base_id) ||
(Auth::user()->client_id && Auth::user()->client_id == $demande->client_id)) &&
$demande->technicalOperation &&
$demande->technicalOperation->statut_rapport_technicien === 'transmis_client' &&
$demande->date_confirmation_passage &&
!$isClosed)
<div class="card" style="border: 3px solid #8b5cf6; background: linear-gradient(135deg, #f5f3ff, #ede9fe);">
    <div class="card-header" style="background: transparent; border-bottom: 2px solid #c4b5fd;">
        <h3 class="card-title" style="color: #7c3aed; font-size: 1.15rem;">
            <i class="fa-solid fa-balance-scale"></i> Validation Finale - Comparaison des Rapports
        </h3>
        <p style="margin-top: 0.5rem; color: #7c3aed; font-size: 0.9rem; font-weight: 600;">
            Comparez les deux rapports ci-dessous et décidez de clôturer ou signaler une non-conformité
        </p>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
        {{-- COLONNE 1: RAPPORT TECHNICIEN --}}
        <div style="background: #f0fdf4; border: 2px solid #a7f3d0; border-radius: 0.75rem; padding: 1.25rem;">
            <h4 style="color: #059669; font-size: 1rem; margin-bottom: 1rem; font-weight: 700;">
                <i class="fa-solid fa-user-cog"></i> Rapport du Technicien
            </h4>

            @if($demande->technicalOperation)
            <div style="margin-bottom: 1rem;">
                <p style="font-size: 0.85rem; color: #047857; margin-bottom: 0.5rem;">
                    <strong>Soumis par:</strong> {{ $demande->technicalOperation->rapportSoumisPar ? $demande->technicalOperation->rapportSoumisPar->nom_complet : 'N/A' }}
                </p>
                <p style="font-size: 0.85rem; color: #047857; margin-bottom: 1rem;">
                    <strong>Date:</strong> {{ $demande->technicalOperation->date_rapport_technicien ? \Carbon\Carbon::parse($demande->technicalOperation->date_rapport_technicien)->format('d/m/Y H:i') : '' }}
                </p>
            </div>

            {{-- Photos du technicien --}}
            <div style="margin-bottom: 1rem;">
                <h5 style="font-size: 0.85rem; color: #047857; margin-bottom: 0.5rem; font-weight: 700;">Photo du Rapport d'Intervention:</h5>
                @if($demande->technicalOperation->photo_carnet_rapport)
                <img src="{{ asset($demande->technicalOperation->photo_carnet_rapport) }}"
                    alt="Rapport d'intervention"
                    style="width: 100%; border-radius: 0.5rem; border: 2px solid #a7f3d0; cursor: pointer; margin-bottom: 0.75rem;"
                    onclick="window.open('{{ asset($demande->technicalOperation->photo_carnet_rapport) }}', '_blank')">
                @else
                <p style="color: #94a3b8; font-size: 0.85rem;">Aucune photo</p>
                @endif
            </div>

            <div style="margin-bottom: 1rem;">
                <h5 style="font-size: 0.85rem; color: #047857; margin-bottom: 0.5rem; font-weight: 700;">Photo de l'Équipement:</h5>
                @if($demande->technicalOperation->photo_equipement_apres)
                <img src="{{ asset($demande->technicalOperation->photo_equipement_apres) }}"
                    alt="Équipement réparé"
                    style="width: 100%; border-radius: 0.5rem; border: 2px solid #a7f3d0; cursor: pointer;"
                    onclick="window.open('{{ asset($demande->technicalOperation->photo_equipement_apres) }}', '_blank')">
                @else
                <p style="color: #94a3b8; font-size: 0.85rem;">Aucune photo</p>
                @endif
            </div>

            @if($demande->technicalOperation->rapport)
            <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 0.5rem; padding: 0.75rem;">
                <h5 style="font-size: 0.85rem; color: #047857; margin-bottom: 0.5rem; font-weight: 700;">Note:</h5>
                <p style="color: #0f172a; font-size: 0.85rem; margin: 0;">{{ $demande->technicalOperation->rapport }}</p>
            </div>
            @endif
            @endif
        </div>

        {{-- COLONNE 2: RAPPORT DEMANDEUR --}}
        <div style="background: #eff6ff; border: 2px solid #bae6fd; border-radius: 0.75rem; padding: 1.25rem;">
            <h4 style="color: #0369a1; font-size: 1rem; margin-bottom: 1rem; font-weight: 700;">
                <i class="fa-solid fa-user"></i> Rapport du Demandeur
            </h4>

            <div style="margin-bottom: 1rem;">
                <p style="font-size: 0.85rem; color: #0369a1; margin-bottom: 0.5rem;">
                    <strong>Soumis par:</strong> {{ optional($demande->createdBy)->nom_complet ?? 'N/A' }}
                </p>
                <p style="font-size: 0.85rem; color: #0369a1; margin-bottom: 1rem;">
                    <strong>Date:</strong> {{ $demande->date_confirmation_passage ? \Carbon\Carbon::parse($demande->date_confirmation_passage)->format('d/m/Y H:i') : '' }}
                </p>
            </div>

            <div style="margin-bottom: 1rem;">
                <h5 style="font-size: 0.85rem; color: #0369a1; margin-bottom: 0.5rem; font-weight: 700;">Passage des Techniciens:</h5>
                <p style="font-size: 1rem; font-weight: 700; color: {{ $demande->techniciens_sont_passes ? '#047857' : '#be123c' }};">
                    @if($demande->techniciens_sont_passes)
                    <i class="fa-solid fa-check"></i> Oui, ils sont passés
                    @else
                    <i class="fa-solid fa-times"></i> Non, personne n'est venu
                    @endif
                </p>
            </div>

            <div style="margin-bottom: 1rem;">
                <h5 style="font-size: 0.85rem; color: #0369a1; margin-bottom: 0.5rem; font-weight: 700;">Résultat de l'Intervention:</h5>
                <p style="font-size: 1rem; font-weight: 700;">
                    @if($demande->resultat_intervention === 'satisfaisant')
                    <span style="color: #047857;">✅ Satisfaisant</span>
                    @elseif($demande->resultat_intervention === 'partiellement_satisfaisant')
                    <span style="color: #b45309;">⚠️ Partiellement Satisfaisant</span>
                    @else
                    <span style="color: #be123c;">❌ Non Satisfaisant</span>
                    @endif
                </p>
            </div>

            @if($demande->commentaire_resultat)
            <div style="background: #dbeafe; border: 1px solid #bae6fd; border-radius: 0.5rem; padding: 0.75rem;">
                <h5 style="font-size: 0.85rem; color: #0369a1; margin-bottom: 0.5rem; font-weight: 700;">Commentaire:</h5>
                <p style="color: #0f172a; font-size: 0.85rem; line-height: 1.6; margin: 0;">{{ $demande->commentaire_resultat }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- ZONE DE DÉCISION --}}
    <div style="background: #fffbeb; border: 2px solid #fde68a; border-radius: 0.75rem; padding: 1.5rem; margin-bottom: 1.5rem;">
        <h4 style="color: #b45309; font-size: 1rem; margin-bottom: 0.75rem; font-weight: 700;">
            <i class="fa-solid fa-hand-point-right"></i> Décision Requise
        </h4>
        <p style="color: #b45309; font-size: 0.9rem; margin-bottom: 1.25rem; line-height: 1.6;">
            Après avoir comparé les deux rapports ci-dessus, veuillez décider si les travaux sont conformes ou non.
        </p>

        <div style="display: flex; gap: 1rem;">
            <form action="{{ route('demandes.validerCloture', $demande) }}" method="POST" style="flex: 1;">
                @csrf
                <button type="submit" style="width: 100%; background: linear-gradient(135deg, #059669, #10b981); color: #ffffff; padding: 1rem 1.5rem; border-radius: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; gap: 0.75rem; box-shadow: 0 6px 16px rgba(5, 150, 105, 0.3); border: none; cursor: pointer; font-size: 1rem;">
                    <i class="fa-solid fa-check-circle" style="font-size: 1.2rem;"></i>
                    <span>CLÔTURER (Conforme)</span>
                </button>
            </form>

            <button onclick="openSignalerNonConformiteModal()" style="flex: 1; background: linear-gradient(135deg, #be123c, #e11d48); color: #ffffff; padding: 1rem 1.5rem; border-radius: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; gap: 0.75rem; box-shadow: 0 6px 16px rgba(190, 18, 60, 0.3); border: none; cursor: pointer; font-size: 1rem;">
                <i class="fa-solid fa-exclamation-triangle" style="font-size: 1.2rem;"></i>
                <span>SIGNALER NON-CONFORMITÉ</span>
            </button>
        </div>
    </div>
</div>
@endif


{{-- ═══════════════════════════════════════════════════════════════════
     MODALS
═══════════════════════════════════════════════════════════════ --}}

<!-- Modal Validation Client -->
<div id="validateClientModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: #ffffff; border-radius: 1rem; padding: 2rem; max-width: 500px; width: 90%; box-shadow: 0 20px 40px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3 style="font-size: 1.1rem; font-weight: 800; color: #047857;">
                <i class="fa-solid fa-check"></i> Valider la Demande
            </h3>
            <button onclick="closeValidateClientModal()" style="background: none; border: none; font-size: 1.5rem; color: #94a3b8; cursor: pointer;">&times;</button>
        </div>

        <form action="{{ route('demandes.validateClient', $demande) }}" method="POST">
            @csrf
            
            {{-- Définition VIP par le Superviseur Client --}}
            <div style="margin-bottom: 1.25rem; background: linear-gradient(135deg, #fef2f2, #fff); border: 2px solid #fecaca; border-radius: 0.75rem; padding: 1rem 1.15rem;">
                <label style="display: flex; align-items: flex-start; gap: 0.75rem; cursor: pointer; margin: 0;">
                    <input type="checkbox" name="est_vip" value="1" {{ $demande->est_vip ? 'checked' : '' }} style="margin-top: 0.2rem; width: 1.25rem; height: 1.25rem; cursor: pointer; accent-color: #dc2626;">
                    <div>
                        <div style="font-weight: 800; color: #991b1b; font-size: 0.95rem; display: flex; align-items: center; gap: 0.4rem;">
                            <i class="fa-solid fa-crown" style="color: #dc2626;"></i> Définir comme Demande VIP
                        </div>
                        <div style="font-size: 0.78rem; color: #7f1d1d; margin-top: 0.25rem; line-height: 1.35;">
                            Cochez si cette demande concerne un bureau de haut cadre ou une urgence absolue. Elle sera immédiatement classée VIP et transmise en priorité critique à Soutarah.
                        </div>
                    </div>
                </label>
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Message (optionnel)</label>
                <textarea name="message" rows="3" style="width: 100%; padding: 0.75rem;" placeholder="Message de validation..."></textarea>
            </div>

            <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                <button type="button" onclick="closeValidateClientModal()" style="padding: 0.65rem 1.1rem; border-radius: 0.75rem; background-color: #f1f5f9; color: #64748b; border: none; font-weight: 700; cursor: pointer;">
                    Annuler
                </button>
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-check"></i> Valider
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Rejet Client -->
<div id="rejectClientModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: #ffffff; border-radius: 1rem; padding: 2rem; max-width: 500px; width: 90%; box-shadow: 0 20px 40px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3 style="font-size: 1.1rem; font-weight: 800; color: #be123c;">
                <i class="fa-solid fa-times"></i> Rejeter la Demande
            </h3>
            <button onclick="closeRejectClientModal()" style="background: none; border: none; font-size: 1.5rem; color: #94a3b8; cursor: pointer;">&times;</button>
        </div>

        <form action="{{ route('demandes.rejectClient', $demande) }}" method="POST">
            @csrf
            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Raison du rejet *</label>
                <textarea name="message" rows="3" required style="width: 100%; padding: 0.75rem;" placeholder="Expliquez pourquoi vous rejetez cette demande..."></textarea>
            </div>

            <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                <button type="button" onclick="closeRejectClientModal()" style="padding: 0.65rem 1.1rem; border-radius: 0.75rem; background-color: #f1f5f9; color: #64748b; border: none; font-weight: 700; cursor: pointer;">
                    Annuler
                </button>
                <button type="submit" style="background: linear-gradient(135deg, #be123c, #e11d48); color: #ffffff; padding: 0.65rem 1.1rem; border-radius: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.5rem; box-shadow: 0 4px 12px rgba(190, 18, 60, 0.2); border: none; cursor: pointer;">
                    <i class="fa-solid fa-times"></i> Rejeter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Rejet Soutarah -->
<div id="rejectSoutarahModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: #ffffff; border-radius: 1rem; padding: 2rem; max-width: 500px; width: 90%; box-shadow: 0 20px 40px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3 style="font-size: 1.1rem; font-weight: 800; color: #be123c;">
                <i class="fa-solid fa-times"></i> Rejeter la Demande
            </h3>
            <button onclick="closeRejectSoutarahModal()" style="background: none; border: none; font-size: 1.5rem; color: #94a3b8; cursor: pointer;">&times;</button>
        </div>

        <form action="{{ route('demandes.rejectSoutarah', $demande) }}" method="POST">
            @csrf
            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Raison du rejet *</label>
                <textarea name="message" rows="3" required style="width: 100%; padding: 0.75rem;" placeholder="Expliquez pourquoi vous rejetez cette demande..."></textarea>
            </div>

            <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                <button type="button" onclick="closeRejectSoutarahModal()" style="padding: 0.65rem 1.1rem; border-radius: 0.75rem; background-color: #f1f5f9; color: #64748b; border: none; font-weight: 700; cursor: pointer;">
                    Annuler
                </button>
                <button type="submit" style="background: linear-gradient(135deg, #be123c, #e11d48); color: #ffffff; padding: 0.65rem 1.1rem; border-radius: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.5rem; box-shadow: 0 4px 12px rgba(190, 18, 60, 0.2); border: none; cursor: pointer;">
                    <i class="fa-solid fa-times"></i> Rejeter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Confirmation Demandeur (PHASE 5) -->
<div id="confirmDemandeurModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: #ffffff; border-radius: 1rem; padding: 2rem; max-width: 500px; width: 90%; box-shadow: 0 20px 40px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3 style="font-size: 1.1rem; font-weight: 800; color: #047857;">
                <i class="fa-solid fa-check-double"></i> Confirmer les Travaux
            </h3>
            <button onclick="closeConfirmDemandeurModal()" style="background: none; border: none; font-size: 1.5rem; color: #94a3b8; cursor: pointer;">&times;</button>
        </div>

        <div style="background-color: #f0f9ff; border: 1px solid #bae6fd; border-radius: 0.75rem; padding: 1rem; margin-bottom: 1.25rem;">
            <i class="fa-solid fa-info-circle" style="color: #0369a1;"></i>
            <span style="color: #0369a1; font-size: 0.85rem;"> En confirmant, vous attestez que les travaux ont été réalisés conformément à vos attentes.</span>
        </div>

        <form action="{{ route('demandes.confirmByDemandeur', $demande) }}" method="POST">
            @csrf
            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Commentaire (optionnel)</label>
                <textarea name="commentaire" rows="3" style="width: 100%; padding: 0.75rem;" placeholder="Votre feedback sur les travaux réalisés..."></textarea>
            </div>

            <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                <button type="button" onclick="closeConfirmDemandeurModal()" style="padding: 0.65rem 1.1rem; border-radius: 0.75rem; background-color: #f1f5f9; color: #64748b; border: none; font-weight: 700; cursor: pointer;">
                    Annuler
                </button>
                <button type="submit" style="background: linear-gradient(135deg, #059669, #10b981); color: #ffffff; padding: 0.75rem 1.25rem; border-radius: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.5rem; box-shadow: 0 6px 16px rgba(5, 150, 105, 0.25); border: none; cursor: pointer;">
                    <i class="fa-solid fa-check-circle"></i> CONFIRMER
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Validation Finale (PHASE 5) -->
<div id="validateFinaleModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: #ffffff; border-radius: 1rem; padding: 2rem; max-width: 500px; width: 90%; box-shadow: 0 20px 40px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3 style="font-size: 1.1rem; font-weight: 800; color: #047857;">
                <i class="fa-solid fa-check-circle"></i> Clôturer la Demande
            </h3>
            <button onclick="closeValidateFinaleModal()" style="background: none; border: none; font-size: 1.5rem; color: #94a3b8; cursor: pointer;">&times;</button>
        </div>

        <div style="background-color: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 0.75rem; padding: 1rem; margin-bottom: 1.25rem;">
            <strong style="color: #047857; font-size: 0.9rem; display: block; margin-bottom: 0.5rem;">
                <i class="fa-solid fa-info-circle"></i> Validation Finale
            </strong>
            <p style="color: #065f46; font-size: 0.85rem; margin: 0;">Cette action clôturera définitivement la demande. Le demandeur et l'équipe seront notifiés.</p>
        </div>

        <form action="{{ route('demandes.validateFinale', $demande) }}" method="POST">
            @csrf
            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Message de clôture (optionnel)</label>
                <textarea name="message" rows="3" style="width: 100%; padding: 0.75rem;" placeholder="Message final pour l'équipe et le demandeur..."></textarea>
            </div>

            <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                <button type="button" onclick="closeValidateFinaleModal()" style="padding: 0.65rem 1.1rem; border-radius: 0.75rem; background-color: #f1f5f9; color: #64748b; border: none; font-weight: 700; cursor: pointer;">
                    Annuler
                </button>
                <button type="submit" style="background: linear-gradient(135deg, #059669, #10b981); color: #ffffff; padding: 0.75rem 1.25rem; border-radius: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.5rem; box-shadow: 0 6px 16px rgba(5, 150, 105, 0.25); border: none; cursor: pointer;">
                    <i class="fa-solid fa-check-double"></i> CLÔTURER
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Rejet Final (PHASE 5) -->
<div id="rejectFinaleModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: #ffffff; border-radius: 1rem; padding: 2rem; max-width: 500px; width: 90%; box-shadow: 0 20px 40px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3 style="font-size: 1.1rem; font-weight: 800; color: #be123c;">
                <i class="fa-solid fa-times-circle"></i> Travaux Non Conformes
            </h3>
            <button onclick="closeRejectFinaleModal()" style="background: none; border: none; font-size: 1.5rem; color: #94a3b8; cursor: pointer;">&times;</button>
        </div>

        <div style="background-color: #fff1f2; border: 1px solid #fecdd3; border-radius: 0.75rem; padding: 1rem; margin-bottom: 1.25rem;">
            <strong style="color: #be123c; font-size: 0.9rem; display: block; margin-bottom: 0.5rem;">
                <i class="fa-solid fa-exclamation-triangle"></i> Attention
            </strong>
            <p style="color: #be123c; font-size: 0.85rem; margin: 0;">En rejetant, vous indiquez que les travaux ne sont pas conformes. Une nouvelle intervention sera nécessaire.</p>
        </div>

        <form action="{{ route('demandes.rejectFinale', $demande) }}" method="POST">
            @csrf
            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Raison du rejet (obligatoire) *</label>
                <textarea name="message" rows="4" required style="width: 100%; padding: 0.75rem;" placeholder="Détaillez les problèmes constatés et ce qui doit être corrigé..."></textarea>
                <small style="color: #94a3b8; font-size: 0.75rem;">Le technicien et l'admin seront notifiés de ces observations.</small>
            </div>

            <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                <button type="button" onclick="closeRejectFinaleModal()" style="padding: 0.65rem 1.1rem; border-radius: 0.75rem; background-color: #f1f5f9; color: #64748b; border: none; font-weight: 700; cursor: pointer;">
                    Annuler
                </button>
                <button type="submit" style="background: linear-gradient(135deg, #be123c, #e11d48); color: #ffffff; padding: 0.75rem 1.25rem; border-radius: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.5rem; box-shadow: 0 6px 16px rgba(190, 18, 60, 0.25); border: none; cursor: pointer;">
                    <i class="fa-solid fa-ban"></i> REJETER
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Signaler Non-Conformité (Superviseur Client) -->
<div id="signalerNonConformiteModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: #ffffff; border-radius: 1rem; padding: 2rem; max-width: 600px; width: 90%; box-shadow: 0 20px 40px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3 style="font-size: 1.1rem; font-weight: 800; color: #be123c;">
                <i class="fa-solid fa-exclamation-triangle"></i> Signaler une Non-Conformité
            </h3>
            <button onclick="closeSignalerNonConformiteModal()" style="background: none; border: none; font-size: 1.5rem; color: #94a3b8; cursor: pointer;">&times;</button>
        </div>

        <div style="background-color: #fff1f2; border: 1px solid #fecdd3; border-radius: 0.75rem; padding: 1rem; margin-bottom: 1.25rem;">
            <strong style="color: #be123c; font-size: 0.9rem; display: block; margin-bottom: 0.5rem;">
                <i class="fa-solid fa-info-circle"></i> Important
            </strong>
            <p style="color: #be123c; font-size: 0.85rem; margin: 0;">
                En signalant une non-conformité, vous indiquez que les rapports ne correspondent pas ou que les travaux ne sont pas satisfaisants.
                Le superviseur Soutarah et l'admin seront notifiés et devront intervenir.
            </p>
        </div>

        <form action="{{ route('demandes.signalerNonConformite', $demande) }}" method="POST">
            @csrf
            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">
                    Décrivez la non-conformité constatée *
                </label>
                <textarea name="message_non_conformite" rows="5" required style="width: 100%; padding: 0.75rem; border: 2px solid #fecdd3; border-radius: 0.75rem;" placeholder="Expliquez en détail:&#10;- Les différences entre les 2 rapports&#10;- Les problèmes constatés&#10;- Ce qui doit être corrigé"></textarea>
                <small style="color: #94a3b8; font-size: 0.75rem;">
                    Ce message sera envoyé au superviseur Soutarah, à l'admin, au technicien et au demandeur.
                </small>
            </div>

            <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                <button type="button" onclick="closeSignalerNonConformiteModal()" style="padding: 0.65rem 1.1rem; border-radius: 0.75rem; background-color: #f1f5f9; color: #64748b; border: none; font-weight: 700; cursor: pointer;">
                    Annuler
                </button>
                <button type="submit" style="background: linear-gradient(135deg, #be123c, #e11d48); color: #ffffff; padding: 0.75rem 1.25rem; border-radius: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.5rem; box-shadow: 0 6px 16px rgba(190, 18, 60, 0.25); border: none; cursor: pointer;">
                    <i class="fa-solid fa-flag"></i> SIGNALER
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Rejet Installation -->
<div id="rejectInstallationModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.5); backdrop-filter: blur(4px); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #ffffff; border-radius: 1.25rem; max-width: 500px; width: 100%; padding: 1.75rem; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
        <h3 style="font-size: 1.1rem; font-weight: 800; color: #be123c; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-ban"></i> Rejeter la Demande d'Installation
        </h3>
        <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 1.25rem;">
            Veuillez indiquer la raison du rejet. Le client recevra une notification avec cette explication.
        </p>

        <form action="{{ route('demandes.rejectInstallation', $demande) }}" method="POST">
            @csrf
            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">
                    Raison du rejet *
                </label>
                <textarea name="message" rows="4" required style="width: 100%; padding: 0.75rem; border: 2px solid #fecdd3; border-radius: 0.75rem;" placeholder="Motif du rejet de la demande d'installation..."></textarea>
            </div>

            <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                <button type="button" onclick="closeRejectInstallationModal()" style="padding: 0.65rem 1.1rem; border-radius: 0.75rem; background-color: #f1f5f9; color: #64748b; border: none; font-weight: 700; cursor: pointer;">
                    Annuler
                </button>
                <button type="submit" style="background: linear-gradient(135deg, #be123c, #e11d48); color: #ffffff; padding: 0.65rem 1.1rem; border-radius: 0.75rem; font-weight: 700; border: none; cursor: pointer;">
                    <i class="fa-solid fa-times"></i> Rejeter l'Installation
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openRejectInstallationModal() {
        document.getElementById('rejectInstallationModal').style.display = 'flex';
    }

    function closeRejectInstallationModal() {
        document.getElementById('rejectInstallationModal').style.display = 'none';
    }

    // Fonctions pour gérer les modals
    function openValidateClientModal() {
        document.getElementById('validateClientModal').style.display = 'flex';
    }

    function closeValidateClientModal() {
        document.getElementById('validateClientModal').style.display = 'none';
    }

    function openRejectClientModal() {
        document.getElementById('rejectClientModal').style.display = 'flex';
    }

    function closeRejectClientModal() {
        document.getElementById('rejectClientModal').style.display = 'none';
    }

    function openRejectSoutarahModal() {
        document.getElementById('rejectSoutarahModal').style.display = 'flex';
    }

    function closeRejectSoutarahModal() {
        document.getElementById('rejectSoutarahModal').style.display = 'none';
    }

    function openConfirmDemandeurModal() {
        document.getElementById('confirmDemandeurModal').style.display = 'flex';
    }

    function closeConfirmDemandeurModal() {
        document.getElementById('confirmDemandeurModal').style.display = 'none';
    }

    function openValidateFinaleModal() {
        document.getElementById('validateFinaleModal').style.display = 'flex';
    }

    function closeValidateFinaleModal() {
        document.getElementById('validateFinaleModal').style.display = 'none';
    }

    function openRejectFinaleModal() {
        document.getElementById('rejectFinaleModal').style.display = 'flex';
    }

    function closeRejectFinaleModal() {
        document.getElementById('rejectFinaleModal').style.display = 'none';
    }

    function openSignalerNonConformiteModal() {
        document.getElementById('signalerNonConformiteModal').style.display = 'flex';
    }

    function closeSignalerNonConformiteModal() {
        document.getElementById('signalerNonConformiteModal').style.display = 'none';
    }

    // Fermer les modals en cliquant à l'extérieur
    document.querySelectorAll('[id$="Modal"]').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.style.display = 'none';
            }
        });
    });
</script>
@endsection