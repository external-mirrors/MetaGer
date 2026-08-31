/**
 * PayPal-Zahlung — Port von pass/resources/js/checkout_paypal.js
 * (metager-keymanager) auf die eigenen JSON-Ziele dieser Anwendung
 * (App\Http\Controllers\ChargeController::paypalOrderCreate/Capture). Die
 * SDK-Logik (Smart Buttons, PaymentFields, Advanced Card Fields) ist
 * unverändert übernommen; nur woher Konfiguration kommt (data-Attribute
 * statt versteckte Formularfelder) und wohin `createOrder`/`onApprove`
 * fetchen (dieselbe Herkunft, nicht der Keymanager) ist neu.
 *
 * Läuft nur, wenn #checkout-paypal im Markup steht — checkout/paypal.blade.php
 * liefert es `hidden`, und dieses Skript deckt es erst auf, sobald das SDK
 * tatsächlich lädt. Ohne Javascript bleibt es verborgen; siehe die
 * Erklärung dort.
 */

import { loadScript } from "@paypal/paypal-js";

const container = document.getElementById("checkout-paypal");

if (container) {
    initializePaypalPayments(container);
}

function initializePaypalPayments(container) {
    const fundingSource = container.dataset.fundingSource;
    const directCardEnabled = container.dataset.directCardEnabled === "1";

    const scriptData = {
        "client-id": container.dataset.clientId,
        components: ["buttons", "marks", "payment-fields", "funding-eligibility"],
        "disable-funding": "sofort,ideal",
        currency: "EUR",
        // Carries the CSP nonce onto the SDK's own injected <script> tag —
        // paypal-js recognizes this exact key and applies it as `nonce`,
        // same convention resources/views/spende/paymentMethod.blade.php
        // already relies on for its own PayPal script tag.
        "data-csp-nonce": container.dataset.nonce,
    };

    if (container.dataset.clientToken) {
        scriptData["data-client-token"] = container.dataset.clientToken;
    }

    if (fundingSource !== "paypal") {
        scriptData["enable-funding"] = fundingSource;
    }

    if (fundingSource === "card" && directCardEnabled) {
        scriptData.components = ["card-fields", "funding-eligibility", "buttons"];
    }

    loadScript(scriptData)
        .then((paypal) => {
            if (!paypal.isFundingEligible(fundingSource)) {
                recordFundingNotEligible(fundingSource);
                document.location.href = container.dataset.notEligibleUrl;
                return;
            }

            container.hidden = false;

            if (fundingSource === "card" && directCardEnabled) {
                loadCardPayment(paypal, container);
            } else if (fundingSource === "paypal") {
                paypal.Buttons(getPaypalCheckoutData(container, null)).render("#checkout-paypal-payment-button");
            } else {
                const paymentFieldsContainer = document.getElementById("checkout-paypal-payment-fields");
                if (paymentFieldsContainer) {
                    paymentFieldsContainer.hidden = false;
                }

                const { backgroundColor, textColor } = themeColors();
                paypal
                    .PaymentFields({
                        fundingSource,
                        style: {
                            input: {
                                background: backgroundColor,
                                color: textColor,
                                "font-size": "16px",
                                padding: "0.4rem 0.75rem",
                            },
                            body: {
                                background: backgroundColor,
                                color: textColor,
                                padding: 0,
                            },
                        },
                        fields: {},
                    })
                    .render("#checkout-paypal-payment-fields");

                paypal.Buttons(getPaypalCheckoutData(container, fundingSource)).render("#checkout-paypal-payment-button");
            }
        })
        .catch((err) => {
            console.error("failed to load the PayPal JS SDK script", err);
        });
}

/**
 * Remembers a funding source the SDK itself declined to offer, so a future
 * visit could skip straight to a known-eligible one — mirrors keymanager's
 * own bookkeeping in pass/resources/js/checkout_paypal.js, unread by
 * anything today but kept, since it's cheap and future-proofs a smarter
 * chooser page later.
 */
export function recordFundingNotEligible(fundingSource, storage = localStorage) {
    let disabledFunding = storage.getItem("funding_not_eligible");
    disabledFunding = disabledFunding === null ? [] : JSON.parse(disabledFunding);
    disabledFunding.push(fundingSource);
    storage.setItem("funding_not_eligible", JSON.stringify(disabledFunding));
    return disabledFunding;
}

