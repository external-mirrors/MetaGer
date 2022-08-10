require('es6-promise').polyfill();
require('fetch-ie8');

// Find the key id for the browser-verification
document.querySelectorAll("link").forEach(element => {
    let href = element.href;
    let matches = href.match(/http[s]{0,1}:\/\/[^\/]+\/index\.css\?id=(.+)/i);
    if (!matches) {
        return true;
    }
    try {
        // Should get blocked by csp
        eval("window.sp = 1;");
    } catch (err) { }

    let key = matches[1];
    let url = "/img/logo.png?id=" + key;
    if (window.sp == 1) {
        url += "&sp"
    }

    if (navigator.webdriver) {
        url += "&wd"
    }

    return fetch(url);
});

