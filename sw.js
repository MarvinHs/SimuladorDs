// sw.js — Service worker del simulador.
// - Archivos estáticos (CSS, JS, íconos): caché primero, para abrir rápido.
// - Páginas: red primero; sin conexión se muestra offline.html.
// - API (api/*.php): siempre red, nunca se guarda en caché.
const VERSION = 'v1';
const STATIC_CACHE = `simuladords-static-${VERSION}`;
// Solo lo imprescindible; el resto de estáticos se guarda al usarse.
const PRECACHE = [
    '/offline.html',
    '/assets/icons/icon-192.png',
    '/assets/icons/icon-512.png',
    '/manifest.webmanifest',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then((cache) => cache.addAll(PRECACHE))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(keys.filter((k) => k !== STATIC_CACHE).map((k) => caches.delete(k))))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const req = event.request;
    if (req.method !== 'GET') return;

    const url = new URL(req.url);
    if (url.origin !== self.location.origin) return; // CDN, analytics: el navegador decide
    if (url.pathname.startsWith('/api/')) return;     // la IA siempre en vivo

    // Navegación: red primero, página sin conexión como respaldo
    if (req.mode === 'navigate') {
        event.respondWith(
            fetch(req).catch(() => caches.match('/offline.html'))
        );
        return;
    }

    // Estáticos: caché primero y actualización en segundo plano
    if (url.pathname.startsWith('/assets/') || url.pathname === '/manifest.webmanifest') {
        event.respondWith(
            caches.open(STATIC_CACHE).then(async (cache) => {
                const cached = await cache.match(req);
                const network = fetch(req)
                    .then((res) => {
                        if (res.ok) cache.put(req, res.clone());
                        return res;
                    })
                    .catch(() => cached);
                return cached || network;
            })
        );
    }
});
