/*
 * 自毁型 Service Worker（kill switch）
 * ------------------------------------------------------------
 * 旧版 sw.js 使用 cache-first 缓存了所有 GET 页面（含首页 HTML 与 CSRF token），
 * 导致代码修复后浏览器仍展示旧的仪表盘页面、点击"退出"时因 token 过期返回 419。
 * 这里保留该文件并让其自动清理缓存 + 注销自身，已有客户端下次访问即可恢复。
 */
self.addEventListener('install', () => {
    self.skipWaiting();
});

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys()
            .then(keys => Promise.all(keys.map(key => caches.delete(key))))
            .catch(() => {})
            .then(() => self.registration.unregister())
            .then(() => self.clients.matchAll({ type: 'window' }))
            .then(clients => {
                clients.forEach(client => {
                    try {
                        client.navigate(client.url);
                    } catch (e) {
                        // 跨域或不可控客户端，忽略
                    }
                });
            })
            .catch(() => {})
    );
});

// 注销过程中不再拦截任何请求，全部走网络
self.addEventListener('fetch', () => {});
