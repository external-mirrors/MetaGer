let customAmountSwitch = document.querySelector(
  "#content-container.amount #custom-amount-switch"
);
if (customAmountSwitch) {
  customAmountSwitch.addEventListener("change", (e) => {
    if (e.target.checked) {
      let customAmountInput = document.querySelector(
        "#content-container.amount #amount"
      );
      if (customAmountInput) {
        customAmountInput.focus();
      }
    }
  });
}

if (document.querySelector("#content-container.paymentMethod")) {
  paypal.getFundingSources().forEach(function (fundingSource) {
    let mark = paypal.Marks({ fundingSource: fundingSource });
    console.log(fundingSource);
    if (mark.isEligible()) {
      /*let paymentMethodLinkContainer = document.createElement("li");
      paymentMethodLinkContainer.classList.add("payment-method");
      paymentMethodLinkContainer.id = fundingSource;
      document
        .getElementById("payment-methods")
        .appendChild(paymentMethodLinkContainer);
      mark.render("#" + fundingSource);*/
    }
  });
}

if (document.querySelector("#content-container.paypal-subscription")) {
  paypal.Buttons(paypalOptions()).render("#paypal-buttons");
  function paypalOptions() {
    let amount = document.querySelector("input[name=amount]").value;
    let interval = document.querySelector("input[name=interval]").value;
    let funding_source = document.querySelector("input[name=funding_source]").value;

    let paypalOptions = {};

    if (interval != "once") {
      let plan_id = document.querySelector("input[name=plan-id]").value;
      let subscription_data = {
        'plan_id': plan_id,
        'application_context': {
          'shipping_preference': 'NO_SHIPPING'
        },
        'plan': {
          'billing_cycles': [{
            'sequence': 1,
            'total_cycles': 0,
            'pricing_scheme': {
              'fixed_price': {
                'currency_code': 'EUR',
                'value': amount
              }
            }
          }]
        }
      };
      paypalOptions.createSubscription = function (data, actions) {
        return actions.subscription.create(subscription_data);
      }
      paypalOptions.onApprove = paymentSuccessful;
    } else {
      let checkout_data = {
        purchase_units: [{
          amount: {
            currency_code: "EUR",
            value: amount
          }
        }],
        intent: "CAPTURE",
        application_context: {
          shipping_preference: 'NO_SHIPPING'
        }
      };
      paypalOptions.createOrder = function (data, actions) {
        let order_url = document.querySelector("input[name=order-url]").value;
        return fetch(order_url).then(response => response.json()).then(order => order.id);
      }
    }
    paypalOptions.application_context = { shipping_preference: 'NO_SHIPPING' };
    paypalOptions.fundingSource = funding_source;
    paypalOptions.onApprove = function (data, actions) {
      let order_url = document.querySelector("input[name=order-url]").value;
      return fetch(order_url, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          orderID: data.orderID
        })
      }).then(response => response.json()).then(orderData => paymentSuccessful(orderData));
    }
    return paypalOptions;

    paypal.Buttons({
      createSubscription: function (data, actions) {
        return actions.subscription.create();
      },
      onApprove: function (data, actions) {

      }
    }).render('#paypal-buttons');
  }
  function paymentSuccessful(data) {
    console.log("success", data);
  }
  function cardSubscription() {
    let cardFields = document.createElement("div");
    cardFields.id = "card-fields";

    let number = document.createElement("input");
    number.type = "text";
    number.name = "card";
    number.id = "card-number";
    cardFields.appendChild(number);

    let expiration = document.createElement("input");
    expiration.type = "text";
    expiration.name = "expiration";
    expiration.id = "card-expiration";
    cardFields.appendChild(expiration);

    document.querySelector("#paypal-buttons").appendChild(cardFields);

    paypal.HostedFields.render({
      createOrder: () => { },
      onApprove: () => { },
      fields: {
        number: "#card-number",
        expirationDate: "#card-expiration"
      }
    });
  }
}
