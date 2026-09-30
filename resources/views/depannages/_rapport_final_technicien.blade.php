{{-- ═══════════════════════════════════════════════════════════════════
     SECTION: RAPPORT FINAL TECHNICIEN (Avec photos)
     Visible pour Admin, Superviseurs et Technicien après soumission
     ═══════════════════════════════════════════════════════════════════ --}}

{{-- Rapport déjà soumis - Affichage --}}
@if($depannage->statut_rapport_technicien === 'soumis' || $depannage->statut_rapport_technicien === 'transmis_client')
@php
$statutTerrainConfig = [
'resolu' => ['emoji' => '✅', 'label' => 'Résolu & Terminé', 'bg' => '#f0fdf4', 'border' => '#059669', 'color' => '#047857'],
'en_attente_piece' => ['emoji' => '📦', 'label' => 'En attente de pièce de rechange', 'bg' => '#eff6ff', 'border' => '#1d4ed8', 'color' => '#1e40af'],
'partiellement_resolu' => ['emoji' => '⚠️', 'label' => 'Partiellement résolu (Autre passage requis)', 'bg' => '#fffbeb', 'border' => '#b45309', 'color' => '#92400e'],
'non_resolu' => ['emoji' => '⛔', 'label' => 'Non résolu / Bloqué', 'bg' => '#fef2f2', 'border' => '#dc2626', 'color' => '#b91c1c'],
// anciens statuts (compatibilité)
'résolu' => ['emoji' => '✅', 'label' => 'Résolu & Terminé', 'bg' => '#f0fdf4', 'border' => '#059669', 'color' => '#047857'],
'en cours' => ['emoji' => '⚙️', 'label' => 'En cours', 'bg' => '#f0f9ff', 'border' => '#0ea5e9', 'color' => '#0369a1'],
];
$stConf = $statutTerrainConfig[$depannage->statut] ?? ['emoji' => 'ℹ️', 'label' => $depannage->statut, 'bg' => '#f8fafc', 'border' => '#94a3b8', 'color' => '#475569'];
$isBlockingStatut = in_array($depannage->statut, ['en_attente_piece', 'non_resolu']);
@endphp
<div class="card" style="border: 2px solid {{ $stConf['border'] }};">
    <div class="card-header" style="background: {{ $stConf['bg'] }};">
        <h3 class="card-title" style="color: {{ $stConf['color'] }};">
            <i class="fa-solid fa-check-circle"></i> Rapport Final Technicien
        </h3>
        @if($depannage->date_rapport_technicien)
        <p style="margin-top: 0.5rem; color: {{ $stConf['color'] }}; font-size: 0.85rem;">
            Soumis le {{ \Carbon\Carbon::parse($depannage->date_rapport_technicien)->format('d/m/Y à H:i') }}
            par {{ $depannage->rapportSoumisPar ? $depannage->rapportSoumisPar->nom_complet : 'N/A' }}
        </p>
        @endif

        @if($depannage->ri_soutarah)
        <div style="margin-top: 1rem; padding: 0.75rem 1rem; background: #eff6ff; border: 2px solid #3b82f6; border-radius: 0.5rem; display: inline-flex; align-items: center; gap: 0.6rem;">
            <span style="font-size: 1.15rem;">🔖</span>
            <div>
                <span style="font-size: 0.72rem; color: #1d4ed8; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700;">RI Soutarah</span>
                <div style="font-size: 1.05rem; font-weight: 800; color: #1e3a8a; letter-spacing: 0.02em;">{{ $depannage->ri_soutarah }}</div>
            </div>
        </div>
        @else
        <div style="margin-top: 1rem; padding: 0.75rem 1rem; background: #fff7ed; border: 2px dashed #fdba74; border-radius: 0.5rem; display: inline-flex; align-items: center; gap: 0.6rem;">
            <span style="font-size: 1.15rem;">⚠️</span>
            <div>
                <span style="font-size: 0.72rem; color: #9a3412; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700;">RI Soutarah</span>
                <div style="font-size: 0.95rem; font-weight: 800; color: #92400e; text-transform: uppercase;">Non renseigné</div>
            </div>
        </div>
        @endif
    </div>

    {{-- Badge statut terrain --}}
    <div style="margin: 1rem 0 1.25rem 0; padding: 0.9rem 1.2rem; background: {{ $stConf['bg'] }}; border: 2px solid {{ $stConf['border'] }}; border-radius: 0.75rem; display: flex; align-items: center; gap: 1rem;">
        <span style="font-size: 1.6rem;">{{ $stConf['emoji'] }}</span>
        <div>
            <div style="font-size: 0.78rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700;">Statut terrain</div>
            <div style="font-size: 1.05rem; font-weight: 700; color: {{ $stConf['color'] }};">{{ $stConf['label'] }}</div>
        </div>
    </div>

    {{-- Détails du statut terrain (si renseignés) --}}
    @if($depannage->details_statut_terrain)
    <div style="background: #fff7ed; border: 1.5px solid #fed7aa; border-radius: 0.75rem; padding: 1rem; margin-bottom: 1.25rem;">
        <p style="font-size: 0.85rem; color: #9a3412; font-weight: 700; margin: 0 0 0.4rem 0;">
            <i class="fa-solid fa-triangle-exclamation"></i> Détails du technicien
        </p>
        <p style="color: #431407; line-height: 1.6; margin: 0; white-space: pre-wrap;">{{ $depannage->details_statut_terrain }}</p>
    </div>
    @endif

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1rem;">
        <!-- Photo rapport d'intervention -->
        <div>
            <h4 style="font-size: 0.9rem; color: #047857; margin-bottom: 0.75rem; font-weight: 700;">
                <i class="fa-solid fa-file-contract"></i> Photo du Rapport d'Intervention
            </h4>
            @if($depannage->photo_carnet_rapport)
            <img src="{{ asset($depannage->photo_carnet_rapport) }}" alt="Photo du rapport d'intervention"
                style="width: 100%; border-radius: 0.75rem; border: 2px solid #a7f3d0; cursor: pointer;"
                onclick="window.open('{{ asset($depannage->photo_carnet_rapport) }}', '_blank')">
            @else
            <p style="color: #94a3b8;">Aucune photo</p>
            @endif
        </div>

        <!-- Photo équipement -->
        <div>
            <h4 style="font-size: 0.9rem; color: #047857; margin-bottom: 0.75rem; font-weight: 700;">
                <i class="fa-solid fa-tools"></i> Photo de l'Équipement
            </h4>
            @if($depannage->photo_equipement_apres)
            <img src="{{ asset($depannage->photo_equipement_apres) }}" alt="Équipement réparé"
                style="width: 100%; border-radius: 0.75rem; border: 2px solid #a7f3d0; cursor: pointer;"
                onclick="window.open('{{ asset($depannage->photo_equipement_apres) }}', '_blank')">
            @else
            <p style="color: #94a3b8;">Aucune photo</p>
            @endif
        </div>
    </div>

    @if($depannage->rapport)
    <div style="background: #f0fdf4; padding: 1rem; border-radius: 0.75rem; border: 1px solid #a7f3d0;">
        <h4 style="font-size: 0.9rem; color: #047857; margin-bottom: 0.5rem; font-weight: 700;">Compte-Rendu Détaillé:</h4>
        <p style="color: #0f172a; line-height: 1.6; margin: 0; white-space: pre-wrap;">{{ $depannage->rapport }}</p>
    </div>
    @endif

    @if($depannage->statut_rapport_technicien === 'transmis_client')
    <div style="background: #dbeafe; border: 1px solid #bae6fd; border-radius: 0.75rem; padding: 1rem; margin-top: 1rem;">
        <p style="color: #0369a1; font-size: 0.85rem; margin: 0;">
            <i class="fa-solid fa-check-double"></i>
            <strong>Statut:</strong> Rapport validé par Soutarah et transmis au superviseur client
            le {{ $depannage->date_transmission_rapport_client ? \Carbon\Carbon::parse($depannage->date_transmission_rapport_client)->format('d/m/Y à H:i') : '' }}
        </p>
    </div>
    @endif
