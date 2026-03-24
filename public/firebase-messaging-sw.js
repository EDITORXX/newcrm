try {
    importScripts('https://www.gstatic.com/firebasejs/10.14.1/firebase-app-compat.js');
    importScripts('https://www.gstatic.com/firebasejs/10.14.1/firebase-messaging-compat.js');
    firebase.initializeApp({"apiKey":"AIzaSyBJJ-kxw1FguyDMHAOplrMcEYv8FffvP0U","authDomain":"base-crm-5ed6c.firebaseapp.com","projectId":"base-crm-5ed6c","storageBucket":"base-crm-5ed6c.firebasestorage.app","messagingSenderId":"665396257954","appId":"1:665396257954:web:7af92dafea8e1de75be3fb"});
    var messaging = firebase.messaging();
    messaging.onBackgroundMessage(function(payload) {
        var data = payload.data || {};
        var notification = payload.notification || {};
        var title = notification.title || data.title || 'New Notification';
        return self.registration.showNotification(title, {
            body: notification.body || data.body || '',
            icon: '/icon-192.png',
            badge: '/icon-192.png',
            tag: data.tag || 'crm-notification',
            requireInteraction: true,
            data: { url: data.url || data.click_action || '/' }
        });
    });
} catch(e) {
    console.warn('Firebase SW init skipped:', e);
}

self.addEventListener('push', function(event) {
    if (event.data) {
        try {
            var payload = event.data.json();
            var n = payload.notification || payload.data || {};
            var title = n.title || 'New Notification';
            event.waitUntil(self.registration.showNotification(title, {
                body: n.body || '',
                icon: '/icon-192.png',
                badge: '/icon-192.png',
                tag: n.tag || 'crm-notification',
                requireInteraction: true,
                data: { url: n.url || n.click_action || '/' }
            }));
        } catch(e) {}
    }
});

self.addEventListener('notificationclick', function(event) {
    event.notification.close();
    var url = (event.notification.data && event.notification.data.url) || '/';
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function(clientList) {
            for (var i = 0; i < clientList.length; i++) {
                if (clientList[i].url.indexOf(url) !== -1 && 'focus' in clientList[i]) return clientList[i].focus();
            }
            if (clients.openWindow) return clients.openWindow(url);
        })
    );
});