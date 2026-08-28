import { removeSetting } from "./messaging";
import { bindLogoutClears } from "./accountBreadcrumb";

// Forget the returning-user breadcrumb when the key is removed from here.
bindLogoutClears();

document
  .querySelectorAll(".setting-form select, .filter-form select")
  .forEach((element) => {
    element.addEventListener("change", (e) => {
      e.target.form.submit();
    });
  });

(() => {
  let removeKeyBtn = document.getElementById("remove-key");
  if (removeKeyBtn == null) return;
  removeKeyBtn.addEventListener("click", e => {
    if (document.getElementById("plugin-btn") != null) return;
    let url = new URL(e.target.href);
    e.preventDefault();
    removeSetting("key").then((answer) => {
      document.location.href = url;
    });
    return false;
  })
})();