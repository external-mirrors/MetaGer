document.addEventListener("DOMContentLoaded", (event) => {
  document
    .querySelectorAll(".js-only")
    .forEach((el) => el.classList.remove("js-only"));
  document.querySelectorAll(".no-js").forEach((el) => el.classList.add("hide"));
  document.querySelectorAll(".print-button").forEach((el) =>
    el.addEventListener("pointerdown", () => {
      window.print();
    })
  );
  document.querySelectorAll(".copyLink").forEach((el) => {
    let input_field = el.querySelector("input[type=text]");
    let copy_button = el.querySelector("button");
    if (copy_button) {
      copy_button.addEventListener("click", (e) => {
        // Select all the text
        let key = input_field.value;
        input_field.select();
        navigator.clipboard
          .writeText(key)
          .then(() => {
            copy_button.classList.add("success");
            setTimeout(() => {
              copy_button.classList.remove("success");
            }, 3000);
          })
          .catch((reason) => {
            console.error(reason);
            copy_button.classList.add("failure");
            setTimeout(() => {
              copy_button.classList.remove("failure");
            }, 3000);
          });
      });
    }
  });
});

reportJSAvailabilityForAuthenticatedSearch();
function reportJSAvailabilityForAuthenticatedSearch() {
  let Cookies = require("js-cookie");
  let key_cookie = Cookies.get("key");
  if (key_cookie !== undefined) {
    Cookies.set("js_available", "true", { sameSite: 'Lax' });
  }
}