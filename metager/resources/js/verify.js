require('es6-promise').polyfill();
require('fetch-ie8');

// Find the key id for the browser-verification
document.querySelectorAll("link").forEach(element => {
    let href = element.href;
    let matches = href.match(/http[s]{0,1}:\/\/[^\/]+\/index\.css\?id=(.+)/i);
    if (!matches) {
        return true;
    }
    let key = matches[1];
    let url = "/img/logo.png?id=" + key;

    return fetch(url);
});

