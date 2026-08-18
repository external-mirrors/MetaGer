/**
 * Relative time formatting for result timestamps ("3 days ago").
 *
 * This used to be moment.js. moment resolves its locales through a dynamic
 * require, which webpack answered by bundling all ~130 of them into every entry
 * that imported it — the single largest thing in the result page bundle. Vite
 * cannot resolve that require at all, so under Vite moment would have silently
 * fallen back to English for every non-English visitor.
 *
 * Intl.RelativeTimeFormat is in the browser already, in every locale the browser
 * supports, at no download cost.
 */

/**
 * How many of each unit make up the next one, smallest first. The final entry
 * has no ceiling, so the loop in formatRelativeTime always terminates on years.
 */
const UNITS = [
    { perNext: 60, unit: "second" },
    { perNext: 60, unit: "minute" },
    { perNext: 24, unit: "hour" },
    { perNext: 7, unit: "day" },
    { perNext: 4.34524, unit: "week" },
    { perNext: 12, unit: "month" },
    { perNext: Infinity, unit: "year" },
];

/**
 * Constructing an Intl formatter is the expensive part of using one, and the
 * result page reformats every timestamp again on each resultsChanged event.
 */
const formatters = new Map();

function formatterFor(locale) {
    let formatter = formatters.get(locale);

    if (formatter === undefined) {
        formatter = new Intl.RelativeTimeFormat(locale, { numeric: "auto" });
        formatters.set(locale, formatter);
    }

    return formatter;
}

/**
 * Render an offset in seconds as relative time.
 *
 * @param {number} seconds Signed offset from now; negative is in the past.
 * @param {string} locale  BCP 47 tag, e.g. "de-DE".
 * @returns {string}
 */
export function formatRelativeTime(seconds, locale) {
    let amount = seconds;

    for (const { perNext, unit } of UNITS) {
        if (Math.abs(amount) < perNext) {
            return formatterFor(locale).format(Math.round(amount), unit);
        }

        amount /= perNext;
    }
}

/**
 * Rewrite every <span class="date" data-timestamp="…"> in the document.
 *
 * The timestamps are unix seconds. Anything unparseable is left as the server
 * rendered it, which is a readable absolute date.
 *
 * @param {Document|Element} root
 * @param {number} nowSeconds
 */
export function renderRelativeDates(root = document, nowSeconds = Date.now() / 1000) {
    const locale = root.ownerDocument?.documentElement?.lang
        || document.documentElement.getAttribute("lang")
        || "en";

    root.querySelectorAll("span.date").forEach((element) => {
        const timestamp = Number(element.dataset.timestamp);

        if (!Number.isFinite(timestamp)) {
            return;
        }

        element.textContent = formatRelativeTime(timestamp - nowSeconds, locale);
    });
}
