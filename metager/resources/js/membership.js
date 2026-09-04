// Add event when custom amount is selected to focus the input field
document.querySelector("#amount-custom")?.addEventListener("change", (e) => {
  if (!e.target.checked) {
    return;
  }
  document.querySelector("#amount-custom-value").select();
});

// Mark IBAN field required when payment method is selected
document.addEventListener("DOMContentLoaded", (e) => {
  updateIBANRequired();
});
document.querySelectorAll("input[name=payment-method]").forEach((input) => {
  input.addEventListener("change", (e) => {
    updateIBANRequired();
  });
});

(async () => {
  validateAmount();
  document.querySelector("#amount-custom-value")?.addEventListener("change", validateAmount);
  document.querySelectorAll('input[type=radio][name=amount]').forEach(element => {
    element.addEventListener("change", e => {
      // Der Nachweis-Kasten steht nur im Formular einer natürlichen Person:
      // eine Firma hat keinen ermäßigten Beitrag, sondern einen eigenen
      // Mindestbeitrag. Ohne das Fragezeichen war der Beitragsschritt einer
      // Firma nach dem ersten Klick tot.
      document.querySelector("#reduction-container")?.classList.add("hidden");
    });
  });
})();

/**
 * Der Mindestbeitrag dieses Formulars.
 *
 * Er hängt an der Art der Mitgliedschaft — 2,50 € für eine Person, deutlich
 * mehr für eine Firma — und wird deshalb vom Blade aus App\Support\MembershipFee
 * mitgegeben statt hier verdrahtet. Ohne das Attribut bleibt es bei 2,50, dem
 * niedrigsten Wert, den die Validierung kennt: raten soll das Skript nicht.
 */
function minimumAmount() {
  let container = document.querySelector("#membership-fee");
  let minimum = parseFloat(container?.dataset.minAmount);
  return isNaN(minimum) ? 2.5 : minimum;
}

function validateAmount() {
  let amount_element = document.querySelector("input[name=amount]:checked");
  if (amount_element == null) return;
  let custom_amount_element = document.querySelector("#amount-custom-value");
  let value = amount_element.value;

  if (value == "custom") {
    value = parseFloat(custom_amount_element.value);
  } else {
    value = parseFloat(value);
  }
  if (isNaN(value)) {
    document.querySelector("#reduction-container")?.classList.add("hidden");
    custom_amount_element.value = "";
    return
  };

  if (custom_amount_element.value != value) {
    custom_amount_element.value = value;
  }

  let minimum = minimumAmount();
  if (value < minimum) {
    custom_amount_element.value = minimum;
  }

  if (document.querySelector("input[name=type]").value == "company") return;

  if (value < 5) {
    document.querySelector("#reduction-container")?.classList.remove("hidden");
  } else {
    document.querySelector("#reduction-container")?.classList.add("hidden");
  }
}

function updateIBANRequired() {
  let required =
    document.querySelector("#payment-method-directdebit")?.checked == true;
  let iban_input = document.querySelector("#iban");
  if (required) {
    iban_input?.setAttribute("required", required);
  } else {
    iban_input?.removeAttribute("required");
  }
}

if (navigator.webdriver) {
  let token_container = document.querySelector('input[name="_token}"]');
  if (token_container && navigator.webdriver) {
    token_container.value = "";
  }
}
