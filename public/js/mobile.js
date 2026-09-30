/**
 * MAINTEO Mobile - JavaScript
 * Gestion PWA, offline, interactions
 */

// ══════════════════════════════════════════════════════════════════════════════
// CONFIGURATION
// ══════════════════════════════════════════════════════════════════════════════
const MAINTEO_MOBILE = {
    version: '1.0.0',
    dbName: 'MainteoMobileDB',
    dbVersion: 1
};

// ══════════════════════════════════════════════════════════════════════════════
// DÉTECTION RÉSEAU
// ══════════════════════════════════════════════════════════════════════════════
let isOnline = navigator.onLine;

window.addEventListener('online', () => {
    isOnline = true;
    console.log('[Mobile] Connexion rétablie');
    showToast('Connexion rétablie', 'success');
    syncOfflineData();
});

window.addEventListener('offline', () => {
    isOnline = false;
    console.log('[Mobile] Hors ligne');
    showToast('Mode hors ligne activé', 'warning');
});

// ══════════════════════════════════════════════════════════════════════════════
// NOTIFICATIONS TOAST
// ══════════════════════════════════════════════════════════════════════════════
function showToast(message, type = 'info') {
    // Supprimer anciens toasts
    const existingToast = document.querySelector('.mobile-toast');
    if (existingToast) existingToast.remove();
    
    const toast = document.createElement('div');
    toast.className = `mobile-toast mobile-toast-${type}`;
    toast.textContent = message;
    
    const colors = {
        success: '#059669',
        error: '#be123c',
        warning: '#b45309',
        info: '#0369a1'
    };
    
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        background: ${colors[type] || colors.info};
        color: white;
        padding: 12px 24px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        z-index: 10000;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        animation: slideDown 0.3s ease;
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideUp 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Animations CSS inline
const style = document.createElement('style');
style.textContent = `
    @keyframes slideDown {
        from { opacity: 0; transform: translate(-50%, -20px); }
        to { opacity: 1; transform: translate(-50%, 0); }
    }
    @keyframes slideUp {
        from { opacity: 1; transform: translate(-50%, 0); }
        to { opacity: 0; transform: translate(-50%, -20px); }
    }
`;
document.head.appendChild(style);

// ══════════════════════════════════════════════════════════════════════════════
// INDEXEDDB - STOCKAGE OFFLINE
// ══════════════════════════════════════════════════════════════════════════════
function openDB() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open(MAINTEO_MOBILE.dbName, MAINTEO_MOBILE.dbVersion);
        
        request.onerror = () => reject(request.error);
        request.onsuccess = () => resolve(request.result);
        
        request.onupgradeneeded = (event) => {
            const db = event.target.result;
            
            // Store pour rapports offline
            if (!db.objectStoreNames.contains('rapports')) {
                db.createObjectStore('rapports', { keyPath: 'id', autoIncrement: true });
            }
            
            // Store pour photos offline
            if (!db.objectStoreNames.contains('photos')) {
                db.createObjectStore('photos', { keyPath: 'id', autoIncrement: true });
            }
        };
    });
}

// ══════════════════════════════════════════════════════════════════════════════
// SYNCHRONISATION DONNÉES OFFLINE
// ══════════════════════════════════════════════════════════════════════════════
async function syncOfflineData() {
    if (!isOnline) return;
    
    try {
        const db = await openDB();
        
        // Sync rapports
        const rapports = await getAllFromStore(db, 'rapports');
        console.log(`[Sync] ${rapports.length} rapports à synchroniser`);
        
        for (const rapport of rapports) {
            try {
                const response = await fetch('/api/mobile/rapports/sync', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(rapport)
                });
                
                if (response.ok) {
                    await deleteFromStore(db, 'rapports', rapport.id);
                    console.log(`[Sync] Rapport ${rapport.id} synchronisé`);
                }
            } catch (err) {
                console.error(`[Sync] Erreur rapport ${rapport.id}:`, err);
            }
        }
        
        if (rapports.length > 0) {
            showToast(`${rapports.length} rapport(s) synchronisé(s)`, 'success');
        }
        
    } catch (err) {
        console.error('[Sync] Erreur générale:', err);
    }
}