/**
 * Maps a rejected `cardFields.submit()` onto one of the card-error element
 * ids checkout/paypal.blade.php renders (`checkout-paypal-card-error-*`) —
 * the same three-step fallback as the ported keymanager JS: a processor
 * response code, then any `.details[]` descriptions, then a "3DS" string
 * match, then a generic fallback. Pulled out as its own pure function (no
 * DOM) so the mapping itself is testable without a real CardFields error
 * shape from a live SDK.
 *
 * @returns {{ elementId: string, detailMessages: string[] }}
 */
export function mapCardSubmitError(error) {
    try {
        const processorResponseCode = error.purchase_units[0].payments.captures[0].processor_response.response_code;
        return { elementId: `checkout-paypal-card-error-${processorResponseCode}`, detailMessages: [] };
    } catch {
        // no processor response code on this error shape
    }

    if (Array.isArray(error?.details) && error.details.length > 0) {
        return {
            elementId: null,
            detailMessages: error.details.map((detail) => detail.description),
        };
    }

    const message = typeof error?.toString === "function" ? error.toString() : String(error);
    return {
        elementId: message.includes("3DS") ? "checkout-paypal-card-error-3ds" : "checkout-paypal-card-error-1330",
        detailMessages: [],
    };
}

function themeColors() {
    const isDarkMode = window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches;
    const rootStyle = window.getComputedStyle(document.documentElement);
    const backgroundColor = (rootStyle.getPropertyValue("--background-color") || (isDarkMode ? "#222" : "#fff")).trim();
    const textColor = (rootStyle.getPropertyValue("--font-color") || (isDarkMode ? "#fff" : "#000")).trim();
    return { backgroundColor, textColor };
}

function getPaypalCheckoutData(container, fundingSource) {
    const isDarkMode = window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches;
    const directCardEnabled = container.dataset.directCardEnabled === "1";

    const checkoutData = {
        style: {
            color: isDarkMode ? "black" : "gold",
            shape: "rect",
            label: "paypal",
            layout: "vertical",
            height: 50,
        },
        fundingSource,
        onClick: () => {
            hidePaypalMessage();
            validateRevocation();
        },
        createOrder: () => {
            return fetch(container.dataset.orderCreateUrl, {
                method: "POST",
                headers: { "Content-Type": "application/json;charset=utf-8" },
                credentials: "same-origin",
            })
                .then((response) => response.json())
                .then((order) => {
                    container.dataset.paymentReferenceId = order.payment_reference;
                    return order.paypal_order_id;
                });
        },
        onCancel: () => {
            showPaypalMessage(container.dataset.cancelMessage);
        },
        onError: (err) => {
            console.error(err);
            if (err && err.errors && err.errors.length > 0) {
                err.errors.forEach((error) => {
                    if (error.msg) {
                        showPaypalMessage(error.msg);
                    }
                });
            } else {
                showPaypalMessage(container.dataset.errorMessage);
            }
        },
        onApprove: () => {
            return fetch(container.dataset.orderCaptureUrl, {
                method: "POST",
                headers: { "Content-Type": "application/json;charset=utf-8" },
                credentials: "same-origin",
                body: JSON.stringify({ payment_reference: container.dataset.paymentReferenceId }),
            })
                .then((response) => {
                    if (response.status !== 200) {
                        return response.json().then((jsonResponse) => {
                            throw jsonResponse;
                        });
                    }
                    return response.json();
                })
                .then((orderData) => {
                    if (typeof orderData.redirect_url !== "undefined") {
                        document.location.href = orderData.redirect_url;
                    }
                });
        },
        onInit: (data, actions) => {
            actions.disable();
            const revocationCheckbox = document.getElementById("checkout-revocation");
            revocationCheckbox.addEventListener("change", (e) => {
                if (e.target.checked) {
                    actions.enable();
                } else {
                    actions.disable();
                }
            });
            document.getElementById("checkout-paypal-loading").hidden = true;
            const paymentFields = document.getElementById("checkout-paypal-payment-fields");
            if (paymentFields) {
                paymentFields.hidden = false;
            }
            const paymentButton = document.getElementById("checkout-paypal-payment-button");
            if (paymentButton) {
                paymentButton.hidden = false;
            }
            document.getElementById("checkout-paypal-revocation-container").hidden = false;
        },
    };

    if (fundingSource === "card" && directCardEnabled) {
        checkoutData.onCancel = () => {
            lockForm(false);
            showPaypalMessage(container.dataset.cancelMessage);
        };
        checkoutData.onError = () => {
            lockForm(false);
            showPaypalMessage(container.dataset.errorMessage);
        };
    }

    return checkoutData;
}

