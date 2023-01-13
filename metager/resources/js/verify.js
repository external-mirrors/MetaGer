require("es6-promise").polyfill();
require("fetch-ie8");

// Find the key id for the browser-verification
document.querySelectorAll("link").forEach((element) => {
  let href = element.href;
  let matches = href.match(/http[s]{0,1}:\/\/[^\/]+\/index\.css\?id=(.+)/i);
  if (!matches) {
    return true;
  }
  try {
    // Should get blocked by csp
    eval("window.sp = 1;");
  } catch (err) {}

  let key = matches[1];
  let url = "/img/logo.png?id=" + key;
  if (window.sp == 1) {
    url += "&sp";
  }

  if (navigator.webdriver) {
    url += "&wd";
  }

  fetch(url).then((res) => {
    if (res.status === 200) {
      let url = document.querySelector("meta[name=url]").content;
      let nonce = document.querySelector("meta[name=nonce]").content;
      let check = `/index.css?id=${nonce}`;

      let interval = setInterval(function () {
        let links = document.querySelectorAll("link");
        for (let i = 0; i < links.length; i++) {
          if (links[i].href.includes(check)) {
            clearInterval(interval);
            history.replaceState(null, "", url);
            history.go();
          }
        }
      }, 100);
    }
  });
});
