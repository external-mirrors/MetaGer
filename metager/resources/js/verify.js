require("es6-promise").polyfill();
require("fetch-ie8");

let interval;
try {
  // Should get blocked by csp
  eval("window.sp = 1;");
} catch (err) { }

let key = getKey();
let url = "/img/logo.png?id=" + key;
if (window.sp == 1) {
  url += "&sp";
}

if (navigator.webdriver) {
  url += "&wd";
}

fetch(url).then((res) => {
  if (res.status === 200) {
    interval = setInterval(verify, 100);
    verify();
  }
});

function verify() {
  let styleSheet = getStyleSheet();
  if (!styleSheet || !("cssRules" in styleSheet) || styleSheet.cssRules.length === 0) {
    return false;
  }
  console.log(styleSheet);
  let url = document.querySelector("meta[name=url]").content;
  clearInterval(interval);
  history.replaceState(null, "", url);
  history.go();
}

function getStyleSheet() {
  let styleSheets = document.styleSheets;
  for (let i = 0; i < styleSheets.length; i++) {
    let styleSheet = styleSheets[i];
    let matches = styleSheet.href.match(/index\.css\?id=([a-f0-9]{32})$/);
    if (!matches) {
      continue;
    }
    return styleSheet;
  }
  return null;
}

function getKey() {
  let styleSheet = getStyleSheet();
  let matches = styleSheet.href.match(/index\.css\?id=([a-f0-9]{32})$/);
  if (matches) {
    return matches[1];
  }
}