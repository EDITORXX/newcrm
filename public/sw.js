// Service Worker for Real Estate CRM
const CACHE_NAME = 'real-estate-crm-v1';
const urlsToCache = [
  '/',
  '/login',
  '/install-app',
  '/favicon.ico',
  '/manifest.json'
];

// Install event - cache resources
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        console.log('Service Worker: Cache opened');
        // Cache resources one by one to handle failures gracefully
        return Promise.allSettled(
          urlsToCache.map(url => {
            return fetch(url)
              .then(response => {
                if (response.ok) {
                  return cache.put(url, response);
                }
              })
              .catch(error => {
                console.warn(`Service Worker: Failed to cache ${url}:`, error);
              });
          })
        );
      })
      .then(() => {
        console.log('Service Worker: Installation complete');
      })
      .catch((error) => {
        console.error('Service Worker: Cache failed', error);
      })
  );
  self.skipWaiting();
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME) {
            console.log('Service Worker: Deleting old cache', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
  return self.clients.claim();
});

// Push notification - show notification when app is in background (Android PWA)
self.addEventListener('push', function(event) {
  if (!event.data) return;
  var data = {};
  try {
    data = event.data.json();
  } catch (e) {
    data = { title: 'Base CRM', body: event.data.text() || 'New update' };
  }
  var title = data.title || 'Base CRM';
  var options = {
    body: data.body || data.message || 'New notification',
    icon: '/icon-192.png',
    badge: '/icon-192.png',
    tag: data.tag || 'crm-notification',
    data: { url: data.url || '/', ...(data.data || {}) },
    requireInteraction: !!data.requireInteraction
  };
  event.waitUntil(
    self.registration.showNotification(title, options)
  );
});

// Click on notification - open app to URL
self.addEventListener('notificationclick', function(event) {
  event.notification.close();
  var url = event.notification.data && event.notification.data.url ? event.notification.data.url : '/';
  event.waitUntil(
    self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function(clientList) {
      for (var i = 0; i < clientList.length; i++) {
        if (clientList[i].url && 'focus' in clientList[i]) {
          clientList[i].navigate(url);
          return clientList[i].focus();
        }
      }
      if (self.clients.openWindow) return self.clients.openWindow(url);
    })
  );
});

// Fetch event - serve from cache, fallback to network
self.addEventListener('fetch', (event) => {
  // Only handle GET requests
  if (event.request.method !== 'GET') {
    return;
  }

  // Skip cross-origin requests
  if (!event.request.url.startsWith(self.location.origin)) {
    return;
  }

  // Skip caching for HTML pages (always fetch fresh)
  if (event.request.destination === 'document' || 
      event.request.headers.get('accept').includes('text/html')) {
    event.respondWith(
      fetch(event.request).catch(() => {
        // Fallback to cache only if network fails
        return caches.match(event.request);
      })
    );
    return;
  }

  event.respondWith(
    caches.match(event.request)
      .then((response) => {
        // Always fetch from network first for HTML pages
        if (event.request.destination === 'document') {
          return fetch(event.request)
            .then((networkResponse) => {
              return networkResponse;
            })
            .catch(() => {
              // Fallback to cache only if network fails
              return response;
            });
        }
        
        // Return cached version or fetch from network
        if (response) {
          return response;
        }
        return fetch(event.request)
          .then((networkResponse) => {
            // Cache successful responses (but not HTML pages)
            if (networkResponse && networkResponse.status === 200 && 
                event.request.destination !== 'document') {
              const responseToCache = networkResponse.clone();
              caches.open(CACHE_NAME).then((cache) => {
                cache.put(event.request, responseToCache);
              }).catch(() => {
                // Ignore cache errors
              });
            }
            return networkResponse;
          })
          .catch(() => {
            // If both fail, return offline page if available
            if (event.request.destination === 'document') {
              return caches.match('/');
            }
          });
      })
      .catch(() => {
        // Fallback to network
        return fetch(event.request);
      })
  );
});
