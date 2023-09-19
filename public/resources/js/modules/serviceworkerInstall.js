const serviceworkerConfigs = {
    cacheResponseName: "v1",
    networkErrorMessage: "Network error happened",
    responseError: 408
};

const serviceWorkerManager = {
    async addResourcesToCache(resources) {
        const cache = await caches.open(serviceworkerConfigs.cacheResponseName);
        await cache.addAll(resources);
    },
    async putInCache(request, response) {
        const cache = await caches.open(serviceworkerConfigs.cacheResponseName);
        await cache.put(request, response);
    },
    async cacheFirst({request, preloadResponsePromise, fallbackUrl}) {
        const responseFromCache = await caches.match(request);
        if (responseFromCache) return responseFromCache;
    
        const preloadResponse = await preloadResponsePromise;
        if (preloadResponse) {
            putInCache(request, preloadResponse.clone());
            return preloadResponse;
        }
    
        try {
            const responseFromNetwork = await fetch(request);
            putInCache(request, responseFromNetwork.clone());
            return responseFromNetwork;
        } catch (error) {
            const fallbackResponse = await caches.match(fallbackUrl);
            if (fallbackResponse) return fallbackResponse;
            return new Response(serviceworkerConfigs.networkErrorMessage, {
                status: serviceworkerConfigs.responseError,
                headers: {
                    "Content-Type": "text/plain"
                },
            });
        }
    },
    async enableNavigationPreload() {
        if (self.registration.navigationPreload) {
            await self.registration.navigationPreload.enable();
        }
    },
    async deleteCache(key) {
        await caches.delete(key);
    },
    async deleteOldCaches() {
        const cacheKeepList = [serviceworkerConfigs.cacheResponseName];
        const keyList = await caches.keys();
        const cachesToDelete = keyList.filter((key) => !cacheKeepList.includes(key));
        await Promise.all(cachesToDelete.map(deleteCache));
    },
};

self.addEventListener("activate", function(event) {
    event.waitUntil(serviceWorkerManager.deleteOldCaches());
    event.waitUntil(serviceWorkerManager.enableNavigationPreload());
});

self.addEventListener("install", function(event) {
    event.waitUntil(
        serviceWorkerManager.addResourcesToCache([
            "/favicon.ico",
            "/images/logo.png"
        ]),
    );
});

self.addEventListener("fetch", function(event) {
    event.respondWith(serviceWorkerManager.cacheFirst({request: event.request, preloadResponsePromise: event.preloadResponse, fallbackUrl: "/favicon.ico"}));
});

self.addEventListener("message", function(event) {
    console.log("Message received ", event);
});

export default serviceWorkerManager;