/**
 * Service Worker
 */

const _version = 'v6';
const cacheName = 'v5';
const cacheList = [
    'offline.html',
];

const log = msg => {
    console.log(`[ServiceWorker ${_version}] ${msg}`);
}


// Life cycle: INSTALL
self.addEventListener('install', event => {
    self.skipWaiting();
    log('INSTALL');
    caches.open(cacheName).then(cache => {
        log('Caching app shell');
        return cache.addAll(cacheList);
    })
});

// Life cycle: ACTIVATE
self.addEventListener('activate', event => {
    log('Activate');
    event.waitUntil(
        caches.keys().then(keyList => {
            return Promise.all(keyList.map(key => {
                if (key !== cacheName) {
                    log('Removing old cache ' + key);
                    return caches.delete(key);
                }
            }));
        })
    );
});


// Functional: PUSH
self.addEventListener('push', event => {
    log('Push ' + event.data.text());

    const title = 'My PWA!';
    const options = {
        body: event.data.text()
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', function (event) {
    log("push clicked");
    event.notification.close();
    event.waitUntil(
        clients.openWindow('http://localhost:8080/')
    )
})


self.addEventListener('fetch', (event) => {
    if (event.request.mode === 'navigate') {
        event.respondWith((async () => {
            try {
                const preloadResp = await event.preloadResponse;

                if (preloadResp) {
                    return preloadResp;
                }
                const networkResp = await fetch(event.request);
                return networkResp;
            } catch (error) {
                const cache = await caches.open(cacheName);
                const cachedResp = await cache.match('offline.html');
                return cachedResp;
            }
        })());
    }
});
