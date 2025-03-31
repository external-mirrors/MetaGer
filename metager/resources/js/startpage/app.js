import "./tiles";
import { initializeSuggestions } from "../suggest";

// Register Keyboard listener for quicklinks on startpage
(async () => {
  let sidebar_toggle = document.querySelector("#sidebarToggle");
  let skip_links_container = document.querySelector(".skiplinks");

  document.addEventListener("keyup", (e) => {
    if (e.key == "Escape") {
      // Disable sidebar if opened
      if (sidebar_toggle && sidebar_toggle.checked) {
        sidebar_toggle.checked = false;
      }
      if (skip_links_container.contains(document.activeElement)) {
        document.activeElement.blur();
      } else {
        document.querySelector(".skiplinks > a").focus();
      }
    }
  });
  skip_links_container.querySelector(".escape").classList.add("hidden");
  document.addEventListener(
    "keydown",
    (e) => {
      if (e.key == "Escape" || e.key == "Tab") {
        skip_links_container
          .querySelector(".escape")
          .classList.remove("hidden");
      }
    },
    { once: true }
  );

  initializeSuggestions();
})();