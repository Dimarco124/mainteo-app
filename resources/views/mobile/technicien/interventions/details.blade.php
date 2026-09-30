@extends('mobile.technicien.layout')

@section('title', 'Intervention #' . $depannage->id)
@section('page-title', 'Ticket #' . $depannage->id)

@section('mobile-content')
<div style="padding: 1rem; padding-bottom: 100px;">
    
    {{-- HEADER INTERVENTION --}}
    <div style="background: linear-gradient(135deg, var(--main-emerald), var(--main-emerald-light)); color: #fff; border-radius: 1rem; padding: 1.5rem; margin-bottom: 1rem; box-shadow: 0 4px 12px rgba(5, 150, 105, 0.2);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
            <div>
                <div style="font-size: 0.85rem; opacity: 0.9; margin-bottom: 0.25rem;">
                    @if($depannage->type_intervention === 'Dépannage')
                        <i class="fa-solid fa-wrench"></i> Dépannage
                    @elseif($depannage->type_intervention === 'Maintenance')
                        <i class="fa-solid fa-screwdriver-wrench"></i> Maintenance
                    @else
                        <i class="fa-solid fa-gears"></i> Installation
                    @endif
                </div>
                <h2 style="font-size: 1.5rem; font-weight: 800; margin: 0;">Ticket #{{ $depannage->id }}</h2>
            </div>
            
            @php
                $statutBg = match($depannage->statut) {
                    'résolu', 'resolu' => 'background: #10b981;',
                    'en cours' => 'background: #3b82f6;',
                    'en_attente_piece' => 'background: #f59e0b;',
                    'non_resolu' => 'background: #ef4444;',
                    default => 'background: rgba(255,255,255,0.2);'
                };
            @endphp
            <span style="{{ $statutBg }} padding: 0.5rem 0.75rem; border-radius: 0.5rem; font-size: 0.8rem; font-weight: 700; white-space: nowrap;">
                {{ ucfirst($depannage->statut) }}
            </span>
        </div>
        
        @if($depannage->urgence)
        <div style="display: inline-flex; align-items: center; gap: 0.5rem; background: rgba(255,255,255,0.2); padding: 0.5rem 1rem; border-radius: 0.75rem;">
            @if($depannage->urgence === 'Critique')
                <i class="fa-solid fa-fire"></i>
                <span style="font-weight: 700;">CRITIQUE</span>
            @elseif($depannage->urgence === 'Urgent')
                <i class="fa-solid fa-exclamation-triangle"></i>
                <span style="font-weight: 700;">URGENT</span>
            @else
                <i class="fa-solid fa-clock"></i>
                <span>{{ $depannage->urgence }}</span>
            @endif
        </div>
        @endif
    </div>

    {{-- RI SOUTARAH --}}
    @if($depannage->ri_soutarah)
    <div style="background: #fffbeb; border: 2px solid #f59e0b; border-radius: 0.75rem; padding: 1rem; margin-bottom: 1rem;">
        <div style="display: flex; align-items: center; gap: 0.5rem; color: #b45309;">
            <i class="fa-solid fa-bookmark"></i>
            <strong>RI Soutarah:</strong>
            <span style="font-family: monospace; font-weight: 700;">{{ $depannage->ri_soutarah }}</span>
        </div>
    </div>
    @endif

    {{-- INFORMATIONS ÉQUIPEMENT --}}
    <div class="card" style="margin-bottom: 1rem;">
        <h3 style="font-size: 1rem; font-weight: 700; color: var(--text-dark); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-microchip" style="color: var(--main-emerald);"></i>
            Équipement
        </h3>
        
        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
            @if($depannage->equipement)
            <div>
                <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.25rem;">Nom</div>
                <div style="font-size: 0.95rem; font-weight: 600; color: var(--text-dark);">{{ $depannage->equipement->equipement_nom }}</div>
            </div>
            @endif
            
            @if($depannage->equipement_reference)
            <div>
                <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.25rem;">Référence</div>
                <div style="font-size: 0.95rem; font-weight: 600; color: var(--text-dark); font-family: monospace;">{{ $depannage->equipement_reference }}</div>
            </div>
            @endif
            
            @if($depannage->equipement && $depannage->equipement->site)
            <div>
                <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.25rem;">Site</div>
                <div style="font-size: 0.95rem; font-weight: 600; color: var(--text-dark);">
                    <i class="fa-solid fa-map-marker-alt" style="color: var(--main-emerald);"></i>
                    {{ $depannage->equipement->site->nom_site }}
                </div>
            </div>
            @endif
            
            @if($depannage->equipement && $depannage->equipement->client)
            <div>
                <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.25rem;">Client</div>
                <div style="font-size: 0.95rem; font-weight: 600; color: var(--text-dark);">
                    <i class="fa-solid fa-building" style="color: var(--main-emerald);"></i>
                    {{ $depannage->equipement->client->nom }}
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- DESCRIPTION DU PROBLÈME --}}
    <div class="card" style="margin-bottom: 1rem;">
        <h3 style="font-size: 1rem; font-weight: 700; color: #be123c; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-triangle-exclamation"></i>
            Description du Problème
        </h3>
        
        <p style="font-size: 0.95rem; line-height: 1.6; color: var(--text-dark); background: #f8fafc; padding: 1rem; border-radius: 0.75rem; border: 1px solid var(--border-color); margin-bottom: 1rem;">
            {{ $depannage->description_panne }}
        </p>
        
        <div style="display: flex; gap: 1rem; font-size: 0.85rem; color: var(--text-muted);">
            <div>
                <i class="fa-solid fa-calendar"></i>
                {{ is_string($depannage->date_demande) ? $depannage->date_demande : $depannage->date_demande->format('d/m/Y H:i') }}
            </div>
            @if($depannage->demandeur)
            <div>
                <i class="fa-solid fa-user"></i>
                {{ $depannage->demandeur->nom }}
            </div>
            @endif
        </div>
        
        {{-- Photos jointes --}}
        @if($depannage->photo_panne || $depannage->fichier_joint)
        <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
            <h4 style="font-size: 0.9rem; font-weight: 700; color: var(--text-dark); margin-bottom: 0.75rem;">
                <i class="fa-solid fa-paperclip" style="color: var(--main-emerald);"></i> Pièces jointes
            </h4>
            
            @if($depannage->photo_panne)
            <div style="margin-bottom: 0.75rem;">
                <img src="{{ asset($depannage->photo_panne) }}" alt="Photo panne" style="width: 100%; border-radius: 0.75rem; cursor: pointer;" onclick="window.open('{{ asset($depannage->photo_panne) }}', '_blank')">
                <a href="{{ asset($depannage->photo_panne) }}" target="_blank" style="display: block; text-align: center; margin-top: 0.5rem; font-size: 0.85rem; color: var(--main-emerald); font-weight: 600; text-decoration: none;">
                    <i class="fa-solid fa-expand"></i> Agrandir
                </a>
            </div>
            @endif
            
            @if($depannage->fichier_joint)
            <a href="{{ asset($depannage->fichier_joint) }}" target="_blank" style="display: flex; align-items: center; gap: 0.75rem; padding: 1rem; background: #f8fafc; border: 1px solid var(--border-color); border-radius: 0.75rem; text-decoration: none; color: var(--text-dark);">
                <i class="fa-solid fa-file-lines" style="font-size: 1.5rem; color: var(--main-emerald);"></i>
                <div style="flex: 1;">
                    <div style="font-weight: 600;">Document joint</div>
                    <div style="font-size: 0.8rem; color: var(--text-muted);">Cliquez pour télécharger</div>
                </div>
                <i class="fa-solid fa-download" style="color: var(--main-emerald);"></i>
            </a>
            @endif
        </div>
        @endif
    </div>

    {{-- CONFIRMATION RÉCEPTION --}}
    @php
        $user = Auth::user();
        $peutConfirmer = false;
        if ($depannage->technicien_id == $user->id) {
            $peutConfirmer = true;
        } elseif ($depannage->equipe_id && $depannage->equipe) {
            if ($depannage->equipe->chef_equipe == $user->id || $depannage->equipe->membres->contains('id', $user->id)) {
                $peutConfirmer = true;
            }
        }
    @endphp

    @if($peutConfirmer)
        @if($depannage->confirmation_reception !== 'confirmé' && in_array($depannage->statut, ['en cours', 'approuvé - en attente assignation']))
        <div class="card" style="margin-bottom: 1rem; border: 2px solid #3b82f6; background: linear-gradient(135deg, #eff6ff, #dbeafe);">
            <h3 style="font-size: 1rem; font-weight: 700; color: #1e40af; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-hand-point-up"></i>
                Confirmer la Réception
            </h3>
            <p style="font-size: 0.9rem; color: #1e40af; margin-bottom: 1rem; line-height: 1.6;">
                Confirmez que vous avez bien reçu cette intervention et que vous allez la prendre en charge.
            </p>
            <form action="{{ route('depannages.confirmReception', $depannage->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn-mobile btn-primary-mobile" style="width: 100%; justify-content: center; padding: 1rem;">
                    <i class="fa-solid fa-check-circle"></i>
                    JE CONFIRME LA RÉCEPTION
                </button>
            </form>
        </div>
        @elseif($depannage->confirmation_reception === 'confirmé')
        <div class="card" style="margin-bottom: 1rem; border: 2px solid #10b981; background: #ecfdf5;">
            <div style="text-align: center; padding: 0.5rem;">
                <i class="fa-solid fa-circle-check" style="font-size: 2rem; color: #10b981; margin-bottom: 0.5rem; display: block;"></i>
                <p style="color: #047857; font-weight: 700; margin: 0;">
                    ✅ Réception confirmée le {{ \Carbon\Carbon::parse($depannage->date_confirmation_reception)->format('d/m/Y à H:i') }}
                </p>
            </div>
        </div>
        @endif
    @endif

    {{-- COMPTE-RENDU TECHNICIEN --}}
    @if($peutConfirmer)
        @if($depannage->statut_rapport_technicien === 'rejete')
        {{-- Rapport rejeté --}}
        <div class="card" style="margin-bottom: 1rem; border: 3px solid #be123c; background: #fff1f2;">
            <h3 style="font-size: 1rem; font-weight: 700; color: #be123c; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-triangle-exclamation"></i>
                Rapport Rejeté - Correction Requise
            </h3>
            <div style="background: #fff; padding: 1rem; border-radius: 0.75rem; border: 1px solid #fecdd3; margin-bottom: 1rem;">
                <p style="font-size: 0.85rem; color: #9f1239; font-weight: 600; margin-bottom: 0.5rem;">
                    <i class="fa-solid fa-comment-dots"></i> Motif du rejet :
                </p>
                <p style="font-size: 0.9rem; color: #be123c; line-height: 1.5;">
                    {{ $depannage->raison_rejet_rapport ?? 'Le rapport ou la photo n\'est pas conforme.' }}
                </p>
            </div>
            <a href="{{ route('depannages.rapportForm', $depannage->id) }}" class="btn-mobile btn-danger-mobile" style="width: 100%; justify-content: center;">
                <i class="fa-solid fa-pen-to-square"></i>
                Corriger & Resoumettre
            </a>
        </div>
        
        @elseif(!in_array($depannage->statut, ['resolu', 'résolu']))
        {{-- Faire le rapport --}}
        <div class="card" style="margin-bottom: 1rem; border: 2px solid #1d4ed8; background: linear-gradient(135deg, #eff6ff, #dbeafe);">
            <h3 style="font-size: 1rem; font-weight: 700; color: #1d4ed8; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-file-signature"></i>
                @if($depannage->statut_rapport_technicien === 'soumis')
                    Mettre à Jour le Rapport
                @else
                    Soumettre le Rapport
                @endif
            </h3>
            
            @if($depannage->statut_rapport_technicien === 'soumis')
            <p style="font-size: 0.9rem; color: #1e40af; margin-bottom: 1rem; line-height: 1.6;">
                <i class="fa-solid fa-info-circle"></i>
                Rapport intermédiaire soumis. Vous pouvez le modifier ou finaliser l'intervention.
            </p>
            @else
            <p style="font-size: 0.9rem; color: #1e40af; margin-bottom: 1rem; line-height: 1.6;">
                Remplissez le formulaire avec le statut terrain, le compte-rendu et les photos.
            </p>
            @endif
            
            <a href="{{ route('depannages.rapportForm', $depannage->id) }}" class="btn-mobile btn-primary-mobile" style="width: 100%; justify-content: center;">
                <i class="fa-solid fa-pen-to-square"></i>
                @if($depannage->statut_rapport_technicien === 'soumis')
                    Mettre à Jour le Rapport
                @else
                    Faire le Rapport
                @endif
            </a>
        </div>
        
        @else
        {{-- Rapport soumis --}}
        <div class="card" style="margin-bottom: 1rem; border: 2px solid #059669;">
            <h3 style="font-size: 1rem; font-weight: 700; color: #059669; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-check-circle"></i>
                Votre Rapport
            </h3>
            <p style="font-size: 0.85rem; color: #047857; margin-bottom: 1rem;">
                Soumis le {{ $depannage->date_rapport_technicien ? \Carbon\Carbon::parse($depannage->date_rapport_technicien)->format('d/m/Y à H:i') : 'N/A' }}
            </p>
            @if($depannage->rapport)
            <div style="background: #f0fdf4; padding: 1rem; border-radius: 0.75rem; border: 1px solid #a7f3d0;">
                <p style="font-size: 0.9rem; color: var(--text-dark); line-height: 1.6; margin: 0;">
                    {{ $depannage->rapport }}
                </p>
            </div>
            @endif
        </div>
        @endif
    @endif

    {{-- ASSIGNATION --}}
    <div class="card" style="margin-bottom: 1rem;">
        <h3 style="font-size: 1rem; font-weight: 700; color: var(--text-dark); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-user-gear" style="color: var(--main-emerald);"></i>
            Assignation
        </h3>
        
        @if($depannage->technicien)
        <div style="margin-bottom: 0.75rem;">
            <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.25rem;">Technicien</div>
            <div style="font-size: 0.95rem; font-weight: 600; color: var(--text-dark);">
                <i class="fa-solid fa-user"></i>
                {{ $depannage->technicien->nom_complet }}
            </div>
        </div>
        @endif
        
        @if($depannage->equipe)
        <div>
            <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.25rem;">Équipe</div>
            <div style="font-size: 0.95rem; font-weight: 600; color: var(--text-dark);">
                <i class="fa-solid fa-users"></i>
                {{ $depannage->equipe->nom_equipe }}
            </div>
        </div>
        @endif
    </div>

    {{-- WORKFLOW STATUS --}}
    <div class="card">
        <h3 style="font-size: 1rem; font-weight: 700; color: var(--text-dark); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-list-check" style="color: var(--main-emerald);"></i>
            État d'avancement
        </h3>
        
        @php
            $etapes = [
                ['label' => 'Créée', 'done' => true],
                ['label' => 'Assignée', 'done' => $depannage->technicien_id || $depannage->equipe_id],
                ['label' => 'Réception confirmée', 'done' => $depannage->confirmation_reception === 'confirmé'],
                ['label' => 'En cours', 'done' => in_array($depannage->statut, ['en cours', 'résolu', 'resolu'])],
                ['label' => 'Rapport soumis', 'done' => in_array($depannage->statut_rapport_technicien, ['soumis', 'transmis_client', 'validé'])],
                ['label' => 'Clôturée', 'done' => $depannage->statut_validation_finale_client === 'validé'],
            ];
        @endphp
        
        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
            @foreach($etapes as $index => $etape)
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; {{ $etape['done'] ? 'background: var(--main-emerald); color: #fff;' : 'background: #e2e8f0; color: var(--text-muted);' }}">
                    @if($etape['done'])
                        <i class="fa-solid fa-check"></i>
                    @else
                        {{ $index + 1 }}
                    @endif
                </div>
                <span style="font-size: 0.9rem; font-weight: 600; {{ $etape['done'] ? 'color: var(--text-dark);' : 'color: var(--text-muted);' }}">
                    {{ $etape['label'] }}
                </span>
            </div>
            @endforeach
        </div>
    </div>