function loadCardPayment(paypal, container) {
    const cardContainer = document.getElementById("checkout-paypal-card-container");
    const skeleton = document.getElementById("checkout-paypal-card-form-skeleton");
    const cardForm = skeleton.content.querySelector("#checkout-paypal-card-form").cloneNode(true);
    cardForm.hidden = true;
    cardContainer.appendChild(cardForm);

    const { backgroundColor, textColor } = themeColors();

    const cardFieldsOptions = getPaypalCheckoutData(container, "card");
    cardFieldsOptions.style = {
        body: { padding: 0, background: backgroundColor },
        input: { background: backgroundColor, color: textColor, "font-size": "16px", padding: "0.4rem 0.75rem" },
    };

    const cardFields = paypal.CardFields(cardFieldsOptions);
    if (!cardFields.isEligible()) {
        showCardError("checkout-paypal-card-error-generic");
        return;
    }

    Promise.all([
        cardFields.NameField({ placeholder: "John Doe" }).render("#checkout-paypal-card-name"),
        cardFields.NumberField({ placeholder: "4111 1111 1111 1111" }).render("#checkout-paypal-card-number"),
        cardFields.ExpiryField({ placeholder: "12/23" }).render("#checkout-paypal-card-expiration"),
        cardFields.CVVField().render("#checkout-paypal-card-cvv"),
    ]).then(() => {
        cardForm.hidden = false;
        document.getElementById("checkout-paypal-loading").hidden = true;
    });

    cardForm.addEventListener("submit", (event) => {
        if (!cardForm.checkValidity()) {
            return;
        }
        event.preventDefault();
        hideCardErrors();
        lockForm(true, cardForm);

        cardFields
            .getState()
            .then((data) => {
                if (!data.isFormValid) {
                    showCardError("checkout-paypal-card-error-1330");
                    return;
                }

                return cardFields.submit().catch((error) => {
                    console.error(error);
                    const { elementId, detailMessages } = mapCardSubmitError(error);
                    if (elementId) {
                        showCardError(elementId);
                    }
                    if (detailMessages.length > 0) {
                        const errorsContainer = document.getElementById("checkout-paypal-card-errors");
                        errorsContainer.hidden = false;
                        for (const description of detailMessages) {
                            const errorElement = document.createElement("div");
                            errorElement.className = "checkout-consent__error";
                            errorElement.textContent = description;
                            errorsContainer.appendChild(errorElement);
                        }
                    }
                });
            })
            .finally(() => {
                lockForm(false, cardForm);
            });
    });
}

function validateRevocation() {
    const revocationCheckbox = document.getElementById("checkout-revocation");
    if (!revocationCheckbox.checkValidity()) {
        revocationCheckbox.reportValidity();
    }
}

function showCardError(elementId) {
    const errorsContainer = document.getElementById("checkout-paypal-card-errors");
    if (!errorsContainer) {
        return;
    }
    errorsContainer.hidden = false;
    const errorElement = document.getElementById(elementId) || document.getElementById("checkout-paypal-card-error-generic");
    if (errorElement) {
        errorElement.hidden = false;
    }
}

function hideCardErrors() {
    const errorsContainer = document.getElementById("checkout-paypal-card-errors");
    if (errorsContainer) {
        errorsContainer.hidden = true;
        errorsContainer.querySelectorAll(".checkout-consent__error").forEach((el) => {
            el.hidden = true;
        });
    }
    hidePaypalMessage();
}

function lockForm(lock, cardForm) {
    const submitButton = cardForm.querySelector("#checkout-paypal-card-submit");
    const formElements = cardForm.querySelectorAll("input, select, textarea, button");
    if (!submitButton) {
        return;
    }

    formElements.forEach((el) => {
        el.disabled = lock;
    });
    submitButton.classList.toggle("loading", lock);
}

function showPaypalMessage(message) {
    const msgDiv = document.getElementById("checkout-paypal-message");
    if (msgDiv) {
        msgDiv.textContent = message;
        msgDiv.hidden = false;
        msgDiv.scrollIntoView({ behavior: "smooth" });
    }
}

function hidePaypalMessage() {
    const msgDiv = document.getElementById("checkout-paypal-message");
    if (msgDiv) {
        msgDiv.textContent = "";
        msgDiv.hidden = true;
    }
}
