/**
 * Mask the key field the way the old keymanager page did — and reveal it while
 * it has focus, so a key read off a note is never typed blind.
 *
 * The field is served as type="text" on purpose: without JavaScript that is the
 * only way a visitor sees what they type, and a password manager that honours
 * the autocomplete="current-password" token can still save it. But the
 * browser's *own* password manager only offers to save and to re-fill a field
 * it can see is a password — a real type="password". So once JavaScript runs we
 * give it one, and take the visibility back by uncovering the field whenever it
 * has focus. The net effect for a JS visitor is the old behaviour: masked at
 * rest, legible while being filled, and remembered by the browser.
 *
 * @param {HTMLInputElement} input
 */
export function maskKeyFieldWhileUnfocused(input) {
    mask();

    input.addEventListener("focus", () => {
        input.type = "text";
    });
    input.addEventListener("blur", mask);

    function mask() {
        // Not while the visitor is in the field: changing an input's type
        // mid-edit drops the caret to the end in some browsers, and the field
        // reformats itself on every keystroke (resources/js/login.js).
        if (document.activeElement !== input) {
            input.type = "password";
        }
    }
}
