export let funding_source = document.querySelector(
    "input[name=funding_source]"
);
if (funding_source) {
    funding_source = funding_source.value;
}

export let orderID;

export function paypalOptions() {
    let amount = document.querySelector("input[name=amount]").value;
    let interval = document.querySelector("input[name=interval]").value;

    let paypalOptions = {};

    if (interval != "once") {
        let plan_id = document.querySelector("input[name=plan-id]").value;
        let subscription_data = {
            plan_id: plan_id,
            application_context: {
                shipping_preference: "NO_SHIPPING",
            },
            plan: {
                billing_cycles: [
                    {
                        sequence: 1,
                        total_cycles: 0,
                        pricing_scheme: {
                            fixed_price: {
                                currency_code: "EUR",
                                value: amount,
                            },
                        },
                    },
                ],
            },
        };
        paypalOptions.createSubscription = function (data, actions) {
            return actions.subscription.create(subscription_data);
        };
        paypalOptions.onApprove = paymentSuccessful;
    } else {
        paypalOptions.createOrder = function (data, actions) {
            let order_url = document.querySelector("input[name=order-url]").value;
            return fetch(order_url)
                .then((response) => response.json())
                .then((order) => {
                    orderID = order.id;
                    return order.id;
                });
        };
        paypalOptions.onApprove = function (data, actions) {
            let order_url = document.querySelector("input[name=order-url]").value;
            return fetch(order_url, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    orderID: orderID,
                }),
            })
                .then((response) => response.json());
        };
    }
    paypalOptions.application_context = { shipping_preference: "NO_SHIPPING" };
    paypalOptions.fundingSource = funding_source;

    return paypalOptions;
}

function paymentSuccessful(data) {
    console.log("success", data);
}