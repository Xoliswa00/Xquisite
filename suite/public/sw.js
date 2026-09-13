// Service worker for browser push notifications (staff/admin area and the
// public booking portal share this one file — served from the site root so
// its scope covers both). Nothing here needs a build step; edit and reload.

self.addEventListener('install', (event) => {
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(self.clients.claim());
});

self.addEventListener('push', (event) => {
  if (!event.data) {
    return;
  }

  let payload;
  try {
    payload = event.data.json();
  } catch (e) {
    payload = { title: 'Xquisite Creations', body: event.data.text() };
  }

  const title = payload.title || 'Xquisite Creations';
  const options = {
    body: payload.body || '',
    icon: payload.icon || '/img/android-icon-192x192.png',
    badge: payload.badge || '/img/favicon-96x96.png',
    tag: payload.tag || undefined,
    renotify: !!payload.tag,
    requireInteraction: !!payload.requireInteraction,
    data: payload.data || {},
  };

  event.waitUntil(self.registration.showNotification(title, options));
});

// Clicking the notification focuses an already-open tab on the target URL if
// one exists, otherwise opens a new one — rather than always spawning a tab.
self.addEventListener('notificationclick', (event) => {
  event.notification.close();

  const targetUrl = (event.notification.data && event.notification.data.url) || '/';

  event.waitUntil(
    self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windows) => {
      for (const client of windows) {
        if (client.url === targetUrl && 'focus' in client) {
          return client.focus();
        }
      }
      if (self.clients.openWindow) {
        return self.clients.openWindow(targetUrl);
      }
    })
  );
});
