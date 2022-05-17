let parallel_fetches = 8;

let totals = [];

document.addEventListener("DOMContentLoaded", () => {
    load();
});

function load() {
    let parallel = Math.floor(parallel_fetches / 2)

    let fetches = [];
    fetches = fetches.concat(loadSameTimes(parallel));
    fetches = fetches.concat(loadTotals(parallel));
    if (fetches.length > 0) {
        let allData = Promise.all(fetches)
        allData.then((res) => {
            load();
        });
    } else {
        updateMedians();
    }
}

function updateMedians() {
    let median_elements = document.querySelectorAll("tr > td.total.loading");
}

function loadTotals(parallel) {
    let loading_elements = document.querySelectorAll("tr > td.median.loading");
    let fetches = [];
    for (let i = 0; i < loading_elements.length; i++) {
        let element = loading_elements[i];
        let date = element.parentNode.querySelector(".date").dataset.date;

        let total_requests = localStorage.getItem("totals-" + date)
        if (total_requests) {
            element.innerHTML = total_requests
            element.classList.remove("loading");
            continue;
        }

        if (fetches.length < parallel) {
            fetches.push(fetch('/admin/count/count-data-total?date=' + date)
                .then(response => response.json())
                .then(response => {
                    total_requests = response.data.total;
                    localStorage.setItem("totals-" + date, total_requests);
                    element.innerHTML = total_requests
                    element.classList.remove("loading");
                    let sum = 0;
                    for (let j = 0; j < totals.length; j++) {
                        sum += totals[j];
                    }

                })
                .catch(reason => {
                    element.innerHTML = "-"
                    element.classList.remove("loading");
                }));
        } else {
            break;
        }
    }
    return fetches;
}

function loadSameTimes(parallel) {
    let loading_elements = document.querySelectorAll("tr > td.same-time.loading");
    let fetches = [];

    for (let i = 0; i < loading_elements.length; i++) {
        let element = loading_elements[i];
        let date = element.parentNode.querySelector(".date").dataset.date;

        let total_requests = localStorage.getItem("until-" + date)
        if (total_requests) {
            element.innerHTML = total_requests
            element.classList.remove("loading");
            continue;
        }

        if (fetches.length < parallel) {
            fetches.push(fetch('/admin/count/count-data-until?date=' + date)
                .then(response => response.json())
                .then(response => {
                    let total_requests = response.data.total
                    localStorage.setItem("until-" + date, total_requests);
                    element.innerHTML = total_requests
                    element.classList.remove("loading");
                })
                .catch(reason => {
                    element.innerHTML = "-"
                    element.classList.remove("loading");
                }));
        } else {
            break;
        }
    }
    return fetches
}