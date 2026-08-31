import { describe, expect, it } from "vitest";

import { mapCardSubmitError, recordFundingNotEligible } from "./checkout-paypal";

describe("recordFundingNotEligible", () => {
    it("starts a fresh list when nothing was recorded before", () => {
        const store = new Map();
        const storage = {
            getItem: (key) => (store.has(key) ? store.get(key) : null),
            setItem: (key, value) => store.set(key, value),
        };

        const result = recordFundingNotEligible("card", storage);

        expect(result).toEqual(["card"]);
        expect(JSON.parse(store.get("funding_not_eligible"))).toEqual(["card"]);
    });

    it("appends to an existing list rather than overwriting it", () => {
        const store = new Map([["funding_not_eligible", JSON.stringify(["p24"])]]);
        const storage = {
            getItem: (key) => (store.has(key) ? store.get(key) : null),
            setItem: (key, value) => store.set(key, value),
        };

        const result = recordFundingNotEligible("blik", storage);

        expect(result).toEqual(["p24", "blik"]);
    });
});

describe("mapCardSubmitError", () => {
    it("prefers the processor response code when one is present", () => {
        const error = {
            purchase_units: [{ payments: { captures: [{ processor_response: { response_code: "5120" } }] } }],
            details: [{ description: "should not be used" }],
        };

        const result = mapCardSubmitError(error);

        expect(result.elementId).toBe("checkout-paypal-card-error-5120");
        expect(result.detailMessages).toEqual([]);
    });

    it("falls back to .details[] descriptions when there is no processor response code", () => {
        const error = { details: [{ description: "Card declined" }, { description: "Try another card" }] };

        const result = mapCardSubmitError(error);

        expect(result.elementId).toBeNull();
        expect(result.detailMessages).toEqual(["Card declined", "Try another card"]);
    });

    it("maps a 3DS-mentioning error to the 3ds element when neither of the above apply", () => {
        const error = new Error("3DS authentication required but not completed");

        const result = mapCardSubmitError(error);

        expect(result.elementId).toBe("checkout-paypal-card-error-3ds");
        expect(result.detailMessages).toEqual([]);
    });

    it("falls back to the generic invalid-card element for an unrecognized error shape", () => {
        const result = mapCardSubmitError(new Error("something else entirely"));

        expect(result.elementId).toBe("checkout-paypal-card-error-1330");
        expect(result.detailMessages).toEqual([]);
    });

    it("handles a plain string thrown instead of an Error object", () => {
        // cardFields.submit() can reject with something that is not an
        // Error — .toString/.details/.purchase_units may all be undefined.
        const result = mapCardSubmitError("network failure");

        expect(result.elementId).toBe("checkout-paypal-card-error-1330");
        expect(result.detailMessages).toEqual([]);
    });
});
