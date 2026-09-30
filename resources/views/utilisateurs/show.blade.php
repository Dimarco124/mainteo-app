@extends('layouts.app')

@section('title', 'Détails Utilisateur - ' . $user->nom_complet)

@section('content')
    <div class="header">
        <div>
            <a href="{{ route('utilisateurs.index') }}"
                style="color: #64748b; text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.4rem; margin-bottom: 0.5rem;">
                <i class="fa-solid fa-arrow-left"></i> Retour à la liste
            </a>
            <h1>Détails du Compte : {{ $user->nom_complet }}</h1>
        </div>
        <div style="display: flex; gap: 0.75rem;">
            @if(auth()->user()->type_utilisateur === 'admin')
                <a href="{{ route('utilisateurs.edit', $user->id) }}" class="btn-primary">
                    <i class="fa-solid fa-edit"></i> Modifier
                </a>
                @if($user->type_utilisateur !== 'admin')
                    <form action="{{ route('utilisateurs.destroy', $user->id) }}" method="POST" 
                        onsubmit="return confirm('⚠️ Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible.');"
                        style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger">
                            <i class="fa-solid fa-trash"></i> Supprimer
                        </button>
                    </form>
                @endif
            @endif
        </div>
    </div>

    <!-- Informations Générales -->
    <div class="card">
        <h2 style="font-size: 1.1rem; font-weight: 800; color: #1e293b; margin-bottom: 1.25rem; border-bottom: 2px solid #e2e8f0; padding-bottom: 0.75rem;">
            <i class="fa-solid fa-user"></i> Informations Générales
        </h2>
        
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
            <div>
                <label style="display: block; font-size: 0.75rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.4rem;">
                    Nom
                </label>
                <p style="font-size: 1rem; color: #1e293b; font-weight: 600; margin: 0;">{{ $user->nom }}</p>
            </div>

            <div>
                <label style="display: block; font-size: 0.75rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.4rem;">
                    Prénom
                </label>
                <p style="font-size: 1rem; color: #1e293b; font-weight: 600; margin: 0;">{{ $user->prenom ?? 'N/A' }}</p>
            </div>

            <div>
                <label style="display: block; font-size: 0.75rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.4rem;">
                    Adresse E-mail
                </label>
                <p style="font-size: 1rem; color: #1e293b; font-weight: 600; margin: 0;">
                    <i class="fa-solid fa-envelope" style="color: #059669;"></i> {{ $user->email }}
                </p>
            </div>

            <div>
                <label style="display: block; font-size: 0.75rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.4rem;">
                    Téléphone
                </label>
                <p style="font-size: 1rem; color: #1e293b; font-weight: 600; margin: 0;">
                    @if($user->telephone)
                        <i class="fa-solid fa-phone" style="color: #059669;"></i> {{ $user->telephone }}
                    @else
                        <span style="color: #94a3b8;">Non renseigné</span>
                    @endif
                </p>
            </div>
        </div>
    </div>

    <!-- Type de Compte et Rôle -->
    <div class="card">
        <h2 style="font-size: 1.1rem; font-weight: 800; color: #1e293b; margin-bottom: 1.25rem; border-bottom: 2px solid #e2e8f0; padding-bottom: 0.75rem;">
            <i class="fa-solid fa-id-badge"></i> Type de Compte et Rôle
        </h2>

        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
            <div>
                <label style="display: block; font-size: 0.75rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.4rem;">
                    Type de Compte
                </label>
                @if($user->type_utilisateur === 'admin')
                    <span style="background-color: #dbeafe; color: #1e40af; padding: 0.5rem 1rem; border-radius: 0.5rem; font-size: 0.9rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.5rem;">
                        <i class="fa-solid fa-crown"></i> Admin - Accès Total
                    </span>
                @elseif($user->type_utilisateur === 'superviseur_soutarah')
                    <span style="background-color: #eff6ff; color: #1e40af; padding: 0.5rem 1rem; border-radius: 0.5rem; font-size: 0.9rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.5rem;">
                        <i class="fa-solid fa-user-tie"></i> Superviseur Soutarah
                    </span>
                @elseif($user->type_utilisateur === 'superviseur_client')
                    <span style="background-color: #ecfdf5; color: #047857; padding: 0.5rem 1rem; border-radius: 0.5rem; font-size: 0.9rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.5rem;">
                        <i class="fa-solid fa-map-location-dot"></i> Superviseur Client
                    </span>
                @elseif($user->type_utilisateur === 'demandeur')
                    <span style="background-color: #fef3c7; color: #92400e; padding: 0.5rem 1rem; border-radius: 0.5rem; font-size: 0.9rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.5rem;">
                        <i class="fa-solid fa-clipboard-check"></i> Demandeur
                    </span>
                @elseif($user->type_utilisateur === 'technicien')
                    <span style="background-color: #fffbeb; color: #b45309; padding: 0.5rem 1rem; border-radius: 0.5rem; font-size: 0.9rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.5rem;">
                        <i class="fa-solid fa-hard-hat"></i> Technicien
                    </span>
                @elseif($user->type_utilisateur === 'chef technicien')
                    <span style="background-color: #fffbeb; color: #92400e; padding: 0.5rem 1rem; border-radius: 0.5rem; font-size: 0.9rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.5rem;">
                        <i class="fa-solid fa-user-tie"></i> Chef Technicien (Legacy)
                    </span>
                @endif
            </div>

            <div>
                <label style="display: block; font-size: 0.75rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.4rem;">
                    Statut
                </label>
                @if($user->statut === 'actif')
                    <span style="background-color: #d1fae5; color: #065f46; padding: 0.5rem 1rem; border-radius: 0.5rem; font-size: 0.9rem; font-weight: 700;">
                        <i class="fa-solid fa-circle-check"></i> Actif
                    </span>
                @else
                    <span style="background-color: #fee2e2; color: #991b1b; padding: 0.5rem 1rem; border-radius: 0.5rem; font-size: 0.9rem; font-weight: 700;">
                        <i class="fa-solid fa-circle-xmark"></i> Inactif
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Rattachements selon le rôle -->
    @if($user->type_utilisateur === 'superviseur_client')
        <div class="card" style="background-color: #ecfdf5; border: 1px solid #a7f3d0;">
            <h2 style="font-size: 1.1rem; font-weight: 800; color: #047857; margin-bottom: 1.25rem; border-bottom: 2px solid #a7f3d0; padding-bottom: 0.75rem;">
                <i class="fa-solid fa-map-location-dot"></i> Rattachement Superviseur Client
            </h2>

            @if($user->base_id && $user->baseSite)
                <!-- Rattachement à une BASE (grande entreprise) -->
                <div style="background-color: #dbeafe; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
                    <p style="font-size: 0.85rem; color: #1e40af; font-weight: 700; margin-bottom: 0.5rem;">
                        🏢 Rattaché à une BASE (grande entreprise)
                    </p>
                    <p style="font-size: 0.9rem; color: #1e3a8a; margin: 0;">
                        <strong>Base :</strong> {{ $user->baseSite->nom_base }} (Code: {{ $user->baseSite->code_base }})<br>
                        <strong>Entreprise :</strong> {{ $user->baseSite->client ? $user->baseSite->client->nom : 'N/A' }}
                    </p>
                </div>
            @elseif($user->client_id && $user->client)
                <!-- Rattachement CLIENT DIRECT (sans base) -->
                <div style="background-color: #fef3c7; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
                    <p style="font-size: 0.85rem; color: #92400e; font-weight: 700; margin-bottom: 0.5rem;">
                        🏛️ Rattaché DIRECTEMENT à une ENTREPRISE (sans base)
                    </p>
                    <p style="font-size: 0.9rem; color: #78350f; margin: 0;">
                        <strong>Entreprise :</strong> {{ $user->client->nom }} (Code: {{ $user->client->code }})
                    </p>
                </div>
            @else
                <p style="color: #94a3b8; font-style: italic;">Aucun rattachement défini</p>
            @endif

            <p style="font-size: 0.8rem; color: #065f46; margin: 0;">
                <i class="fa-solid fa-info-circle"></i> Le superviseur client valide les demandes de SA base ou de SON entreprise uniquement.
            </p>
        </div>
    @endif

    @if($user->type_utilisateur === 'superviseur_soutarah')
        <div class="card" style="background-color: #eff6ff; border: 1px solid #bfdbfe;">
            <h2 style="font-size: 1.1rem; font-weight: 800; color: #1e40af; margin-bottom: 1.25rem; border-bottom: 2px solid #bfdbfe; padding-bottom: 0.75rem;">
                <i class="fa-solid fa-user-tie"></i> Assignment Superviseur Soutarah
            </h2>

            @if($user->assignment)
                @if($user->assignment->base)
                    <div style="background-color: #dbeafe; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
                        <p style="font-size: 0.85rem; color: #1e40af; font-weight: 700; margin-bottom: 0.5rem;">
                            📍 Assigné à une BASE
                        </p>
                        <p style="font-size: 0.9rem; color: #1e3a8a; margin: 0;">
                            <strong>Base :</strong> {{ $user->assignment->base->nom_base }}<br>
                            <strong>Entreprise :</strong> {{ $user->assignment->base->client ? $user->assignment->base->client->nom : 'N/A' }}
                        </p>
                    </div>
                @elseif($user->assignment->client)
                    <div style="background-color: #fef3c7; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
                        <p style="font-size: 0.85rem; color: #92400e; font-weight: 700; margin-bottom: 0.5rem;">
                            🏢 Assigné à une ENTREPRISE
                        </p>
                        <p style="font-size: 0.9rem; color: #78350f; margin: 0;">
                            <strong>Entreprise :</strong> {{ $user->assignment->client->nom }}
                        </p>
                    </div>
                @endif
            @else
                <p style="color: #94a3b8; font-style: italic;">Aucune assignation définie</p>
                <p style="font-size: 0.8rem; color: #1e40af; margin-top: 0.75rem;">
                    <i class="fa-solid fa-info-circle"></i> Vous pouvez créer une assignation dans le menu 
                    <a href="{{ route('assignments.index') }}" style="color: #059669; text-decoration: underline;">Assignments</a>.
                </p>
            @endif
        </div>
    @endif

    @if($user->type_utilisateur === 'demandeur')
        <div class="card" style="background-color: #fef3c7; border: 1px solid #fde68a;">
            <h2 style="font-size: 1.1rem; font-weight: 800; color: #92400e; margin-bottom: 1.25rem; border-bottom: 2px solid #fde68a; padding-bottom: 0.75rem;">
                <i class="fa-solid fa-map-pin"></i> Sites Assignés (Demandeur)
            </h2>

            @if($user->sitesAssignes && $user->sitesAssignes->count() > 0)
                <div style="background-color: #fffbeb; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
                    <p style="font-size: 0.85rem; color: #92400e; font-weight: 700; margin-bottom: 0.75rem;">
                        📍 Sites assignés à ce demandeur
                    </p>
                    <ul style="list-style: none; padding: 0; margin: 0;">
                        @foreach($user->sitesAssignes as $site)
                            <li style="padding: 0.5rem 0; border-bottom: 1px solid #fde68a;">
                                <strong>{{ $site->nom_site }}</strong> 
                                @if($site->baseSite)
                                    - {{ $site->baseSite->nom_base }} ({{ $site->baseSite->client ? $site->baseSite->client->nom : 'N/A' }})
                                @elseif($site->client)
                                    - {{ $site->client->nom }} (Client direct)
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @else
                <p style="color: #94a3b8; font-style: italic;">Aucun site assigné</p>
            @endif

            <p style="font-size: 0.8rem; color: #78350f; margin: 0;">
                <i class="fa-solid fa-info-circle"></i> Le demandeur peut créer des demandes uniquement pour ses sites assignés.
            </p>
        </div>
    @endif

    @if($user->type_utilisateur === 'technicien' || $user->type_utilisateur === 'chef technicien')
        <div class="card" style="background-color: #fffbeb; border: 1px solid #fde68a;">
            <h2 style="font-size: 1.1rem; font-weight: 800; color: #b45309; margin-bottom: 1.25rem; border-bottom: 2px solid #fde68a; padding-bottom: 0.75rem;">
                <i class="fa-solid fa-hard-hat"></i> Informations Technicien
            </h2>

            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
                <div>
                    <label style="display: block; font-size: 0.75rem; color: #78350f; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.4rem;">
                        Spécialité
                    </label>
                    <p style="font-size: 1rem; color: #92400e; font-weight: 600; margin: 0;">
                        {{ $user->specialite ?? 'Non renseignée' }}
                    </p>
                </div>

                <div>
                    <label style="display: block; font-size: 0.75rem; color: #78350f; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.4rem;">
                        Équipe
                    </label>
                    <p style="font-size: 1rem; color: #92400e; font-weight: 600; margin: 0;">
                        @if($user->equipe)
                            {{ $user->equipe->nom_equipe }}
                        @else
                            <span style="color: #94a3b8;">Aucune équipe</span>
                        @endif
                    </p>
                </div>
            </div>

            @if($user->depannages && $user->depannages->count() > 0)
                <div style="margin-top: 1.5rem;">
                    <label style="display: block; font-size: 0.75rem; color: #78350f; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem;">
                        Statistiques Interventions
                    </label>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                        <div style="background-color: #fef3c7; padding: 0.75rem; border-radius: 0.5rem; text-align: center;">
                            <p style="font-size: 1.5rem; font-weight: 800; color: #92400e; margin: 0;">
                                {{ $user->depannages->count() }}
                            </p>
                            <p style="font-size: 0.75rem; color: #78350f; margin: 0;">Total</p>
                        </div>
                        <div style="background-color: #fef3c7; padding: 0.75rem; border-radius: 0.5rem; text-align: center;">
                            <p style="font-size: 1.5rem; font-weight: 800; color: #92400e; margin: 0;">
                                {{ $user->depannages->whereIn('statut', ['en attente', 'en cours'])->count() }}
                            </p>
                            <p style="font-size: 0.75rem; color: #78350f; margin: 0;">En cours</p>
                        </div>
                        <div style="background-color: #fef3c7; padding: 0.75rem; border-radius: 0.5rem; text-align: center;">
                            <p style="font-size: 1.5rem; font-weight: 800; color: #92400e; margin: 0;">
                                {{ $user->depannages->where('statut', 'résolu')->count() }}
                            </p>
                            <p style="font-size: 0.75rem; color: #78350f; margin: 0;">Résolues</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- Dates de création et modification -->
    <div class="card">
        <h2 style="font-size: 1.1rem; font-weight: 800; color: #1e293b; margin-bottom: 1.25rem; border-bottom: 2px solid #e2e8f0; padding-bottom: 0.75rem;">
            <i class="fa-solid fa-calendar"></i> Informations Système
        </h2>

        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
            <div>
                <label style="display: block; font-size: 0.75rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.4rem;">
                    Date de Création
                </label>
                <p style="font-size: 0.9rem; color: #1e293b; margin: 0;">
                    <i class="fa-solid fa-clock" style="color: #64748b;"></i> 
                    {{ $user->created_at ? $user->created_at->format('d/m/Y à H:i') : 'N/A' }}
                </p>
            </div>

            <div>
                <label style="display: block; font-size: 0.75rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.4rem;">
                    Dernière Modification
                </label>
                <p style="font-size: 0.9rem; color: #1e293b; margin: 0;">
                    <i class="fa-solid fa-clock" style="color: #64748b;"></i> 
                    {{ $user->updated_at ? $user->updated_at->format('d/m/Y à H:i') : 'N/A' }}
                </p>
            </div>
        </div>
    </div>
@endsection
