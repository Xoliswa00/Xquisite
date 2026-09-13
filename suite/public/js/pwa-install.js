/**
 * Prompts an eligible visitor to add the site to their home screen — the
 * prerequisite for push notifications to work at all on iOS Safari, and a
 * smoother path to it on Android.
 *
 * - Android/Chromium: the browser fires `beforeinstallprompt` when the page
 *   meets install criteria (manifest + service worker + HTTPS). We capture
 *   it, suppress the browser's own mini-infobar, and show our own banner —
 *   clicking it replays the browser's real native install dialog via
 *   `deferredPrompt.prompt()`.
 * - iOS Safari: Apple exposes no install-prompt API at all — there is no
 *   event to listen for and no way to trigger the native dialog from JS.
 *   The only thing possible is a custom on-screen instruction, shown only
 *   to iOS Safari visitors who haven't already installed the page and
 *   haven't dismissed this before.
 *
 * Wire-up: a container carrying `data-install-banner`, with
 * `data-android-text` / `data-ios-text` for the copy to show in each case,
 * holding a `[data-install-text]` node, an `[data-install-action]` button
 * (Android only — hidden otherwise), and a `[data-install-dismiss]` control.
 */
(function () {
  var STORAGE_KEY = 'xq-install-banner-dismissed';
  var deferredPrompt = null;

  function dismissed() {
    try {
      return localStorage.getItem(STORAGE_KEY) === '1';
    } catch (e) {
      return false;
    }
  }

  function remember() {
    try {
      localStorage.setItem(STORAGE_KEY, '1');
    } catch (e) {}
  }

  function isStandalone() {
    return (
      (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) ||
      window.navigator.standalone === true
    );
  }

  function isIosSafari() {
    var ua = window.navigator.userAgent;
    var isIos = /iPad|iPhone|iPod/.test(ua) && !window.MSStream;
    var isSafari = /Safari/.test(ua) && !/CriOS|FxiOS|EdgiOS/.test(ua);
    return isIos && isSafari;
  }

  function banner() {
    return document.querySelector('[data-install-banner]');
  }

  function hide() {
    var el = banner();
    if (el) el.hidden = true;
  }

  function show(mode) {
    var el = banner();
    if (!el || dismissed() || isStandalone()) return;

    var text = el.querySelector('[data-install-text]');
    var action = el.querySelector('[data-install-action]');

    if (mode === 'android') {
      if (text) text.textContent = el.dataset.androidText || 'Install this app for one-tap notifications.';
      if (action) action.hidden = false;
    } else {
      if (text) text.textContent = el.dataset.iosText || 'On iPhone: tap Share, then "Add to Home Screen", to enable notifications.';
      if (action) action.hidden = true;
    }

    el.hidden = false;
  }

  window.addEventListener('beforeinstallprompt', function (e) {
    e.preventDefault();
    deferredPrompt = e;
    show('android');
  });

  window.addEventListener('appinstalled', function () {
    deferredPrompt = null;
    remember();
    hide();
  });

  document.addEventListener('DOMContentLoaded', function () {
    var el = banner();
    if (!el) return;

    el.querySelectorAll('[data-install-dismiss]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        remember();
        hide();
      });
    });

    el.querySelectorAll('[data-install-action]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        if (!deferredPrompt) return;
        deferredPrompt.prompt();
        deferredPrompt.userChoice.finally(function () {
          deferredPrompt = null;
          hide();
        });
      });
    });

    // iOS gets no event to wait for — check eligibility immediately.
    if (isIosSafari() && !isStandalone() && !dismissed()) {
      show('ios');
    }
  });
})();
