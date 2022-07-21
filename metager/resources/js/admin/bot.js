require('es6-promise').polyfill();
require('fetch-ie8');

import picassoCanvas from '../picasso';

if (document.location.href.match(/\/\/metager\.org/i) !== null) {
    checkPicasso();
}

document.querySelectorAll("div.user input, div.user select").forEach(element => {
    element.addEventListener("change", event => {
        element.form.submit();
    });

});

function checkPicasso() {
    let pcso = document.getElementById("current-user").dataset.pcso;
    if (!pcso) {
        let pcso = picassoCanvas();
        let new_url = new URL(document.location);
        new_url.searchParams.append("pcso", pcso);
        document.location = new_url;
        console.log(new_url);
    }
}