@extends('layouts.app')

@section('title', 'Affecter une équipe - Demande #' . $demande->numero_demande)

@section('content')
<div class="header">
    <div class="page-title">
        <a href="{{ route('operations.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.4rem; margin-bottom: 0.5rem;">
            <i class="fa-solid fa-arrow-left"></i> Retour aux Opérations
        </a>
        <h1>Affecter une équipe</h1>
        <p>Créer une opération technique pour la demande #{{ $demande->numero_demande }}</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1.1fr 1fr; gap: 1.5rem;">
    {{-- ═══════════════════════════════════════════════════════════════
         COLONNE GAUCHE : RAPPEL DE LA DEMANDE
    ════════════════════════════════════════════════════════════════ --}}
    <div>
        <div class="card">
            <h3 style="font-size: 0.95rem; font-weight: 800; color: #0f172a; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-file-lines" style="color: #059669;"></i> Rappel de la demande
            </h3>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; font-size: 0.82rem;">
                <div style="background-color: #f8fafc; padding: 0.7rem 0.85rem; border-radius: 0.55rem;">
                    <p style="color: #64748b; font-weight: 700; font-size: 0.7rem; text-transform: uppercase; margin-bottom: 0.2rem;">Client</p>
                    <p style="color: #0f172a; font-weight: 600;">{{ $demande->client->nom ?? 'N/A' }}</p>
                </div>
                <div style="background-color: #f8fafc; padding: 0.7rem 0.85rem; border-radius: 0.55rem;">
                    <p style="color: #64748b; font-weight: 700; font-size: 0.7rem; text-transform: uppercase; margin-bottom: 0.2rem;">Base</p>
                    <p style="color: #0f172a; font-weight: 600;">{{ $demande->base->nom_base ?? 'N/A' }}</p>
                </div>
                <div style="background-color: #f8fafc; padding: 0.7rem 0.85rem; border-radius: 0.55rem;">
                    <p style="color: #64748b; font-weight: 700; font-size: 0.7rem; text-transform: uppercase; margin-bottom: 0.2rem;">Site</p>
                    <p style="color: #0f172a; font-weight: 600;">{{ $demande->site->nom_site ?? 'N/A' }}</p>
                </div>
                <div style="background-color: #f8fafc; padding: 0.7rem 0.85rem; border-radius: 0.55rem;">
                    <p style="color: #64748b; font-weight: 700; font-size: 0.7rem; text-transform: uppercase; margin-bottom: 0.2rem;">Demandeur</p>
                    <p style="color: #0f172a; font-weight: 600;">{{ $demande->createdBy->nom_complet }}</p>
                </div>
                <div style="background-color: #f8fafc; padding: 0.7rem 0.85rem; border-radius: 0.55rem;">
                    <p style="color: #64748b; font-weight: 700; font-size: 0.7rem; text-transform: uppercase; margin-bottom: 0.2rem;">Équipement</p>
                    <p style="color: #0f172a; font-weight: 600;">{{ $demande->equipement->equipement_nom ?? 'N/A' }}</p>
                </div>
                <div style="background-color: #f8fafc; padding: 0.7rem 0.85rem; border-radius: 0.55rem;">
                    <p style="color: #64748b; font-weight: 700; font-size: 0.7rem; text-transform: uppercase; margin-bottom: 0.2rem;">Urgence</p>
                    @php
                        $urgStyles = [
                            'faible' => '#f1f5f9:#64748b:#cbd5e1',
                            'moyen' => '#f0f9ff:#0369a1:#bae6fd',
                            'urgent' => '#fffbeb:#b45309:#fde68a',
                            'critique' => '#fff1f2:#be123c:#fecdd3'
                        ];
                        $u = explode(':', $urgStyles[$demande->niveau_urgence] ?? $urgStyles['moyen']);
                    @endphp
                    <span class="badge" style="background-color: {{ $u[0] }}; color: {{ $u[1] }}; border: 1px solid {{ $u[2] }};">
                        {{ ucfirst($demande->niveau_urgence) }}
                    </span>
                </div>
            </div>

            <div style="margin-top: 1rem;">
                <p style="color: #64748b; font-weight: 700; font-size: 0.7rem; text-transform: uppercase; margin-bottom: 0.4rem;">Description du problème</p>
                <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 0.55rem; padding: 0.85rem; color: #334155; font-size: 0.82rem; line-height: 1.6; white-space: pre-wrap;">
                    {{ $demande->description }}
                </div>
            </div>

            @if($demande->photo_panne)
            <div style="margin-top: 1rem;">
                <p style="color: #64748b; font-weight: 700; font-size: 0.7rem; text-transform: uppercase; margin-bottom: 0.4rem;">Photo de la panne</p>
                <img src="{{ asset('storage/' . $demande->photo_panne) }}"
                     alt="Photo panne"
                     style="width: 100%; max-height: 260px; object-fit: cover; border-radius: 0.55rem; border: 1px solid #e2e8f0;">
            </div>
            @endif

            {{-- Bloc appel Phase 1 --}}
            <div style="margin-top: 1.25rem; padding: 1rem; background: linear-gradient(135deg, #ecfdf5, #ffffff); border: 1px solid #a7f3d0; border-radius: 0.65rem;">
                <p style="font-size: 0.82rem; font-weight: 800; color: #047857; margin-bottom: 0.5rem;">
                    <i class="fa-solid fa-phone"></i> Compte-rendu appel &amp; date confirmée
                </p>
                @if($demande->appel_date)
                    <table style="width: 100%; font-size: 0.78rem; margin-bottom: 0.5rem;">
                        <tr>
                            <td style="padding: 0.2rem 0; color: #64748b; width: 140px;">Appelé par:</td>
                            <td style="padding: 0.2rem 0; color: #0f172a; font-weight: 600;">{{ $demande->appelEffectuePar->nom_complet ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 0.2rem 0; color: #64748b;">Date appel:</td>
                            <td style="padding: 0.2rem 0; color: #0f172a; font-weight: 600;">{{ $demande->appel_date->format('d/m/Y H:i') }}</td>
                        </tr>
                        @if($demande->date_confirmee_avec_demandeur)
                        <tr>
                            <td style="padding: 0.2rem 0; color: #059669; font-weight: 700;">Date convenue:</td>
                            <td style="padding: 0.2rem 0; color: #047857; font-weight: 800;">
                                <i class="fa-solid fa-calendar-check"></i> {{ $demande->date_confirmee_avec_demandeur->format('d/m/Y') }}
                            </td>
                        </tr>
                        @endif
                    </table>
                    @if($demande->appel_notes)
                        <div style="background-color: #ffffff; border: 1px solid #d1fae5; border-radius: 0.4rem; padding: 0.6rem 0.75rem; font-size: 0.76rem; color: #065f46; white-space: pre-wrap; line-height: 1.5; max-height: 160px; overflow-y: auto;">
                            {!! nl2br(e(Str::limit($demande->appel_notes, 600))) !!}
                        </div>
                    @endif
                @else
                    <p style="font-size: 0.8rem; color: #b45309; background: #fffbeb; padding: 0.5rem 0.6rem; border-radius: 0.4rem; border: 1px solid #fde68a;">
                        <i class="fa-solid fa-triangle-exclamation"></i> Aucun appel enregistré pour cette demande. Vous pouvez quand même affecter une équipe, mais il est recommandé d'appeler d'abord le demandeur.
                    </p>
                @endif
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
         COLONNE DROITE : FORMULAIRE D'AFFECTATION
    ════════════════════════════════════════════════════════════════ --}}
    <div>
        <div class="card" style="position: sticky; top: 90px;">
            <h3 style="font-size: 0.95rem; font-weight: 800; color: #0284c7; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-clipboard-list-check"></i> Affectation de l'opération
            </h3>

            <form action="{{ route('operations.storeFromDemande') }}" method="POST">
                @csrf
                <input type="hidden" name="demande_id" value="{{ $demande->id }}">

                {{-- Équipe --}}
                <div style="margin-bottom: 1.1rem;">
                    <label style="display: block; font-size: 0.82rem; color: #0f172a; margin-bottom: 0.4rem; font-weight: 800;">
                        <i class="fa-solid fa-people-group" style="color: #059669;"></i> Sélectionner l'équipe *
                    </label>
                    <select name="equipe_id" id="equipeSelect" required
                            style="width: 100%; padding: 0.7rem 0.85rem; border-radius: 0.55rem; border: 1px solid #cbd5e1; font-size: 0.85rem; background-color: #fff;">
                        <option value="">— Choisir une équipe —</option>
                        @foreach($equipes as $eq)
                            <option value="{{ $eq->id }}" data-chef="{{ $eq->chef->nom_complet ?? 'Aucun chef' }}" data-effectif="{{ $eq->membres->count() + ($eq->chef ? 1 : 0) }}"
                                    {{ old('equipe_id') == $eq->id ? 'selected' : '' }}>
                                {{ $eq->nom_equipe }}
                                @if($eq->chef)
                                    (👑 {{ $eq->chef->nom_complet }})
                                @endif
                                — {{ $eq->membres->count() + ($eq->chef ? 1 : 0) }} personne(s)
                            </option>
                        @endforeach
                    </select>
                    @error('equipe_id')
                        <p style="font-size: 0.72rem; color: #be123c; margin-top: 0.25rem;">{{ $message }}</p>
                    @enderror

                    <div id="equipeInfo" style="display: none; margin-top: 0.6rem; padding: 0.65rem 0.75rem; background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 0.5rem; font-size: 0.78rem;">
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: #166534; font-weight: 700;">👑 Chef:</span>
                            <span id="equipeChef" style="color: #0f172a; font-weight: 600;">—</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-top: 0.2rem;">
                            <span style="color: #166534; font-weight: 700;">👥 Effectif:</span>
                            <span id="equipeEffectif" style="color: #0f172a; font-weight: 600;">—</span>
                        </div>
                    </div>
                </div>

                {{-- RI Soutarah --}}
                <div style="margin-bottom: 1.1rem;">
                    <label style="display: block; font-size: 0.82rem; color: #0f172a; margin-bottom: 0.4rem; font-weight: 800;">
                        <i class="fa-solid fa-barcode" style="color: #2563eb;"></i> N° RI Soutarah / Référence Interne
                    </label>
                    <input type="text" name="ri_soutarah" id="ri_soutarah"
                           value="{{ old('ri_soutarah', $demande->numero_reference_externe) }}"
                           maxlength="50"
                           placeholder="Ex: RI-2026-001"
                           style="width: 100%; padding: 0.7rem 0.85rem; border-radius: 0.55rem; border: 1.5px solid #93c5fd; font-size: 0.9rem; font-weight: 700; color: #1e3a8a; background-color: #f8fafc;">
                    <small style="font-size: 0.72rem; color: #64748b; margin-top: 0.25rem; display: block;">
                        @if($demande->numero_reference_externe)
                            ✅ Pré-rempli avec le N° de référence externe de la demande.
                        @else
                            Optionnel : Numéro de fiche ou référence Soutarah pour le suivi de l'intervention.
                        @endif
                    </small>
                    @error('ri_soutarah')
                        <p style="font-size: 0.72rem; color: #be123c; margin-top: 0.25rem;">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Date prévue (Plage de dates Fixe convenue sur la demande) --}}
                <div style="margin-bottom: 1.1rem;">
                    <label style="display: block; font-size: 0.82rem; color: #0f172a; margin-bottom: 0.4rem; font-weight: 800;">
                        <i class="fa-solid fa-lock" style="color: #2563eb;"></i> Période d'intervention convenue sur la demande (Fixe)
                    </label>
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem;">
                        <div>
                            <label style="display: block; font-size: 0.75rem; color: #64748b; margin-bottom: 0.2rem;">Date de Début</label>
                            <input type="date" name="date_debut_prevue" id="dateDebutPrevue"
                                   value="{{ old('date_debut_prevue', $demande->date_debut_souhaitee?->format('Y-m-d') ?? $demande->date_confirmee_avec_demandeur?->format('Y-m-d')) }}"
                                   readonly
                                   style="width: 100%; padding: 0.65rem 0.75rem; border-radius: 0.55rem; border: 1px solid #cbd5e1; font-size: 0.85rem; background-color: #f8fafc; color: #475569; cursor: not-allowed;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.75rem; color: #64748b; margin-bottom: 0.2rem;">Date de Fin</label>
                            <input type="date" name="date_fin_prevue" id="dateFinPrevue"
                                   value="{{ old('date_fin_prevue', $demande->date_debut_souhaitee?->format('Y-m-d')) }}"
                            ✅ Pré-remplie depuis la demande : {{ $demande->date_debut_souhaitee->format('d/m/Y') }}
                        @elseif($demande->date_confirmee_avec_demandeur)
                            ✅ Pré-remplie avec la date convenue avec le demandeur
                        @else
                            À renseigner si possible
                        @endif
                    </small>
                </div>

                {{-- Commentaire --}}
                <div style="margin-bottom: 1.4rem;">
                    <label style="display: block; font-size: 0.82rem; color: #0f172a; margin-bottom: 0.4rem; font-weight: 800;">
                        <i class="fa-solid fa-comment-dots" style="color: #7c3aed;"></i> Notes / Consignes pour l'équipe
                    </label>
                    <textarea name="commentaire_operation" rows="5"
                              placeholder="Ex: Appeler M. X à l'arrivée, Accès par la porte arrière, Prévoir joint d'étanchéité modèle Y..."
                              style="width: 100%; padding: 0.7rem 0.85rem; border-radius: 0.55rem; border: 1px solid #cbd5e1; font-size: 0.82rem; resize: vertical; line-height: 1.5; font-family: inherit;">{{ old('commentaire_operation') }}</textarea>
                    <small style="font-size: 0.72rem; color: #64748b; margin-top: 0.2rem; display: block;">
                        La description originale de la demande sera automatiquement ajoutée en bas du rapport.
                    </small>
                </div>

                {{-- Récapitulatif avant validation --}}
                <div style="background: linear-gradient(135deg, #eff6ff, #f0f9ff); border: 1px solid #bfdbfe; border-radius: 0.6rem; padding: 0.85rem 1rem; margin-bottom: 1.1rem;">
                    <p style="font-size: 0.78rem; color: #1e40af; font-weight: 700; margin-bottom: 0.3rem;">
                        <i class="fa-solid fa-circle-info"></i> Ce que fera le système automatiquement :
                    </p>
                    <ul style="font-size: 0.76rem; color: #1e3a8a; margin: 0; padding-left: 1.1rem; line-height: 1.55;">
                        <li>Créer l'opération technique (dépannage)</li>
                        <li>Ajouter l'équipe assignée et la date</li>
                        <li>Créer automatiquement une <strong style="color:#1d4ed8;">entrée dans le Planning</strong></li>
                        <li>Notifier 🔔 <strong>chef + membres</strong> de l'équipe</li>
                        <li>Notifier 📧 le demandeur et les superviseurs client</li>
                    </ul>
                </div>

                <div style="display: flex; gap: 0.65rem; justify-content: flex-end; flex-wrap: wrap;">
                    <a href="{{ route('operations.index') }}" style="padding: 0.7rem 1.1rem; border-radius: 0.6rem; background-color: #f1f5f9; color: #475569; border: none; font-weight: 700; cursor: pointer; text-decoration: none; font-size: 0.85rem;">
                        <i class="fa-solid fa-xmark"></i> Annuler
                    </a>
                    <button type="submit" class="btn-primary" style="padding: 0.7rem 1.3rem; font-size: 0.85rem; background: linear-gradient(135deg, #059669, #10b981);">
                        <i class="fa-solid fa-rocket"></i> Créer &amp; Affecter l'opération
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Afficher infos équipe sélectionnée
const equipeSelect = document.getElementById('equipeSelect');
const equipeInfo = document.getElementById('equipeInfo');
const equipeChef = document.getElementById('equipeChef');
const equipeEffectif = document.getElementById('equipeEffectif');

equipeSelect.addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    if (opt && opt.value) {
        equipeChef.textContent = opt.dataset.chef || '—';
        equipeEffectif.textContent = (opt.dataset.effectif || '0') + ' personne(s)';
        equipeInfo.style.display = 'block';
    } else {
        equipeInfo.style.display = 'none';
    }
});
// Initialiser si une valeur est pré-sélectionnée (old)
equipeSelect.dispatchEvent(new Event('change'));
</script>
@endsection
