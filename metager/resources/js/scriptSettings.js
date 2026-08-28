import { bindLogoutClears } from "./accountBreadcrumb";

// The settings page has no account block of its own any more — the pill and the
// site menu carry it on every page, including this one. What is left here is the
// sidebar's logout link, which still has to forget the returning-user
// breadcrumb.
bindLogoutClears();

document
  .querySelectorAll(".setting-form select, .filter-form select")
  .forEach((element) => {
    element.addEventListener("change", (e) => {
      e.target.form.submit();
    });
  });
