export default function updateProxyLinks() {
    // Get current lang identifier from the pathname (e.g. /en/, /de/, etc.)
    const pathMatch = window.location.pathname.match(/^\/(\w{2}-\w{2})(?:\/|$)/);
    const langIdentifier = pathMatch ? pathMatch[1] : null;

    // API-level check: does the browser have WebSocket and localStorage at all?
    const supportsWebsockets = (function () {
        try {
            return (
                typeof window.WebSocket !== 'undefined' &&
                typeof window.localStorage !== 'undefined'
            );
        } catch (e) {
            return false;
        }
    })();

    if (!supportsWebsockets) {
        // Do not modify link actions if novnc is not supported
        return;
    }

    document.querySelectorAll('a.result-open-proxy').forEach(function (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            if (link.dataset.wsTesting) return; // Prevent double-click while testing

            const href = link.dataset.proxyLink;
            if (!href) {
                // No proxy link — follow the original href normally.
                followOriginalLink(link);
                return;
            }

            // Build a WebSocket test URL from the SafeBrowse host embedded in the proxy link.
            let wsTestUrl;
            try {
                const proxyUrl = new URL(href);
                const wsProto = proxyUrl.protocol === 'https:' ? 'wss:' : 'ws:';
                wsTestUrl = wsProto + '//' + proxyUrl.host + '/proxy/browser-session';
            } catch (err) {
                // Malformed URL — fall back to the original link.
                followOriginalLink(link);
                return;
            }

            link.dataset.wsTesting = '1';
            link.style.opacity = '0.5';
            link.style.cursor = 'wait';

            let settled = false;
            const settle = function (ok) {
                if (settled) return;
                settled = true;
                clearTimeout(timeout);
                try { testWs.close(); } catch (_) {}

                link.style.opacity = '';
                link.style.cursor = '';
                delete link.dataset.wsTesting;

                if (ok) {
                    window.open(href, 'metagerproxy');
                } else {
                    // WebSocket check failed — fall back to the original link so the
                    // user can still reach the content without the anonymous proxy.
                    followOriginalLink(link);
                }
            };

            const testWs = new WebSocket(wsTestUrl);
            // 5-second timeout — generous enough to rule out transient slowness
            const timeout = setTimeout(function () { settle(false); }, 5000);

            testWs.addEventListener('open', function () { settle(true); });
            testWs.addEventListener('error', function () { settle(false); });
        });
    });
}

function followOriginalLink(link) {
    const href = link.getAttribute('href');
    if (!href) return;
    if (link.target === '_blank') {
        window.open(href, '_blank', 'noopener');
    } else {
        window.location.href = href;
    }
}
