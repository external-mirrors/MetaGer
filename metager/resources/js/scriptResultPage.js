require('es6-promise').polyfill();
require('fetch-ie8');
import resultSaver from './result-saver.js';

document.addEventListener("DOMContentLoaded", (event) => {
  if (document.readyState == 'complete') {
    initialize();
  } else {
    document.addEventListener("readystatechange", e => {
      if (document.readyState == 'complete') {
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
}


let link, newtab, top;
let verifying = false;

function submitFilterOnChange() {
  // All normal select fields
  document.querySelectorAll("#options #options-box select").forEach((value, index) => {
    value.addEventListener("change", e => e.target.form.submit());
  });
  // Custom date picker
  document.querySelector("#options #options-box input[name=fc]").addEventListener("change", e => {
    if (!e.target.checked) {
      e.target.form.submit();
    }
  })
  // Custom date selected
  document.querySelectorAll("#options #options-box input[name=ff], #options #options-box input[name=ft]").forEach((value, index) => {
    value.addEventListener("change", e => {
      let ff_value = document.querySelector("#options #options-box input[name=ff]").value;
      let ft_value = document.querySelector("#options #options-box input[name=ft]").value;
      if (ff_value != '' && ft_value != '') {
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
  if (element.target == '_blank' || event.ctrlKey || event.metaKey) {
    newtab = true;
  } else if (element.target == "_top") {
    top = true;
  }


  let promise_fetch = verify();
  if (typeof promise_fetch !== "undefined" && link !== "#") {
    console.log(link);
    promise_fetch.then(response => {
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
};

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
    body: data
  });
}

function enableFormResetter() {
  var deleteButton = document.querySelector("#search-delete-btn");
  var timeout = null;

  deleteButton.onclick = (e) => {
    if (timeout != null) {
      clearTimeout(timeout);
      timeout = null;
    }
    document.querySelector("input[name=\"eingabe\"]").value = "";
    document.querySelector("input[name=\"eingabe\"]").focus();
  };

  document.querySelector("input[name=\"eingabe\"]").addEventListener("focusin", (e) => {
    deleteButton.style.display = "initial";
  });

  document.querySelector("input[name=\"eingabe\"]").addEventListener("focusout", (e) => {
    timeout = window.setTimeout(function () {
      deleteButton.style.display = "none";
      timeout = null;
    }, 500);
  });
}

function loadMoreResults() {
  var searchKey = document.querySelector("meta[name=searchkey]").content
  var updateUrl = document.location.href;
  updateUrl += "&loadMore=loader_" + searchKey + "&script=yes";

  updateUrl = updateUrl.replace("/meta.ger3", "/loadMore");

  var currentlyLoading = false;
  var counter = 0;
  // Regularily check for not yet delivered Results
  var resultLoader = window.setInterval(function () {
    if (!currentlyLoading) {
      counter++;
      if (counter >= 10) {
        clearInterval(resultLoader);
      }
      currentlyLoading = true;
      fetch(updateUrl)
        .then(response => response.json())
        .then(data => {
          // Check if we can clear the interval (once every searchengine has answered)
          if (!data || data.finished) {
            clearInterval(resultLoader);
          }

          if (typeof data.changedResults != "undefined") {
            for (var key in data.changedResults) {
              var value = data.changedResults[key];
              // If there are more results than the given index we will prepend otherwise we will append the result
              if (!data.imagesearch) {
                var results = document.querySelectorAll(".result:not(.ad)");
                var replacement = document.createElement("div");
                replacement.innerHTML = value.trim();
                results[key].parentNode.replaceChild(replacement.firstChild, results[key]);
              } else {
                var results = document.querySelectorAll(".image-container > .image");
                var replacement = document.createElement("div");
                replacement.innerHTML = value.trim();
                results[key].parentNode.replaceChild(replacement.firstChild, results[key]);
              }
            }
            botProtection();
          }

          // If there are new results we can add them
          if (typeof data.newResults != "undefined") {
            for (var key in data.newResults) {
              var value = data.newResults[key];

              // If there are more results than the given index we will prepend otherwise we will append the result
              if (!data.imagesearch) {
                var resultContainer = document.querySelector("#results");
                var results = document.querySelectorAll(".result:not(.ad)");
                var replacement = document.createElement("div");
                replacement.innerHTML = value.trim();
                if (key == 0) {
                  resultContainer.insertBefore(replacement.firstChild, results[0]);
                } else if (typeof results[key] != "undefined") {
                  resultContainer.insertBefore(replacement.firstChild, results[key]);
                } else if (typeof results[key - 1] != "undefined") {
                  resultContainer.appendChild(replacement.firstChild);
                }
              } else {
                var resultContainer = document.querySelector("#results");
                var results = document.querySelectorAll(".image-container > .image");
                var replacement = document.createElement("div");
                replacement.innerHTML = value.trim();
                if (key == 0) {
                  resultContainer.insertBefore(replacement.firstChild, results[0]);
                } else if (typeof results[key] != "undefined") {
                  resultContainer.insertBefore(replacement.firstChild, results[key]);
                } else if (typeof results[key - 1] != "undefined") {
                  resultContainer.appendChild(replacement.firstChild);
                }
              }
            }
            botProtection();
            if (document.querySelectorAll(".no-results-error").length > 0 && (document.querySelectorAll(".image-container > .image").length > 0) || document.querySelectorAll(".result:not(.ad)").length > 0) {
              document.querySelectorAll(".no-results-error").forEach(element => {
                element.remove();
              });
              if (document.querySelector(".alert.alert-danger > ul") != null && document.querySelector(".alert.alert-danger > ul").children().length == 0) {
                document.querySelectorAll(".alert.alert-danger").forEach(element => {
                  element.remove();
                });
              }
            }
          }

          currentlyLoading = false;
        });
    }
  }, 1000);
}

function enableResultSaver() {
  document.querySelectorAll("#results .result .result-options .saver").forEach(element => {
    element.addEventListener("click", event => {
      console.log(event);
      let id = event.target.dataset.id;
      resultSaver(id);
    });
  });
}