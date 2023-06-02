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
  let base_url = document.querySelector("input[name=baseurl]").value;
  paypal.getFundingSources().forEach(function (fundingSource) {
    let mark = paypal.Marks({ fundingSource: fundingSource });
    console.log(fundingSource);
    if (
      mark.isEligible() &&
      fundingSource !== "card" &&
      fundingSource !== "sepa"
    ) {
      let paymentMethodContainer = document.createElement("li");
      paymentMethodContainer.classList.add("paypal");
      let atag = document.createElement("a");
      atag.href = `${base_url}/${fundingSource}`;
      paymentMethodContainer.appendChild(atag);
      let imagecontainer = document.createElement("div");
      imagecontainer.classList.add("image");
      atag.appendChild(imagecontainer);
      let imagetag = document.createElement("img");
      imagetag.setAttribute("src", `/img/funding_source/${fundingSource}.svg`);
      imagecontainer.appendChild(imagetag);
      document
        .querySelector("#payment-methods")
        .appendChild(paymentMethodContainer);
    }
  });
}

if (document.querySelector("#content-container.paypal-subscription")) {
  let funding_source = document.querySelector(
    "input[name=funding_source]"
  ).value;
  let paymentFieldsContainer = document.createElement("div");
  paymentFieldsContainer.id = "payment-fields";
  document
    .querySelector("#content-container.paypal-subscription")
    .appendChild(paymentFieldsContainer);
  if (funding_source == "card") {
    // Create Creditcard fields
    let card_form = document
      .getElementById("card-form-skeleton")
      .cloneNode(true);
    card_form.id = "card-form";
    card_form.classList.remove("hidden");
    document.getElementById("card-form-skeleton").remove();
    paymentFieldsContainer.appendChild(card_form);

    let paypal_options = paypalOptions();
    let fontColor = window.getComputedStyle(
      document.querySelector("body")
    ).color;
    console.log(fontColor);
    paypal.HostedFields.render({
      createOrder: paypal_options.createOrder,
      fields: {
        number: {
          selector: "#card-number",
          placeholder: "4111 1111 1111 1111",
        },
        cvv: {
          selector: "#card-cvv",
          placeholder: "123",
        },
        expirationDate: {
          selector: "#card-expiration",
          placeholder: "MM/YY",
        },
      },
      styles: {
        input: {
          color: fontColor,
          padding: "0.5rem 1rem",
          fontSize: "1.2rem",
        },
      },
    }).then((cardFields) => {
      document
        .getElementById("card-form")
        .addEventListener("submit", (event) => {
          event.preventDefault();
          cardFields.submit().then(() => {});
        });
    });
  } else {
    let paymentButtonContainer = document.createElement("div");
    paymentButtonContainer.id = "payment-button";

    document
      .querySelector("#content-container.paypal-subscription")
      .appendChild(paymentButtonContainer);
    paypal
      .PaymentFields({
        fundingSource: funding_source,
        style: {
          textColor: "white",
          base: {
            backgroundColor: "white",
            textColor: "white",
            color: "white",
          },
          input: {
            backgroundColor: "white",
          },
        },
        fields: {},
      })
      .render("#payment-fields");
    paypal.Buttons(paypalOptions()).render("#payment-button");
  }

  function paypalOptions() {
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
          .then((order) => order.id);
      };
      paypalOptions.onApprove = function (data, actions) {
        let order_url = document.querySelector("input[name=order-url]").value;
        return fetch(order_url, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({
            orderID: data.orderID,
          }),
        })
          .then((response) => response.json())
          .then((orderData) => paymentSuccessful(orderData));
      };
    }
    paypalOptions.application_context = { shipping_preference: "NO_SHIPPING" };
    paypalOptions.fundingSource = funding_source;

    return paypalOptions;
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
      createOrder: () => {},
      onApprove: () => {},
      fields: {
        number: "#card-number",
        expirationDate: "#card-expiration",
      },
    });
  }
}
