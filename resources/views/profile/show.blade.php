@extends('layouts.app')

@section('title', 'Mon Profil')

@section('content')
<div class="header">
    <div class="page-title">
        <h1>Mon Profil</h1>
        <p>Gérez vos informations personnelles et votre compte.</p>
    </div>
</div>

<div class="profile-grid">
    <!-- Informations du profil -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title" style="color: #059669;"><i class="fa-solid fa-user-circle"></i> Informations Personnelles</h3>
        </div>
        
        <form action="{{ route('profile.update') }}" method="POST">
            @csrf
            @method('PUT')
            
            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Nom *</label>
                <input type="text" name="nom" value="{{ old('nom', $user->nom) }}" required style="width: 100%; padding: 0.75rem;">
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Prénom *</label>
                <input type="text" name="prenom" value="{{ old('prenom', $user->prenom) }}" required style="width: 100%; padding: 0.75rem;">
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Email *</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required style="width: 100%; padding: 0.75rem;">
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Téléphone</label>
                <input type="text" name="telephone" value="{{ old('telephone', $user->telephone) }}" placeholder="+237 6XX XXX XXX" style="width: 100%; padding: 0.75rem;">
            </div>

            <div style="padding: 1rem; background-color: #f8fafc; border-radius: 0.75rem; margin-bottom: 1.5rem;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; font-size: 0.85rem;">
                    <div>
                        <span style="color: #64748b;">Rôle :</span>
                        <strong style="color: #0f172a; text-transform: capitalize;">{{ $user->type_utilisateur }}</strong>
                    </div>
                    <div>
                        <span style="color: #64748b;">Statut :</span>
                        <span class="badge badge-success">Actif</span>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-primary" style="width: 100%; justify-content: center;">
                <i class="fa-solid fa-save"></i> Enregistrer les modifications
            </button>
        </form>
    </div>

    <!-- Changer le mot de passe -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title" style="color: #3b82f6;"><i class="fa-solid fa-lock"></i> Sécurité du Compte</h3>
        </div>
        
        <form action="{{ route('profile.password') }}" method="POST">
            @csrf
            @method('PUT')
            
            <div style="background-color: #eff6ff; border: 1px solid #bfdbfe; border-radius: 0.75rem; padding: 1rem; margin-bottom: 1.5rem;">
                <div style="display: flex; gap: 0.75rem; align-items: start;">
                    <i class="fa-solid fa-shield-halved" style="color: #3b82f6; font-size: 1.5rem;"></i>
                    <div style="font-size: 0.8rem; color: #1e40af;">
                        <strong>Sécurité renforcée</strong><br>
                        Choisissez un mot de passe fort avec au moins 6 caractères, incluant lettres et chiffres.
                    </div>
                </div>
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Mot de passe actuel *</label>
                <input type="password" name="current_password" required style="width: 100%; padding: 0.75rem;" placeholder="••••••••">
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Nouveau mot de passe *</label>
                <input type="password" name="new_password" required style="width: 100%; padding: 0.75rem;" placeholder="••••••••">
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Confirmer le nouveau mot de passe *</label>
                <input type="password" name="new_password_confirmation" required style="width: 100%; padding: 0.75rem;" placeholder="••••••••">
            </div>

            <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; background: linear-gradient(135deg, #3b82f6, #60a5fa);">
                <i class="fa-solid fa-key"></i> Changer le mot de passe
            </button>
        </form>

        <!-- Informations supplémentaires -->
        <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #e2e8f0;">
            <h4 style="font-size: 0.9rem; font-weight: 700; color: #0f172a; margin-bottom: 1rem;">
                <i class="fa-solid fa-info-circle" style="color: #64748b;"></i> Informations du compte
            </h4>
            <div style="display: flex; flex-direction: column; gap: 0.75rem; font-size: 0.8rem;">
                @if($user->specialite)
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: #64748b;">Spécialité :</span>
                    <strong style="color: #0f172a;">{{ $user->specialite }}</strong>
                </div>
                @endif
                @if($user->client)
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: #64748b;">Entreprise :</span>
                    <strong style="color: #0f172a;">{{ $user->client->nom }}</strong>
                </div>
                @endif
                @if($user->baseSite)
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: #64748b;">Base :</span>
                    <strong style="color: #0f172a;">{{ $user->baseSite->nom_base }}</strong>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