</div>
@endif

{{-- Section Examen Superviseur Soutarah / Admin --}}
@if((Auth::user()->isAdmin() || Auth::user()->isSuperviseurSoutarah()) && $depannage->statut_rapport_technicien === 'soumis')
@if(in_array($depannage->statut, ['resolu', 'résolu']))
{{-- CAS 1 : Intervention RÉSOLUE & TERMINÉE -- Soutarah peut Valider & Transmettre au Client --}}
<div class="card" style="margin-bottom: 1.5rem; border: 2px solid #059669;">
    <div class="card-header" style="background: #f0fdf4;">
        <h3 class="card-title" style="color: #047857;">
            <i class="fa-solid fa-user-check"></i> Examen du Rapport Technicien (Soutarah / Admin)
        </h3>
        <p style="margin-top: 0.5rem; color: #047857; font-size: 0.85rem;">
            L'intervention est résolue. Vérifiez le compte-rendu et les photos transmises. Validez pour transmettre au client ou rejetez si des corrections sont requises.
        </p>
    </div>

    @if($depannage->statut_validation_finale_client === 'non_conforme')
    <div style="background: #fef2f2; border: 1px solid #fecdd3; border-radius: 0.5rem; padding: 0.85rem 1rem; margin: 1rem 1.25rem 0 1.25rem;">
        <p style="color: #9f1239; font-weight: 700; font-size: 0.85rem; margin: 0 0 0.25rem 0; display: flex; align-items: center; gap: 0.4rem;">
            <i class="fa-solid fa-triangle-exclamation" style="color: #e11d48;"></i> Rapport refusé par le Superviseur Client
        </p>
        <p style="color: #334155; font-size: 0.85rem; margin: 0; line-height: 1.5;">
            <strong>Motif indiqué par le client :</strong> {{ $depannage->raison_rejet_rapport }}
        </p>
        <p style="color: #64748b; font-size: 0.8rem; margin-top: 0.4rem; margin-bottom: 0;">
            Vous pouvez rejeter ce rapport au technicien ci-dessous pour qu'il procède aux corrections requises.
        </p>
    </div>
    @endif

    <div style="display: flex; gap: 1rem; padding: 1.25rem; flex-wrap: wrap;">
        <form action="{{ route('depannages.transmettreRapportClient', $depannage->id) }}" method="POST" style="flex: 1; min-width: 220px;">
            @csrf
            <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; padding: 0.75rem; font-size: 0.9rem; font-weight: 600;">
                <i class="fa-solid fa-check"></i> Valider et Transmettre au Client
            </button>
        </form>

        <button type="button" onclick="openRejectRapportModal()" style="flex: 1; min-width: 220px; background-color: #dc2626; color: white; border: none; border-radius: 0.375rem; font-weight: 600; cursor: pointer; padding: 0.75rem; font-size: 0.9rem; display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;">
            <i class="fa-solid fa-rotate-left"></i> Rejeter au Technicien (Correction)
        </button>
    </div>
