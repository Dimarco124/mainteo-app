// Service Worker MAINTEO Mobile
// Version 1.0.0

const CACHE_NAME = 'mainteo-mobile-v1';
const urlsToCache = [
  '/mobile',
  '/mobile/technicien/dashboard',
  '/mobile/technicien/interventions',
  '/mobile/technicien/planning',
  '/mobile/technicien/profil',
  '/css/mobile.css',
  '/js/mobile.js',
  '/manifest.json',
  'https://fonts.googleapis.com/css2?family=Varela+Round&display=swap',
  'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'
];

// Installation du Service Worker
self.addEventListener('install', event => {
  console.log('[SW] Installation...');
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('[SW] Mise en cache des ressources');
        return cache.addAll(urlsToCache);
      })
      .catch(err => {
        console.error('[SW] Erreur lors de la mise en cache:', err);
      })
  );
  self.skipWaiting();
});

// Activation du Service Worker
self.addEventListener('activate', event => {
  console.log('[SW] Activation...');
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheName !== CACHE_NAME) {
            console.log('[SW] Suppression ancien cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
  return self.clients.claim();
});

// Stratégie de mise en cache : Network First, fallback to Cache
self.addEventListener('fetch', event => {
  const { request } = event;
  
  // Ignorer les requêtes non-GET
  if (request.method !== 'GET') {
    return;
  }

  // Ignorer les requêtes vers d'autres domaines (sauf fonts & icons)
  if (!request.url.startsWith(self.location.origin) && 
      !request.url.includes('fonts.googleapis.com') &&
      !request.url.includes('cdnjs.cloudflare.com')) {
    return;
  }

  event.respondWith(
    fetch(request)
      .then(response => {
        // Si la réponse est valide, on la met en cache
        if (response && response.status === 200 && response.type === 'basic') {
          const responseToCache = response.clone();
          caches.open(CACHE_NAME)
            .then(cache => {
              cache.put(request, responseToCache);
            });
        }
        return response;
      })
      .catch(() => {
        // Si le réseau échoue, on cherche dans le cache
        return caches.match(request)
          .then(cachedResponse => {
            if (cachedResponse) {
              console.log('[SW] Réponse depuis le cache:', request.url);
              return cachedResponse;
            }
            
            // Si pas en cache et pas de réseau, page offline
            if (request.destination === 'document') {
              return caches.match('/mobile/offline');
            }
          });
      })
  );
});

// Synchronisation en arrière-plan (pour les rapports offline)
self.addEventListener('sync', event => {
  console.log('[SW] Synchronisation en arrière-plan:', event.tag);
  
  if (event.tag === 'sync-rapports') {
    event.waitUntil(syncRapports());
  }
});

// Fonction de synchronisation des rapports
async function syncRapports() {
  console.log('[SW] Synchronisation des rapports...');
  
  try {
    const db = await openDB();
    const rapports = await getRapportsPendingSync(db);
    
    for (const rapport of rapports) {
      try {
        const response = await fetch('/api/mobile/rapports/sync', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify(rapport)
        });
        
        if (response.ok) {
          await deleteRapportFromDB(db, rapport.id);
          console.log('[SW] Rapport synchronisé:', rapport.id);
        }
      } catch (err) {
        console.error('[SW] Erreur sync rapport:', err);
      }
    }
  } catch (err) {
    console.error('[SW] Erreur générale sync:', err);
  }
}

// Helpers IndexedDB
function openDB() {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open('MainteoMobileDB', 1);
    
    request.onerror = () => reject(request.error);
    request.onsuccess = () => resolve(request.result);
    
    request.onupgradeneeded = event => {
      const db = event.target.result;
      if (!db.objectStoreNames.contains('rapports')) {
        db.createObjectStore('rapports', { keyPath: 'id', autoIncrement: true });
      }
    };
  });
}

function getRapportsPendingSync(db) {
  return new Promise((resolve, reject) => {
    const transaction = db.transaction(['rapports'], 'readonly');
    const store = transaction.objectStore('rapports');
    const request = store.getAll();
    
    request.onerror = () => reject(request.error);
    request.onsuccess = () => resolve(request.result);
  });
}

function deleteRapportFromDB(db, id) {
  return new Promise((resolve, reject) => {
    const transaction = db.transaction(['rapports'], 'readwrite');
    const store = transaction.objectStore('rapports');
    const request = store.delete(id);
    
    request.onerror = () => reject(request.error);
    request.onsuccess = () => resolve();
  });
}

console.log('[SW] Service Worker MAINTEO Mobile chargé');