// Helpers IndexedDB
function getAllFromStore(db, storeName) {
    return new Promise((resolve, reject) => {
        const transaction = db.transaction([storeName], 'readonly');
        const store = transaction.objectStore(storeName);
        const request = store.getAll();
        
        request.onerror = () => reject(request.error);
        request.onsuccess = () => resolve(request.result);
    });
}

function addToStore(db, storeName, data) {
    return new Promise((resolve, reject) => {
        const transaction = db.transaction([storeName], 'readwrite');
        const store = transaction.objectStore(storeName);
        const request = store.add(data);
        
        request.onerror = () => reject(request.error);
        request.onsuccess = () => resolve(request.result);
    });
}

function deleteFromStore(db, storeName, id) {
    return new Promise((resolve, reject) => {
        const transaction = db.transaction([storeName], 'readwrite');
        const store = transaction.objectStore(storeName);
        const request = store.delete(id);
        
        request.onerror = () => reject(request.error);
        request.onsuccess = () => resolve();
    });
}

// ══════════════════════════════════════════════════════════════════════════════
// GÉOLOCALISATION
// ══════════════════════════════════════════════════════════════════════════════
function getCurrentPosition() {
    return new Promise((resolve, reject) => {
        if (!navigator.geolocation) {
            reject(new Error('Géolocalisation non supportée'));
            return;
        }
        
        navigator.geolocation.getCurrentPosition(
            position => resolve({
                latitude: position.coords.latitude,
                longitude: position.coords.longitude,
                accuracy: position.coords.accuracy
            }),
            error => reject(error),
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    });
}

// Ouvrir GPS externe (Google Maps / Apple Maps)
function openGPS(lat, lon, label) {
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
    const url = isIOS
        ? `maps://maps.google.com/maps?daddr=${lat},${lon}&amp;ll=`
        : `https://www.google.com/maps/dir/?api=1&destination=${lat},${lon}`;
    
    window.location.href = url;
}

// ══════════════════════════════════════════════════════════════════════════════
// VIBRATION TACTILE
// ══════════════════════════════════════════════════════════════════════════════
function vibrate(pattern = 10) {
    if ('vibrate' in navigator) {
        navigator.vibrate(pattern);
    }
}

// ══════════════════════════════════════════════════════════════════════════════
// PULL TO REFRESH
// ══════════════════════════════════════════════════════════════════════════════
let startY = 0;
let pulling = false;

document.addEventListener('touchstart', (e) => {
    if (window.scrollY === 0) {
        startY = e.touches[0].pageY;
        pulling = false;
    }
});

document.addEventListener('touchmove', (e) => {
    if (startY > 0) {
        const currentY = e.touches[0].pageY;
        const pullDistance = currentY - startY;
        
        if (pullDistance > 80 && !pulling) {
            pulling = true;
            vibrate(20);
            console.log('[Mobile] Pull to refresh détecté');
        }
    }
});

document.addEventListener('touchend', () => {
    if (pulling) {
        location.reload();
    }
    startY = 0;
    pulling = false;
});

// ══════════════════════════════════════════════════════════════════════════════
// INITIALISATION
// ══════════════════════════════════════════════════════════════════════════════
document.addEventListener('DOMContentLoaded', () => {
    console.log(`[Mobile] MAINTEO Mobile v${MAINTEO_MOBILE.version} chargé`);
    
    // Vérifier si données offline à synchroniser
    if (isOnline) {
        setTimeout(syncOfflineData, 1000);
    }
    
    // Afficher statut réseau
    console.log(`[Mobile] Statut réseau: ${isOnline ? 'En ligne' : 'Hors ligne'}`);
});

// ══════════════════════════════════════════════════════════════════════════════
// EXPORT FONCTIONS GLOBALES
// ══════════════════════════════════════════════════════════════════════════════
window.MainteoMobile = {
    showToast,
    getCurrentPosition,
    openGPS,
    vibrate,
    syncOfflineData,
    openDB,
    addToStore,
    getAllFromStore,
    deleteFromStore
};

console.log('[Mobile] API MainteoMobile disponible');
