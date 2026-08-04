// Service worker for the POS terminal only (registered with scope: '/pos').
// Keeps /pos and its same-origin built assets available offline so a
// refresh or reopened tab still renders the terminal — including the
// product catalog, which is server-rendered inline into the page rather
// than fetched separately, so caching the page itself is enough to cover it.
//
// Bump CACHE_VERSION on any change to this file's caching behavior; the
// activate handler purges every cache that doesn't match the current name.
const CACHE_VERSION = 'pos-v1';
const NAV_TIMEOUT_MS = 3000;

self.addEventListener('install', (event) => {
    // Take over immediately on the next activate rather than waiting for
    // every open /pos tab to close first (the standard SW-update trap).
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        (async () => {
            const keys = await caches.keys();
            await Promise.all(
                keys.filter((key) => key !== CACHE_VERSION).map((key) => caches.delete(key))
            );
            await self.clients.claim();
        })()
    );
});

async function networkFirstWithTimeout(request) {
    const cache = await caches.open(CACHE_VERSION);

    return new Promise((resolve) => {
        let settled = false;

        const timer = setTimeout(async () => {
            if (settled) return;
            settled = true;
            const cached = await cache.match(request);
            resolve(cached || fetch(request));
        }, NAV_TIMEOUT_MS);

        fetch(request).then(async (response) => {
            if (settled) return; // already resolved from cache after timeout
            clearTimeout(timer);
            settled = true;
            cache.put(request, response.clone());
            resolve(response);
        }).catch(async () => {
            if (settled) return;
            clearTimeout(timer);
            settled = true;
            const cached = await cache.match(request);
            resolve(cached || Response.error());
        });
    });
}

async function cacheFirst(request) {
    const cache = await caches.open(CACHE_VERSION);
    const cached = await cache.match(request);
    if (cached) return cached;

    const response = await fetch(request);
    cache.put(request, response.clone());
    return response;
}

self.addEventListener('fetch', (event) => {
    const { request } = event;

    // Never touch non-GET requests — /pos/checkout's POST must always hit
    // the network directly; offline handling for it lives in page JS.
    if (request.method !== 'GET') return;
    if (new URL(request.url).origin !== self.location.origin) return;

    if (request.mode === 'navigate') {
        event.respondWith(networkFirstWithTimeout(request));
        return;
    }

    // Vite's built assets are content-hashed/immutable — safe to cache-first.
    if (new URL(request.url).pathname.startsWith('/build/')) {
        event.respondWith(cacheFirst(request));
    }
});

// Background Sync API (where supported) — the sync event itself can't touch
// localStorage (a service worker has no synchronous storage access to the
// page's data), so it just wakes any open /pos tab to run its own
// trySyncQueue(), which owns the actual queue and retry logic.
self.addEventListener('sync', (event) => {
    if (event.tag !== 'pos-sync-offline-queue') return;

    event.waitUntil(
        (async () => {
            const clients = await self.clients.matchAll({ type: 'window' });
            clients.forEach((client) => client.postMessage({ type: 'pos-sync-trigger' }));
        })()
    );
});