</div>
@else
{{-- CAS 2 : Intervention NON TERMINÉE (En attente de pièce, Partiellement résolu, Non résolu) --}}
{{-- PAS DE BOUTON VALIDER ET TRANSMETTRE AU CLIENT car l'intervention n'est pas résolue ! --}}
<div class="card" style="margin-bottom: 1.5rem; border: 2px solid #f59e0b; background: #fffbeb;">
    <div class="card-header" style="background: #fef3c7;">
        <h3 class="card-title" style="color: #b45309;">
            <i class="fa-solid fa-circle-info"></i> Rapport Intermédiaire Technicien — Suivi Soutarah
        </h3>
        <p style="margin-top: 0.35rem; color: #92400e; font-size: 0.85rem;">
            Le technicien a soumis un statut intermédiaire. L'intervention ne peut pas être transmise au client tant qu'elle n'est pas clôturée en statut <strong>Résolu & Terminé</strong>.
        </p>
    </div>
    <div style="padding: 1.25rem;">
        <div style="background: #ffffff; border: 1.5px solid #fde68a; border-radius: 0.75rem; padding: 1.25rem; margin-bottom: 1rem;">
            <div style="display: flex; align-items: flex-start; gap: 1rem;">
                <span style="font-size: 1.8rem; flex-shrink: 0;">
                    @if($depannage->statut === 'non_resolu') ⛔
                    @elseif($depannage->statut === 'en_attente_piece') 📦
                    @else ⚠️
                    @endif
                </span>
                <div>
                    <div style="font-size: 0.95rem; font-weight: 700; color: #92400e; margin-bottom: 0.3rem;">
                        Action requise par Soutarah :
                    </div>
                    @if($depannage->statut === 'non_resolu')
                    <p style="color: #78350f; font-size: 0.88rem; margin: 0; line-height: 1.6;">
                        Le technicien signale un blocage sur le terrain. Analysez la situation avec lui (accès, sécurité, outillage) et planifiez un autre passage.
                    </p>
                    @elseif($depannage->statut === 'en_attente_piece')
                    <p style="color: #78350f; font-size: 0.88rem; margin: 0; line-height: 1.6;">
                        Le technicien attend des pièces de rechange. Commandez les pièces nécessaires puis le technicien fera un autre passage pour finaliser.
                    </p>
                    @else
                    <p style="color: #78350f; font-size: 0.88rem; margin: 0; line-height: 1.6;">
                        Dépannage temporaire effectué. Un autre passage est nécessaire pour le remplacement final de la pièce ou le réglage définitif.
                    </p>
                    @endif
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 1rem; justify-content: flex-end;">
            <button type="button" onclick="openRejectRapportModal()" style="background-color: #dc2626; color: white; border: none; border-radius: 0.375rem; font-weight: 600; cursor: pointer; padding: 0.65rem 1.25rem; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-rotate-left"></i> Rejeter le Rapport au Technicien (Demande de correction)
            </button>
        </div>
    </div>
