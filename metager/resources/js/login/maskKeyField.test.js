import { afterEach, beforeEach, describe, expect, it } from "vitest";

import { maskKeyFieldWhileUnfocused } from "./maskKeyField";

/**
 * The key field is served as type="text" — visible without JavaScript, and
 * savable by a password manager that reads the autocomplete token. This is the
 * enhancement on top: give the browser's built-in manager the real
 * type="password" field it needs, without making the visitor type blind.
 *
 * A visitor reported losing exactly this after the sign-in page moved out of
 * the keymanager: the old field was type="password", so every manager offered
 * to remember the key, and the rebuilt field (type="text", autocomplete="off")
 * offered nothing.
 */
describe("maskKeyFieldWhileUnfocused", () => {
    /** @type {HTMLInputElement} */
    let input;

    beforeEach(() => {
        document.body.innerHTML = '<input id="login-key" type="text">';
        input = document.getElementById("login-key");
    });

    afterEach(() => {
        document.body.innerHTML = "";
    });

    it("masks the field at rest, so the browser sees a password to remember", () => {
        maskKeyFieldWhileUnfocused(input);

        expect(input.type).toBe("password");
    });

    it("uncovers the field while it has focus, so a key is never typed blind", () => {
        maskKeyFieldWhileUnfocused(input);

        input.focus();
        expect(input.type).toBe("text");

        input.blur();
        expect(input.type).toBe("password");
    });

    it("leaves an already-focused field visible when it wires up", () => {
        // The field carries autofocus; JavaScript runs after the browser has
        // acted on it. Masking it then would blank what the visitor is looking
        // at and drop their caret.
        input.focus();

        maskKeyFieldWhileUnfocused(input);

        expect(input.type).toBe("text");
    });
});
