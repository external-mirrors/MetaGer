import { statistics } from "./statistics";

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
  let key_keyup = function (e) {
    let el = e.target;
    console.log(el);
    if (el.value.match(/^\d{6}$/)) {
      let clone = el.cloneNode(true);
      clone.setAttribute("type", "text");
      clone.setAttribute("autocomplete", "one-time-code");
      el.replaceWith(clone);
      clone.addEventListener("keyup", key_keyup);
      clone.addEventListener("focus", key_focus);
      clone.addEventListener("blur", key_blur);
      clone.focus();
      clone.setSelectionRange(clone.value.length, clone.value.length);
    } else {
      let clone = el.cloneNode(true);
      clone.setAttribute("type", "password");
      clone.removeAttribute("autocomplete");
      el.replaceWith(clone);
      clone.addEventListener("keyup", key_keyup);
      clone.addEventListener("focus", key_focus);
      clone.addEventListener("blur", key_blur);
      clone.focus();
      clone.setSelectionRange(clone.value.length, clone.value.length);
    }
  };
  let key_focus = function (e) {
    e.target.type = "text";
  };
  let key_blur = function (e) {
    if (!e.target.value.match(/^\d{6}$/)) {
      e.target.type = "password";
    }
  };
  document.querySelectorAll("input[type=password][name=key]").forEach((el) => {
    el.addEventListener("keyup", key_keyup);
    el.addEventListener("focus", key_focus);
    el.addEventListener("blur", key_blur);
    el.form.addEventListener("submit", (e) => {
      if (!el.value.match(/^\d{6}$/)) {
        el.type = "password";
      }
    });
    let error_key = new URLSearchParams(document.location.search).get(
      "invalid_key"
    );
    if (error_key != null && error_key.length > 0) {
      el.dispatchEvent(new Event("keyup"));
    }
  });

  let sidebarToggle = document.getElementById("sidebarToggle");
  if (sidebarToggle) {
    document.querySelectorAll("label[for=sidebarToggle]").forEach((label) => {
      label.addEventListener("click", (e) => {
        e.preventDefault();
        sidebarToggle.checked = !sidebarToggle.checked;
      });
    });
  }

  // Add a element with the ID "plugin-btn" if it does not exist on this page
  // Used to determine if a web extension is installed
  if (document.getElementById("plugin-btn") == null) {
    let new_container = document.createElement("div");
    new_container.classList.add("hidden");
    new_container.id = "plugin-btn";
    document.querySelector("body").appendChild(new_container);
  }

  backButtons();
});

reportJSAvailabilityForAuthenticatedSearch();
function reportJSAvailabilityForAuthenticatedSearch() {
  let Cookies = require("js-cookie");
  let key_cookie = Cookies.get("key");

  if (key_cookie !== undefined) {
    Cookies.set("js_available", "true", { sameSite: "Lax" });
  }
}

// Implement Back button functionality
function backButtons() {
  document.querySelectorAll(".back-button").forEach((button) => {
    button.style.display = "block";
    button.addEventListener("click", (e) => {
      let href = button.href;
      // Use the defined URL on the button if there is one
      if (href && href.trim().length !== 0 && href.trim() != "#") {
        return;
      }
      e.preventDefault();
      history.back();
    });
  });
}

(async () => {
  statistics.registerPageLoadEvents();
})();

document.addEventListener("readystatechange", (e) => {
  if (document.readyState == "complete") {
    setTimeout(() => {
      // Check if a web extension is active
      let extension_installed = document.getElementById("plugin-btn") == null;
      if (extension_installed) {
        updateWebExtensionStatus(new Date().getTime());
      } else {
        updateWebExtensionStatus("no");
      }
    }, 250);
  }
});

function updateWebExtensionStatus(time) {
  let Cookies = require("js-cookie");
  let extension_cookie = Cookies.get("webextension");
  if (time != "no" && extension_cookie == undefined) {
    Cookies.set("webextension", time, { sameSite: "Lax" });
  } else if (time == "no" && extension_cookie != undefined) {
    Cookies.remove("webextension");
  }
  if (localStorage) {
    localStorage.setItem("webextension", time);
  }
  window.webextension = time;
  window.dispatchEvent(
    new CustomEvent("webextension_status_update", { detail: time })
  );
}
