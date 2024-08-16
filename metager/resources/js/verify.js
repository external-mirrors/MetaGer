require("es6-promise").polyfill();
require("fetch-ie8");

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

let interval;
let unload = false;

window.addEventListener("beforeunload", () => {
  unload = true;
})

fetch(url).then(result => {
  webextensioncheck().then(() => {
    interval = setInterval(verify, 100);
    verify();
  });
});

async function webextensioncheck() {
  return new Promise(resolve => {
    if (localStorage && localStorage.getItem("webextension") != null) {
      return resolve();
    } else if (window.webextension != undefined) {
      return resolve();
    } else {
      let timeout = setTimeout(() => {
        resolve();
      }, 500);
      window.addEventListener("webextension_status_update", e => {
        clearTimeout(timeout);
        resolve();
      });
    }
  });
}

function getKey() {
  let nonce_element = document.querySelector("meta[name=nonce]");
  return nonce_element.content;
}

function verify() {
  if (unload) {
    clearInterval(interval);
    return;
  }
  let styleSheet = getStyleSheet();
  if (!styleSheet || !("cssRules" in styleSheet) || styleSheet.cssRules.length === 0) {
    return false;
  }
  clearInterval(interval);
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

