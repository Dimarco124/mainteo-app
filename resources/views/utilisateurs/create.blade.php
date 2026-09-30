@extends('layouts.app')

@section('title', 'Nouveau Compte d\'Accès')

@section('content')
<style>
    .form-grid-2 {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.25rem;
        margin-bottom: 1.5rem;
    }
    @media (max-width: 768px) {
        .form-grid-2 {
            grid-template-columns: 1fr;
        }
    }
</style>
<div class="header">
    <div>
        <a href="{{ route('utilisateurs.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.4rem; margin-bottom: 0.5rem;">
            <i class="fa-solid fa-arrow-left"></i> Retour
        </a>
        <h1>Créer un Nouveau Compte</h1>
        <p style="color: #64748b; font-size: 0.85rem;">Admin, Technicien ou Superviseur de base</p>
    </div>
</div>

<div class="card" style="max-width: 820px;">
    <form action="{{ route('utilisateurs.store') }}" method="POST">
        @csrf

        <!-- 1. Identité & Connexion -->
        <h3 style="font-size: 1rem; font-weight: 800; color: #059669; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-id-card"></i> Informations de Connexion
        </h3>

        <div class="form-grid-2">
            <div>
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Nom *</label>
                <input type="text" name="nom" value="{{ old('nom') }}" required placeholder="ex: NGUEMA" style="width: 100%; padding: 0.75rem;">
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Prénom</label>
                <input type="text" name="prenom" value="{{ old('prenom') }}" placeholder="ex: Jean" style="width: 100%; padding: 0.75rem;">
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Email (Identifiant) *</label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="exemple@email.com" style="width: 100%; padding: 0.75rem;">
                @error('email')
                <span style="color: #be123c; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Mot de Passe *</label>
                <input type="password" name="mot_de_passe" required placeholder="••••••••" style="width: 100%; padding: 0.75rem;">
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Téléphone</label>
                <input type="text" name="telephone" value="{{ old('telephone') }}" placeholder="+237 6XX XXX XXX" style="width: 100%; padding: 0.75rem;">
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Type de Compte *</label>
                <select name="type_utilisateur" id="roleSelect" required style="width: 100%; padding: 0.75rem;">
                    <option value="">-- Sélectionner le type de compte --</option>
                    <optgroup label="🏢 PERSONNEL SOUTARAH (Interne)">
                        <option value="superviseur_soutarah" {{ old('type_utilisateur') == 'superviseur_soutarah' ? 'selected' : '' }}>👔 Superviseur Soutarah - Gère UNE base/entreprise</option>
                        <option value="technicien" {{ old('type_utilisateur') == 'technicien' ? 'selected' : '' }}>🔧 Technicien - Interventions terrain</option>
                    </optgroup>
                    <optgroup label="👥 PERSONNEL CLIENT (Externe)">
                        <option value="superviseur_client" {{ old('type_utilisateur', $selectedBaseId ? 'superviseur_client' : '') == 'superviseur_client' ? 'selected' : '' }}>🔍 Superviseur Client - Valide demandes d'UNE base/entreprise</option>
                        <option value="demandeur" {{ old('type_utilisateur') == 'demandeur' ? 'selected' : '' }}>📝 Demandeur - Crée demandes pour UN site</option>
                    </optgroup>
                </select>
                <div style="font-size: 0.7rem; color: #64748b; margin-top: 0.35rem; line-height: 1.4;">
                    <i class="fa-solid fa-info-circle"></i> Le titre de <strong>Chef Technicien</strong> s'attribue dans le menu <a href="{{ route('equipes.index') }}" style="color: #059669; text-decoration: underline;">Équipes</a> en désignant un technicien comme chef d'équipe.
                </div>
                <div style="font-size: 0.7rem; color: #1e40af; background-color: #dbeafe; padding: 0.5rem; border-radius: 0.5rem; margin-top: 0.5rem; line-height: 1.4;">
                    <i class="fa-solid fa-shield-halved"></i> <strong>Note de sécurité :</strong> Les comptes <strong>Admin</strong> ne peuvent pas être créés via cette interface. Ils doivent être créés manuellement en base de données pour des raisons de sécurité.
                </div>
                @error('type_utilisateur')
                <span style="color: #be123c; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <hr style="border: 0; border-top: 1px solid #e2e8f0; margin-bottom: 1.5rem;">

        <!-- 2. Section Superviseur Client (Base OU Entreprise sans bases) -->
        <div id="sectionSuperviseurClient" style="display: none; background-color: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 0.75rem; padding: 1.25rem; margin-bottom: 1.5rem;">
            <h3 style="font-size: 0.95rem; font-weight: 800; color: #047857; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-map-location-dot"></i> Rattachement Superviseur Client
            </h3>
            <p style="font-size: 0.8rem; color: #065f46; margin-bottom: 1rem;">
                <i class="fa-solid fa-info-circle"></i> Le superviseur client peut être rattaché à <strong>UNE base</strong> (grande entreprise) OU à <strong>UNE entreprise sans bases</strong> (petite entreprise).
            </p>

            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-size: 0.85rem; color: #065f46; margin-bottom: 0.4rem; font-weight: 700;">
                    Type de rattachement * <span style="color: #be123c;">(Obligatoire)</span>
                </label>
                <select name="superviseur_client_type" id="superviseurClientType" style="width: 100%; padding: 0.75rem; background: #fff;" onchange="toggleSuperviseurClientFields()">
                    <option value="">-- Choisir le type --</option>
                    <option value="base" {{ old('superviseur_client_type') == 'base' ? 'selected' : '' }}>Base (grande entreprise avec bases)</option>
                    <option value="client" {{ old('superviseur_client_type') == 'client' ? 'selected' : '' }}>Entreprise directe (petite entreprise sans bases)</option>
                </select>
            </div>

            <!-- Si Base sélectionnée -->
            <div id="superviseurClientBaseField" style="display: none; margin-bottom: 1.25rem;">
                <label style="display: block; font-size: 0.85rem; color: #065f46; margin-bottom: 0.4rem; font-weight: 700;">
                    Base d'Affectation * <span style="color: #be123c;">(Obligatoire)</span>
                </label>
                <select name="base_id" id="baseSelectClient" style="width: 100%; padding: 0.75rem; background: #fff;">
                    <option value="">-- Sélectionner une base --</option>
                    @foreach($bases as $b)
                    <option value="{{ $b->id }}" {{ old('base_id', $selectedBaseId) == $b->id ? 'selected' : '' }}>
                        {{ $b->nom_base }} - {{ $b->client->nom ?? 'Client' }} (Code: {{ $b->code_base }})
                    </option>
                    @endforeach
                </select>
                <small style="color: #065f46; font-size: 0.75rem; margin-top: 0.25rem; display: block;">
                    Plusieurs superviseurs clients peuvent être assignés à une même base.
                </small>
                @error('base_id')
                <span style="color: #be123c; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>

            <!-- Si Entreprise directe sélectionnée -->
            <div id="superviseurClientEntrepriseField" style="display: none; margin-bottom: 1.25rem;">
                <label style="display: block; font-size: 0.85rem; color: #065f46; margin-bottom: 0.4rem; font-weight: 700;">
                    Entreprise (sans bases) * <span style="color: #be123c;">(Obligatoire)</span>
                </label>
                <select name="client_id" id="clientSelectSuperviseur" style="width: 100%; padding: 0.75rem; background: #fff;">
                    <option value="">-- Sélectionner une entreprise --</option>
                    @foreach($clients as $c)
                        @if($c->bases->count() == 0)
                        <option value="{{ $c->id }}" {{ old('client_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->nom }} (Code: {{ $c->code }})
                        </option>
                        @endif
                    @endforeach
                </select>
                <small style="color: #065f46; font-size: 0.75rem; margin-top: 0.25rem; display: block;">
                    Seules les entreprises SANS bases sont listées ici.
                </small>
                @error('client_id')
                <span style="color: #be123c; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- 2B. Section Superviseur Soutarah (Assigné via Assignments) -->
        <div id="sectionSuperviseurSoutarah" style="display: none; background-color: #eff6ff; border: 1px solid #bfdbfe; border-radius: 0.75rem; padding: 1.25rem; margin-bottom: 1.5rem;">
            <h3 style="font-size: 0.95rem; font-weight: 800; color: #1e40af; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-user-tie"></i> Superviseur Soutarah - Assignation
            </h3>
            
            <div style="background-color: #dbeafe; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
                <p style="font-size: 0.85rem; color: #1e3a8a; margin-bottom: 0.5rem; font-weight: 600;">
                    <i class="fa-solid fa-info-circle"></i> Rôle du Superviseur Soutarah
                </p>
                <ul style="font-size: 0.8rem; color: #1e40af; line-height: 1.6; margin: 0; padding-left: 1.25rem;">
                    <li>Possède les <strong>mêmes interfaces que l'admin</strong></li>
                    <li>Voit et gère <strong>uniquement les données de SA base/company assignée</strong></li>
                    <li>Ne peut pas accéder aux données des autres bases</li>
                    <li>Aide l'admin à distribuer la charge de travail</li>
                </ul>
            </div>

            <div style="background-color: #fef3c7; border: 1px solid #fde68a; border-radius: 0.5rem; padding: 0.85rem;">
                <strong style="color: #92400e; font-size: 0.85rem; display: block; margin-bottom: 0.35rem;">
                    <i class="fa-solid fa-link"></i> Assignation à une Base ou Company
                </strong>
                <p style="font-size: 0.8rem; color: #78350f; margin: 0;">
                    Après création du compte, allez dans le menu <a href="{{ route('assignments.index') }}" style="color: #059669; text-decoration: underline; font-weight: 700;">Assignments</a> pour assigner ce superviseur à :
                </p>
                <ul style="font-size: 0.8rem; color: #78350f; margin: 0.5rem 0 0 1.25rem; padding: 0;">
                    <li><strong>UNE base</strong> (si l'entreprise cliente a plusieurs bases)</li>
                    <li><strong>UNE company</strong> (si l'entreprise cliente n'a pas de bases)</li>
                </ul>
            </div>
        </div>

        <!-- 2C. Section Demandeur (Sites multiples) -->
        <div id="sectionDemandeur" style="display: none; background-color: #fef3c7; border: 1px solid #fde68a; border-radius: 0.75rem; padding: 1.25rem; margin-bottom: 1.5rem;">
            <h3 style="font-size: 0.95rem; font-weight: 800; color: #92400e; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-map-pin"></i> Rattachement aux Sites (Demandeur)
            </h3>
            <p style="font-size: 0.8rem; color: #78350f; margin-bottom: 1rem;">
                <i class="fa-solid fa-info-circle"></i> Sélectionnez UN ou PLUSIEURS sites pour ce demandeur. Il pourra créer des demandes uniquement pour ces sites.
            </p>

            <div>
                <label style="display: block; font-size: 0.85rem; color: #78350f; margin-bottom: 0.4rem; font-weight: 700;">
                    Sites d'Affectation * <span style="color: #be123c;">(Au moins 1 site requis)</span>
                </label>
                <select name="site_ids[]" id="siteSelectMultiple" multiple style="width: 100%; min-height: 200px; padding: 0.75rem; background: #fff;">
                    @foreach($sites as $site)
                    <option value="{{ $site->id }}" {{ is_array(old('site_ids')) && in_array($site->id, old('site_ids')) ? 'selected' : '' }}>
                        {{ $site->nom_site }} - {{ $site->baseSite ? $site->baseSite->nom_base : 'N/A' }} ({{ $site->baseSite && $site->baseSite->client ? $site->baseSite->client->nom : 'Client' }})
                    </option>
                    @endforeach
                </select>
                <small style="color: #78350f; font-size: 0.75rem; display: block; margin-top: 0.5rem;">
                    💡 Maintenez <strong>Ctrl</strong> (Windows) ou <strong>Cmd</strong> (Mac) pour sélectionner plusieurs sites
                </small>
                @error('site_ids')
                <span style="color: #be123c; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- 3. Section Technicien -->
        <div id="sectionTechnicien" style="display: none; background-color: #fffbeb; border: 1px solid #fde68a; border-radius: 0.75rem; padding: 1.25rem; margin-bottom: 1.5rem;">
            <h3 style="font-size: 0.95rem; font-weight: 800; color: #b45309; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-hard-hat"></i> Informations Technicien
            </h3>

            <div class="form-grid-2" style="margin-bottom: 0;">
                <div>
                    <label style="display: block; font-size: 0.85rem; color: #b45309; margin-bottom: 0.4rem; font-weight: 700;">Spécialité</label>
                    <input type="text" name="specialite" value="{{ old('specialite') }}" placeholder="ex: Frigoriste CVC" style="width: 100%; padding: 0.75rem; background: #fff;">
                </div>

                <div>
                    <label style="display: block; font-size: 0.85rem; color: #b45309; margin-bottom: 0.4rem; font-weight: 700;">Équipe (optionnel)</label>
                    <select name="equipe_id" style="width: 100%; padding: 0.75rem; background: #fff;">
                        <option value="">Aucune équipe</option>
                        @foreach($equipes as $eq)
                        <option value="{{ $eq->id }}" {{ old('equipe_id') == $eq->id ? 'selected' : '' }}>{{ $eq->nom_equipe }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; font-size: 1rem; padding: 0.85rem;">
            <i class="fa-solid fa-check-circle"></i> Créer le Compte
        </button>
    </form>
</div>

<!-- Script JS : Affiche la section selon le rôle -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const roleSelect = document.getElementById('roleSelect');
        const secSuperviseurClient = document.getElementById('sectionSuperviseurClient');
        const secSuperviseurSoutarah = document.getElementById('sectionSuperviseurSoutarah');
        const secDemandeur = document.getElementById('sectionDemandeur');
        const secTech = document.getElementById('sectionTechnicien');

        function toggleSections() {
            const role = roleSelect.value;

            // Cacher toutes les sections
            secSuperviseurClient.style.display = 'none';
            secSuperviseurSoutarah.style.display = 'none';
            secDemandeur.style.display = 'none';
            secTech.style.display = 'none';

            // Afficher selon le rôle
            if (role === 'superviseur_client') {
                secSuperviseurClient.style.display = 'block';
                // Initialiser les champs superviseur client si nécessaire
                toggleSuperviseurClientFields();
            } else if (role === 'superviseur_soutarah') {
                secSuperviseurSoutarah.style.display = 'block';
            } else if (role === 'demandeur') {
                secDemandeur.style.display = 'block';
            } else if (role === 'technicien') {
                secTech.style.display = 'block';
            }
        }

        roleSelect.addEventListener('change', toggleSections);

        // Au chargement
        toggleSections();
    });

    // Fonction pour toggle entre Base et Entreprise directe (Superviseur Client)
    function toggleSuperviseurClientFields() {
        const typeSelect = document.getElementById('superviseurClientType');
        const baseField = document.getElementById('superviseurClientBaseField');
        const entrepriseField = document.getElementById('superviseurClientEntrepriseField');
        
        if (!typeSelect) return;
        
        const type = typeSelect.value;
        
        // Cacher tous les champs
        baseField.style.display = 'none';
        entrepriseField.style.display = 'none';
        
        // Afficher selon le type
        if (type === 'base') {
            baseField.style.display = 'block';
        } else if (type === 'client') {
            entrepriseField.style.display = 'block';
        }
    }
</script>
@endsection