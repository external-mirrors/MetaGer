import { describe, expect, it } from "vitest";

import { replaceKeepingFoldsOpen } from "./folds";

function fold(host) {
    return `<details class="result-more-from-host" data-host="${host}"><summary>${host}</summary><ul></ul></details>`;
}

function results(...hosts) {
    const container = document.createElement("div");
    container.innerHTML = hosts.map(fold).join("");
    return container;
}

/**
 * While engines are still answering, scriptResultPage.js swaps the whole result list once a
 * second. A fold someone had opened must not snap shut under them because a late engine answered.
 */
describe("replaceKeepingFoldsOpen", () => {
    it("reopens a fold that was open before the swap", () => {
        const container = results("a.example", "b.example");
        container.querySelector('[data-host="b.example"]').open = true;

        replaceKeepingFoldsOpen(container, results("a.example", "b.example").innerHTML);

        expect(container.querySelector('[data-host="a.example"]').open).toBe(false);
        expect(container.querySelector('[data-host="b.example"]').open).toBe(true);
    });

    it("follows the host when the swap moves its fold", () => {
        const container = results("a.example", "b.example");
        container.querySelector('[data-host="a.example"]').open = true;

        replaceKeepingFoldsOpen(container, results("c.example", "b.example", "a.example").innerHTML);

        expect([...container.querySelectorAll("details[open]")].map((d) => d.dataset.host)).toEqual(["a.example"]);
    });

    it("leaves every fold closed when none was open", () => {
        const container = results("a.example");

        replaceKeepingFoldsOpen(container, results("a.example", "b.example").innerHTML);

        expect(container.querySelectorAll("details[open]")).toHaveLength(0);
    });
});