</div>
@endif
@endif

<!-- Modal Rejet du Rapport Technicien -->
<div id="rejectRapportModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.5); backdrop-filter: blur(4px); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #ffffff; border-radius: 0.75rem; max-width: 500px; width: 100%; padding: 1.5rem; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
        <h3 style="font-size: 1.05rem; font-weight: 700; color: #9f1239; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-rotate-left"></i> Rejeter le Rapport au Technicien
        </h3>
        <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 1.25rem;">
            Indiquez le motif du rejet et les instructions pour le technicien. Il recevra une notification et pourra corriger son rapport.
        </p>

        <form action="{{ route('depannages.rejeterRapportTechnicien', $depannage->id) }}" method="POST">
            @csrf
            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-size: 0.85rem; color: #334155; margin-bottom: 0.4rem; font-weight: 600;">
                    Motif du rejet / Instructions pour le technicien <span style="color: #dc2626;">*</span>
                </label>
                <textarea name="raison_rejet" rows="4" required class="form-control" style="width: 100%; padding: 0.6rem; border: 1px solid #cbd5e1; border-radius: 0.375rem; font-size: 0.85rem;" placeholder="Ex: La photo du rapport est illisible. Merci de reprendre une photo nette et de compléter le détail des pièces remplacées."></textarea>
            </div>

            <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                <button type="button" onclick="closeRejectRapportModal()" style="padding: 0.6rem 1.1rem; border-radius: 0.375rem; background-color: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; font-weight: 600; cursor: pointer;">
                    Annuler
                </button>
                <button type="submit" style="background-color: #dc2626; color: #ffffff; padding: 0.6rem 1.1rem; border-radius: 0.375rem; font-weight: 600; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 0.4rem;">
                    <i class="fa-solid fa-paper-plane"></i> Transmettre le Rejet
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openRejectRapportModal() {
        document.getElementById('rejectRapportModal').style.display = 'flex';
    }

    function closeRejectRapportModal() {
        document.getElementById('rejectRapportModal').style.display = 'none';
    }
