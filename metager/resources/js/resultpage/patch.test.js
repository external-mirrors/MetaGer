import { afterEach, describe, expect, it, vi } from "vitest";

import { patchResults, rememberServerMarkup } from "./patch";

function result(hash, { index = 1, title = hash, fold = null, image = null } = {}) {
    const details = fold
        ? `<details class="result-more-from-host" data-host="${fold}"><summary>${fold}</summary><ul></ul></details>`
        : "";
    const img = image ? `<img src="http://localhost:8080/meta/picture?data=${image}" alt="">` : "";
    return `<div class="result" data-count="${hash}" data-index="${index}"><h2>${title}</h2>${img}${details}</div>`;
}

function list(...items) {
    return items.join("");
}

/** A result list as the page has it after load: server markup, remembered. */
function page(html) {
    const container = document.createElement("div");
    container.id = "results";
    container.innerHTML = html;
    document.body.append(container);
    rememberServerMarkup(container);
    return container;
}

function hashes(container) {
    return [...container.children].map((element) => element.dataset.count ?? element.className);
}

afterEach(() => {
    document.body.innerHTML = "";
    vi.restoreAllMocks();
});

describe("patchResults", () => {
    it("keeps the nodes of results that did not change", () => {
        const container = page(list(result("a"), result("b")));
        const [a, b] = container.children;

        patchResults(container, list(result("a"), result("b")));

        expect(container.children[0]).toBe(a);
        expect(container.children[1]).toBe(b);
    });

    it("inserts a late result at its rank without touching the others", () => {
        const container = page(list(result("a", { index: 1 }), result("c", { index: 2 })));
        const [a, c] = container.children;

        patchResults(container, list(result("a", { index: 1 }), result("b", { index: 2 }), result("c", { index: 3 })));

        expect(hashes(container)).toEqual(["a", "b", "c"]);
        expect(container.children[0]).toBe(a);
        expect(container.children[2]).toBe(c);
        expect(c.dataset.index).toBe("3");
        expect(container.children[1].classList.contains("result-arrived")).toBe(true);
    });

    it("leaves a result's own changes in place, such as the dates the page reformats", () => {
        const container = page(list(result("a")));
        const a = container.children[0];
        a.querySelector("h2").textContent = "vor 3 Tagen";

        patchResults(container, list(result("a")));

        expect(container.children[0]).toBe(a);
        expect(a.querySelector("h2").textContent).toBe("vor 3 Tagen");
    });

    it("replaces a result the server now renders differently", () => {
        const container = page(list(result("a", { title: "old" }), result("b")));

        patchResults(container, list(result("a", { title: "new" }), result("b")));

        expect(hashes(container)).toEqual(["a", "b"]);
        expect(container.querySelector("h2").textContent).toBe("new");
        expect(container.children[0].classList.contains("result-arrived")).toBe(false);
    });

    /**
     * The first run against the real page doubled the list: a replaced result was taken out of
     * the bookkeeping before it was known to be replaced, so it was neither kept nor removed.
     */
    it("never leaves the replaced element behind", () => {
        const container = page(list(result("a", { title: "old" }), result("b", { title: "old" }), result("c")));

        patchResults(container, list(result("c"), result("a", { title: "new" }), result("b", { title: "new" })));

        expect(hashes(container)).toEqual(["c", "a", "b"]);
        expect([...container.querySelectorAll("h2")].map((h) => h.textContent)).toEqual(["c", "new", "new"]);
    });

    it("keeps a result whose only difference is the image proxy's freshly encrypted URL", () => {
        const container = page(list(result("a", { image: "eyJpdiI6IkFBQSJ9" })));
        const a = container.children[0];

        patchResults(container, list(result("a", { image: "eyJpdiI6IkJCQiJ9" })));

        expect(container.children[0]).toBe(a);
        expect(container.children).toHaveLength(1);
    });

    it("removes results that are no longer in the list", () => {
        const container = page(list(result("a"), result("b")));

        patchResults(container, list(result("b")));

        expect(hashes(container)).toEqual(["b"]);
    });

    it("moves results the new ranking moved", () => {
        const container = page(list(result("a"), result("b")));
        const [a, b] = container.children;

        patchResults(container, list(result("b"), result("a")));

        expect([...container.children]).toEqual([b, a]);
    });

    it("keys the blocks between results by the result they follow", () => {
        const news = '<div class="news-row">news</div>';
        const container = page(list(result("a"), result("b"), news, result("c")));
        const block = container.querySelector(".news-row");

        patchResults(container, list(result("x"), result("a"), result("b"), news, result("c")));

        expect(container.querySelector(".news-row")).toBe(block);
        expect(hashes(container)).toEqual(["x", "a", "b", "news-row", "c"]);
    });

    it("keeps the reader on what they were looking at when results arrive above it", () => {
        const container = page(list(result("a"), result("c")));
        const c = container.children[1];
        vi.spyOn(window, "scrollY", "get").mockReturnValue(400);
        const scrollBy = vi.spyOn(window, "scrollBy").mockImplementation(() => {});
        let top = 10;
        vi.spyOn(container.children[0], "getBoundingClientRect").mockReturnValue({ top: -200, bottom: -100 });
        vi.spyOn(c, "getBoundingClientRect").mockImplementation(() => ({ top, bottom: top + 100 }));

        const original = container.insertBefore.bind(container);
        vi.spyOn(container, "insertBefore").mockImplementation((node, at) => {
            top = 130; // the inserted result pushed c down by 120px
            return original(node, at);
        });

        patchResults(container, list(result("a"), result("b"), result("c")));

        expect(scrollBy).toHaveBeenCalledWith(0, 120);
    });

    it("does not scroll at the very top of the page, where new results should be seen", () => {
        const container = page(list(result("a")));
        vi.spyOn(window, "scrollY", "get").mockReturnValue(0);
        const scrollBy = vi.spyOn(window, "scrollBy").mockImplementation(() => {});

        patchResults(container, list(result("x"), result("a")));

        expect(scrollBy).not.toHaveBeenCalled();
    });
});

/**
 * A late engine can add to a host's fold (Search\HostCrowding), which re-renders the result that
 * holds it. A fold someone had opened must not snap shut under them because of it.
 */
describe("patchResults and host folds", () => {
    it("keeps a fold open across a re-render of its result", () => {
        const container = page(list(result("a", { fold: "a.example" }), result("b", { fold: "b.example" })));
        container.querySelector('[data-host="b.example"]').open = true;

        patchResults(
            container,
            list(result("a", { fold: "a.example" }), result("b", { title: "b, now with more", fold: "b.example" }))
        );

        expect(container.querySelector('[data-host="a.example"]').open).toBe(false);
        expect(container.querySelector('[data-host="b.example"]').open).toBe(true);
    });

    it("follows the result when the new ranking moves it", () => {
        const container = page(list(result("a", { fold: "a.example" }), result("b", { fold: "b.example" })));
        container.querySelector('[data-host="a.example"]').open = true;

        patchResults(container, list(result("c", { fold: "c.example" }), result("b", { fold: "b.example" }), result("a", { fold: "a.example" })));

        expect([...container.querySelectorAll("details[open]")].map((d) => d.dataset.host)).toEqual(["a.example"]);
    });

    it("leaves every fold closed when none was open", () => {
        const container = page(list(result("a", { fold: "a.example" })));

        patchResults(container, list(result("a", { title: "changed", fold: "a.example" }), result("b", { fold: "b.example" })));

        expect(container.querySelectorAll("details[open]")).toHaveLength(0);
    });
});
