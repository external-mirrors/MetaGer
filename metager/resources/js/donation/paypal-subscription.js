import { funding_source, paypalOptions } from "./paypal-options";

export function processPaypalSubscription() {
    addPaymentContainers();

    let create_subscription_url = document.querySelector("input[name=create-subscription-url]").value;

    let redirect_url = null;

    paypal.Buttons({
        fundingSource: funding_source,
        createSubscription: () => {
            return fetch(create_subscription_url, {
                method: "POST"
            }).then(response => response.json()).then(response => {
                redirect_url = response.redirect_url;
                return response.id;
            });
        },
        onApprove: () => {
            window.location.replace(redirect_url);
        }
    }).render("#paypal-payment-button");
}

function addPaymentContainers() {
    let contentContainer = document.querySelector("#content-container.paypal");

    let paymentButtonContainer = document.createElement("div");
    paymentButtonContainer.id = "paypal-payment-button";
    contentContainer.appendChild(paymentButtonContainer);
}