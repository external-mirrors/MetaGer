import { describe, expect, it } from "vitest";

import {
    caretAfterFormat,
    describeKey,
    formatKey,
    isSubmittable,
} from "./keyInput";

/**
 * The sign-in field, pinned against the keymanager's own rules.
 *
 * These are not our rules to choose: `Key.IS_VALID_UUID` in pass/app/Key.js is
 * a v4-strict match, and `POST /key/enter` accepts a dashed UUID, the same 32
 * hex digits undashed, or a six-character short code. If this field starts
 * refusing something that route accepts, the only symptom is a visitor who
 * cannot sign in with a key that works — and no error anywhere.
 */
const A_KEY = "5e9c1a2b-4f6d-4c3e-9a71-2b8d0f4e6c15";

describe("formatKey", () => {
    it("groups a key into 8-4-4-4-12 as it is typed", () => {
        expect(formatKey("5e9c1a2b4f6d")).toBe("5e9c1a2b-4f6d");
        expect(formatKey("5e9c1a2b4f6d4c3e9a712b8d0f4e6c15")).toBe(A_KEY);
    });

    it("leaves a key that is already grouped alone", () => {
        expect(formatKey(A_KEY)).toBe(A_KEY);
    });

    it("regroups a key pasted with the dashes somewhere else", () => {
        expect(formatKey("5e9c-1a2b4f6d4c3e-9a712b8d0f4e6c15")).toBe(A_KEY);
    });

    it("lowercases and trims, because both come back from a copy and paste", () => {
        expect(formatKey("  5E9C1A2B-4F6D-4C3E-9A71-2B8D0F4E6C15 ")).toBe(A_KEY);
    });

    it("holds off until there is something to group", () => {
        // Eight digits is one block, and one block needs no dash. Adding one
        // here would put a dash in the field before the typist has earned it.
        expect(formatKey("5e9c1a2b")).toBe("5e9c1a2b");
        expect(formatKey("5e9c1a2b4")).toBe("5e9c1a2b-4");
    });

    it("stops at the length of a key", () => {
        expect(formatKey(A_KEY.replace(/-/g, "") + "ffff")).toBe(A_KEY);
    });

    it("does not touch a value that is not a key being typed", () => {
        // A six-character code is a legitimate thing to have in this field.
        // Grouping it, or stripping its letters, would be a field fighting its
        // own contents.
        expect(formatKey("h7k2p9")).toBe("h7k2p9");
        expect(formatKey("Z12345")).toBe("z12345");
    });
});

describe("caretAfterFormat", () => {
    it("keeps the caret at the end while typing forwards", () => {
        expect(caretAfterFormat("5e9c1a2b4", 9, "5e9c1a2b-4")).toBe(10);
    });

    it("keeps the caret on its own digit when a dash appears ahead of it", () => {
        // Editing the fourth digit of a full key: the caret sits after four hex
        // digits before formatting and must still sit after four afterwards.
        expect(caretAfterFormat(A_KEY, 4, A_KEY)).toBe(4);
    });

    it("counts digits rather than characters, so dashes never move it", () => {
        const raw = "5e9c-1a2b4f6d4c3e-9a712b8d0f4e6c15";
        // Nine hex digits precede this position in the pasted value.
        expect(caretAfterFormat(raw, 10, A_KEY)).toBe(10);
    });

    it("puts an empty field's caret at the front", () => {
        expect(caretAfterFormat("", 0, "")).toBe(0);
    });
});

describe("describeKey", () => {
    it("recognises a complete key", () => {
        expect(describeKey(A_KEY)).toBe("key");
        expect(describeKey(A_KEY.toUpperCase())).toBe("key");
    });

    it("recognises a six-character code", () => {
        expect(describeKey("123456")).toBe("code");
        expect(describeKey("h7k2p9")).toBe("code");
    });

    it("calls half a key partial and not wrong", () => {
        // The one distinction that earns its keep. Telling somebody their key
        // is invalid while they are still typing it is how a correct message
        // stops being believed.
        expect(describeKey("5e9c1a2b-4f6d")).toBe("partial");
        expect(describeKey("5")).toBe("partial");
        expect(describeKey("zz")).toBe("partial");
    });

    it("names a key of the right length that is not one", () => {
        // Third block does not start with 4: not a v4 UUID, and the keymanager
        // will fold it into a different account rather than reject it.
        expect(describeKey("5e9c1a2b-4f6d-1c3e-9a71-2b8d0f4e6c15")).toBe("malformed");
        // Fourth block outside 8-b.
        expect(describeKey("5e9c1a2b-4f6d-4c3e-3a71-2b8d0f4e6c15")).toBe("malformed");
    });

    it("names letters no key contains", () => {
        expect(describeKey("5e9c1a2b-4f6d-4c3e-9a71-2b8d0f4e6c1!")).toBe("illegal");
    });

    it("names a value too long to still be a code and not hex", () => {
        expect(describeKey("zzzzzzzz")).toBe("malformed");
    });

    it("says nothing about an empty field", () => {
        expect(describeKey("")).toBe("empty");
        expect(describeKey("   ")).toBe("empty");
    });
});

describe("isSubmittable", () => {
    it("lets a key and a code through", () => {
        expect(isSubmittable(A_KEY)).toBe(true);
        expect(isSubmittable("123456")).toBe(true);
    });

    it("lets an empty field through, because the form also carries a file", () => {
        // The upload path posts this same form with the key field untouched.
        // Blocking an empty field here would break it, and the break would look
        // like a button that does nothing.
        expect(isSubmittable("")).toBe(true);
    });

    it("holds back what is visibly not a key yet", () => {
        expect(isSubmittable("5e9c1a2b-4f6d")).toBe(false);
        expect(isSubmittable("5e9c1a2b-4f6d-1c3e-9a71-2b8d0f4e6c15")).toBe(false);
    });
});
