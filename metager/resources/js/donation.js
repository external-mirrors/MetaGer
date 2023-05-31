let customAmountSwitch = document.querySelector("#content-container.amount #custom-amount-switch");
if (customAmountSwitch) {
    customAmountSwitch.addEventListener("change", e => {
        if (e.target.checked) {
            let customAmountInput = document.querySelector("#content-container.amount #amount");
            if (customAmountInput) {
                customAmountInput.focus();
            }
        }
    });
}