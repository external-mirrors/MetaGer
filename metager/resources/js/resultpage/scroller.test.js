import { afterEach, describe, expect, it, vi } from "vitest";

import { enableInlineScrollers, PAGE } from "./scroller";

/**
 * A row as news.blade.php renders it. jsdom lays nothing out, so the row's
 * widths and scroll position are stood in for, and scrollBy is recorded.
 */
function frame({ visible = 600, total = 1500 } = {}) {
    const div = document.createElement("div");
    div.className = "inline-scroller";
    div.dataset.scrollBack = "Zurückblättern";
    div.dataset.scrollForward = "Weiterblättern";
    div.innerHTML = `<div id="news" class="inline-results"><div class="inline-result"><a href="#">a</a></div></div>`;
    document.body.append(div);

    const row = div.querySelector(".inline-results");
    let left = 0;
    Object.defineProperty(row, "clientWidth", { value: visible });
    Object.defineProperty(row, "scrollWidth", { value: total });
    Object.defineProperty(row, "scrollLeft", { get: () => left, set: (value) => (left = value) });
    row.scrollTo = (x) => {
        left = x;
        row.dispatchEvent(new Event("scroll"));
    };
    row.scrollBy = vi.fn();

    return { div, row };
}

const buttons = (div) => ({
    back: div.querySelector(".inline-scroll-button.back"),
    forward: div.querySelector(".inline-scroll-button.forward"),
});

afterEach(() => {
    document.body.innerHTML = "";
});

describe("the ‹ › buttons on a row", () => {
    it("are buttons out of the tab order, named in the page's language", () => {
        const { div } = frame();
        enableInlineScrollers(document);

        const { back, forward } = buttons(div);
        for (const [button, label] of [[back, "Zurückblättern"], [forward, "Weiterblättern"]]) {
            expect(button.type).toBe("button");
            expect(button.tabIndex).toBe(-1);
            expect(button.getAttribute("aria-label")).toBe(label);
        }
    });

    it("sit beside the row, not in it, so they do not scroll away", () => {
        const { div, row } = frame();
        enableInlineScrollers(document);

        expect(buttons(div).back.parentElement).toBe(div);
        expect(row.querySelector("button")).toBeNull();
    });

    it("are both hidden when the row fits", () => {
        const { div } = frame({ visible: 600, total: 600 });
        enableInlineScrollers(document);

        const { back, forward } = buttons(div);
        expect(back.hidden).toBe(true);
        expect(forward.hidden).toBe(true);
    });

    it("show only the way there is still something to see", () => {
        const { div, row } = frame({ visible: 600, total: 1500 });
        enableInlineScrollers(document);
        const { back, forward } = buttons(div);

        expect([back.hidden, forward.hidden]).toEqual([true, false]);

        row.scrollTo(400);
        expect([back.hidden, forward.hidden]).toEqual([false, false]);

        row.scrollTo(900);
        expect([back.hidden, forward.hidden]).toEqual([false, true]);
    });

    it("move most of the visible row per click, either way", () => {
        const { div, row } = frame({ visible: 600 });
        enableInlineScrollers(document);
        const { back, forward } = buttons(div);

        forward.click();
        back.click();

        expect(row.scrollBy.mock.calls.map(([options]) => options.left)).toEqual([600 * PAGE, -600 * PAGE]);
    });

    it("jump rather than glide for someone who asked for less motion", () => {
        const { div, row } = frame();
        const win = { matchMedia: (query) => ({ matches: query.includes("reduced-motion") }) };
        enableInlineScrollers(document, win);

        buttons(div).forward.click();

        expect(row.scrollBy.mock.calls[0][0].behavior).toBe("auto");
    });

    it("are added once, however often the page asks", () => {
        const { div } = frame();
        enableInlineScrollers(document);
        enableInlineScrollers(document);

        expect(div.querySelectorAll(".inline-scroll-button")).toHaveLength(2);
    });
});
