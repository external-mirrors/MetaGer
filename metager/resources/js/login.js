import {
    caretAfterFormat,
    describeKey,
    formatKey,
    isSubmittable,
} from "./login/keyInput";

/**
 * The sign-in page, enhanced.
 *
 * Every path through this page works without any of it. The field takes a key
 * and the form posts it; the file input is a plain file input; the "no key yet"
 * link is a link. What this file adds is the grouping of the digits as they are
 * typed, a message when what is in the field cannot be a key, a check before
 * signing in to a key with nothing on it — and the one control that cannot
 * exist without JavaScript, the camera scanner, which is therefore the one
 * control the page renders hidden and this file reveals.
 *
 * The messages are read off data attributes rather than written here. They are
 * translated, and the page is served in twelve languages.
 */

const form = document.getElementById("login-form");
const input = document.getElementById("login-key");
const keyMessage = document.getElementById("login-key-message");

if (form && input) {
    enhanceKeyField();
    enhanceFileField();
    enhanceQrScanner();
    guardEmptyKeys();
}

/**
 * @param {HTMLElement|null} element
 * @param {string|undefined} text
 */
function say(element, text) {
    if (!element) {
        return;
    }

    if (text) {
        element.textContent = text;
        element.hidden = false;
    } else {
        element.textContent = "";
        element.hidden = true;
    }
}

/** What to say about the field's contents once the visitor has moved on. */
function reviewKeyField() {
    const shape = describeKey(input.value);
    const text = keyMessage ? keyMessage.dataset : {};

    say(
        keyMessage,
        {
            empty: null,
            key: null,
            code: null,
            illegal: text.illegal,
            malformed: text.malformed,
            partial: text.incomplete,
        }[shape]
    );
}

function enhanceKeyField() {
    input.addEventListener("input", () => {
        const raw = input.value;
        const formatted = formatKey(raw);

        if (formatted !== raw) {
            const caret = caretAfterFormat(raw, input.selectionStart ?? raw.length, formatted);
            input.value = formatted;
            input.setSelectionRange(caret, caret);
        }

        // While typing, only say something about a value that cannot become a
        // key no matter what is typed next. "Not finished yet" is not news, and
        // saying it is how a correct message stops being believed.
        const text = keyMessage ? keyMessage.dataset : {};
        say(keyMessage, describeKey(input.value) === "illegal" ? text.illegal : null);
    });

    // Having moved on is the moment a half-typed key becomes worth mentioning.
    input.addEventListener("blur", reviewKeyField);
}

/**
 * Choosing a file clears the key field.
 *
 * The form carries both and `POST /key/enter` reads the key first, so a
 * leftover half-typed key silently wins over the file the visitor just picked —
 * they would watch themselves be told their key is invalid after uploading a
 * backup.
 */
function enhanceFileField() {
    const file = document.getElementById("login-file");
    if (!file) {
        return;
    }

    file.addEventListener("change", () => {
        if (file.files && file.files.length > 0) {
            input.value = "";
            say(keyMessage, null);
        }
    });
}

/**
 * A key with nothing on it is almost always a typo.
 *
 * It is not an error — an empty key is a real key, and somebody who has just
 * spent theirs has every right to sign in to it. But a mistyped character often
 * lands on another well-formed key, and the only visible difference is a
 * balance of zero. So: ask, once, and only for a complete key.
 *
 * If the keyserver does not answer, the form submits. A balance check is not
 * worth standing between a visitor and their account.
 */
function guardEmptyKeys() {
    const dialog = document.getElementById("login-empty-key");
    const endpoint = form.dataset.chargeEndpoint;
    const usable = dialog && endpoint && typeof dialog.showModal === "function";

    let confirmed = false;

    form.addEventListener("submit", async (event) => {
        if (confirmed) {
            return;
        }

        const value = input.value.trim().toLowerCase();

        if (!isSubmittable(value)) {
            event.preventDefault();
            reviewKeyField();
            input.focus();
            return;
        }

        if (!usable || describeKey(value) !== "key") {
            return;
        }

        event.preventDefault();

        let charge = null;
        try {
            const response = await fetch(endpoint + encodeURIComponent(value), {
                headers: { Accept: "application/json" },
            });
            if (response.ok) {
                charge = (await response.json()).charge;
            }
        } catch {
            // Unreachable keyserver: nothing to warn about, so do not.
        }

        if (charge !== 0) {
            confirmed = true;
            form.submit();
            return;
        }

        const shown = dialog.querySelector("#login-empty-key-value");
        if (shown) {
            shown.textContent = value;
        }

        dialog.returnValue = "";
        dialog.addEventListener(
            "close",
            () => {
                if (dialog.returnValue === "confirm") {
                    confirmed = true;
                    form.submit();
                } else {
                    input.focus();
                }
            },
            { once: true }
        );
        dialog.showModal();
    });
}

/**
 * The camera scanner.
 *
 * Hidden in the markup and revealed here, because it is the one thing on this
 * page a browser without JavaScript cannot do at all — offering it and then
 * doing nothing would be worse than not offering it.
 *
 * The library is imported on the click rather than with the page: it is by far
 * the largest thing this page could pull in and almost nobody uses it. Its
 * decoder runs in a worker built from a blob URL, which is why
 * build/nginx/configuration/nginx.conf carries `worker-src 'self' blob:`.
 */
function enhanceQrScanner() {
    const option = document.getElementById("login-qr");
    const button = document.getElementById("login-qr-open");
    const overlay = document.getElementById("login-scanner");
    const message = document.getElementById("login-qr-message");

    if (!option || !button || !overlay || typeof overlay.showModal !== "function") {
        return;
    }

    option.hidden = false;

    const video = overlay.querySelector("video");
    let scanner = null;

    function stop() {
        if (scanner) {
            scanner.stop();
            scanner.destroy();
            scanner = null;
        }
        if (overlay.open) {
            overlay.close();
        }
    }

    // Covers the close button, the Escape key and the scan itself — a <dialog>
    // routes all three through here.
    overlay.addEventListener("close", () => {
        stop();
        button.focus();
    });

    overlay.querySelector(".login-scanner__close").addEventListener("click", () => overlay.close());

    button.addEventListener("click", async () => {
        say(message, null);

        const { default: QrScanner } = await import("qr-scanner");

        if (!(await QrScanner.hasCamera())) {
            say(message, message.dataset.noCamera);
            return;
        }

        scanner = new QrScanner(
            video,
            (result) => {
                let key = null;
                try {
                    key = new URL(result.data).searchParams.get("key");
                } catch {
                    key = null;
                }

                overlay.close();

                if (key === null) {
                    say(message, message.dataset.invalid);
                    return;
                }

                // Into the field rather than straight to the account: the scan
                // then goes through the same submit as a typed key, and that is
                // what carries `redirect_success` and the app's callback
                // markers. Handing the key to the keymanager from here would
                // drop both.
                input.value = formatKey(key);
                form.requestSubmit();
            },
            { highlightScanRegion: true, highlightCodeOutline: true }
        );

        overlay.showModal();
        scanner.start();
    });
}
