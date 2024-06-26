let worker = {};

export default {
    init: function() {
        navigator.serviceWorker.register('/serviceworkerInstall.js', {scope: '/'})
            .then(function(registration) {})
            .catch(function(error) {});
    },
    triggerEvent: function(action, resource) {
        if(typeof worker.postMessage === 'function') console.log(worker.postMessage({action, resource}));
    },
    unset: function() {
        navigator.serviceWorker.getRegistrations().then(function(registrations) {
            for (let registration of registrations) registration.unregister();
        });
    },
    dispatchPushNotification: async function(body, icon = '/resources/images/logo.png', tag) {
        const registration = await navigator.serviceWorker.getRegistration();
        registration.showNotification("Boar", {body, icon, vibrate: [200, 100, 200, 100, 200, 100, 200], tag});
    }
}