document.addEventListener("DOMContentLoaded", (event) => {
    document.querySelectorAll(".amount-custom").forEach(element => {
        element.onclick = (e) => {
            setTimeout(() => {
                document.querySelector("#custom-amount").focus();
            }, 100);
        }
    });
    updateRequiredForName();
    document.querySelector("#donate-button").removeAttribute("disabled");
    document.querySelectorAll("input[name=person]").forEach(element => {
        element.onclick = (e) => {
            updateRequiredForName();
        }
        element.onchange = (e) => {
            updateRequiredForName();
        }
    });
});

function updateRequiredForName() {
    let privateCheckbox = document.querySelector("#private");
    let companyCheckbox = document.querySelector("#company");
    let firstname = document.querySelector("#firstname");
    let lastname = document.querySelector("#lastname");
    let companyname = document.querySelector("#companyname");
    if(!privateCheckbox || !companyCheckbox || !firstname || !lastname || !companyname){
        return;
    }
    if(privateCheckbox.checked){
        firstname.required = "required";
        lastname.required = "required";
        companyname.removeAttribute("required");
    }
    if(companyCheckbox.checked){
        companyname.required = "required";
        firstname.removeAttribute("required");
        lastname.removeAttribute("required");
    }
}