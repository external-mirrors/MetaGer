require("es6-promise").polyfill();
require("fetch-ie8");
import resultSaver from "./result-saver.js";

let mutationCheckerInterval;

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
  initQueryInputField();
  submitFilterOnChange();
  botProtection();
  loadMoreResults();
  enableResultSaver();
  enablePagination();
  mutationCheckerInterval = setInterval(mutationChecker, 2000);
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
            if (!mutationCheckerInterval) {
              mutationCheckerInterval = setInterval(mutationChecker, 2000);
            }
          }

          if ("quicktips" in data) {
            let container = document.createElement("div");
            container.innerHTML = data.quicktips;
            let new_quicktips = container.querySelector("#additions-container");
            document.getElementById("resultpage-container").append(new_quicktips);
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

function mutationChecker() {
  let validAttributes = ["class", "id", "data-count", "data-index"];
  let elements = document.querySelectorAll("#results > .result");
  let attributeRemoved = false;
  for (let i = 0; i < elements.length; i++) {
    for (let j = 0; j < elements[i].attributes.length; j++) {
      if (!validAttributes.includes(elements[i].attributes[j].name)) {
        elements[i].attributes.removeNamedItem(elements[i].attributes[j].name);
        attributeRemoved = true;
      }
    }
  }
  if (!attributeRemoved && mutationCheckerInterval) {
    clearInterval(mutationCheckerInterval);
    mutationCheckerInterval = null;
  }
}

function initQueryInputField() {
  document.querySelector(".search-input").classList.remove("search-delete-js-only");
  let field = document.querySelector("input[name=eingabe]");
  let value = field.value;
  field.attributes.removeNamedItem("value");
  field.value = value;
}