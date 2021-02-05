document.addEventListener("DOMContentLoaded", (event) => {
  document.querySelectorAll(".js-only").forEach(el => el.classList.remove("js-only"));
  document.querySelectorAll(".no-js").forEach(el => el.classList.add("hide"));
});