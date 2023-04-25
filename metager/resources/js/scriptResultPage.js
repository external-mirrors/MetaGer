require("es6-promise").polyfill();
require("fetch-ie8");
import resultSaver from "./result-saver.js";

document.addEventListener("DOMContentLoaded", (event) => {
  if (document.readyState == "complete") {
    initialize();
  } else {
    document.addEventListener("readystatechange", (e) => {
      if (document.readyState == "complete") {
        initialize();
      }
    });
  }
});

function initialize() {
  submitFilterOnChange();
  botProtection();
  enableFormResetter();
  loadMoreResults();
  enableResultSaver();
  enablePagination();
}

let link, newtab, top;
let verifying = false;

function submitFilterOnChange() {
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
}

function botProtection() {
  document.querySelectorAll(".result a").forEach((element) => {
    element.addEventListener("click", verify_link);
  });

  document.addEventListener("pointermove", verify);
  document.addEventListener("pointerdown", verify);
  document.addEventListener("scroll", verify);
}

function verify_link(event) {
  let element = event.target;
  link = element.href;
  newtab = false;
  top = false;
  if (element.target == "_blank" || event.ctrlKey || event.metaKey) {
    newtab = true;
  } else if (element.target == "_top") {
    top = true;
  }

  let promise_fetch = verify();
  if (typeof promise_fetch !== "undefined" && link !== "#") {
    console.log(link);
    promise_fetch.then((response) => {
      if (!newtab) {
        if (top) {
          window.top.location.href = link;
        } else {
          document.location.href = link;
        }
      }
    });
  }
  return newtab;
}

function verify(event) {
  if (verifying) return;
  verifying = true;
  document.querySelectorAll(".result a").forEach((element) => {
    element.removeEventListener("click", verify_link);
  });
  document.removeEventListener("pointermove", verify);
  document.removeEventListener("pointerdown", verify);
  document.removeEventListener("scroll", verify);

  let data = "hv=" + document.querySelector('meta[name="hv"]').content;

  return fetch("/img/cat.png", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: data,
  });
}

function enableFormResetter() {
  var deleteButton = document.querySelector("#search-delete-btn");
  var timeout = null;

  if (deleteButton) {
    deleteButton.onclick = (e) => {
      if (timeout != null) {
        clearTimeout(timeout);
        timeout = null;
      }
      document.querySelector('input[name="eingabe"]').value = "";
      document.querySelector('input[name="eingabe"]').focus();
    };
  }

  let input_field = document.querySelector('input[name="eingabe"]');
  if (input_field) {
    input_field.addEventListener("focusin", (e) => {
      deleteButton.style.display = "initial";
    });
    input_field.addEventListener("focusout", (e) => {
      timeout = window.setTimeout(function () {
        deleteButton.style.display = "none";
        timeout = null;
      }, 500);
    });
  }
}

function loadMoreResults() {
  var searchKey = document.querySelector("meta[name=searchkey]").content;
  var updateUrl = document.location.href;
  updateUrl += "&loadMore=loader_" + searchKey + "&script=yes";

  updateUrl = updateUrl.replace("/meta.ger3", "/loadMore");

  if (updateUrl.match(/focus=bilder/)) {
    return;
  }

  var currentlyLoading = false;
  var counter = 0;

  var fetchResults = function () {
    if (!currentlyLoading) {
      counter++;
      if (counter >= 10) {
        clearInterval(resultLoader);
      }
      currentlyLoading = true;
      fetch(updateUrl)
        .then((response) => response.json())
        .then((data) => {
          // Check if we can clear the interval (once every searchengine has answered)
          if (!data || data.finished) {
            clearInterval(resultLoader);
          }

          if ("results" in data) {
            let container = document.createElement("div");
            container.innerHTML = data.results;
            let new_source = container.querySelector("#results").innerHTML;
            document.querySelector("#results").innerHTML = new_source;
            botProtection();
          }

          currentlyLoading = false;
        });
    }
  };

  // Regularily check for not yet delivered Results
  var resultLoader = window.setInterval(fetchResults, 1000);
  fetchResults();
}

function enableResultSaver() {
  document
    .querySelectorAll("#results .result .result-options .saver")
    .forEach((element) => {
      element.addEventListener("click", (event) => {
        console.log(event);
        let id = event.target.dataset.id;
        resultSaver(id);
      });
    });
}

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
