/**
 * Browser push opt-in. Loaded as a plain <script src> (not through Vite) so
 * this never depends on remembering `npm run build` — edit and reload.
 *
 * Reads its config from data attributes on <body> (set per-layout):
 *   data-push-subscribe-url, data-push-unsubscribe-url, data-push-vapid-key
 * and the CSRF token both layouts already expose via
 * <meta name="csrf-token">.
 *
 * Exposes two globals: requestBrowserNotificationPermission() — the name the
 * staff layout's notification-panel button already called before this file
 * existed (previously an undefined function; wiring it up here, not
 * renaming it) — and disableBrowserNotifications() for the customer portal's
 * off switch.
 */
(function () {
  function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);
    for (let i = 0; i < rawData.length; ++i) {
      outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
  }

  function csrfToken() {
    const el = document.querySelector('meta[name="csrf-token"]');
    return el ? el.content : '';
  }

  function config() {
    const b = document.body.dataset;
    return {
      subscribeUrl: b.pushSubscribeUrl,
      unsubscribeUrl: b.pushUnsubscribeUrl,
      vapidKey: b.pushVapidKey,
    };
  }

  async function enablePushNotifications() {
    if (!('serviceWorker' in navigator) || !('PushManager' in window) || !('Notification' in window)) {
      alert('Push notifications aren’t supported in this browser.');
      return;
    }

    const { subscribeUrl, vapidKey } = config();
    if (!subscribeUrl || !vapidKey) {
      return;
    }

    const permission = await Notification.requestPermission();
    if (permission !== 'granted') {
      alert('Notifications are blocked. Enable them in your browser’s site settings to turn this on.');
      return;
    }

    const registration = await navigator.serviceWorker.register('/sw.js');
    await navigator.serviceWorker.ready;

    let subscription = await registration.pushManager.getSubscription();
    if (!subscription) {
      subscription = await registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: urlBase64ToUint8Array(vapidKey),
      });
    }

    const json = subscription.toJSON();
    const res = await fetch(subscribeUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken(), Accept: 'application/json' },
      body: JSON.stringify({ endpoint: json.endpoint, keys: json.keys }),
    });

    if (!res.ok) {
      throw new Error('Subscribe request failed: ' + res.status);
    }
  }

  async function disablePushNotifications() {
    const { unsubscribeUrl } = config();
    if (!('serviceWorker' in navigator)) {
      return;
    }

    const registration = await navigator.serviceWorker.getRegistration('/sw.js');
    const subscription = registration && (await registration.pushManager.getSubscription());

    if (subscription && unsubscribeUrl) {
      const endpoint = subscription.endpoint;
      await subscription.unsubscribe();
      await fetch(unsubscribeUrl, {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken(), Accept: 'application/json' },
        body: JSON.stringify({ endpoint }),
      });
    }
  }

  window.requestBrowserNotificationPermission = function () {
    enablePushNotifications().catch(function (e) {
      console.error(e);
      alert('Could not enable notifications. Please try again.');
    });
  };

  window.disableBrowserNotifications = function () {
    disablePushNotifications().catch(function (e) {
      console.error(e);
    });
  };
})();
