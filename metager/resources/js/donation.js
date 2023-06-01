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
    if (mark.isEligible()) {
      let paymentMethodLinkContainer = document.createElement("li");
      paymentMethodLinkContainer.classList.add("payment-method");
      paymentMethodLinkContainer.id = fundingSource;
      document
        .getElementById("payment-methods")
        .appendChild(paymentMethodLinkContainer);
      mark.render("#" + fundingSource);
      console.log(fundingSource);
    }
  });
}
