import { afterEach, beforeEach, describe, expect, it } from "vitest";

import {
    bindLogoutClears,
    enhanceLoggedOutStartpage,
    initAccountBreadcrumb,
    recordSignedIn,
    _internal,
} from "./accountBreadcrumb";

const { STORAGE_KEY } = _internal;

beforeEach(() => {
    window.localStorage.clear();
    document.body.innerHTML = "";
});

afterEach(() => {
    window.localStorage.clear();
});

describe("recordSignedIn", () => {
    it("sets a boolean flag on a signed-in page, storing nothing key-derived", () => {
        document.body.innerHTML = `<a id="sidebar-key-remove" href="#">log out</a>`;

        recordSignedIn(document);

        const stored = JSON.parse(window.localStorage.getItem(STORAGE_KEY));
        expect(stored.seen).toBe(true);
        expect(typeof stored.ts).toBe("number");
        expect(Object.keys(stored).sort()).toEqual(["seen", "ts"]);
    });

    it("does nothing on a page with no signed-in marker", () => {
        recordSignedIn(document);
        expect(window.localStorage.getItem(STORAGE_KEY)).toBeNull();
    });
});

describe("enhanceLoggedOutStartpage", () => {
    // The strings live on the container as data-*, rendered server-side out of
    // lang/, so this module never builds user-visible text in any of the
    // twenty-nine locales.
    const startpageMarkup = `
        <div id="searchbar-replacement"
             data-welcome-back-hook="Willkommen zurück."
             data-welcome-back-message="Sie waren hier schon angemeldet."
             data-welcome-back-button="Wieder anmelden">
            <div class="hook-line" data-hook-line>Die Suchmaschine, die Sie nicht verfolgt.</div>
            <a class="btn startpage-login-btn" data-login-button>Mit meinem Schlüssel anmelden</a>
            <div class="helper-line" data-helper-line>Ihr Schlüssel ist Ihr Zugang.</div>
        </div>`;

    it("rewrites the three strings in place when an account is remembered", () => {
        window.localStorage.setItem(STORAGE_KEY, JSON.stringify({ seen: true, ts: 1 }));
        document.body.innerHTML = startpageMarkup;

        expect(enhanceLoggedOutStartpage(document)).toBe(true);
        expect(document.querySelector("[data-hook-line]").textContent).toBe("Willkommen zurück.");
        expect(document.querySelector("[data-helper-line]").textContent).toBe("Sie waren hier schon angemeldet.");
        expect(document.querySelector("[data-login-button]").textContent).toBe("Wieder anmelden");
        expect(document.querySelector("#searchbar-replacement").getAttribute("data-returning")).toBe("true");
    });

    /**
     * The reason this rewrites rather than reveals. The first version added a
     * class that hid `> .helper-line`, and the small-print duplicate warning
     * carried both .helper-line and .helper-line-small — so the one message the
     * whole feature exists for vanished exactly when the breadcrumb fired.
     * Nothing may be added to or removed from the tree here.
     */
    it("adds and removes no elements, so the layout cannot shift", () => {
        window.localStorage.setItem(STORAGE_KEY, JSON.stringify({ seen: true, ts: 1 }));
        document.body.innerHTML = startpageMarkup;
        const before = document.querySelectorAll("#searchbar-replacement *").length;

        enhanceLoggedOutStartpage(document);

        expect(document.querySelectorAll("#searchbar-replacement *").length).toBe(before);
        expect(document.querySelectorAll("#searchbar-replacement .hidden").length).toBe(0);
    });

    it("leaves the copy untouched when nothing is remembered", () => {
        document.body.innerHTML = startpageMarkup;

        expect(enhanceLoggedOutStartpage(document)).toBe(false);
        expect(document.querySelector("[data-hook-line]").textContent).toBe("Die Suchmaschine, die Sie nicht verfolgt.");
        expect(document.querySelector("#searchbar-replacement").hasAttribute("data-returning")).toBe(false);
    });

    it("ignores a corrupt storage value", () => {
        window.localStorage.setItem(STORAGE_KEY, "not json");
        document.body.innerHTML = startpageMarkup;

        expect(enhanceLoggedOutStartpage(document)).toBe(false);
    });

    /**
     * A blade that has lost one of the data-* attributes must leave the original
     * copy standing. It is more generic, not wrong — an empty headline would be.
     */
    it("keeps the original copy for any string the markup does not carry", () => {
        window.localStorage.setItem(STORAGE_KEY, JSON.stringify({ seen: true, ts: 1 }));
        document.body.innerHTML = `
            <div id="searchbar-replacement" data-welcome-back-button="Wieder anmelden">
                <div class="hook-line" data-hook-line>Die Suchmaschine, die Sie nicht verfolgt.</div>
                <a data-login-button>Mit meinem Schlüssel anmelden</a>
            </div>`;

        expect(enhanceLoggedOutStartpage(document)).toBe(true);
        expect(document.querySelector("[data-hook-line]").textContent).toBe("Die Suchmaschine, die Sie nicht verfolgt.");
        expect(document.querySelector("[data-login-button]").textContent).toBe("Wieder anmelden");
    });
});

describe("bindLogoutClears", () => {
    it("forgets the breadcrumb when the logout link is clicked", () => {
        window.localStorage.setItem(STORAGE_KEY, JSON.stringify({ seen: true, ts: 1 }));
        document.body.innerHTML = `<a id="sidebar-key-remove" href="#">log out</a>`;

        bindLogoutClears(document);
        document.querySelector("#sidebar-key-remove").dispatchEvent(new Event("click"));

        expect(window.localStorage.getItem(STORAGE_KEY)).toBeNull();
    });

    /**
     * The settings page used to carry its own logout button, `#remove-key`, and
     * this module bound that too. The page no longer has an account block at
     * all — the sidebar's is on it like on every other page — so a second
     * selector here would only describe markup that does not exist.
     */
    it("does not go looking for the settings page's old logout button", () => {
        window.localStorage.setItem(STORAGE_KEY, JSON.stringify({ seen: true, ts: 1 }));
        document.body.innerHTML = `<a id="remove-key" href="#">log out</a>`;

        bindLogoutClears(document);
        document.querySelector("#remove-key").dispatchEvent(new Event("click"));

        expect(window.localStorage.getItem(STORAGE_KEY)).not.toBeNull();
    });
});

describe("initAccountBreadcrumb", () => {
    it("records and then, on a later signed-out visit, rewrites the copy", () => {
        document.body.innerHTML = `<a id="sidebar-key-remove" href="#">log out</a>`;
        initAccountBreadcrumb(document);

        document.body.innerHTML = `
            <div id="searchbar-replacement" data-welcome-back-hook="Willkommen zurück.">
                <div class="hook-line" data-hook-line>Die Suchmaschine, die Sie nicht verfolgt.</div>
            </div>`;
        initAccountBreadcrumb(document);

        expect(document.querySelector("[data-hook-line]").textContent).toBe("Willkommen zurück.");
    });

    /**
     * A webextension visitor gets no #sidebar-key-remove: their key never
     * reaches us, so the sidebar has nothing to log out of. Nothing to remember
     * either — the extension keeps them signed in across cookie loss, which is
     * the whole thing this breadcrumb exists to soften.
     */
    it("remembers nothing for an anonymous webextension session", () => {
        document.body.innerHTML = `<div class="sidebar-account">anonym angemeldet</div>`;

        initAccountBreadcrumb(document);

        expect(window.localStorage.getItem(STORAGE_KEY)).toBeNull();
    });
});
