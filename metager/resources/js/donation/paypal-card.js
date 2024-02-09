import { paypalOptions, paymentSuccessful, orderID } from "./paypal-options";

/**
 * Process PayPal Card payments
 */
function processPaypalCard() {
    let container = document.querySelector("#content-container");

    // Create Creditcard fields
    let card_form = document
        .getElementById("card-form-skeleton")
        .cloneNode(true);
    card_form.id = "card-form";
    card_form.classList.remove("hidden");
    document.getElementById("card-form-skeleton").remove();
    container.appendChild(card_form);

    let amount = document.querySelector("input[name=amount]").value;
    let interval = document.querySelector("input[name=interval]").value;


    let cardFields = paypal.CardFields({
        ...paypalOptions(),
        onError: err => {
        },
        style: {
            input: {
                "font-size": "16px",
                "padding": "0.4rem 0.75rem",
            },
        }
    }
    );

    if (cardFields.isEligible()) {
        const nameField = cardFields.NameField({ placeholder: "John Doe" });
        nameField.render("#card-name");

        const numberField = cardFields.NumberField({ placeholder: "4111 1111 1111 1111" });
        numberField.render("#card-number");

        const expiryField = cardFields.ExpiryField({ placeholder: "123" });
        expiryField.render("#card-expiration");

        const cvvField = cardFields.CVVField();
        cvvField.render("#card-cvv");

        document
            .getElementById("card-form")
            .addEventListener("submit", (event) => {
                event.preventDefault();
                hideErrors();
                lockForm(true);
                cardFields.submit().then(() => {
                }).catch((error) => {
                    console.error(error);
                    lockForm(false);
                    try {
                        let processor_response_code =
                            error.purchase_units[0].payments.captures[0]
                                .processor_response.response_code;
                        showError(`error-${processor_response_code}`);
                    } catch (e) { }

                    try {
                        let card_errors_container = document.querySelector("#card-errors");
                        if (card_errors_container.classList.contains("hidden")) {
                            card_errors_container.classList.remove("hidden");
                        }
                        for (let i = 0; i < error.details.length; i++) {
                            let error_container = document.createElement("div");
                            error_container.classList.add("error");
                            error_container.textContent = error.details[i].description;
                            card_errors_container.appendChild(error_container);
                        }
                    } catch (e) { }
                });
            });
    }
}

function showError(errorId) {
    let error_container = document.querySelector("#card-errors");
    if (!error_container) {
        return;
    }
    if (error_container.classList.contains("hidden")) {
        error_container.classList.remove("hidden");
    }
    let error_element = error_container.querySelector(`#${errorId}`);
    if (!error_element) {
        error_element = error_container.querySelector("#error-generic");
    }
    if (error_element.classList.contains("hidden")) {
        error_element.classList.remove("hidden");
    }
}

function hideErrors() {
    let error_container = document.querySelector("#card-errors");
    if (!error_container) {
        return;
    }
    if (!error_container.classList.contains("hidden")) {
        error_container.classList.add("hidden");
    }
    error_container.querySelectorAll(".error").forEach(error_element => {
        if (!error_element.classList.contains("hidden")) {
            error_element.classList.add("hidden");
        }
    });
}

/**
 * Prevents multiple submissions of the card form
 * 
 * @param {boolean} lock 
 */
function lockForm(lock) {
    let submit_button = document.querySelector("#card-form #card-submit");
    if (!submit_button) {
        return;
    }
    submit_button.disabled = lock;
}

export default processPaypalCard;