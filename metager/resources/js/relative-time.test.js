import { describe, expect, it } from "vitest";

import { formatRelativeTime, renderRelativeDates } from "./relative-time";

const MINUTE = 60;
const HOUR = 60 * MINUTE;
const DAY = 24 * HOUR;

/**
 * These pin the output of the Intl.RelativeTimeFormat replacement for moment.js.
 *
 * The point of the swap was that moment's locales were resolved through a
 * dynamic require: webpack bundled all of them, Vite bundles none of them. So
 * the German expectations below are the ones that matter — under Vite with
 * moment still in place they would all have come out in English.
 */
describe("formatRelativeTime", () => {
    it("describes the recent past in the requested language", () => {
        expect(formatRelativeTime(-30, "en-GB")).toBe("30 seconds ago");
        expect(formatRelativeTime(-30, "de-DE")).toBe("vor 30 Sekunden");
        expect(formatRelativeTime(-30, "es-ES")).toBe("hace 30 segundos");
    });

    it("picks the largest unit the span still reads naturally in", () => {
        expect(formatRelativeTime(-5 * MINUTE, "en-GB")).toBe("5 minutes ago");
        expect(formatRelativeTime(-3 * HOUR, "en-GB")).toBe("3 hours ago");
        expect(formatRelativeTime(-3 * DAY, "en-GB")).toBe("3 days ago");
        expect(formatRelativeTime(-3 * 7 * DAY, "en-GB")).toBe("3 weeks ago");
        expect(formatRelativeTime(-90 * DAY, "en-GB")).toBe("3 months ago");
        expect(formatRelativeTime(-3 * 365 * DAY, "en-GB")).toBe("3 years ago");
    });

    it("uses words rather than numbers where the language has them", () => {
        // numeric: "auto" — this is what makes it read like moment's fromNow().
        expect(formatRelativeTime(-DAY, "en-GB")).toBe("yesterday");
        expect(formatRelativeTime(-DAY, "de-DE")).toBe("gestern");
    });

    it("handles the future, which a result timestamp occasionally is", () => {
        expect(formatRelativeTime(2 * HOUR, "en-GB")).toBe("in 2 hours");
        expect(formatRelativeTime(2 * HOUR, "de-DE")).toBe("in 2 Stunden");
    });

    it("falls back to the base language when a region is unknown", () => {
        expect(formatRelativeTime(-3 * DAY, "de-XX")).toBe("vor 3 Tagen");
    });
});

describe("renderRelativeDates", () => {
    it("rewrites every timestamped span and leaves the rest alone", () => {
        document.documentElement.lang = "de-DE";
        document.body.innerHTML = `
            <span class="date" data-timestamp="1000"></span>
            <span class="date" data-timestamp="not-a-number">unverändert</span>
            <span class="other">unverändert</span>
        `;

        renderRelativeDates(document, 1000 + 3 * DAY);

        const spans = document.querySelectorAll("span");

        expect(spans[0].textContent).toBe("vor 3 Tagen");
        expect(spans[1].textContent).toBe("unverändert");
        expect(spans[2].textContent).toBe("unverändert");
    });

    it("reads the language off the document, as the result page sets it", () => {
        document.documentElement.lang = "en-GB";
        document.body.innerHTML = '<span class="date" data-timestamp="0"></span>';

        renderRelativeDates(document, 3 * DAY);

        expect(document.querySelector("span.date").textContent).toBe("3 days ago");
    });
});
