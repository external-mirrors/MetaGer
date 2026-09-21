import { afterEach, describe, expect, it, vi } from "vitest";

/**
 * membership.js reports a remote-controlled browser (navigator.webdriver) in a
 * hidden field, and the server refuses the first step when it is set. Without
 * JS the field stays empty, so the form keeps working with JS disabled.
 */
async function loadWith(webdriver) {
    vi.resetModules();
    document.body.innerHTML = '<form><input type="hidden" name="client_automation" value=""></form>';
    Object.defineProperty(navigator, "webdriver", { value: webdriver, configurable: true });
    await import("./membership.js");
    return document.querySelector("input[name=client_automation]").value;
}

afterEach(() => {
    document.body.innerHTML = "";
});

describe("membership automation flag", () => {
    it("is set when the browser is remote controlled", async () => {
        expect(await loadWith(true)).toBe("1");
    });

    it("stays empty for an ordinary browser", async () => {
        expect(await loadWith(false)).toBe("");
    });
});
