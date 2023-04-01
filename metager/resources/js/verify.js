require("es6-promise").polyfill();
require("fetch-ie8");

try {
  // Should get blocked by csp
  eval("window.sp = 1;");
} catch (err) {}

let key = getKey();
let url = "/img/logo.png?id=" + key;
if (window.sp == 1) {
  url += "&sp";
}

if (navigator.webdriver) {
  url += "&wd";
}

fetch(url);

function getKey() {
  let nonce_element = document.querySelector("meta[name=nonce]");
  return nonce_element.content;
}
