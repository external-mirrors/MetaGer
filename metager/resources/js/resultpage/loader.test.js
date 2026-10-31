import { describe, expect, it } from "vitest";

import { ENGINE_POLLS, MAX_POLLS, nextPollDelay } from "./loader";

describe("nextPollDelay", () => {
    it("stops as soon as the server says the search is finished", () => {
        expect(nextPollDelay(1, { finished: true, resolving: false })).toBeNull();
    });

    it("stops on an answer it cannot read", () => {
        expect(nextPollDelay(1, null)).toBeNull();
    });

    it("asks every second while engines may still answer", () => {
        expect(nextPollDelay(1, { finished: false })).toBe(1000);
        expect(nextPollDelay(ENGINE_POLLS - 1, { finished: false })).toBe(1000);
    });

    it("gives up on engines after ten polls, as it always has", () => {
        expect(nextPollDelay(ENGINE_POLLS, { finished: false, resolving: false })).toBeNull();
    });

    it("keeps asking, more slowly, while late links are being resolved", () => {
        expect(nextPollDelay(ENGINE_POLLS, { finished: false, resolving: true })).toBe(2000);
        expect(nextPollDelay(MAX_POLLS - 1, { finished: false, resolving: true })).toBe(2000);
    });

    it("outlasts the server's 45 second window, so the server is what ends it", () => {
        const seconds = ENGINE_POLLS * 1 + (MAX_POLLS - ENGINE_POLLS) * 2;
        expect(seconds).toBeGreaterThan(45);
        expect(nextPollDelay(MAX_POLLS, { finished: false, resolving: true })).toBeNull();
    });
});
