const WS_TEST_STORAGE_KEY = "safebrowse-ws-ok";

let wsTestInProgress = false;

export default function updateProxyLinks() {
    if (!browserSupportsSafebrowse()) {
        // Links keep their native href (old proxy) when SafeBrowse won't work in this browser
        return;
    }

    const links = document.querySelectorAll("a.result-open-proxy");
    if (links.length === 0) return;

    // Start the reachability test early so the result is usually cached before the first click
    testWebsocketConnection(links[0].dataset.proxyLink);

    links.forEach(function (link) {
        if (link.dataset.proxyHandlerAttached) return; // updateProxyLinks runs again after "load more"
        link.dataset.proxyHandlerAttached = "1";

        link.addEventListener("click", function (e) {
            const href = link.dataset.proxyLink;
            // Only intercept when SafeBrowse is known to be reachable. window.open must be
            // called synchronously within the click handler — popup blockers silently drop
            // calls made after an async WebSocket test. In all other cases the browser
            // follows the native href (old proxy) instead.
            if (!href || getCachedWebsocketResult() !== "ok") return;
            e.preventDefault();
            // Reuses the named tab if already open: since all SafeBrowse parameters travel in
            // the URL hash, this only fires hashchange there instead of reloading the app.
            const proxyWindow = window.open(href, "metagerproxy");
            if (proxyWindow) proxyWindow.focus();
        });
    });
}

/**
 * The SafeBrowse frontend needs more than WebSocket support to boot: its bundle constructs
 * EventTarget subclasses (Chrome 64+ / Safari 14+) and accesses globalThis and Web Storage.
 * Browsers failing any of these would hang on the SafeBrowse loading screen, so they keep
 * the native href instead.
 */
function browserSupportsSafebrowse() {
    try {
        new EventTarget();
        return (
            typeof globalThis !== "undefined" &&
            typeof window.WebSocket !== "undefined" &&
            typeof window.localStorage !== "undefined" &&
            typeof window.sessionStorage !== "undefined"
        );
    } catch (e) {
        return false;
    }
}

function getCachedWebsocketResult() {
    try {
        return sessionStorage.getItem(WS_TEST_STORAGE_KEY);
    } catch (e) {
        return null;
    }
}

/**
 * Tests whether a WebSocket connection to the SafeBrowse host can be established (some
 * networks and browser configurations block WebSockets). A success is cached for the
 * browsing session; failures are not cached, so the test is retried on the next page.
 */
function testWebsocketConnection(proxyLink) {
    if (wsTestInProgress || !proxyLink || getCachedWebsocketResult() === "ok") return;

    let wsTestUrl;
    try {
        const proxyUrl = new URL(proxyLink, window.location.href);
        const wsProto = proxyUrl.protocol === "https:" ? "wss:" : "ws:";
        wsTestUrl = wsProto + "//" + proxyUrl.host + "/proxy/browser-session";
    } catch (e) {
        return;
    }

    let testWs;
    try {
        testWs = new WebSocket(wsTestUrl);
    } catch (e) {
        // e.g. blocked by Content-Security-Policy — some browsers throw synchronously here
        return;
    }
    wsTestInProgress = true;

    let settled = false;
    const settle = function (ok) {
        if (settled) return;
        settled = true;
        wsTestInProgress = false;
        clearTimeout(timeout);
        try { testWs.close(); } catch (e) { }
        if (ok) {
            try { sessionStorage.setItem(WS_TEST_STORAGE_KEY, "ok"); } catch (e) { }
        }
    };

    const timeout = setTimeout(function () { settle(false); }, 5000);
    testWs.addEventListener("open", function () { settle(true); });
    testWs.addEventListener("error", function () { settle(false); });
}
