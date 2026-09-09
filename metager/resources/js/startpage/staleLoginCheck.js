/**
 * The startpage, restored from the back/forward cache, asking once whether it
 * is still telling the truth.
 *
 * The problem is not the HTTP cache. The startpage says `private, no-cache,
 * must-revalidate` and carries a validator (App\Http\Middleware\HttpCache::revalidatable),
 * so a stored copy is never reused without asking the server first. The
 * back/forward cache obeys none of that: it is a snapshot of a live page, kept
 * so that Back is instant, and only `Cache-Control: no-store` keeps a page out
 * of it — which the startpage cannot afford, because it would throw away the
 * validator that makes the busiest page in the product revalidate for 304
 * bytes.
 *
 * So a visitor who signs in and then presses Back gets the *logged-out*
 * startpage again, cookie in the jar and all — and on that page
 * accountBreadcrumb.js has already run, so the largest text on it reads
 * "welcome back". That is a support mail that writes itself.
 *
 * `pageshow` with `event.persisted` is exactly the signal: it is true only for
 * a restore from the back/forward cache and false for every ordinary load. So
 * this asks nothing on a normal visit — a normal visit already got its answer
 * from the server that rendered it.
 *
 * Three properties worth keeping:
 *
 *   - **Only on an anonymous render.** `#searchbar-replacement` is the landing
 *     hero, which the server emits only for a visitor it resolved as signed
 *     out. On the signed-in startpage there is nothing to re-check: a stale
 *     "you are signed in" cannot lock anyone out.
 *   - **One request per restore.** Not a poll: whatever changed happened before
 *     the snapshot was restored, so there is nothing to wait for and no reason
 *     to ask twice about the same moment. The old five-second loop this
 *     endpoint was built for (resources/js/utility.js, removed in e504c185a)
 *     was waiting for an extension that had not started yet. But every restore
 *     is its own moment and gets its own question — see the note on `inFlight`.
 *   - **It cannot loop.** The reload it triggers is an ordinary load, whose
 *     `pageshow` carries `persisted: false` — so even if the reloaded page
 *     somehow rendered anonymously again, this would not fire a second time.
 *     The `asked` flag below is the belt to that pair of braces.
 *
 * Progressive enhancement, like everything else here: with no JavaScript the
 * page is merely stale, which is what it was before this existed.
 */

/** Where the hero carries the endpoint, rendered server-side out of route(). */
const ENDPOINT_ATTRIBUTE = "data-login-check";

/**
 * @param {object} options
 * @param {Document} options.doc
 * @param {Window} options.win
 * @param {Function} options.fetcher   Injected for the tests; defaults to window.fetch.
 * @param {Function} options.reload    Injected for the tests; jsdom has no real navigation.
 * @returns {boolean} whether a listener was installed at all.
 */
export function initStaleLoginCheck(options = {}) {
    const {
        doc = document,
        win = window,
        fetcher = (...args) => win.fetch(...args),
        reload = () => win.location.reload(),
    } = options;

    const hero = doc.querySelector("#searchbar-replacement");
    if (hero === null) return false;

    const endpoint = hero.getAttribute(ENDPOINT_ATTRIBUTE);
    if (!endpoint) return false;

    // In flight, not "ever asked". A restored page is the *same* page instance
    // with its JavaScript state intact, so a flag that latches would answer for
    // the rest of that page's life: come back once while genuinely signed out
    // (401, correctly no reload), sign in elsewhere, come back again — and the
    // second restore, the stale one, would never ask. Every restore is a fresh
    // question, because the answer can have changed in between. This only stops
    // a second request while one is still open.
    let inFlight = false;

    win.addEventListener("pageshow", (event) => {
        if (!event.persisted || inFlight) return;
        inFlight = true;
        void askWhetherStillSignedOut(endpoint, fetcher, reload).finally(() => {
            inFlight = false;
        });
    });

    return true;
}

/**
 * A 200 means the server disagrees with the page the visitor is looking at, and
 * the page is the one that is out of date.
 *
 * Every failure is silent on purpose. This runs on a page that is already
 * rendered and already usable; a network error, an offline restore, or a
 * response we cannot read are all reasons to leave it alone, never to reload
 * it out from under someone.
 */
async function askWhetherStillSignedOut(endpoint, fetcher, reload) {
    try {
        const response = await fetcher(endpoint, {
            method: "POST",
            credentials: "same-origin",
            headers: { Accept: "application/json" },
        });

        if (response.ok) reload();
    } catch (e) {
        /* see above */
    }
}

// Exported for the tests; not part of the public surface.
export const _internal = { ENDPOINT_ATTRIBUTE, askWhetherStillSignedOut };
