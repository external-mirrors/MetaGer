require("es6-promise").polyfill();
require("fetch-ie8");
require("chart.js/dist/chart.js");

let parallel_fetches = 8;

let data = [];
let lang = document.getElementById("data-table").dataset.interface;
let chart = null;

load();

function load() {
  let parallel = Math.floor(parallel_fetches / 2);

  let fetches = [];
  let fetches_same_time = loadSameTimes(parallel);
  let fetches_total = loadTotals(parallel);
  fetches = fetches.concat(fetches_same_time, fetches_total);
  if (fetches.length > 0) {
    let allData = Promise.all(fetches);
    allData.then((res) => {
      updateTable();
      updateChart();
      updateRecord();
      load();
    });
  } else {
    updateTable();
    updateChart();
    updateRecord();
  }
}

function updateRecord() {
  let record_total = null;
  for (let i = 0; i < data.length; i++) {
    let total = data[i]["total"];
    let same_time = data[i]["same_time"];
    let date =
      document.querySelectorAll("tbody tr .date")[i].dataset.date_formatted;
    if (
      typeof total === "number" &&
      typeof same_time === "number" &&
      (record_total === null || record_total < total)
    ) {
      record_total = total;
      let record_same_time_element = document.querySelector(
        ".record .record-same-time"
      );
      let record_total_element = document.querySelector(
        ".record .record-total"
      );
      record_same_time_element.innerHTML = same_time.toLocaleString("de-DE", {
        maximumFractionDigits: 0,
      });
      record_same_time_element.classList.remove("loading");
      record_total_element.innerHTML = record_total.toLocaleString("de-DE", {
        maximumFractionDigits: 0,
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
      let total_element = document.querySelector(
        '[data-days_ago="' + i + '"] .total'
      );
      total_element.innerHTML = data[i]["total"].toLocaleString("de-DE", {
        maximumFractionDigits: 0,
      });
      total_element.classList.remove("loading");
      if (typeof sum !== undefined && i > 0) {
        sum += data[i]["total"];
      }
    } else {
      sum = undefined;
    }
    if (typeof data[i]["same_time"] === "number") {
      // Update Total Number
      let same_time_element = document.querySelector(
        '[data-days_ago="' + i + '"] .same-time'
      );
      same_time_element.innerHTML = data[i]["same_time"].toLocaleString(
        "de-DE",
        {
          maximumFractionDigits: 0,
        }
      );
      same_time_element.classList.remove("loading");
    }
    if (typeof sum !== undefined) {
      let median_element = document.querySelector(
        '[data-days_ago="' + i + '"] .median'
      );
      let median = 0;
      if (i > 0) {
        median = sum / i;
      }
      median_element.innerHTML = median.toLocaleString("de-DE", {
        maximumFractionDigits: 0,
      });
      median_element.classList.remove("loading");
      let total_median_days_element = document.querySelector(
        ".total-median .median-days"
      );
      total_median_days_element.classList.remove("loading");
      total_median_days_element.innerHTML = i + 1;
      let total_median_values_element = document.querySelector(
        ".total-median .median-value"
      );
      total_median_values_element.classList.remove("loading");
      total_median_values_element.innerHTML = median.toLocaleString("de-DE", {
        maximumFractionDigits: 0,
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

    let days_ago = parseInt(element.parentNode.dataset.days_ago);

    if (fetches.length < parallel) {
      fetches.push(
        fetch(
          "/admin/count/count-data-total?date=" + date + "&interface=" + lang,
          { redirect: "error" }
        )
          .then((response) => {
            if (response.status === 302) {
              // We are not logged in anymore
              history.go();
            } else {
              return response.json();
            }
          })
          .then((response) => {
            let total_requests = parseInt(response.data.total);
            if (!data[days_ago]) {
              data[days_ago] = {};
            }
            data[days_ago]["total"] = total_requests;
          })
          .catch((reason) => {
            // We are not logged in anymore
            history.go();
          })
      );
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

    let days_ago = parseInt(element.parentNode.dataset.days_ago);

    if (fetches.length < parallel) {
      fetches.push(
        fetch(
          "/admin/count/count-data-until?date=" + date + "&interface=" + lang,
          { redirect: "error" }
        )
          .then((response) => {
            if (response.status === 302) {
              // We are not logged in anymore
              history.go();
            } else {
              return response.json();
            }
          })
          .then((response) => {
            let total_requests = parseInt(response.data.total);
            if (!data[days_ago]) {
              data[days_ago] = {};
            }
            data[days_ago]["same_time"] = total_requests;
          })
          .catch((reason) => {
            if (!data[days_ago]) {
              data[days_ago] = {};
            }
            data[days_ago]["same_time"] = 0;
          })
      );
    } else {
      break;
    }
  }
  return fetches;
}

function updateChart() {
  if (chart == undefined) {
    createChart();
  } else {
    let totals = [];
    let until_nows = [];
    let labels = [];
    for (let i = 0; i < data.length; i++) {
      totals.unshift(data[i]["total"]);
      until_nows.unshift(data[i]["same_time"]);
      labels.unshift(
        document.querySelectorAll("tbody tr td.date")[i].dataset.date_formatted
      );
    }

    chart.data.datasets[0].data = totals;
    chart.data.datasets[1].data = until_nows;
    chart.data.labels = labels;

    chart.update();
  }
}

function createChart() {
  let backgroundColor_total = "rgb(255, 127, 0)";
  let backgroundColor_until_now = "rgb(67, 134, 221)";
  let labels = [];
  let data_points_total = [];
  let data_points_until_now = [];

  let css_style = window.getComputedStyle(document.getElementById("graph"));
  let config = {
    type: "line",
    data: {
      labels: labels,
      datasets: [
        {
          label: "Gesamt",
          backgroundColor: backgroundColor_total,
          borderColor: backgroundColor_total,
          data: data_points_total,
        },
        {
          label: "Zur gleichen Zeit",
          backgroundColor: backgroundColor_until_now,
          borderColor: backgroundColor_until_now,
          data: data_points_until_now,
        },
      ],
    },
    options: {
      scales: {
        x: {
          ticks: {
            color: css_style.getPropertyValue("--chart-font-color"),
          },
          grid: {
            borderColor: css_style.getPropertyValue("--grid-axis-color"),
            color: css_style.getPropertyValue("--grid-color"),
          },
        },
        y: {
          ticks: {
            color: css_style.getPropertyValue("--chart-font-color"),
          },
          grid: {
            borderColor: css_style.getPropertyValue("--grid-axis-color"),
            color: css_style.getPropertyValue("--grid-color"),
          },
        },
      },
    },
  };
  chart = new Chart(document.getElementById("chart"), config);
  updateChart();
}
