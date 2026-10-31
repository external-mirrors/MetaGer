import { afterEach, beforeAll, describe, expect, it } from "vitest";

import { enableResultMenus } from "./menu";

function result(id) {
    const div = document.createElement("div");
    div.className = "result";
    div.innerHTML = `<div class="result-footer">
        <a class="result-open-proxy" href="#">Anonym öffnen</a>
        <div class="result-menu">
            <input type="checkbox" id="${id}" class="result-toggle">
            <label class="toggle-result-options" for="${id}">⋯</label>
            <div class="result-options"><ul><li><a href="#">ausblenden</a></li></ul></div>
        </div>
    </div>`;
    document.body.append(div);
    return div.querySelector(".result-toggle");
}

beforeAll(() => {
    // Listeners on the document: enabled once, as the page does.
    enableResultMenus(document);
});

afterEach(() => {
    document.body.innerHTML = "";
});

describe("the ⋯ menu", () => {
    it("closes on a click outside it", () => {
        const toggle = result("a");
        toggle.checked = true;

        document.querySelector(".result-open-proxy").click();

        expect(toggle.checked).toBe(false);
    });

    it("stays open on a click inside it", () => {
        const toggle = result("a");
        toggle.checked = true;

        document.querySelector(".result-options a").click();

        expect(toggle.checked).toBe(true);
    });

    it("closes on Escape and gives focus back", () => {
        const toggle = result("a");
        toggle.checked = true;

        document.dispatchEvent(new KeyboardEvent("keydown", { key: "Escape" }));

        expect(toggle.checked).toBe(false);
        expect(document.activeElement).toBe(toggle);
    });

    it("closes the other menus when one opens", () => {
        const first = result("a");
        const second = result("b");
        first.checked = true;

        second.checked = true;
        second.dispatchEvent(new Event("change", { bubbles: true }));

        expect(first.checked).toBe(false);
        expect(second.checked).toBe(true);
    });
});
