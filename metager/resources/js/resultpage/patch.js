/**
 * Bring the result list up to date with a freshly rendered one, touching only what changed.
 *
 * Load-more used to swap the whole list through innerHTML whenever an engine answered. Every
 * result was a new node, so whatever the reader was doing went with it: the result under the
 * pointer moved, keyboard focus and text selections were lost, and the browser's own scroll
 * anchoring had nothing to anchor to. With Serper's late links that swap could happen for half a
 * minute after the page loaded.
 *
 * Now each top-level element is matched by key — a result by its hash, anything else by what it
 * is and the result it follows — and kept as it is when the server rendered it the same way. New
 * results are inserted at their rank, changed ones replaced in place, and if that moved what the
 * reader was looking at, the page scrolls back to it.
 *
 * "The same way" is compared against the markup the server sent, not the live node: the page
 * reformats dates and rewrites proxy links after load, so live nodes never match a fresh render.
 * {@link rememberServerMarkup} records the page's own markup before any of that runs.
 */

/** element => its markup as the server rendered it, minus the attributes synced in place */
const serverMarkup = new WeakMap();

/** Position attributes, which every result inserted above changes; updated rather than compared. */
const POSITION_ATTRIBUTES = ["data-index", "data-target"];

/**
 * Images go through /meta/picture?data=…, whose data is encrypted with a fresh IV and an expiry
 * one day from now on every render, so the same image never has the same URL twice. Compared as
 * is, every result with an image looked changed on every poll, and was replaced, image and all.
 * The old URL stays valid for a day, so keeping it is safe.
 */
const PICTURE_DATA = /(\/meta\/picture\?data=)[^"&]*/g;

/**
 * Record the server's markup for every element in `container`. Call before anything else on the
 * page modifies the list.
 */
export function rememberServerMarkup(container) {
    for (const element of container.children) {
        serverMarkup.set(element, markupOf(element));
    }
}

/**
 * Patch `container` to match `html`, the inner markup of a freshly rendered result list.
 */
export function patchResults(container, html) {
    const template = document.createElement("template");
    template.innerHTML = html;

    const anchor = scrollAnchor(container);
    const anchorTop = anchor?.getBoundingClientRect().top;

    const current = new Map();
    keyed(container.children).forEach(([key, element]) => current.set(key, element));

    const next = keyed(template.content.children).map(([key, element]) => {
        const markup = markupOf(element);
        const existing = current.get(key);

        if (existing !== undefined && serverMarkup.get(existing) === markup) {
            current.delete(key);
            syncPositions(existing, element);
            return existing;
        }

        // A replaced element stays in `current`, so it is removed with the ones that are gone.
        if (existing !== undefined) {
            reopenFolds(existing, element);
        } else if (key.startsWith("result:")) {
            element.classList.add("result-arrived");
        }
        serverMarkup.set(element, markup);
        return element;
    });

    for (const gone of current.values()) {
        gone.remove();
    }
    next.forEach((element, position) => {
        const at = container.children[position] ?? null;
        if (at !== element) {
            container.insertBefore(element, at);
        }
    });

    if (anchor?.isConnected) {
        const moved = anchor.getBoundingClientRect().top - anchorTop;
        if (moved !== 0) {
            window.scrollBy(0, moved);
        }
    }
}

/**
 * Key every element: a result by its hash; anything else — news, videos, ad-blocker hints,
 * alerts, the pager — by what it is and the result it follows, which stays put when results are
 * inserted elsewhere.
 */
function keyed(elements) {
    const seen = new Map();
    let previous = "start";

    return [...elements].map((element) => {
        let key;
        if (element.dataset.count !== undefined) {
            key = "result:" + element.dataset.count;
            previous = key;
        } else {
            key = element.tagName + "." + element.className + "#" + element.id + " after " + previous;
        }

        const occurrence = seen.get(key) ?? 0;
        seen.set(key, occurrence + 1);
        return [occurrence === 0 ? key : key + " " + occurrence, element];
    });
}

function markupOf(element) {
    const copy = element.cloneNode(true);
    POSITION_ATTRIBUTES.forEach((attribute) => copy.removeAttribute(attribute));
    return copy.outerHTML.replace(PICTURE_DATA, "$1");
}

function syncPositions(existing, rendered) {
    for (const attribute of POSITION_ATTRIBUTES) {
        const value = rendered.getAttribute(attribute);
        if (value !== null && existing.getAttribute(attribute) !== value) {
            existing.setAttribute(attribute, value);
        }
    }
}

/**
 * A result is replaced when a late engine adds to its host fold (Search\HostCrowding), and a fold
 * someone had opened must not snap shut under them because of it.
 */
function reopenFolds(existing, replacement) {
    const open = new Set(
        [...existing.querySelectorAll("details.result-more-from-host[open]")].map((fold) => fold.dataset.host)
    );
    for (const fold of replacement.querySelectorAll("details.result-more-from-host")) {
        if (open.has(fold.dataset.host)) {
            fold.open = true;
        }
    }
}

/**
 * The first element of the list still on screen, which is what the reader is looking at — or
 * nothing at the very top of the page, where what arrives above should simply be seen. Browsers
 * that anchor scrolling themselves do the same; measuring after the patch then finds nothing moved.
 */
function scrollAnchor(container) {
    if (window.scrollY === 0) {
        return null;
    }
    for (const element of container.children) {
        const box = element.getBoundingClientRect();
        if (box.bottom > 0) {
            return box.top < window.innerHeight ? element : null;
        }
    }
    return null;
}
