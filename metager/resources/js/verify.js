require('es6-promise').polyfill();
require('fetch-ie8');

import picassoCanvas from './picasso';

// This is the result of the picasso canvas fingerprint that we'll submit back to our server
let canvasValue = null;
if (document.location.href.match(/\/\/metager\.org/i) !== null) {
    canvasValue = picassoCanvas();
}

// Find the key id for the browser-verification
document.querySelectorAll("link").forEach(element => {
    let href = element.href;
    let matches = href.match(/http[s]{0,1}:\/\/[^\/]+\/index\.css\?id=(.+)/i);
    if (!matches) {
        return true;
    }
    let key = matches[1];
    let url = "/img/logo.png?id=" + key;
    if (canvasValue !== null) {
        url += "&c=" + canvasValue;
    }

    return fetch(url);
});

