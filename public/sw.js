/**
 * Service worker Adventiste — met en cache les assets Vite pour usage dégradé hors ligne.
 * Les pages HTML restent chargées depuis le réseau (session Laravel).
 */
const CACHE = 'adventiste-assets-v1';

self.addEventListener('install', (event) => {
    event.waitUntil(self.skipWaiting());
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k))),
        ),
    );
    event.waitUntil(self.clients.claim());
});

self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);
    if (url.origin !== self.location.origin) {
        return;
    }
    if (!url.pathname.includes('/build/assets/')) {
        return;
    }
    event.respondWith(
        caches.open(CACHE).then(async (cache) => {
            try {
                const res = await fetch(event.request);
                if (res.ok) {
                    cache.put(event.request, res.clone());
                }
                return res;
            } catch {
                const cached = await cache.match(event.request);
                if (cached) {
                    return cached;
                }
                throw new Error('offline');
            }
        }),
    );
});