</div>

{{-- BOTTOM ACTION BAR --}}
@if($peutConfirmer)
<div class="mobile-bottom-action-bar">
    @if($depannage->confirmation_reception !== 'confirmé' && in_array($depannage->statut, ['en cours', 'approuvé - en attente assignation']))
        <form action="{{ route('depannages.confirmReception', $depannage->id) }}" method="POST" style="flex: 1;">
            @csrf
            <button type="submit" class="btn-mobile btn-primary-mobile" style="width: 100%;">
                <i class="fa-solid fa-check-circle"></i>
                Confirmer Réception
            </button>
        </form>
    @elseif($depannage->statut_rapport_technicien === 'rejete' || !in_array($depannage->statut, ['resolu', 'résolu']))
        <a href="{{ route('depannages.rapportForm', $depannage->id) }}" class="btn-mobile btn-primary-mobile" style="flex: 1;">
            <i class="fa-solid fa-pen-to-square"></i>
            @if($depannage->statut_rapport_technicien === 'rejete')
                Corriger le Rapport
            @elseif($depannage->statut_rapport_technicien === 'soumis')
                Mettre à Jour
            @else
                Faire le Rapport
            @endif
        </a>
    @else
        <a href="{{ route('depannages.show', $depannage->id) }}" class="btn-mobile btn-secondary-mobile" style="flex: 1;">
            <i class="fa-solid fa-eye"></i>
            Voir Version Desktop
        </a>
    @endif
    
    @if($depannage->demandeur && $depannage->demandeur->telephone)
    <a href="tel:{{ $depannage->demandeur->telephone }}" class="btn-mobile btn-outline-mobile" style="width: 56px; padding: 0; justify-content: center;">
        <i class="fa-solid fa-phone"></i>
    </a>
    @endif
</div>
@endif

@endsection
