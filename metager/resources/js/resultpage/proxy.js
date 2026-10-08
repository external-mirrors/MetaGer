const WS_TEST_STORAGE_KEY = "safebrowse-ws-ok";

let wsTestInProgress = false;
// Mirrors the sessionStorage cache in memory, for browsers that deny storage access outright
// (Firefox "block all cookies" makes even reading sessionStorage throw a SecurityError). Without
// it the test result would be forgotten the instant it is produced and no click would ever be
// intercepted, silently sending exactly those users to the old proxy.
let wsTestResult = null;

// One SafeBrowse session for the results opened from this page, for browsers that cannot store
// SafeBrowse's own session id: every proxy link carries this id (#…&sid=), and such a browser
// uses it instead of starting a session per tab. Browsers with storage ignore it. SafeBrowse only
// accepts it from a same-origin referrer, so a copied link cannot hand a session to someone else
// (or a crafted one fix the session a victim then uses).
const pageSessionId = randomUuid();

function randomUuid() {
    if (window.crypto && typeof window.crypto.randomUUID === "function") {
        try { return window.crypto.randomUUID(); } catch (e) { /* insecure context (local http) */ }
    }
    const b = window.crypto.getRandomValues(new Uint8Array(16));
    b[6] = (b[6] & 0x0f) | 0x40;
    b[8] = (b[8] & 0x3f) | 0x80;
    const h = Array.from(b, function (x) { return x.toString(16).padStart(2, "0"); }).join("");
    return h.slice(0, 8) + "-" + h.slice(8, 12) + "-" + h.slice(12, 16) + "-" + h.slice(16, 20) + "-" + h.slice(20);
}

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
        const oldProxyHref = link.href;
        const href = link.dataset.proxyLink ? link.dataset.proxyLink + "&sid=" + pageSessionId : "";
        // The SafeBrowse URL is the link's own href, so that middle click, Ctrl+click and "open in
        // new tab" open SafeBrowse as well. Should WebSockets turn out not to work there,
        // SafeBrowse itself sends the tab on to the &fallback= URL (the old proxy).
        if (href) link.href = href;

        link.addEventListener("click", function (e) {
            // Intercept unless the reachability test has actually come back negative. window.open
            // must be called synchronously within the click handler — popup blockers silently drop
            // calls made after an async WebSocket test — so a click landing before the test
            // settles cannot wait for it. Guessing "reachable" is the better guess: SafeBrowse
            // bounces a genuinely unreachable backend to the &fallback= URL in the hash (this same
            // old-proxy link), whereas guessing the other way sends everyone who clicks early to
            // the old proxy with no way back. That window is every page load for users whose
            // browser denies storage, since the cached result cannot survive for them.
            if (!href) return;
            if (getCachedWebsocketResult() === "failed") {
                // Followed natively, now to the old proxy.
                link.href = oldProxyHref;
                return;
            }
            e.preventDefault();
            // Every result in a tab of its own: SafeBrowse binds each of its tabs to a tab of the
            // remote browser, and all of them share the one session.
            window.open(href, "_blank", "noopener");
        });
    });
}

/**
 * The SafeBrowse frontend needs more than WebSocket support to boot: its bundle constructs
 * EventTarget subclasses (Chrome 64+ / Safari 14+) and accesses globalThis. Browsers failing
 * either of these would hang on the SafeBrowse loading screen, so they keep the native href
 * instead. Web Storage is deliberately not required: SafeBrowse treats it as optional, and
 * merely touching window.localStorage throws in a browser configured to block all cookies —
 * which used to make this whole check fail and send those users to the old proxy.
 */
function browserSupportsSafebrowse() {
    try {
        new EventTarget();
        return (
            typeof globalThis !== "undefined" &&
            typeof window.WebSocket !== "undefined"
        );
    } catch (e) {
        return false;
    }
}

function getCachedWebsocketResult() {
    if (wsTestResult !== null) return wsTestResult;
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
        wsTestResult = ok ? "ok" : "failed";
        if (ok) {
            // Persisting it only saves the retest on the next result page; when storage is
            // blocked the in-memory result above still carries this page's clicks. A failure is
            // deliberately not persisted — it may well be transient, and it is retested on the
            // next page rather than disabling SafeBrowse for the rest of the browsing session.
            try { sessionStorage.setItem(WS_TEST_STORAGE_KEY, "ok"); } catch (e) { }
        }
    };

    const timeout = setTimeout(function () { settle(false); }, 5000);
    testWs.addEventListener("open", function () { settle(true); });
    testWs.addEventListener("error", function () { settle(false); });
}
