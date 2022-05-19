let parallel_fetches = 8;

let data = [];

load();

function load() {
    let parallel = Math.floor(parallel_fetches / 2)

    let fetches = [];
    fetches = fetches.concat(loadSameTimes(parallel));
    fetches = fetches.concat(loadTotals(parallel));
    if (fetches.length > 0) {
        let allData = Promise.all(fetches)
        allData.then((res) => {
            updateTable();
            updateRecord();
            load();
        });
    } else {
        updateTable();
        updateRecord();
    }
}

function updateRecord() {
    let record_total = null;
    for (let i = 0; i < data.length; i++) {
        let total = data[i]["total"]
        let same_time = data[i]["same_time"]
        let date = document.querySelectorAll("tbody tr .date")[i].dataset.date_formatted
        if (typeof total === "number" && typeof same_time === "number" && (record_total === null || record_total < total)) {
            record_total = total;
            let record_same_time_element = document.querySelector(".record .record-same-time");
            let record_total_element = document.querySelector(".record .record-total");
            record_same_time_element.innerHTML = same_time.toLocaleString('de-DE', {
                maximumFractionDigits: 0
            });
            record_same_time_element.classList.remove("loading");
            record_total_element.innerHTML = record_total.toLocaleString('de-DE', {
                maximumFractionDigits: 0
            });
            record_total_element.classList.remove("loading");
            let record_date_element = document.querySelector(".record .record-date");
            record_date_element.classList.remove("loading");
            record_date_element.innerHTML = date;
        }
    }
}

function updateTable() {
    let sum = 0;
    for (let i = 0; i < data.length; i++) {
        if (typeof data[i]["total"] === "number") {
            // Update Total Number
            let total_element = document.querySelector("[data-days_ago=\"" + i + "\"] .total")
            total_element.innerHTML = data[i]["total"].toLocaleString('de-DE', {
                maximumFractionDigits: 0
            })
            total_element.classList.remove("loading")
            sum += data[i]["total"]
        } else {
            sum = undefined;
        }
        if (typeof data[i]["same_time"] === "number") {
            // Update Total Number
            let same_time_element = document.querySelector("[data-days_ago=\"" + i + "\"] .same-time")
            same_time_element.innerHTML = data[i]["same_time"].toLocaleString('de-DE', {
                maximumFractionDigits: 0
            })
            same_time_element.classList.remove("loading")
        }
        if (typeof sum !== undefined) {
            let median_element = document.querySelector("[data-days_ago=\"" + i + "\"] .median");
            let median = 0;
            if (i > 0) {
                median = sum / i;
            }
            median_element.innerHTML = median.toLocaleString('de-DE', {
                maximumFractionDigits: 0
            });
            median_element.classList.remove("loading");
            let total_median_days_element = document.querySelector(".total-median .median-days");
            total_median_days_element.classList.remove("loading")
            total_median_days_element.innerHTML = i + 1
            let total_median_values_element = document.querySelector(".total-median .median-value");
            total_median_values_element.classList.remove("loading")
            total_median_values_element.innerHTML = median.toLocaleString('de-DE', {
                maximumFractionDigits: 0
            });
        }
    }
}

function loadTotals(parallel) {
    let loading_elements = document.querySelectorAll("tr > td.total.loading");
    let fetches = [];
    for (let i = 0; i < loading_elements.length; i++) {
        let element = loading_elements[i];
        let date = element.parentNode.querySelector(".date").dataset.date;

        let days_ago = parseInt(element.parentNode.dataset.days_ago)

        let total_requests = parseInt(localStorage.getItem("totals-" + date))
        if (days_ago === 0) {
            if (!data[days_ago]) {
                data[days_ago] = {}
            }
            data[days_ago]["total"] = 0;
        } else if (total_requests) {
            if (!data[days_ago]) {
                data[days_ago] = {}
            }
            data[days_ago]["total"] = total_requests;
        } else if (fetches.length < parallel) {
            fetches.push(fetch('/admin/count/count-data-total?date=' + date)
                .then(response => response.json())
                .then(response => {
                    total_requests = parseInt(response.data.total);
                    if (!data[days_ago]) {
                        data[days_ago] = {}
                    }
                    data[days_ago]["total"] = total_requests;
                    localStorage.setItem("totals-" + date, total_requests);
                })
                .catch(reason => {
                    if (!data[days_ago]) {
                        data[days_ago] = {}
                    }
                    data[days_ago]["total"] = 0;
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

        let days_ago = parseInt(element.parentNode.dataset.days_ago)

        let total_requests = parseInt(localStorage.getItem("until-" + date))
        if (total_requests) {
            if (!data[days_ago]) {
                data[days_ago] = {}
            }
            data[days_ago]["same_time"] = total_requests;
        } else if (fetches.length < parallel) {
            fetches.push(fetch('/admin/count/count-data-until?date=' + date)
                .then(response => response.json())
                .then(response => {
                    let total_requests = parseInt(response.data.total)
                    if (!data[days_ago]) {
                        data[days_ago] = {}
                    }
                    data[days_ago]["same_time"] = total_requests;
                })
                .catch(reason => {
                    if (!data[days_ago]) {
                        data[days_ago] = {}
                    }
                    data[days_ago]["same_time"] = 0;
                }));
        } else {
            break;
        }
    }
    return fetches
}