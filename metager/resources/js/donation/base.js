import processPaypalCard from './paypal-card';
import { paypalOptions, funding_source } from './paypal-options';
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
  let orderID;


  if (funding_source == "card") {
    processPaypalCard();
  } else {
    let paymentFieldsContainer = document.createElement("div");
    paymentFieldsContainer.id = "payment-fields";
    document
      .querySelector("#content-container.paypal-subscription")
      .appendChild(paymentFieldsContainer);
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
        expirationDate: "#card-expiration",
      },
    });
  }
}
