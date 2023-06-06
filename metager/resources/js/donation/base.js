import processPaypalCard from './paypal-card';
import { processPaypalSubscription } from './paypal-subscription';
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
      let invertLightImages = ["p24", "applepay", "bancontact", "boleto", "eps", "mercadopago", "multibanco", "oxxo", "paidy", "satispay"];
      if (invertLightImages.includes(fundingSource)) {
        imagetag.classList.add("invert-light");
      }
      document
        .querySelector("#payment-methods")
        .appendChild(paymentMethodContainer);
    }
  });
}

if (document.querySelector("#content-container.paypal")) {
  let interval = document.querySelector("#content-container.paypal input[name=interval]").value;
  if (interval == "once") {
    if (funding_source == "card") {
      processPaypalCard();
    } else {
      if (funding_source != "paypal") {
        let paymentFieldsContainer = document.createElement("div");
        paymentFieldsContainer.id = "payment-fields";
        document
          .querySelector("#content-container.paypal")
          .appendChild(paymentFieldsContainer);

        paypal
          .PaymentFields({
            fundingSource: funding_source,
            styles: {
              base: {
                color: "white",
              }
            },
            fields: {},
          })
          .render("#payment-fields");
      }
      let paymentButtonContainer = document.createElement("div");
      paymentButtonContainer.id = "payment-button";

      document
        .querySelector("#content-container.paypal")
        .appendChild(paymentButtonContainer);
      paypal.Buttons(paypalOptions()).render("#payment-button");
    }
  } else {
    processPaypalSubscription();
  }
}
