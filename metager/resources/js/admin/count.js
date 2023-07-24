require("es6-promise").polyfill();
require("fetch-ie8");
require("chart.js/dist/chart.js");

let parallel_fetches = 8;

let data = {};
let lang = document.getElementById("data-table").dataset.interface;
let chart = null;
let record = {
  count: 0,
  same_time: 0,
  date: "1.1.1970",
};

load();

async function load() {
  let parallel = Math.floor(parallel_fetches / 2);

  let fetches = loadData(parallel);

  if (fetches.length > 0) {
    let allData = await Promise.all(fetches);
    updateChart();
    load();
    /*
    allData
      .then((res) => {
        updateTable();
        updateChart();
        updateRecord();
        load();
      })
      .catch((reason) => {
        if (reason === "Unauthorized") {
          // Not logged in anymore. Reload
          history.go();
        }
      });
      */
  } else {
    updateChart();
    updateRecord();
  }
}

function updateRecord() {
  if (record.count === 0) {
    return;
  }
  let record_same_time_element = document.querySelector(
    ".record .record-same-time"
  );
  let record_total_element = document.querySelector(".record .record-total");
  record_same_time_element.innerHTML = record.same_time.toLocaleString(
    "de-DE",
    {
      maximumFractionDigits: 0,
    }
  );
  record_same_time_element.classList.remove("loading");
  record_total_element.innerHTML = record.count.toLocaleString("de-DE", {
    maximumFractionDigits: 0,
  });
  record_total_element.classList.remove("loading");
  let record_date_element = document.querySelector(".record .record-date");
  record_date_element.classList.remove("loading");
  record_date_element.innerHTML = record.date;
}

function loadData(parallel) {
  let loading_elements = document.querySelectorAll("tr.loading");
  let fetches = [];

  for (let i = 0; i < loading_elements.length; i++) {
    let element = loading_elements[i];
    let date = element.dataset.date;

    if (fetches.length < parallel) {
      fetches.push(
        fetch("/admin/count/count-data?date=" + date + "&interface=" + lang)
          .then((response) => {
            if (response.status === 404) {
              // File Could not be found
              return {
                data: {
                  total: 0,
                  until_now: 0,
                },
              };
            } else if (response.redirected) {
              throw "Unauthorized";
            } else {
              return response.json();
            }
          })
          .then((response) => {
            let total_requests = parseInt(response.data.total);
            let until_now = parseInt(response.data.until_now);

            element.classList.remove("loading");
            let same_time_element = element.querySelector("td.same-time");
            same_time_element.dataset.same_time = until_now;
            same_time_element.textContent = until_now.toLocaleString("de-DE", {
              maximumFractionDigits: 0,
            });

            if (!isNaN(total_requests)) {
              let total_element = element.querySelector("td.total");
              total_element.dataset.total = total_requests;
              total_element.textContent = total_requests.toLocaleString("de-DE", {
                maximumFractionDigits: 0,
              });
            }

            // Update total sums
            let elements = document.querySelectorAll("tbody tr");
            let sum = 0;
            for (let j = 0; j < elements.length; j++) {
              let total = parseInt(
                elements[j].querySelector(".total").dataset.total
              );

              if (j === 0 || total === 0 || isNaN(total)) {
                continue;
              }
              sum += total;
              let median_element = elements[j].querySelector(".median");
              let median = new Number(
                (sum / j).toFixed(0)
              ).toLocaleString("de-DE", {
                maximumFractionDigits: 0,
              });
              median_element.textContent = median;

              let total_median_days_element = document.querySelector(
                ".total-median .median-days"
              );
              let total_median_count_element = document.querySelector(
                ".total-median .median-value"
              );
              total_median_days_element.classList.remove("loading");
              total_median_days_element.textContent = j + 1;
              total_median_count_element.classList.remove("loading");
              total_median_count_element.textContent = median;
            }
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
    let backgroundColor_total = "rgb(255, 127, 0)";
    let backgroundColor_until_now = "rgb(67, 134, 221)";
    let backgroundColor_highlight = "#30b330";

    let totals = [];
    let until_nows = [];
    let labels = [];
    let elements = document.querySelectorAll("tbody tr");
    for (let i = 0; i < elements.length; i++) {
      let total = parseInt(elements[i].querySelector(".total").dataset.total);
      let until_now = parseInt(
        elements[i].querySelector(".same-time").dataset.same_time
      );
      let formatted_date = elements[i].dataset.date;
      if (!isNaN(total)) {
        if (total > record.count) {
          record.count = total;
          record.same_time = until_now;
          record.date = formatted_date;
          updateRecord();
        }

        totals.unshift(total);
      } else {
        totals.unshift(null);
      }
      until_nows.unshift(until_now);
      labels.unshift(formatted_date);
    }

    chart.data.datasets[0].data = totals;
    chart.data.datasets[0].backgroundColor = el => {
      if (shouldHighlight(el, labels)) {
        return backgroundColor_highlight;
      } else {
        return backgroundColor_total;
      }
    }
    chart.data.datasets[0].borderColor = el => {
      if (shouldHighlight(el, labels)) {
        return backgroundColor_highlight;
      } else {
        return backgroundColor_total;
      }
    }
    chart.data.datasets[1].data = until_nows;
    chart.data.datasets[1].backgroundColor = el => {
      if (shouldHighlight(el, labels)) {
        return backgroundColor_highlight;
      } else {
        return backgroundColor_until_now;
      }
    }
    chart.data.datasets[1].borderColor = el => {
      if (shouldHighlight(el, labels)) {
        return backgroundColor_highlight;
      } else {
        return backgroundColor_until_now;
      }
    }
    chart.data.labels = labels;

    chart.update();
  }
}

function shouldHighlight(el, labels) {
  let currentWeekday = new Date().toLocaleString('en-us', { weekday: 'long' });
  let elementWeekday = new Date(labels[el.dataIndex]).toLocaleString('en-us', { weekday: 'long' });
  if (currentWeekday == elementWeekday) {
    return true;
  } else {
    return false;
  }
}

function createChart() {
  let backgroundColor_total = "rgb(255, 127, 0)";
  let backgroundColor_until_now = "rgb(67, 134, 221)";
  let backgroundColor_highlight = "rgb(0,0,0)";
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

// JS for Date picker
document.querySelector("#start").addEventListener("change", (e) => {
  let min = e.target.value;
  document.querySelector("#end").min = min;
});
document.querySelector("#end").addEventListener("change", (e) => {
  let max = e.target.value;
  document.querySelector("#start").max = max;
});
