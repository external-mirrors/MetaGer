var expirationTimer = window.setInterval(expireQueries, 1000);
var expiration_enabled = true;

var queryLoader = window.setInterval(loadQueries, 60000);

document.getElementById("regexp").oninput = checkRegexp;
document.getElementById("check-against").oninput = checkRegexp;
window.addEventListener("load", function () {
  checkRegexp();
});

document.querySelector("#head > button").addEventListener("click", function () {
  let paused = this.classList.toggle("paused");
  if (paused) {
    window.clearInterval(expirationTimer);
    this.classList.add("btn-danger");
    this.classList.remove("btn-success");
  } else {
    expirationTimer = window.setInterval(expireQueries, 1000);
    this.classList.add("btn-success");
    this.classList.remove("btn-danger");
  }
});

function expireQueries() {
  let now = Math.round(Date.now() / 1000);
  let queries = document.querySelectorAll("#queries tbody tr");
  queries.forEach((query, index) => {
    let expiration = query.dataset.expiration;
    if (now > expiration) {
      query.remove();
    }
  });
}

function loadQueries() {
  let latest_update = document.getElementById("queries").dataset.latest;
  let base_url = document.getElementById("queries").dataset.api;

  let url = base_url + "?since=" + encodeURI(latest_update);

  fetch(url, { redirect: "error" })
    .then((response) => response.json())
    .then((data) => {
      let latest = data.latest;
      document.getElementById("queries").dataset.latest = latest;
      let queries = data.queries;

      // Check if dark or not
      let current_queries = document.querySelectorAll("#queries tbody tr");
      let dark =
        current_queries.length > 0
          ? !current_queries[current_queries.length - 1].classList.contains(
            "dark"
          )
          : false;
      for (let key in queries) {
        let tr_element = document.createElement("tr");
        tr_element.dataset.expiration = queries[key].expiration_timestamp;
        if (dark) {
          tr_element.classList.add("dark");
          dark = false;
        } else {
          dark = true;
        }

        let time_element = document.createElement("td");
        time_element.innerHTML = queries[key].time_string;
        tr_element.append(time_element);

        let referer_element = document.createElement("td");
        referer_element.classList.add("referer");
        referer_element.title = queries[key].referer;
        referer_element.innerHTML = queries[key].referer;
        tr_element.append(referer_element);

        let request_time_element = document.createElement("td");
        request_time_element.innerHTML = queries[key].request_time;
        tr_element.append(request_time_element);

        let focus_element = document.createElement("td");
        focus_element.innerHTML = queries[key].focus;
        tr_element.append(focus_element);

        let interface_element = document.createElement("td");
        interface_element.innerHTML = queries[key].locale;
        tr_element.append(interface_element);

        let query_element = document.createElement("td");
        query_element.innerHTML = queries[key].query;
        tr_element.append(query_element);

        document.querySelector("#queries tbody").append(tr_element);
      }

      checkRegexp();
    })
    .catch((reason) => {
      // We are not logged in anymore
      history.go();
    });
}

function checkRegexp() {
  let banRegexps = [];

  document.querySelectorAll("#loadedbans tbody td").forEach((value, index) => {
    banRegexps.push(value.innerHTML);
  });
  if (document.getElementById("regexp").value.length > 0)
    banRegexps.push(document.getElementById("regexp").value);

  if (banRegexps.length == 0) return;

  let elements = document.querySelectorAll("#queries tbody tr");
  elements.forEach((query, index) => {
    let query_params = query.querySelectorAll("td");
    let query_string = query_params[query_params.length - 1].innerHTML;

    let matches = false;

    banRegexps.forEach((regexp) => {
      if (query_string.match(regexp)) {
        matches = true;
        return false;
      }
    });
    if (matches) {
      query.classList.add("matches");
    } else {
      query.classList.remove("matches");
    }
  });
}
