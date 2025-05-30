import { loadScript } from "@paypal/paypal-js";

let creditcard_container = document.querySelector("#creditcard-data");
const membership_form = document.getElementById("membership-form");
let card_fields = null;
let cancel_callback = null;
let success_callback = null;

export function initializeCreditcard() {
    let required =
        document.querySelector("#payment-method-creditcard").checked == true;


    if (!required) return;
    if (card_fields == null) {
        creditcard_container.classList.add("loading");
        let background_color =
            window
                .getComputedStyle(document.querySelector("html"))
                .getPropertyValue("background-color") ?? "white";
        let color =
            window
                .getComputedStyle(document.querySelector("html"))
                .getPropertyValue("color") ?? "black";
        /**
         * Border Styles
         */
        let border_color =
            window
                .getComputedStyle(document.querySelector("#firstname"))
                .getPropertyValue("border-bottom-color") ?? "lightgrey";
        let border_width =
            window
                .getComputedStyle(document.querySelector("#firstname"))
                .getPropertyValue("border-bottom-width") ?? "2px";
        let border_radius =
            window
                .getComputedStyle(document.querySelector("#firstname"))
                .getPropertyValue("border-bottom-right-radius") ?? "0px";
        let border_style =
            window
                .getComputedStyle(document.querySelector("#firstname"))
                .getPropertyValue("border-bottom-style") ?? "inset";
        let line_height =
            window
                .getComputedStyle(document.querySelector("#firstname"))
                .getPropertyValue("line-height") ?? "1";
        let height =
            window
                .getComputedStyle(document.querySelector("#firstname"))
                .getPropertyValue("height") ?? "36px";
        let font_size =
            window
                .getComputedStyle(document.querySelector("#firstname"))
                .getPropertyValue("font-size") ?? "36px";
        let font_family =
            window
                .getComputedStyle(document.querySelector("#firstname"))
                .getPropertyValue("font-family") ?? "";
        let client_id = document.querySelector("#payment-method-creditcard").dataset.clientid;
        loadScript({ clientId: client_id, components: ["card-fields"], currency: "EUR", vault: true, intent: "authorize" }).then(paypal => {
            card_fields = paypal.CardFields({
                createOrder: createOrder, onError: onError, onApprove: approveOrder, onCancel: handleCancel, style: {
                    'body': {
                        padding: '1px',
                    },
                    'input': {
                        'padding': '0.5rem 1rem',
                        'font-size': '16px',
                        background: background_color,
                        color: color,
                        border: border_width + " " + border_style + " " + border_color,
                        'border-radius': border_radius,
                        'line-height': line_height,
                        height: height,
                        'font-size': font_size,
                        'font-family': font_family
                    },
                    'input:focus': {
                        'box-shadow': 'none',
                        'outline': 'auto',
                    }
                }
            });
            if (card_fields.isEligible()) {
                let render_promises = [];
                const name_field = card_fields.NameField({});
                render_promises.push(name_field.render("#creditcard-name"));
                const number_field = card_fields.NumberField({});
                render_promises.push(number_field.render("#creditcard-number"));
                const valid_until_field = card_fields.ExpiryField({});
                render_promises.push(valid_until_field.render("#creditcard-valid-until"));
                const cvv_field = card_fields.CVVField({});
                render_promises.push(cvv_field.render("#creditcard-cvv"));
                Promise.all(render_promises).then(() => creditcard_container.classList.remove("loading"));
            }
        });



        let createOrder = async (data, actions) => {
            let form_data = new FormData(membership_form);
            return fetch(membership_form.action, {
                body: form_data,
                method: "POST",
                headers: {
                    Accept: "application/json"
                }
            }).then(async response => {
                let response_json = await response.json();
                try {
                    cancel_callback = response_json.cancel_url;
                    success_callback = response_json.success_url
                } catch (ignored) { }
                if (response.status == 200) {
                    return response_json.order_id;
                } else {
                    handle_card_error("custom", response_json.message);
                    actions.restart();
                }
            }).catch(error => {
                // ToDo handle order creation error
            });
        };
        let approveOrder = async (data, actions) => {
            if (success_callback != null) {
                return fetch(success_callback, { headers: { Accept: "application/json" } }).then(async response => {
                    if (response.status == 200) {
                        try {
                            let response_json = await response.json();
                            success_callback = response_json.success_url;
                            document.location.href = success_callback;
                        } catch (error) {
                            handleCancel();
                            actions.restart();
                        }
                    } else {
                        try {
                            let response_json = await response.json();
                            cancel_callback = response_json.cancel_url;
                        } catch (ignored) { }
                        handleCancel();
                        actions.restart();
                    }
                });
            } else {
                handleCancel();
                actions.restart();
            }
        }
        let onError = async error => {
            return handleCancel();
        }
    }
    membership_form.addEventListener("submit", handleSubmit);
}

export function uninitializeCreditcard() {
    membership_form.removeEventListener("submit", handleSubmit);
}

function handleSubmit(e) {
    e.preventDefault();
    toggleFormSubmit(false);
    clear_card_errors();
    card_fields.getState().then(state => {
        if (!state.isFormValid) {
            handle_card_error("syntax");
        } else {
            card_fields.submit().catch(error => {
                console.error(error);
                handle_card_error("acceptance");
            });
        }
    });
}

function toggleFormSubmit(enabled = true) {
    let submit_button = document.querySelector("button[type=submit]");
    if (enabled) {
        submit_button.classList.add("btn-primary");
        submit_button.classList.remove("btn-disabled");
        submit_button.classList.remove("loading");
        submit_button.disabled = false;
    } else {
        submit_button.classList.remove("btn-primary");
        submit_button.classList.add("btn-disabled");
        submit_button.classList.add("loading");
        submit_button.disabled = true;
    }
}

async function handleCancel() {
    if (cancel_callback != null) {
        return fetch(cancel_callback, {
            headers: {
                Accept: "application/json"
            }
        }).then(() => {
            cancel_callback = null;
            return fetch("/membership/token", { headers: { Accept: "application/json" } });

        }).then(async response => {
            if (response.status == 200) {
                let json_response = await response.json();
                let token = json_response.token;
                document.querySelector("input[name=_token]").value = token;
            }
        }).finally(() => {
            toggleFormSubmit(true);
        });
    }
}

function handle_card_error(error_type, message) {
    clear_card_errors();
    let errors = document.querySelector("#creditcard-data #errors");
    switch (error_type) {
        case "acceptance":
            errors.querySelector("#card-acceptance-error").classList.remove("hidden");
            break;
        case "syntax":
            errors.querySelector("#syntax-error").classList.remove("hidden");
            break;
        case "custom":
            let error_container = document.createElement("div");
            error_container.classList.add("error", "custom-error");
            error_container.textContent = message;
            errors.appendChild(error_container);
            break;
    };
}

function clear_card_errors() {
    let errors = document.querySelector("#creditcard-data #errors");
    errors.querySelectorAll(".custom-error").forEach(element => {
        element.remove();
    });
    errors.querySelectorAll(".error").forEach(element => {
        element.classList.add("hidden");
    });
}