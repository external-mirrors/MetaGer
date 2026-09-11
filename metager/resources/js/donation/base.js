import processPaypalCard from "./paypal-card";
import { processPaypalSubscription } from "./paypal-subscription";
import { paypalOptions, funding_source } from "./paypal-options";
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

// Funding-source picking (wallet vs. giropay/sofort/ideal/etc.) no longer
// happens on this page at all — suma-payments' own hosted PayPal checkout
// page offers that breadth now. This page only carries the donor's name
// (collected once, upfront, for a recurring donation) onto whichever of the
// directdebit/paypal tiles they click — banktransfer and card are untouched.
let donorNameInput = document.querySelector(
  "#content-container.paymentMethod #donor-name"
);
if (donorNameInput) {
  let nameCarryingLinks = document.querySelectorAll(
    "#payment-methods a[data-carries-name]"
  );
  let updateLinks = () => {
    let name = donorNameInput.value.trim();
    nameCarryingLinks.forEach((a) => {
      let url = new URL(a.href, window.location.origin);
      if (name !== "") {
        url.searchParams.set("name", name);
      } else {
        url.searchParams.delete("name");
      }
      a.href = url.toString();
    });
  };
  donorNameInput.addEventListener("input", updateLinks);
  nameCarryingLinks.forEach((a) => {
    a.addEventListener("click", (e) => {
      if (donorNameInput.value.trim() === "") {
        e.preventDefault();
        donorNameInput.reportValidity();
      }
    });
  });
}

if (
  document.querySelector("#content-container.paypal") &&
  !navigator.webdriver
) {
  let interval = document.querySelector(
    "#content-container.paypal input[name=interval]"
  ).value;
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
              },
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
