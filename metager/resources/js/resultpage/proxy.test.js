import { describe, expect, it } from "vitest";

import { tabSessionId } from "./proxy";

const ORIGIN = "https://metager.de";
const KEPT = "0f8fad5b-d9cb-469f-a165-70867728950e";

/** A stand-in for the tab: its name and where it is. */
function tab(name) {
    return { name, location: { origin: ORIGIN } };
}

/**
 * Browsers that block storage open each result into the session whose id the links carry, so the
 * id has to outlive the results page: otherwise every new search starts (and charges) a session.
 */
describe("tabSessionId", () => {
    it("keeps the tab's id across our own pages", () => {
        const win = tab("safebrowse-sid:" + KEPT);

        expect(tabSessionId(win, ORIGIN + "/meta/meta.ger3?eingabe=a")).toBe(KEPT);
        expect(win.name).toBe("safebrowse-sid:" + KEPT);
    });

    it("gives a tab without one a new id and keeps it", () => {
        const win = tab("");

        const id = tabSessionId(win, "");

        expect(id).toMatch(/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/);
        expect(win.name).toBe("safebrowse-sid:" + id);
        expect(tabSessionId(win, ORIGIN + "/")).toBe(id);
    });

    it("does not take an id over from a page of another site", () => {
        // An older browser keeps window.name across sites: the other site could have set it.
        const win = tab("safebrowse-sid:" + KEPT);

        const id = tabSessionId(win, "https://example.com/");

        expect(id).not.toBe(KEPT);
        expect(win.name).toBe("safebrowse-sid:" + id);
    });

    it("does not take an id over without a referrer", () => {
        expect(tabSessionId(tab("safebrowse-sid:" + KEPT), "")).not.toBe(KEPT);
    });

    it("leaves a name it did not set alone", () => {
        const win = tab("results");

        const id = tabSessionId(win, ORIGIN + "/");

        expect(id).toMatch(/^[0-9a-f-]{36}$/);
        expect(win.name).toBe("results");
    });

    it("ignores a malformed id", () => {
        expect(tabSessionId(tab("safebrowse-sid:../x"), ORIGIN + "/")).not.toBe("../x");
    });
});
