@extends('mobile.technicien.layout')

@section('title', 'Paramètres - MAINTEO Mobile')
@section('page-title', 'Paramètres')

@section('mobile-content')
<div style="padding: 1.5rem;">
    <!-- Compte -->
    <div style="background: #ffffff; border-radius: 1rem; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);">
        <h2 style="font-size: 1.1rem; font-weight: 700; color: var(--text-dark); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-user-circle" style="color: var(--main-emerald);"></i>
            Mon Compte
        </h2>
        
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.25rem;">Nom complet</div>
                    <div style="font-size: 0.95rem; font-weight: 600; color: var(--text-dark);">{{ $user->nom_complet }}</div>
                </div>
            </div>
            
            <div style="height: 1px; background: var(--border-color);"></div>
            
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.25rem;">Email</div>
                    <div style="font-size: 0.95rem; font-weight: 600; color: var(--text-dark);">{{ $user->email }}</div>
                </div>
            </div>
            
            <div style="height: 1px; background: var(--border-color);"></div>
            
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.25rem;">Rôle</div>
                    <div style="font-size: 0.95rem; font-weight: 600; color: var(--text-dark);">{{ ucfirst($user->type_utilisateur) }}</div>
                </div>
            </div>
        </div>
        
        <a href="{{ route('profile.show') }}" style="display: flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.85rem 1.25rem; background: var(--bg-body); color: var(--text-normal); border: 1px solid var(--border-color); border-radius: 0.75rem; text-decoration: none; font-size: 0.95rem; font-weight: 700; margin-top: 1.5rem; transition: all 0.2s ease;">
            <i class="fa-solid fa-pen-to-square"></i>
            Modifier mon profil
        </a>
    </div>
    
    <!-- Préférences -->
    <div style="background: #ffffff; border-radius: 1rem; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);">
        <h2 style="font-size: 1.1rem; font-weight: 700; color: var(--text-dark); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-sliders" style="color: #0369a1;"></i>
            Préférences
        </h2>
        
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: #f8fafc; border-radius: 0.5rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <div style="width: 40px; height: 40px; background: #eff6ff; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #0369a1; font-size: 1.1rem;">
                        <i class="fa-solid fa-bell"></i>
                    </div>
                    <span style="font-size: 0.9rem; font-weight: 600; color: var(--text-normal);">Notifications</span>
                </div>
                <label style="position: relative; display: inline-block; width: 50px; height: 28px;">
                    <input type="checkbox" checked style="opacity: 0; width: 0; height: 0;">
                    <span style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: var(--main-emerald); transition: .4s; border-radius: 28px;"></span>
                    <span style="position: absolute; content: ''; height: 20px; width: 20px; left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%;"></span>
                </label>
            </div>
            
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: #f8fafc; border-radius: 0.5rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <div style="width: 40px; height: 40px; background: #fef3c7; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #b45309; font-size: 1.1rem;">
                        <i class="fa-solid fa-mobile-screen"></i>
                    </div>
                    <span style="font-size: 0.9rem; font-weight: 600; color: var(--text-normal);">Mode hors-ligne</span>
                </div>
                <label style="position: relative; display: inline-block; width: 50px; height: 28px;">
                    <input type="checkbox" checked style="opacity: 0; width: 0; height: 0;">
                    <span style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: var(--main-emerald); transition: .4s; border-radius: 28px;"></span>
                    <span style="position: absolute; content: ''; height: 20px; width: 20px; left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%;"></span>
                </label>
            </div>
        </div>
    </div>
    
    <!-- Application -->
    <div style="background: #ffffff; border-radius: 1rem; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);">
        <h2 style="font-size: 1.1rem; font-weight: 700; color: var(--text-dark); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-mobile-alt" style="color: #6b7280;"></i>
            Application
        </h2>
        
        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: #f8fafc; border-radius: 0.5rem;">
                <span style="font-size: 0.9rem; font-weight: 600; color: var(--text-normal);">Version</span>
                <span style="font-size: 0.9rem; font-weight: 700; color: var(--text-dark);">1.0.0</span>
            </div>
            
            <a href="{{ route('dashboard') }}" style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: #f8fafc; border-radius: 0.5rem; text-decoration: none;">
                <span style="font-size: 0.9rem; font-weight: 600; color: var(--text-normal);">Passer à la version desktop</span>
                <i class="fa-solid fa-desktop" style="color: var(--text-muted);"></i>
            </a>
        </div>
    </div>
    
    <!-- Déconnexion -->
    <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit" style="width: 100%; padding: 1rem; background: linear-gradient(135deg, #be123c, #e11d48); color: #ffffff; border: none; border-radius: 0.75rem; font-size: 0.95rem; font-weight: 700; cursor: pointer; box-shadow: 0 4px 12px rgba(190, 18, 60, 0.25); display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
            <i class="fa-solid fa-right-from-bracket"></i>
            Déconnexion
        </button>
    </form>
</div>

<div style="height: 2rem;"></div>
@endsection
