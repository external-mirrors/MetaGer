/**
 * What a MetaGer key looks like while it is being typed.
 *
 * Pure functions, separate from the DOM glue in ../login.js, because this is
 * the only part of the sign-in page with rules of its own — and the rules are
 * the keymanager's, not ours. `Key.IS_VALID_UUID` in pass/app/Key.js is a
 * v4-strict, dash-separated match, and `POST /key/enter` accepts three shapes:
 * that UUID, the same 32 hex digits without dashes (it inserts them itself),
 * and a six-character short code.
 *
 * Nothing here rejects a submission. The field is a convenience — it groups the
 * digits, drops the characters a key cannot contain, and says what it sees.
 * What actually counts as a key is decided by the keyserver, which is the only
 * party that knows whether one exists.
 */

/** 8-4-4-4-12, the shape of a UUID. */
const BLOCKS = [8, 4, 4, 4, 12];

const HEX_DIGITS = BLOCKS.reduce((sum, size) => sum + size, 0);

/** v4-strict, matching Key.IS_VALID_UUID. */
const UUID_V4 = /^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/;

/** A transfer code, and the short keys that share its shape. */
const SHORT_CODE = /^[0-9a-z]{6}$/;

/**
 * The value as the field should show it.
 *
 * Only touches input that could still become a UUID: anything else is left
 * exactly as typed, because a six-character code is also a legitimate thing to
 * have in this field and grouping it would be nonsense.
 *
 * A dash in the wrong place is dropped rather than refused. Refusing means
 * fighting the person pasting a key from somewhere that formats it differently;
 * dropping means the dashes are always ours and always in the right place.
 *
 * @param {string} raw
 * @returns {string}
 */
export function formatKey(raw) {
    const lower = raw.trim().toLowerCase();

    if (!/^[0-9a-f-]*$/.test(lower)) {
        return lower;
    }

    const hex = lower.replace(/-/g, "").slice(0, HEX_DIGITS);

    // Nothing to group yet, and no dash asking to be placed.
    if (hex.length <= BLOCKS[0] && !lower.includes("-")) {
        return hex;
    }

    let out = "";
    let at = 0;
    for (const size of BLOCKS) {
        if (at >= hex.length) {
            break;
        }
        if (out !== "") {
            out += "-";
        }
        out += hex.slice(at, at + size);
        at += size;
    }

    return out;
}

/**
 * Where the caret belongs once {@link formatKey} has moved the dashes around.
 *
 * Counted in hex digits rather than in characters: that is the one measure the
 * dashes do not change, so a caret in the middle of a pasted key stays where
 * the typist put it whether or not a dash was inserted ahead of it.
 *
 * @param {string} raw the value before formatting
 * @param {number} caret the caret position in that value
 * @param {string} formatted the value after formatting
 * @returns {number}
 */
export function caretAfterFormat(raw, caret, formatted) {
    const digits = raw.slice(0, caret).replace(/-/g, "").length;

    if (digits === 0) {
        return 0;
    }

    let seen = 0;
    for (let i = 0; i < formatted.length; i++) {
        if (formatted[i] !== "-") {
            seen++;
        }
        if (seen === digits) {
            return i + 1;
        }
    }

    return formatted.length;
}

/**
 * What the field currently holds.
 *
 * - `empty` — nothing to say.
 * - `key` — a complete, well-formed key.
 * - `code` — a six-character transfer or short code.
 * - `partial` — on its way to being a key, and not wrong yet.
 * - `illegal` — contains characters no key contains.
 * - `malformed` — the right length and not a key.
 *
 * The distinction that earns its keep is `partial` against `malformed`: half a
 * key is not an error, and telling someone their key is invalid while they are
 * still typing it is the fastest way to make a correct message untrustworthy.
 *
 * @param {string} value
 * @returns {"empty"|"key"|"code"|"partial"|"illegal"|"malformed"}
 */
export function describeKey(value) {
    const lower = value.trim().toLowerCase();

    if (lower === "") {
        return "empty";
    }

    if (UUID_V4.test(lower)) {
        return "key";
    }

    if (SHORT_CODE.test(lower)) {
        return "code";
    }

    if (!/^[0-9a-z-]*$/.test(lower)) {
        return "illegal";
    }

    const hex = lower.replace(/-/g, "");

    // Past the point where it could still be a short code, and holding a
    // character a UUID cannot: this is a key being typed wrong, not a code.
    if (!/^[0-9a-f]*$/.test(hex)) {
        return hex.length > 6 ? "malformed" : "partial";
    }

    if (hex.length < HEX_DIGITS) {
        return "partial";
    }

    // Full length and still not a match — the version or variant digit is
    // wrong, which is what a mistyped character usually breaks first.
    return "malformed";
}

/**
 * Whether this value is worth sending. Everything except a value that is
 * visibly not a key yet — an empty field is allowed through, because the form
 * also carries a file, and refusing it here would break the upload path.
 *
 * @param {string} value
 * @returns {boolean}
 */
export function isSubmittable(value) {
    const shape = describeKey(value);

    return shape === "empty" || shape === "key" || shape === "code";
}
