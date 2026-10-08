import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";

import { CHARGE_CHECK_TIMEOUT_MS, fetchCharge } from "./chargeCheck";

const ENDPOINT = "https://metager.de/keys/api/json/key/";
const KEY = "c85067bb-1365-4dcb-a4b7-fb1e3a017105";

/**
 * The balance asked for before signing in — only to decide whether to ask
 * "this key is empty, sure?".
 *
 * The sign-in form waits on this answer before it submits, so the one property
 * that matters most is that it always *ends*. It used to have no timeout: a
 * keyserver that accepted the connection and never answered left the visitor
 * pressing "Anmelden" with nothing happening and nothing on screen to say why.
 */
describe("fetchCharge", () => {
    beforeEach(() => {
        vi.useFakeTimers();
    });

    afterEach(() => {
        vi.useRealTimers();
        vi.unstubAllGlobals();
    });

    it("answers with the key's charge", async () => {
        const fetch = vi.fn().mockResolvedValue(
            new Response(JSON.stringify({ charge: 128 }), { status: 200 })
        );
        vi.stubGlobal("fetch", fetch);

        await expect(fetchCharge(ENDPOINT, KEY)).resolves.toBe(128);
        expect(fetch.mock.calls[0][0]).toBe(ENDPOINT + KEY);
    });

    it("answers with an empty key's zero, which is what the confirmation is for", async () => {
        vi.stubGlobal("fetch", vi.fn().mockResolvedValue(
            new Response(JSON.stringify({ charge: 0 }), { status: 200 })
        ));

        await expect(fetchCharge(ENDPOINT, KEY)).resolves.toBe(0);
    });

    it("does not know when the keyserver says no", async () => {
        vi.stubGlobal("fetch", vi.fn().mockResolvedValue(new Response("", { status: 503 })));

        await expect(fetchCharge(ENDPOINT, KEY)).resolves.toBeNull();
    });

    it("does not know when the keyserver cannot be reached", async () => {
        vi.stubGlobal("fetch", vi.fn().mockRejectedValue(new TypeError("NetworkError")));

        await expect(fetchCharge(ENDPOINT, KEY)).resolves.toBeNull();
    });

    it("gives up on a keyserver that never answers, so the form still submits", async () => {
        // A fetch that only ever ends by being aborted — what a hung
        // connection looks like from the page.
        vi.stubGlobal("fetch", vi.fn((url, { signal }) => new Promise((resolve, reject) => {
            signal.addEventListener("abort", () => reject(signal.reason));
        })));

        const answer = fetchCharge(ENDPOINT, KEY);
        await vi.advanceTimersByTimeAsync(CHARGE_CHECK_TIMEOUT_MS);

        await expect(answer).resolves.toBeNull();
    });

    it("waits long enough for a slow but working keyserver", () => {
        // Too short and the empty-key question is skipped for everyone on a
        // slow connection; it is a question worth asking.
        expect(CHARGE_CHECK_TIMEOUT_MS).toBeGreaterThanOrEqual(2000);
    });
});
