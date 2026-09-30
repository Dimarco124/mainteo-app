{{-- ═══════════════════════════════════════════════════════════════════
     SECTION: CONFIRMATION PASSAGE TECHNICIENS (Demandeur)
     ═══════════════════════════════════════════════════════════════════ --}}

@if(Auth::user()->isDemandeur() && Auth::user()->id == $demande->created_by_user_id && $demande->technicalOperation && $demande->technicalOperation->statut === 'résolu' && !$demande->techniciens_sont_passes)
<div class="card" style="border: 2px solid #0ea5e9; background: linear-gradient(135deg, #f0f9ff, #e0f2fe);">
    <div class="card-header" style="background: transparent; border-bottom: 1px solid #bae6fd;">
        <h3 class="card-title" style="color: #0369a1;">
            <i class="fa-solid fa-clipboard-check"></i> Confirmer le Passage des Techniciens
        </h3>
        <p style="margin-top: 0.5rem; color: #0369a1; font-size: 0.85rem;">
            Veuillez confirmer que les techniciens sont bien passés et donner votre avis sur l'intervention
        </p>
    </div>

    <form action="{{ route('demandes.confirmerPassageTechniciens', $demande) }}" method="POST">
        @csrf

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-size: 0.9rem; color: #0369a1; margin-bottom: 0.75rem; font-weight: 700;">
                <i class="fa-solid fa-user-check"></i> Les techniciens sont-ils passés? *
            </label>
            <div style="display: flex; gap: 1rem;">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; padding: 0.75rem 1.25rem; background: #ffffff; border: 2px solid #bae6fd; border-radius: 0.75rem;">
                    <input type="radio" name="techniciens_sont_passes" value="1" required>
                    <span style="color: #047857; font-weight: 700;">Oui, ils sont passés</span>
                </label>
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; padding: 0.75rem 1.25rem; background: #ffffff; border: 2px solid #bae6fd; border-radius: 0.75rem;">
                    <input type="radio" name="techniciens_sont_passes" value="0" required>
                    <span style="color: #be123c; font-weight: 700;">Non, personne n'est venu</span>
                </label>
            </div>
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-size: 0.9rem; color: #0369a1; margin-bottom: 0.75rem; font-weight: 700;">
                <i class="fa-solid fa-star"></i> Résultat de l'Intervention *
            </label>
            <select name="resultat_intervention" required style="width: 100%; padding: 0.85rem; font-size: 0.9rem; border: 2px solid #bae6fd; border-radius: 0.75rem;">
                <option value="">-- Sélectionner --</option>
                <option value="satisfaisant">✅ Satisfaisant - Le problème est résolu</option>
                <option value="partiellement_satisfaisant">⚠️ Partiellement Satisfaisant - Quelques réserves</option>
                <option value="non_satisfaisant">❌ Non Satisfaisant - Le problème persiste</option>
            </select>
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">
                Commentaire / Remarques
            </label>
            <textarea name="commentaire_resultat" rows="4" placeholder="Décrivez votre expérience, les travaux effectués, votre satisfaction..." 
                      style="width: 100%; padding: 0.75rem; border: 1px solid #bae6fd; border-radius: 0.75rem;"></textarea>
        </div>

        <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 0.75rem; padding: 1rem; margin-bottom: 1.5rem;">
            <p style="color: #b45309; font-size: 0.85rem; margin: 0;">
                <i class="fa-solid fa-info-circle"></i>
                <strong>Info:</strong> Votre confirmation sera envoyée à votre superviseur pour validation finale.
            </p>
        </div>

        <button type="submit" class="btn-primary" style="padding: 0.85rem 1.5rem; font-size: 0.95rem;">
            <i class="fa-solid fa-check-circle"></i> Confirmer et Envoyer
        </button>
    </form>
</div>
@endif

{{-- Confirmation déjà envoyée --}}
@if($demande->techniciens_sont_passes !== null && $demande->date_confirmation_passage)
<div class="card" style="border: 2px solid #0ea5e9;">
    <div class="card-header" style="background: #f0f9ff;">
        <h3 class="card-title" style="color: #0369a1;">
            <i class="fa-solid fa-check-circle"></i> Confirmation du Demandeur
        </h3>
        <p style="margin-top: 0.5rem; color: #0369a1; font-size: 0.85rem;">
            Confirmé le {{ \Carbon\Carbon::parse($demande->date_confirmation_passage)->format('d/m/Y à H:i') }}
        </p>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1rem;">
        <div>
            <h4 style="font-size: 0.9rem; color: #0369a1; margin-bottom: 0.5rem; font-weight: 700;">Passage des Techniciens:</h4>
            <p style="font-size: 1rem; font-weight: 700; color: {{ $demande->techniciens_sont_passes ? '#047857' : '#be123c' }};">
                @if($demande->techniciens_sont_passes)
                    <i class="fa-solid fa-check"></i> Oui, ils sont passés
                @else
                    <i class="fa-solid fa-times"></i> Non, personne n'est venu
                @endif
            </p>
        </div>

        <div>
            <h4 style="font-size: 0.9rem; color: #0369a1; margin-bottom: 0.5rem; font-weight: 700;">Résultat:</h4>
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
    </div>

    @if($demande->commentaire_resultat)
    <div style="background: #f0f9ff; padding: 1rem; border-radius: 0.75rem; border: 1px solid #bae6fd;">
        <h4 style="font-size: 0.9rem; color: #0369a1; margin-bottom: 0.5rem; font-weight: 700;">Commentaire:</h4>
        <p style="color: #0f172a; line-height: 1.6; margin: 0;">{{ $demande->commentaire_resultat }}</p>
    </div>
    @endif
</div>
@endif