</script>

{{-- ═══════════════════════════════════════════════════════════════════
     VALIDATION FINALE PAR LE SUPERVISEUR CLIENT
     Le superviseur client valide le rapport et clôture l'intervention
     ═══════════════════════════════════════════════════════════════════ --}}
{{-- Alerte si le rapport a été rejeté/refusé --}}
@if($depannage->statut_rapport_technicien === 'rejete' || $depannage->statut_validation_finale_client === 'non_conforme')
<div class="card" style="background-color: #fef2f2; border: 1px solid #fecdd3; margin-bottom: 1.5rem;">
    <div style="padding: 1.25rem;">
        <h4 style="color: #9f1239; margin-top: 0; font-size: 1rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-circle-xmark" style="color: #e11d48;"></i> Rapport d'Intervention Refusé / À Corriger
        </h4>
        <div style="background: #ffffff; padding: 0.85rem 1rem; border-radius: 0.5rem; border: 1px solid #fecdd3; margin-top: 0.75rem;">
            <strong style="color: #9f1239; font-size: 0.85rem; display: block; margin-bottom: 0.25rem;">Motif du refus :</strong>
            <p style="color: #334155; margin: 0; font-size: 0.9rem; line-height: 1.6;">
                {{ $depannage->raison_rejet_rapport ?? $depannage->commentaire_validation_client }}
            </p>
        </div>
        @if($depannage->date_rejet_rapport || $depannage->date_validation_finale_client)
        <p style="color: #9f1239; font-size: 0.8rem; margin-top: 0.6rem; margin-bottom: 0;">
            <i class="fa-solid fa-clock"></i> Refusé le {{ \Carbon\Carbon::parse($depannage->date_rejet_rapport ?? $depannage->date_validation_finale_client)->format('d/m/Y à H:i') }}
        </p>
        @endif
    </div>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════════════════
     ACTION SUPERVISEUR CLIENT : Valider OU Refuser le rapport
     ═══════════════════════════════════════════════════════════════════ --}}
