/**
 * Service Worker for Simple Chat PWA (FR-28, FR-29)
 * Supports offline asset caching and background push notification delivery.
 */

const CACHE_NAME = 'simple-chat-v1';
const STATIC_ASSETS = [
    '/',
    '/index.html',
    '/css/style.css',
    '/js/app.js',
    '/js/api.js',
    '/js/auth.js',
    '/js/contacts.js',
    '/js/chat.js',
    '/js/lazy.js',
    '/js/fcm.js',
    '/manifest.json',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
];

// Install Event
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(STATIC_ASSETS).catch((err) => {
                console.warn('Pre-cache partial fail (acceptable in dev):', err);
            });
        })
    );
    self.skipWaiting();
});

// Activate Event: clean old caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
            );
        })
    );
    self.clients.claim();
});

// Fetch Event: Cache first for static assets, Network only for /api/
self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);

    // Bypass API requests to always go to network
    if (url.pathname.startsWith('/api/') || url.pathname.startsWith('/uploads/')) {
        return;
    }

    event.respondWith(
        caches.match(event.request).then((cachedResponse) => {
            if (cachedResponse) {
                return cachedResponse;
            }
            return fetch(event.request).then((networkResponse) => {
                if (networkResponse && networkResponse.status === 200 && networkResponse.type === 'basic') {
                    const responseToCache = networkResponse.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, responseToCache);
                    });
                }
                return networkResponse;
            }).catch(() => {
                // Return cached index.html for navigation requests offline
                if (event.request.mode === 'navigate') {
                    return caches.match('/index.html');
                }
            });
        })
    );
});

// Remote Push Notification Event (FR-29)
self.addEventListener('push', (event) => {
    let payload = { title: 'Pesan Baru', body: 'Anda menerima pesan baru di Simple Chat.' };

    if (event.data) {
        try {
            payload = event.data.json();
        } catch {
            payload.body = event.data.text();
        }
    }

    const options = {
        body: payload.body || payload.notification?.body,
        icon: '/icons/icon-192.png',
        badge: '/icons/icon-192.png',
        data: payload.data || {},
        vibrate: [100, 50, 100],
    };

    event.waitUntil(
        self.registration.showNotification(payload.title || payload.notification?.title || 'Pesan Baru', options)
    );
});

// Notification Click Event
self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    event.waitUntil(
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            for (const client of clientList) {
                if ('focus' in client) {
                    return client.focus();
                }
            }
            if (self.clients.openWindow) {
                return self.clients.openWindow('/');
            }
        })
    );
});
