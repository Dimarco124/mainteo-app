@extends('layouts.app')

@section('title', 'Paramètres')

@section('content')
<div class="header">
    <div class="page-title">
        <h1>Paramètres de l'Application</h1>
        <p>Personnalisez votre expérience et gérez vos préférences.</p>
    </div>
</div>

<div class="settings-grid">
    <!-- Préférences d'affichage -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title" style="color: #7c3aed;"><i class="fa-solid fa-palette"></i> Préférences d'Affichage</h3>
        </div>
        
        <div style="display: flex; flex-direction: column; gap: 1.25rem;">
            <div>
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.75rem; font-weight: 600;">Thème de l'interface</label>
                <div style="display: flex; gap: 1rem;">
                    <div 
                        onclick="changeTheme(false)" 
                        id="theme-light"
                        style="flex: 1; padding: 1rem; border: 2px solid {{ auth()->user()->dark_mode ? '#e2e8f0' : '#10b981' }}; background-color: {{ auth()->user()->dark_mode ? '#f8fafc' : '#ecfdf5' }}; border-radius: 0.75rem; cursor: pointer; text-align: center; transition: all 0.3s;">
                        <i class="fa-solid fa-sun" style="font-size: 2rem; color: #10b981; margin-bottom: 0.5rem; display: block;"></i>
                        <strong style="color: #047857; font-size: 0.85rem;">Clair</strong>
                        <div style="margin-top: 0.25rem;">
                            @if(!auth()->user()->dark_mode)
                            <i class="fa-solid fa-circle-check" style="color: #10b981;"></i>
                            @endif
                        </div>
                    </div>
                    <div 
                        onclick="changeTheme(true)" 
                        id="theme-dark"
                        style="flex: 1; padding: 1rem; border: 2px solid {{ auth()->user()->dark_mode ? '#10b981' : '#e2e8f0' }}; background-color: {{ auth()->user()->dark_mode ? '#1e293b' : '#f8fafc' }}; border-radius: 0.75rem; cursor: pointer; text-align: center; transition: all 0.3s;">
                        <i class="fa-solid fa-moon" style="font-size: 2rem; color: {{ auth()->user()->dark_mode ? '#10b981' : '#64748b' }}; margin-bottom: 0.5rem; display: block;"></i>
                        <strong style="color: {{ auth()->user()->dark_mode ? '#ecfdf5' : '#475569' }}; font-size: 0.85rem;">Sombre</strong>
                        <div style="margin-top: 0.25rem;">
                            @if(auth()->user()->dark_mode)
                            <i class="fa-solid fa-circle-check" style="color: #10b981;"></i>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div style="padding: 1rem; background-color: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 0.75rem;">
                <div style="display: flex; gap: 0.75rem; align-items: start;">
                    <i class="fa-solid fa-check-circle" style="color: #10b981; font-size: 1.3rem;"></i>
                    <div style="font-size: 0.8rem; color: #047857;">
                        <strong>Mode sombre disponible !</strong><br>
                        Changez le thème instantanément pour réduire la fatigue visuelle lors de l'utilisation nocturne.
                    </div>
                </div>
            </div>

            <div>
                <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer; padding: 0.75rem; background-color: #f8fafc; border-radius: 0.75rem;">
                    <input type="checkbox" checked style="width: 20px; height: 20px; cursor: pointer;">
                    <div>
                        <div style="font-weight: 600; font-size: 0.85rem; color: #0f172a;">Afficher les animations</div>
                        <div style="font-size: 0.75rem; color: #64748b;">Transitions et effets visuels dans l'interface</div>
                    </div>
                </label>
            </div>
        </div>
    </div>

    <!-- Notifications -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title" style="color: #f59e0b;"><i class="fa-solid fa-bell"></i> Notifications</h3>
        </div>
        
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <div>
                <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer; padding: 0.75rem; background-color: #f8fafc; border-radius: 0.75rem;">
                    <input type="checkbox" checked style="width: 20px; height: 20px; cursor: pointer;">
                    <div>
                        <div style="font-weight: 600; font-size: 0.85rem; color: #0f172a;">Nouvelles interventions</div>
                        <div style="font-size: 0.75rem; color: #64748b;">Recevoir une notification pour chaque nouvelle demande</div>
                    </div>
                </label>
            </div>

            <div>
                <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer; padding: 0.75rem; background-color: #f8fafc; border-radius: 0.75rem;">
                    <input type="checkbox" checked style="width: 20px; height: 20px; cursor: pointer;">
                    <div>
                        <div style="font-weight: 600; font-size: 0.85rem; color: #0f172a;">Interventions urgentes</div>
                        <div style="font-size: 0.75rem; color: #64748b;">Alerte prioritaire pour les pannes urgentes</div>
                    </div>
                </label>
            </div>

            <div>
                <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer; padding: 0.75rem; background-color: #f8fafc; border-radius: 0.75rem;">
                    <input type="checkbox" style="width: 20px; height: 20px; cursor: pointer;">
                    <div>
                        <div style="font-weight: 600; font-size: 0.85rem; color: #0f172a;">Notifications email</div>
                        <div style="font-size: 0.75rem; color: #64748b;">Recevoir des résumés quotidiens par email</div>
                    </div>
                </label>
            </div>

            <div>
                <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer; padding: 0.75rem; background-color: #f8fafc; border-radius: 0.75rem;">
                    <input type="checkbox" checked style="width: 20px; height: 20px; cursor: pointer;">
                    <div>
                        <div style="font-weight: 600; font-size: 0.85rem; color: #0f172a;">Rappels de maintenance</div>
                        <div style="font-size: 0.75rem; color: #64748b;">Alertes pour les maintenances préventives programmées</div>
                    </div>
                </label>
            </div>
        </div>

        <div style="margin-top: 1.5rem;">
            <button class="btn-primary" style="width: 100%; justify-content: center;">
                <i class="fa-solid fa-save"></i> Enregistrer les préférences
            </button>
        </div>
    </div>
