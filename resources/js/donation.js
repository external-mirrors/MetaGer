document.addEventListener("DOMContentLoaded", (event) => {
    document.querySelectorAll(".amount-custom").forEach(element => {
        element.onclick = (e) => {
            setTimeout(() => {
                document.querySelector("#custom-amount").focus();
            }, 100)
            console.log("test");
        }
    });
});