import { beforeEach, describe, expect, it } from "vitest";

import { getPaypalCheckoutData, mapCardSubmitError, needsPaymentFieldsWidget, recordFundingNotEligible } from "./checkout-paypal";

beforeEach(() => {
    document.body.innerHTML = "";
});

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

describe("needsPaymentFieldsWidget", () => {
    // Regression test: PaymentFields({fundingSource: "card"}) used to be
    // called unconditionally alongside every other funding source. It mounts
    // successfully but has nothing to render for "card" (no brand mark, no
    // extra input field), leaving an empty iframe that still claims its
    // reserved layout space — visible as a blank box above the card button.
    it("is false for card, which has nothing for PaymentFields to show", () => {
        expect(needsPaymentFieldsWidget("card")).toBe(false);
    });

    it.each(["p24", "bancontact", "blik", "eps", "mybank"])("is true for %s", (fundingSource) => {
        expect(needsPaymentFieldsWidget(fundingSource)).toBe(true);
    });
});

describe("getPaypalCheckoutData for the card funding source", () => {
    function buildDom() {
        document.body.innerHTML = `
            <div id="checkout-paypal-message" hidden></div>
            <form id="checkout-paypal-card-form">
                <input id="checkout-paypal-card-name">
                <button type="submit" id="checkout-paypal-card-submit"></button>
            </form>
        `;
        // jsdom does not implement scrollIntoView (showPaypalMessage calls it).
        document.getElementById("checkout-paypal-message").scrollIntoView = () => {};

        const container = document.createElement("div");
        container.dataset.directCardEnabled = "1";
        container.dataset.cancelMessage = "Abgebrochen";
        container.dataset.errorMessage = "Etwas ist schiefgelaufen";

        return { container, cardForm: document.getElementById("checkout-paypal-card-form") };
    }

    // Regression test: onError/onCancel used to call lockForm(false) with no
    // cardForm argument, so lockForm's `cardForm.querySelector(...)` threw a
    // TypeError as soon as the SDK reported a real error (e.g. an invalid
    // client id) — surfacing to the user as "the credit card form throws JS
    // errors" instead of the intended inline error message.
    it("does not throw and shows the error message when the SDK reports an error", () => {
        const { container, cardForm } = buildDom();
        const submitButton = cardForm.querySelector("#checkout-paypal-card-submit");
        submitButton.classList.add("loading");

        const checkoutData = getPaypalCheckoutData(container, "card", cardForm);

        expect(() => checkoutData.onError()).not.toThrow();

        const messageDiv = document.getElementById("checkout-paypal-message");
        expect(messageDiv.hidden).toBe(false);
        expect(messageDiv.textContent).toBe("Etwas ist schiefgelaufen");
        expect(submitButton.classList.contains("loading")).toBe(false);
    });

    it("does not throw and shows the cancel message when the SDK reports a cancellation", () => {
        const { container, cardForm } = buildDom();
        const submitButton = cardForm.querySelector("#checkout-paypal-card-submit");
        submitButton.classList.add("loading");

        const checkoutData = getPaypalCheckoutData(container, "card", cardForm);

        expect(() => checkoutData.onCancel()).not.toThrow();

        const messageDiv = document.getElementById("checkout-paypal-message");
        expect(messageDiv.hidden).toBe(false);
        expect(messageDiv.textContent).toBe("Abgebrochen");
        expect(submitButton.classList.contains("loading")).toBe(false);
    });
});
