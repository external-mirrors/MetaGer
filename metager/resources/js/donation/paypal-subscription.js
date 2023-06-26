import { funding_source, paypalOptions } from "./paypal-options";

export function processPaypalSubscription() {
    addPaymentContainers();

    paypal.Buttons(paypalOptions()).render("#paypal-payment-button");
}

function addPaymentContainers() {
    let contentContainer = document.querySelector("#content-container.paypal");

    let paymentButtonContainer = document.createElement("div");
    paymentButtonContainer.id = "paypal-payment-button";
    contentContainer.appendChild(paymentButtonContainer);
}