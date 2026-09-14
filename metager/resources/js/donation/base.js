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

// Funding-source picking (wallet vs. giropay/sofort/ideal/etc., or which card
// brand) no longer happens on this page at all — suma-payments' own hosted
// checkout page offers that breadth now for every method except banktransfer,
// which needs no name at all. This page only carries the donor's name
// (collected once, upfront, for a recurring donation) onto whichever tile
// they click.
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
