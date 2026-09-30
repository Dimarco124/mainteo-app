/**
 * MAINTEO PWA - Installation Button
 * Affiche un bouton "Installer l'application" qui déclenche l'installation PWA
 */

let deferredPrompt;
let installButton;

// Détecter l'événement d'installation PWA
window.addEventListener('beforeinstallprompt', (e) => {
    console.log('[PWA] Installation disponible');
    
    // Empêcher le prompt par défaut
    e.preventDefault();
    
    // Stocker l'événement pour l'utiliser plus tard
    deferredPrompt = e;
    
    // Afficher le bouton d'installation
    showInstallButton();
});

// Créer et afficher le bouton d'installation
function showInstallButton() {
    // Vérifier si le bouton existe déjà
    if (document.getElementById('pwa-install-btn')) return;
    
    // Créer le bouton
    installButton = document.createElement('div');
    installButton.id = 'pwa-install-btn';
    installButton.innerHTML = `
        <div class="pwa-install-card">
            <button class="pwa-install-button" onclick="installPWA()">
                <i class="fa-solid fa-mobile-screen-button"></i>
                <div class="pwa-install-text">
                    <div class="pwa-install-title">Installer l'Application Mobile</div>
                    <div class="pwa-install-subtitle">Accès rapide depuis votre écran d'accueil</div>
                </div>
            </button>
            <button class="pwa-install-close" onclick="closeInstallButton()" aria-label="Fermer">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>
    `;
    
    // Ajouter au body
    document.body.appendChild(installButton);
    
    // Animation d'entrée
    setTimeout(() => {
        installButton.classList.add('pwa-install-visible');
    }, 500);
}

// Installer la PWA
window.installPWA = async function() {
    console.log('[PWA] Fonction installPWA() appelée');
    console.log('[PWA] deferredPrompt disponible:', !!deferredPrompt);
    
    if (!deferredPrompt) {
        console.log('[PWA] Pas de prompt disponible - Affichage des instructions manuelles');
        // Afficher instructions manuelles
        showManualInstallInstructions();
        return;
    }
    
    try {
        console.log('[PWA] Affichage du prompt natif...');
        // Afficher le prompt natif
        deferredPrompt.prompt();
        
        // Attendre la réponse de l'utilisateur
        const { outcome } = await deferredPrompt.userChoice;
        console.log(`[PWA] Réponse utilisateur: ${outcome}`);
        
        if (outcome === 'accepted') {
            console.log('[PWA] Installation acceptée');
            // Masquer le bouton
            closeInstallButton();
            
            // Afficher notification de succès
            showNotification('✅ Application installée avec succès !', 'success');
        } else {
            console.log('[PWA] Installation refusée');
        }
        
        // Réinitialiser le prompt
        deferredPrompt = null;
    } catch (error) {
        console.error('[PWA] Erreur lors de l\'installation:', error);
        showManualInstallInstructions();
    }
};

// Fermer le bouton d'installation
window.closeInstallButton = function() {
    const btn = document.getElementById('pwa-install-btn');
    if (btn) {
        btn.classList.remove('pwa-install-visible');
        setTimeout(() => {
            btn.remove();
        }, 300);
    }
    
    // Sauvegarder la préférence (ne plus afficher pendant 7 jours)
    localStorage.setItem('pwa-install-dismissed', Date.now());
};

// Instructions installation manuelle
function showManualInstallInstructions() {
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
    const isAndroid = /Android/.test(navigator.userAgent);
    
    let message = 'Pour installer l\'application :\n\n';
    
    if (isIOS) {
        message += '1. Appuyez sur le bouton Partager (carré avec flèche)\n';
        message += '2. Sélectionnez "Sur l\'écran d\'accueil"\n';
        message += '3. Confirmez l\'installation';
    } else if (isAndroid) {
        message += '1. Ouvrez le menu Chrome (⋮)\n';
        message += '2. Sélectionnez "Installer l\'application"\n';
        message += '3. Confirmez l\'installation';
    } else {
        message += '1. Ouvrez le menu de votre navigateur\n';
        message += '2. Cherchez "Installer l\'application"\n';
        message += '3. Confirmez l\'installation';
    }
    
    alert(message);
}

// Notification toast
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `pwa-notification pwa-notification-${type}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.add('pwa-notification-visible');
    }, 100);
    
    setTimeout(() => {
        notification.classList.remove('pwa-notification-visible');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Vérifier si l'app est déjà installée
window.addEventListener('DOMContentLoaded', () => {
    // Si déjà installée (mode standalone)
    if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone) {
        console.log('[PWA] Application déjà installée');
        return;
    }
    
    // Vérifier si l'utilisateur a récemment fermé le prompt
    const dismissed = localStorage.getItem('pwa-install-dismissed');
    if (dismissed) {
        const daysSince = (Date.now() - parseInt(dismissed)) / (1000 * 60 * 60 * 24);
        if (daysSince < 7) {
            console.log('[PWA] Bouton masqué (fermé il y a moins de 7 jours)');
            return;
        }
    }
    
    // Sur iOS, afficher directement les instructions car pas de beforeinstallprompt
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
    if (isIOS && !window.navigator.standalone) {
        setTimeout(showInstallButton, 2000);
    }
});

// Détecter l'installation réussie
window.addEventListener('appinstalled', () => {
    console.log('[PWA] Application installée avec succès');
    showNotification('✅ Application installée ! Vous pouvez maintenant y accéder depuis votre écran d\'accueil.', 'success');
    closeInstallButton();
});

console.log('[PWA] Script d\'installation chargé');
