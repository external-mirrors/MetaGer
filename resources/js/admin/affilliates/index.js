window.addEventListener("load", function () {
    console.log("loaded");

    refreshBlacklist();
    refreshWhitelist();
});

function refreshBlacklist() {
    fetch('/admin/affiliates/json/blacklist')
        .then(response => response.json())
        .catch(error => console.log(error))
        .then(blacklist => {
            document.querySelector("#blacklist-container > .blacklist > h3 > a").innerHTML = "Blacklist (" + blacklist.total + ")";
            document.querySelector("#blacklist-container > .blacklist .skeleton").style.display = "none";
            // Todo add returned Items to the list
        })
        .catch(error => console.log(error));
}

function refreshWhitelist() {
    fetch('/admin/affiliates/json/whitelist')
        .then(response => response.json())
        .catch(error => console.log(error))
        .then(blacklist => {
            document.querySelector("#blacklist-container > .whitelist > h3 > a").innerHTML = "Whitelist (" + blacklist.total + ")";
            document.querySelector("#blacklist-container > .whitelist .skeleton").style.display = "none";
            // Todo add returned Items to the list
        })
        .catch(error => console.log(error));
}