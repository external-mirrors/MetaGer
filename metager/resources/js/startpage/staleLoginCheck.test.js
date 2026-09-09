import { beforeEach, describe, expect, it, vi } from "vitest";

import { initStaleLoginCheck } from "./staleLoginCheck";

const ENDPOINT = "https://metager.de/de/authorized";

const loggedOutMarkup = `
    <div id="searchbar-replacement" data-login-check="${ENDPOINT}">
        <h1 data-hook-line>Die Suchmaschine, die Sie nicht verfolgt.</h1>
    </div>`;

/**
 * A stand-in for Window that records its listeners, so a test can fire a
 * pageshow with the `persisted` flag jsdom will not produce on its own — there
 * is no back/forward cache to restore from.
 */
function fakeWindow() {
    const listeners = {};
    return {
        addEventListener(type, handler) {
            (listeners[type] ??= []).push(handler);
        },
        emit(type, event) {
            (listeners[type] ?? []).forEach((handler) => handler(event));
        },
        listenerCount(type) {
            return (listeners[type] ?? []).length;
        },
    };
}

function setup({ ok = true, markup = loggedOutMarkup } = {}) {
    document.body.innerHTML = markup;
    const win = fakeWindow();
    const fetcher = vi.fn().mockResolvedValue({ ok });
    const reload = vi.fn();

    const installed = initStaleLoginCheck({ doc: document, win, fetcher, reload });

    return { win, fetcher, reload, installed };
}

/**
 * Let the fetch promise settle *and* the `.finally()` that clears `inFlight`
 * run. A microtask is not enough for the second half, and in a browser the gap
 * between two restores is seconds, not ticks — this is a test-timing concern
 * only.
 */
const flush = () => new Promise((resolve) => setTimeout(resolve, 0));

beforeEach(() => {
    document.body.innerHTML = "";
});

describe("initStaleLoginCheck", () => {
    it("asks nothing on an ordinary load — that page just came from the server", async () => {
        const { win, fetcher, reload } = setup();

        win.emit("pageshow", { persisted: false });
        await Promise.resolve();

        expect(fetcher).not.toHaveBeenCalled();
        expect(reload).not.toHaveBeenCalled();
    });

    it("asks once when the page was restored from the back/forward cache", async () => {
        const { win, fetcher } = setup();

        win.emit("pageshow", { persisted: true });
        await Promise.resolve();

        expect(fetcher).toHaveBeenCalledTimes(1);
        const [url, options] = fetcher.mock.calls[0];
        expect(url).toBe(ENDPOINT);
        expect(options.method).toBe("POST");
        expect(options.credentials).toBe("same-origin");
    });

    it("reloads when the server says the visitor is signed in after all", async () => {
        const { win, reload } = setup({ ok: true });

        win.emit("pageshow", { persisted: true });
        await vi.waitFor(() => expect(reload).toHaveBeenCalledTimes(1));
    });

    it("leaves a genuinely signed-out page alone", async () => {
        const { win, reload } = setup({ ok: false });

        win.emit("pageshow", { persisted: true });
        await Promise.resolve();
        await Promise.resolve();

        expect(reload).not.toHaveBeenCalled();
    });

    /**
     * Found by hand, in a browser: back, "Hilfe", back again — and the second
     * return asked nothing.
     *
     * A restored page is the same page instance with its JavaScript state
     * intact, so the flag that made this "a single post" latched for the rest
     * of that page's life. The case it swallowed is the one that matters: come
     * back while genuinely signed out (401, no reload — correct), sign in
     * somewhere else, come back again to the now-stale snapshot, and nothing
     * ever asks again.
     */
    it("asks again on a later restore, because the answer can have changed", async () => {
        const { win, fetcher } = setup({ ok: false });

        win.emit("pageshow", { persisted: true });
        await flush();
        expect(fetcher).toHaveBeenCalledTimes(1);

        win.emit("pageshow", { persisted: true });
        await flush();
        expect(fetcher).toHaveBeenCalledTimes(2);
    });

    /**
     * One request per restore, though — not a poll, and not a stack of them if
     * restores arrive faster than the network answers.
     */
    it("does not start a second request while one is still open", async () => {
        document.body.innerHTML = loggedOutMarkup;
        const win = fakeWindow();
        let release;
        const fetcher = vi.fn(() => new Promise((resolve) => { release = () => resolve({ ok: false }); }));
        initStaleLoginCheck({ doc: document, win, fetcher, reload: vi.fn() });

        win.emit("pageshow", { persisted: true });
        win.emit("pageshow", { persisted: true });
        win.emit("pageshow", { persisted: true });

        expect(fetcher).toHaveBeenCalledTimes(1);

        release();
        await flush();

        win.emit("pageshow", { persisted: true });
        await flush();
        expect(fetcher).toHaveBeenCalledTimes(2);
    });

    /**
     * The signed-in startpage has no #searchbar-replacement. A stale "you are
     * signed in" cannot lock anyone out, so there is nothing to re-check —
     * and not installing the listener keeps the endpoint free of traffic that
     * could never act on the answer.
     */
    it("installs nothing on the signed-in startpage", () => {
        const { win, installed } = setup({ markup: `<div id="search-content"></div>` });

        expect(installed).toBe(false);
        expect(win.listenerCount("pageshow")).toBe(0);
    });

    it("installs nothing when the endpoint was not rendered", () => {
        const { installed } = setup({ markup: `<div id="searchbar-replacement"></div>` });

        expect(installed).toBe(false);
    });

    /** A failing request must leave the page as it is, never throw at it. */
    it("stays quiet when the request fails", async () => {
        document.body.innerHTML = loggedOutMarkup;
        const win = fakeWindow();
        const reload = vi.fn();
        initStaleLoginCheck({
            doc: document,
            win,
            fetcher: vi.fn().mockRejectedValue(new Error("offline")),
            reload,
        });

        win.emit("pageshow", { persisted: true });
        await Promise.resolve();
        await Promise.resolve();

        expect(reload).not.toHaveBeenCalled();
    });
});