@if(Auth::user()->isSuperviseurClient() && $depannage->statut_rapport_technicien === 'transmis_client' && $depannage->statut_validation_finale_client === 'en_attente')
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fa-solid fa-clipboard-check"></i> Décision du Superviseur Client sur le Rapport
        </h3>
    </div>

    <div style="padding: 1.5rem;">
        <p style="color: #475569; font-size: 0.9rem; line-height: 1.6; margin-top: 0; margin-bottom: 1.25rem;">
            Veuillez examiner les détails du rapport et les photos justificatives. Vous pouvez soit <strong>valider et clôturer</strong> l'intervention, soit la <strong>refuser</strong> afin de demander des corrections.
        </p>

        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
            {{-- OPTION 1 : VALIDER ET CLÔTURER --}}
            <div style="background: #f8fafc; padding: 1.25rem; border-radius: 0.5rem; border: 1px solid #e2e8f0;">
                <h4 style="color: #059669; font-size: 0.95rem; margin-top: 0; margin-bottom: 1rem; font-weight: 700;">
                    <i class="fa-solid fa-circle-check"></i> Valider et Clôturer l'Intervention
                </h4>
                <form action="{{ route('depannages.cloturer', $depannage->id) }}" method="POST">
                    @csrf
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; font-size: 0.85rem; color: #334155; margin-bottom: 0.4rem; font-weight: 600;">
                            Commentaire de validation (Optionnel)
                        </label>
                        <textarea name="commentaire_validation" rows="3" class="form-control"
                            placeholder="Remarques éventuelles sur la qualité des travaux..."
                            style="width: 100%; padding: 0.6rem; border: 1px solid #cbd5e1; border-radius: 0.375rem; font-size: 0.85rem;"></textarea>
                    </div>
                    <button type="submit" class="btn-primary"
                        style="width: 100%; justify-content: center; padding: 0.75rem; font-size: 0.9rem; font-weight: 600;">
                        <i class="fa-solid fa-check"></i> Valider et Clôturer
                    </button>
                </form>
            </div>

            {{-- OPTION 2 : REFUSER LE RAPPORT --}}
            <div style="background: #f8fafc; padding: 1.25rem; border-radius: 0.5rem; border: 1px solid #e2e8f0;">
                <h4 style="color: #dc2626; font-size: 0.95rem; margin-top: 0; margin-bottom: 1rem; font-weight: 700;">
                    <i class="fa-solid fa-circle-xmark"></i> Refuser et Demander Correction
                </h4>
                <form action="{{ route('depannages.rejeterRapportClient', $depannage->id) }}" method="POST">
                    @csrf
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; font-size: 0.85rem; color: #334155; margin-bottom: 0.4rem; font-weight: 600;">
                            Motif du refus <span style="color: #dc2626;">*</span>
                        </label>
                        <textarea name="raison_rejet" rows="3" required minlength="5" class="form-control"
                            placeholder="Motif détaillé du refus (ex: photos manquantes, intervention non conforme...)"
                            style="width: 100%; padding: 0.6rem; border: 1px solid #cbd5e1; border-radius: 0.375rem; font-size: 0.85rem;"></textarea>
                    </div>
                    <button type="submit" onclick="return confirm('Êtes-vous sûr de vouloir refuser ce rapport ?')"
                        style="background-color: #dc2626; color: white; border: none; width: 100%; padding: 0.75rem; border-radius: 0.375rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;">
                        <i class="fa-solid fa-rotate-left"></i> Refuser et Renvoyer
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Message si déjà validé et clôturé --}}
@if(in_array($depannage->statut_validation_finale_client, ['validé', 'conforme']))
<div class="card" style="background: linear-gradient(135deg, #f0fdf4, #dcfce7); border: 2px solid #10b981;">
    <div class="card-header" style="background: #ecfdf5;">
        <h3 class="card-title" style="color: #047857;">
            <i class="fa-solid fa-check-circle"></i> Intervention Validée et Clôturée
        </h3>
    </div>

    <div style="padding: 1.25rem;">
        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
            <div style="background: #10b981; color: #fff; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                <i class="fa-solid fa-check"></i>
            </div>
            <div>
                <p style="color: #047857; font-size: 1rem; font-weight: 700; margin: 0;">
                    Intervention clôturée avec succès
                </p>
                <p style="color: #065f46; font-size: 0.85rem; margin: 0;">
                    Validée le {{ $depannage->date_validation_finale_client ? \Carbon\Carbon::parse($depannage->date_validation_finale_client)->format('d/m/Y à H:i') : 'N/A' }}
                    @if($depannage->validatedByClient)
                    par {{ $depannage->validatedByClient->nom_complet }}
                    @endif
                </p>
            </div>
        </div>

        @if($depannage->commentaire_validation_client)
        <div style="background: #f0fdf4; padding: 1rem; border-radius: 0.5rem; border: 1px solid #a7f3d0;">
            <p style="font-size: 0.85rem; color: #047857; font-weight: 700; margin: 0 0 0.5rem 0;">
                <i class="fa-solid fa-comment"></i> Commentaire de validation :
            </p>
            <p style="color: #065f46; line-height: 1.6; margin: 0;">
                {{ $depannage->commentaire_validation_client }}
            </p>
        </div>
        @endif

        @if($depannage->demande)
        <div style="background: #dbeafe; padding: 1rem; border-radius: 0.5rem; margin-top: 1rem;">
            <p style="color: #0369a1; font-size: 0.85rem; margin: 0;">
                <i class="fa-solid fa-link"></i>
                La demande liée <strong>#{{ $depannage->demande->numero_demande }}</strong> a également été clôturée automatiquement.
            </p>
        </div>
        @endif
    </div>
</div>
@endif