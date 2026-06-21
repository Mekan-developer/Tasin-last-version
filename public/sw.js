'use strict';

const CACHE_VERSION = 'v1';
const CACHES = {
    static: `tasin-static-${CACHE_VERSION}`,
    images: `tasin-images-${CACHE_VERSION}`,
    fonts:  `tasin-fonts-${CACHE_VERSION}`,
};

const OFFLINE_URL = '/offline.html';

// ── Install: предзакешировать offline-страницу ────────────────────────────────
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHES.static)
            .then(cache => cache.addAll([OFFLINE_URL]))
            .then(() => self.skipWaiting())
    );
});

// ── Activate: удалить устаревшие кэши ────────────────────────────────────────
self.addEventListener('activate', (event) => {
    const valid = new Set(Object.values(CACHES));
    event.waitUntil(
        caches.keys()
            .then(keys => Promise.all(
                keys.filter(k => !valid.has(k)).map(k => caches.delete(k))
            ))
            .then(() => self.clients.claim())
    );
});

// ── Fetch: стратегии по типу ресурса ─────────────────────────────────────────
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Только GET
    if (request.method !== 'GET') return;
    // Только http(s)
    if (!url.protocol.startsWith('http')) return;

    // Google Fonts — cache first (CDN, меняется редко)
    if (url.hostname === 'fonts.googleapis.com' || url.hostname === 'fonts.gstatic.com') {
        event.respondWith(cacheFirst(request, CACHES.fonts));
        return;
    }

    // Статические assets Vite (имена с хешем) — cache first навсегда
    if (url.pathname.startsWith('/build/assets/')) {
        event.respondWith(cacheFirst(request, CACHES.static));
        return;
    }

    // Изображения из storage — stale-while-revalidate
    if (url.pathname.startsWith('/storage/')) {
        event.respondWith(staleWhileRevalidate(request, CACHES.images));
        return;
    }

    // Иконки и manifest — cache first
    if (url.pathname.startsWith('/icons/') || url.pathname === '/manifest.json') {
        event.respondWith(cacheFirst(request, CACHES.static));
        return;
    }

    // API витрины — только сеть (живые данные, не кешируем)
    if (url.pathname.startsWith('/api/')) return;

    // Inertia AJAX-запросы (заголовок X-Inertia) — только сеть
    if (request.headers.get('X-Inertia')) return;

    // Навигация (HTML-страницы) — network first, при ошибке — offline.html
    if (request.mode === 'navigate') {
        event.respondWith(networkFirstWithFallback(request));
        return;
    }
});

// ── Стратегии ─────────────────────────────────────────────────────────────────

async function cacheFirst(request, cacheName) {
    const cache = await caches.open(cacheName);
    const cached = await cache.match(request);
    if (cached) return cached;

    const response = await fetch(request);
    if (response.ok) cache.put(request, response.clone());
    return response;
}

async function staleWhileRevalidate(request, cacheName) {
    const cache = await caches.open(cacheName);
    const cached = await cache.match(request);

    const fresh = fetch(request).then(response => {
        if (response.ok) cache.put(request, response.clone());
        return response;
    }).catch(() => null);

    return cached ?? await fresh;
}

async function networkFirstWithFallback(request) {
    try {
        return await fetch(request);
    } catch {
        const cache = await caches.open(CACHES.static);
        return (await cache.match(OFFLINE_URL))
            ?? new Response('<h1 style="font-family:sans-serif;text-align:center;margin-top:20vh">Нет соединения</h1>',
                { status: 503, headers: { 'Content-Type': 'text/html; charset=utf-8' } });
    }
}