</div>

<!-- Informations système -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title" style="color: #64748b;"><i class="fa-solid fa-info-circle"></i> Informations Système</h3>
    </div>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
        <div style="padding: 1rem; background-color: #f8fafc; border-radius: 0.75rem;">
            <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 0.25rem;">Version de l'application</div>
            <div style="font-size: 1.1rem; font-weight: 700; color: #0f172a;">MAINTEO GMAO v1.0.0</div>
        </div>

        <div style="padding: 1rem; background-color: #f8fafc; border-radius: 0.75rem;">
            <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 0.25rem;">Dernière connexion</div>
            <div style="font-size: 1.1rem; font-weight: 700; color: #0f172a;">{{ now()->format('d/m/Y H:i') }}</div>
        </div>

        <div style="padding: 1rem; background-color: #f8fafc; border-radius: 0.75rem;">
            <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 0.25rem;">Navigateur</div>
            <div style="font-size: 1.1rem; font-weight: 700; color: #0f172a;">
                <i class="fa-brands fa-chrome" style="color: #059669;"></i> Chrome
            </div>
        </div>

        <div style="padding: 1rem; background-color: #f8fafc; border-radius: 0.75rem;">
            <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 0.25rem;">Langue</div>
            <div style="font-size: 1.1rem; font-weight: 700; color: #0f172a;">
                <i class="fa-solid fa-language" style="color: #3b82f6;"></i> Français
            </div>
        </div>
    </div>

    <div class="settings-system-footer">
        <div style="font-size: 0.8rem; color: #64748b;">
            <i class="fa-solid fa-shield-halved" style="color: #10b981;"></i> 
            Toutes vos données sont sécurisées et cryptées
        </div>
        <a href="#" style="color: #059669; text-decoration: none; font-size: 0.85rem; font-weight: 600;">
            <i class="fa-solid fa-question-circle"></i> Aide & Support
        </a>
    </div>
</div>

<script>
function changeTheme(darkMode) {
    console.log('🎨 Changement de thème demandé:', darkMode);
    console.log('📍 URL:', '{{ route("profile.theme") }}');
    console.log('🔑 CSRF Token:', '{{ csrf_token() }}');
    
    fetch('{{ route("profile.theme") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ dark_mode: darkMode })
    })
    .then(response => {
        console.log('📡 Réponse HTTP:', response.status);
        if (!response.ok) {
            throw new Error('Erreur HTTP: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        console.log('✅ Réponse serveur:', data);
        if (data.success) {
            console.log('🔄 Rechargement de la page...');
            window.location.reload();
        } else {
            alert('Erreur: ' + (data.message || 'Erreur inconnue'));
        }
    })
    .catch(error => {
        console.error('❌ Erreur complète:', error);
        alert('Erreur lors du changement de thème: ' + error.message);
    });
}

// Test au chargement de la page
console.log('🎨 Page paramètres chargée');
console.log('👤 Mode sombre actuel:', {{ auth()->user()->dark_mode ? 'true' : 'false' }});
</script>
@endsection
