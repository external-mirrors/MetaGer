import { initializeSuggestions } from "./suggest";
import updateProxyLinks from "./resultpage/proxy";
import { renderRelativeDates } from "./relative-time";
import { initAccountBreadcrumb } from "./accountBreadcrumb";
import { setMenuOpen } from "./sidebar/menu-toggle";
import { patchResults, rememberServerMarkup } from "./resultpage/patch";
import { nextPollDelay } from "./resultpage/loader";

let bootEvent = new Event("boot");
let resultLoaderEvent = new Event("resultsChanged");

function initialize() {
  initSelectTier();
  initQueryInputField();
  loadMoreResults();
  enableResultSaver();
  enablePagination();
  enableABHints();

  updateProxyLinks();
}

(() => {
  document.addEventListener("boot", () => initAccountBreadcrumb());
})();

// Submit search form when filters change
(() => {
  document.addEventListener("boot", (e) => {
    // All normal select fields
    document
      .querySelectorAll("#options #options-box select")
      .forEach((value, index) => {
        value.addEventListener("change", (e) => e.target.form.submit());
      });
    // Custom date picker
    let custom_date_picker_element = document.querySelector(
      "#options #options-box input[name=fc]"
    );
    if (custom_date_picker_element) {
      custom_date_picker_element.addEventListener("change", (e) => {
        if (!e.target.checked) {
          e.target.form.submit();
        }
      });
    }
    // Custom date selected
    document
      .querySelectorAll(
        "#options #options-box input[name=ff], #options #options-box input[name=ft]"
      )
      .forEach((value, index) => {
        value.addEventListener("change", (e) => {
          let ff_value = document.querySelector(
            "#options #options-box input[name=ff]"
          ).value;
          let ft_value = document.querySelector(
            "#options #options-box input[name=ft]"
          ).value;
          if (ff_value != "" && ft_value != "") {
            e.target.form.submit();
          }
        });
      });
  });
})();

(() => {
  // Before "boot", which is what starts reformatting dates and proxy links in the list; see
  // resultpage/patch.js for why the server's own markup is what later answers are compared with.
  const resultList = document.querySelector("#results");
  if (resultList) {
    rememberServerMarkup(resultList);
  }

  let resultLoaderStarted = false;
  document.addEventListener("boot", loadMoreResults);
  function loadMoreResults() {
    if (resultLoaderStarted) {
      return;
    }
    resultLoaderStarted = true;

    var searchKey = document.querySelector("meta[name=searchkey]").content;
    var updateUrl = document.location.href;
    updateUrl += "&loadMore=loader_" + searchKey + "&script=yes";

    updateUrl = updateUrl.replace("/meta.ger3", "/loadMore");

    if (updateUrl.match(/focus=bilder/)) {
      return;
    }

    let polls = 0;
    const poll = () => {
      fetch(updateUrl)
        .then((response) => response.json())
        .then((data) => {
          polls++;
          showLoadedResults(data);
          const delay = nextPollDelay(polls, data);
          if (delay !== null) {
            setTimeout(poll, delay);
          }
        })
        // A failed poll ends loading; the page already shows what it has.
        .catch(() => {});
    };
    poll();
  }

  function showLoadedResults(data) {
    // Absent when nothing changed since the last poll, which is most of them.
    if ("results" in data) {
      let container = document.createElement("div");
      container.innerHTML = data.results;
      let rendered = container.querySelector("#results");
      let list = document.querySelector("#results");
      if (rendered && list) {
        patchResults(list, rendered.innerHTML);
        document.dispatchEvent(resultLoaderEvent);
      }

      // Remove no results error if results got loaded after the fact by Javascript
      let results = document.querySelectorAll("#results > .result");
      let no_results_error = document.querySelector(".alert .no-results-error");
      if (results.length > 0 && no_results_error !== null) {
        no_results_error.remove();
      }
    }

    if ("quicktips" in data && data.quicktips !== "") {
      let container = document.createElement("div");
      container.innerHTML = data.quicktips;
      let new_quicktips = container.querySelector(
        "#additions-container"
      );
      document
        .getElementById("resultpage-container")
        .append(new_quicktips);
    }

    updateProxyLinks();
  }
})();

