const CACHE_NAME = 'erp-v1';
const urlsToCache = [
    '/',
    '/manifest.json',
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => cache.addAll(urlsToCache))
    );
});

self.addEventListener('fetch', event => {
    // 只缓存 GET 请求，跳过 API 请求
    if (event.request.method !== 'GET' || event.request.url.includes('/api/')) return;

    event.respondWith(
        caches.match(event.request).then(response => {
            return response || fetch(event.request).then(response => {
                // 缓存成功响应
                if (response.status === 200) {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
                }
                return response;
            });
        })
    );
});
