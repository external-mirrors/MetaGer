// Add event when custom amount is selected to focus the input field
document.querySelector("#amount-custom").addEventListener("change", (e) => {
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
    console.log("change");
    updateIBANRequired();
  });
});

function updateIBANRequired() {
  let required =
    document.querySelector("#payment-method-directdebit").checked == true;
  console.log(required);
  let iban_input = document.querySelector("#iban");
  if (required) {
    iban_input.setAttribute("required", required);
  } else {
    iban_input.removeAttribute("required");
  }
}

if (navigator.webdriver) {
  let token_container = document.querySelector('input[name="_token}"]');
  if (token_container && navigator.webdriver) {
    token_container.value = "";
  }
}