// Pagination
(() => {
  document.addEventListener("boot", enablePagination);
  function enablePagination() {
    let last_search_link = document.querySelector(
      "#last-search-link:not(.disabled) > a"
    );
    if (last_search_link) {
      last_search_link.addEventListener("pointerdown", (e) => {
        history.back();
      });
    }
  }
})();

(() => {
  document.addEventListener("boot", initQueryInputField);
  function initQueryInputField() {
    document
      .querySelector(".search-input")
      .classList.remove("search-delete-js-only");
    let field = document.querySelector("input[name=eingabe]");
    let old_value = null;
    let delete_button = document.querySelector("#search-delete-btn");
    delete_button.addEventListener("mousedown", (e) => {
      e.preventDefault();
      old_value = field.value;
      field.value = "";
      return false;
    });
    field.addEventListener("focusout", (e) => {
      if (old_value != null && field.value.length == 0) {
        field.value = old_value;
        old_value = null;
      }
    });
  }
})();

(() => {
  document.addEventListener("boot", enableABHints);
  document.addEventListener("resultsChanged", enableABHints);
  function enableABHints() {
    setTimeout(() => {
      document.querySelectorAll("#results > .ab-hint").forEach((element) => {
        let target = element.dataset.target;
        let targetContainer = document.querySelector(
          '#results > .result[data-index="' + target + '"]'
        );
        // Element is hidden by display value if `offsetParent` is null
        // according to https://developer.mozilla.org/en-US/docs/Web/API/HTMLElement/offsetParent
        if (!targetContainer || targetContainer.offsetParent == null) {
          element.style.display = "block";
        }
      });
    }, 1000);
  }
})();

/*
 * The Yahoo `selectTier` advertising loader used to live here, guarded by
 * "return if #key-link is authorized".
 *
 * It could not run. Every route to the result page carries
 * App\Http\Middleware\AuthenticationValidation (routes/web.php), and every
 * unauthorised branch of it — the key guard's and the legacy Authorization
 * one's — returns redirect(route("startpage")). A result page is therefore only
 * ever rendered for an authorised search, the guard was always true, and the
 * loader below it was dead. It was also the only reader of #key-link, which is
 * why the search bar's key markup counted as untouchable; both are gone now.
 *
 * Removed together with #search-key, its icon and its three LESS blocks.
 * tests/Feature/Search/AdvertisingRemovedTest and the asset test keep it gone.
 */

(() => {
  document.addEventListener("boot", formatDates);
  document.addEventListener("resultsChanged", formatDates);

  function formatDates() {
    renderRelativeDates();
  }
})();

(() => {
  let sidebar_toggle = document.querySelector("#sidebarToggle");
  let skip_links = document.querySelector(".skiplinks");

  document.addEventListener("boot", () => {
    document.addEventListener("keyup", (e) => {
      if (e.key == "Escape") {
        // Disable sidebar if opened
        if (sidebar_toggle && sidebar_toggle.checked) {
          setMenuOpen(sidebar_toggle, false);
        }
        if (skip_links.contains(document.activeElement)) {
          document.activeElement.blur();
        } else {
          document.querySelector(".skiplinks > a").focus();
        }
      }
    });
    skip_links.querySelector(".escape").classList.add("hidden");
    document.addEventListener(
      "keydown",
      (e) => {
        if (e.key == "Escape" || e.key == "Tab") {
          skip_links.querySelector(".escape").classList.remove("hidden");
        }
      },
      { once: true }
    );
  });
})();

(() => {
  initializeSuggestions();
})();

if (document.readyState == "loading") {
  document.addEventListener("DOMContentLoaded", (e) => {
    document.dispatchEvent(bootEvent);
  });
} else {
  document.dispatchEvent(bootEvent);
}
