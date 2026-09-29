/*
 * Xquisite first-party page stats. No cookies, no personal data: scroll depth, time on
 * page, and where clicks land. Reports when the page is hidden or closed.
 */
(function () {
    var s = document.currentScript;
    if (!s || !navigator.sendBeacon) return;

    var pv = s.getAttribute('data-pv'), t = s.getAttribute('data-t'), endpoint = s.getAttribute('data-endpoint');
    var clicks = [], maxScroll = 0, visibleMs = 0, shownAt = document.visibilityState === 'visible' ? Date.now() : 0, sent = 0;

    function docHeight() { return Math.max(document.documentElement.scrollHeight, document.body ? document.body.scrollHeight : 0); }
    function docWidth() { return Math.max(document.documentElement.scrollWidth, window.innerWidth); }

    function scroll() {
        var seen = (window.pageYOffset || document.documentElement.scrollTop) + window.innerHeight;
        var pct = Math.round(Math.min(1, seen / Math.max(1, docHeight())) * 100);
        if (pct > maxScroll) maxScroll = pct;
    }

    document.addEventListener('click', function (e) {
        if (clicks.length + sent >= 300) return;
        var el = e.target.closest ? e.target.closest('a, button, [data-track], [role=button], input[type=submit], label, summary') : null;
        var kind = 'other', label = '', href = '';
        if (el) {
            var tag = el.tagName.toLowerCase();
            kind = tag === 'a' ? 'link' : (tag === 'button' || tag === 'input' || el.getAttribute('role') === 'button' ? 'button' : 'other');
            label = el.getAttribute('data-track') || el.getAttribute('aria-label') || (el.innerText || el.value || el.title || '').replace(/\s+/g, ' ').trim();
            if (tag === 'a' && el.href) {
                try { var u = new URL(el.href, location.href); href = u.origin === location.origin ? u.pathname : u.hostname; } catch (err) {}
            }
        }
        clicks.push({
            x: Math.round(e.pageX / docWidth() * 10000) / 100, y: Math.round(e.pageY),
            v: window.innerWidth < 768 ? 'mobile' : 'desktop', k: kind, l: label.slice(0, 80), h: href.slice(0, 255)
        });
        if (clicks.length >= 10) flush();
    }, true);

    window.addEventListener('scroll', scroll, { passive: true });
    scroll();

    function flush() {
        scroll();
        var seconds = Math.round((visibleMs + (shownAt ? Date.now() - shownAt : 0)) / 1000);
        var body = new FormData();
        body.append('pv', pv); body.append('t', t); body.append('scroll', maxScroll); body.append('seconds', seconds);
        body.append('clicks', JSON.stringify(clicks.slice(0, 30)));
        sent += Math.min(30, clicks.length);
        clicks = clicks.slice(30);
        navigator.sendBeacon(endpoint, body);
    }

    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'hidden') {
            if (shownAt) { visibleMs += Date.now() - shownAt; shownAt = 0; }
            flush();
        } else { shownAt = Date.now(); }
    });
    window.addEventListener('pagehide', function () { if (shownAt) { visibleMs += Date.now() - shownAt; shownAt = 0; } flush(); });
})();
