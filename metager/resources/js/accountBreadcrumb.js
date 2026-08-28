/**
 * Returning-user breadcrumb.
 *
 * The problem: people sign in once, the `key` cookie later disappears (cleared
 * browsing data, browser eviction), and the startpage then looks identical to a
 * first visit — so they create a *second* key and split their token balance.
 *
 * This is a pure progressive enhancement. When a signed-in page renders, we
 * remember a single boolean in localStorage. If the user later lands on the
 * logged-out startpage and that flag is still there, the copy changes from
 * "here is what MetaGer is" to "welcome back, log in again".
 *
 * It *rewrites*, it does not reveal. The first version added a class to
 * #searchbar-replacement and unhid a paragraph, which shifted the layout when
 * the flag fired and — because the class also hid `> .helper-line` — silently
 * took the small-print duplicate warning with it, since that element carried
 * both classes. Swapping three strings in place cannot do either: the elements
 * are always present, always the same size class, and nothing is hidden.
 *
 * Deliberately a boolean and not a key fingerprint: the copy never names the
 * key, so there is nothing to gain from storing a key-derived value on a
 * privacy-focused site. Without JS the startpage still leads with "log in" (see
 * index.blade.php); this only sharpens it.
 *
 * The flag is cleared on a deliberate logout (the settings and sidebar "remove
 * key" links, and the keymanager's own logout — all same origin) so a stale
 * breadcrumb never nags someone who left on purpose.
 */

const STORAGE_KEY = "mg_account_seen";

/** True when this browser has rendered a signed-in MetaGer page before. */
function hasSeenAccount() {
    try {
        const raw = window.localStorage.getItem(STORAGE_KEY);
        if (!raw) return false;
        return JSON.parse(raw).seen === true;
    } catch (e) {
        // Private mode, disabled storage, or corrupt JSON — treat as "nothing remembered".
        return false;
    }
}

function rememberAccount() {
    try {
        window.localStorage.setItem(STORAGE_KEY, JSON.stringify({ seen: true, ts: Date.now() }));
    } catch (e) {
        /* nothing we can do, and nothing depends on it succeeding */
    }
}

function forgetAccount() {
    try {
        window.localStorage.removeItem(STORAGE_KEY);
    } catch (e) {
        /* see rememberAccount */
    }
}

/**
 * On a signed-in page, set the flag.
 *
 * `#sidebar-key-remove` is the marker: the sidebar account block renders it only
 * for a key-guard user who owns a key we can name, and the sidebar is on both
 * the startpage and the result page. A webextension visitor deliberately does
 * not get one — their key never reaches us and the extension keeps them signed
 * in anyway, so there is nothing for a breadcrumb to rescue.
 */
export function recordSignedIn(doc = document) {
    if (doc.querySelector("#sidebar-key-remove")) rememberAccount();
}

/**
 * On the logged-out startpage, swap the copy for the returning-user copy.
 *
 * Every replacement string is rendered server-side into a data-* attribute, so
 * this module never builds user-visible German (or Catalan, or any of the other
 * twenty-eight locales) and the translation stays in lang/.
 */
export function enhanceLoggedOutStartpage(doc = document) {
    const replacement = doc.querySelector("#searchbar-replacement");
    if (!replacement) return false;
    if (!hasSeenAccount()) return false;

    const swaps = [
        ["[data-hook-line]", "welcomeBackHook"],
        ["[data-helper-line]", "welcomeBackMessage"],
        ["[data-login-button]", "welcomeBackButton"],
    ];

    for (const [selector, datasetKey] of swaps) {
        const target = replacement.querySelector(selector);
        const text = replacement.dataset[datasetKey];
        // A missing string means the blade and this module disagree. Leaving the
        // original copy in place is correct — it is not wrong, only more generic.
        if (target && text) target.textContent = text;
    }

    replacement.setAttribute("data-returning", "true");
    return true;
}

/**
 * Deliberate logout must forget the breadcrumb.
 *
 * One selector, not two: `#remove-key` was the settings page's own logout
 * button, and the settings page no longer has an account block — the sidebar's
 * is on that page like every other.
 */
export function bindLogoutClears(doc = document) {
    const el = doc.querySelector("#sidebar-key-remove");
    if (el) el.addEventListener("click", forgetAccount);
}

export function initAccountBreadcrumb(doc = document) {
    recordSignedIn(doc);
    enhanceLoggedOutStartpage(doc);
    bindLogoutClears(doc);
}

// Exported for the tests; not part of the public surface.
export const _internal = { STORAGE_KEY, hasSeenAccount, rememberAccount, forgetAccount };
