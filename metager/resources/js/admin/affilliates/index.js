/**
 * Global variables
 */
let clicksCount = 10;
let clicksSkip = 0;

let blacklistTotal = 0;
let blacklistCount = 5;
let blacklistSkip = 0;

let whitelistTotal = 0;
let whitelistCount = 5;
let whitelistSkip = 0;


window.addEventListener("load", function () {
    refreshBlacklist();
    refreshWhitelist();
    refreshHostlist();

    document.querySelector("#affilliate-clicks .filter").addEventListener("keyup", refreshHostlist);
    document.querySelector("#affilliate-clicks .pagination .forward").addEventListener("click", () => {
        clicksSkip += clicksCount;
        refreshHostlist();
    });
    document.querySelector("#affilliate-clicks .pagination .backward").addEventListener("click", () => {
        clicksSkip -= clicksCount;
        clicksSkip = Math.max(0, clicksSkip);
        refreshHostlist();
    });

    document.querySelector("#blacklist-container .whitelist .pagination .backward").addEventListener("click", e => {
        whitelistSkip = Math.max(whitelistSkip - whitelistCount, 0);
        refreshWhitelist();
    });
    document.querySelector("#blacklist-container .whitelist .pagination .forward").addEventListener("click", e => {
        whitelistSkip = Math.min(whitelistSkip + whitelistCount, whitelistTotal - whitelistCount);
        refreshWhitelist();
    });

    document.querySelector("#blacklist-container .blacklist .pagination .backward").addEventListener("click", e => {
        blacklistSkip = Math.max(blacklistSkip - blacklistCount, 0);
        refreshBlacklist();
    });
    document.querySelector("#blacklist-container .blacklist .pagination .forward").addEventListener("click", e => {
        blacklistSkip = Math.min(blacklistSkip + blacklistCount, blacklistTotal - blacklistCount);
        refreshBlacklist();
    });
});

function refreshBlacklist() {
    let templateHtml = `
        <div>
            <div class="number"></div>
            <div class="hostname"></div>
            <a href="#" class="remove" data-id="">&#x1F5D1;</a>
        </div>
    `;
    let template = document.createElement("template");
    template.innerHTML = templateHtml.trim();
    template = template.content.firstChild;

    let params = new URLSearchParams({
        "count": blacklistCount,
        "skip": blacklistSkip
    });

    fetch(`/admin/affiliates/json/blacklist?${params.toString()}`)
        .then(response => response.json())
        .catch(error => console.log(error))
        .then(blacklist => {
            document.querySelector("#blacklist-container > .blacklist > h3 > a").innerHTML = "Blacklist (" + blacklist.total + ")";
            document.querySelector("#blacklist-container > .blacklist .skeleton").style.display = "none";
            document.querySelector("#blacklist-container > .blacklist > .blacklist-items").innerHTML = "";

            blacklist.results.forEach((value, index) => {
                let newElement = template.cloneNode(true);
                newElement.querySelector(".number").innerHTML = (blacklist.skip + index + 1) + ".";
                newElement.querySelector(".hostname").innerHTML = value.hostname;
                newElement.querySelector(".remove").setAttribute("data-id", value.id);
                newElement.querySelector(".remove").addEventListener("click", removeBlacklistItem);
                document.querySelector("#blacklist-container > .blacklist > .blacklist-items").appendChild(newElement);
            });
            blacklistTotal = blacklist.total;
            let pagination = document.querySelector("#blacklist-container > .blacklist .pagination");
            if (blacklist.total > 0) {
                pagination.style.display = "flex";
            }
            if (blacklistSkip == 0) {
                pagination.querySelector(".backward").classList.add("disabled");
            } else {
                pagination.querySelector(".backward").classList.remove("disabled");
            }
            if (blacklistSkip >= blacklistTotal - blacklistCount) {
                pagination.querySelector(".forward").classList.add("disabled");
            } else {
                pagination.querySelector(".forward").classList.remove("disabled");
            }
        })
        .catch(error => console.log(error));
}

function addBlacklistItem(element) {
    let confirmation = confirm("Are you sure to add the selected entry to the blacklist? This will eliminate all income from that partnershop!");

    if (!confirmation) {
        return;
    }

    let data = {
        "hostname": element.target.dataset.host
    }

    fetch('/admin/affiliates/json/blacklist', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
        .catch(error => console.log(error))
        .then(response => response.json())
        .then(response => {
            refreshBlacklist();
            refreshHostlist();
        });
}

function removeBlacklistItem(element) {

    let confirmation = confirm("Are you sure to delete the selected entry from database? This is permanent!");

    if (!confirmation) {
        return;
    }

    let data = {
        "id": element.target.dataset.id
    }

    fetch('/admin/affiliates/json/blacklist', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
        .catch(error => console.log(error))
        .then(response => response.json())
        .then(response => {
            refreshBlacklist();
            refreshHostlist();
        });
}

function refreshWhitelist() {
    let templateHtml = `
        <div>
            <div class="number"></div>
            <div class="hostname"></div>
            <a href="#" class="remove" data-id="">&#x1F5D1;</a>
        </div>
    `;
    let template = document.createElement("template");
    template.innerHTML = templateHtml.trim();
    template = template.content.firstChild;

    let params = new URLSearchParams({
        "count": whitelistCount,
        "skip": whitelistSkip
    });

    fetch(`/admin/affiliates/json/whitelist?${params.toString()}`)
        .then(response => response.json())
        .catch(error => console.log(error))
        .then(whitelist => {
            console.log(whitelist);
            document.querySelector("#blacklist-container > .whitelist > h3 > a").innerHTML = "Whitelist (" + whitelist.total + ")";
            document.querySelector("#blacklist-container > .whitelist .skeleton").style.display = "none";
            document.querySelector("#blacklist-container > .whitelist > .whitelist-items").innerHTML = "";

            whitelist.results.forEach((value, index) => {
                let newElement = template.cloneNode(true);
                newElement.querySelector(".number").innerHTML = (whitelist.skip + index + 1) + ".";
                newElement.querySelector(".hostname").innerHTML = value.hostname;
                newElement.querySelector(".remove").setAttribute("data-id", value.id);
                newElement.querySelector(".remove").addEventListener("click", removeWhitelistItem);
                document.querySelector("#blacklist-container > .whitelist > .whitelist-items").appendChild(newElement);
            });
            whitelistTotal = whitelist.total;
            let pagination = document.querySelector("#blacklist-container > .whitelist .pagination");
            if (whitelist.total > 0) {
                pagination.style.display = "flex";
            }
            if (whitelistSkip == 0) {
                pagination.querySelector(".backward").classList.add("disabled");
            } else {
                pagination.querySelector(".backward").classList.remove("disabled");
            }
            if (whitelistSkip >= whitelistTotal - whitelistCount) {
                pagination.querySelector(".forward").classList.add("disabled");
            } else {
                pagination.querySelector(".forward").classList.remove("disabled");
            }
        })
        .catch(error => console.log(error));
}

function addWhitelistItem(element) {
    let confirmation = confirm("Are you sure to add the selected entry to the whitelist?");

    if (!confirmation) {
        return;
    }

    let data = {
        "hostname": element.target.dataset.host
    }

    fetch('/admin/affiliates/json/whitelist', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
        .catch(error => console.log(error))
        .then(response => response.json())
        .then(response => {
            refreshWhitelist();
            refreshHostlist();
        });
}

function removeWhitelistItem(element) {

    let confirmation = confirm("Are you sure to delete the selected entry from whitelist? This is permanent!");

    if (!confirmation) {
        return;
    }

    let data = {
        "id": element.target.dataset.id
    }

    fetch('/admin/affiliates/json/whitelist', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
        .catch(error => console.log(error))
        .then(response => response.json())
        .then(response => {
            refreshWhitelist();
            refreshHostlist();
        });
}

function refreshHostlist() {
    let templateHtml = `
        <details class="host">
            <summary class="host-information">
                <div class="number"></div>
                <div class="host"></div>
                <div class="clicks"></div>
                <a href="#" class="add-to-whitelist" title="Add host to whitelist" data-host="">&#128077;</a>
                <a href="#" class="add-to-blacklist" title="Add host to blacklist" data-host="">&#128078;</a>
            </summary>
            <div class="clicks">
                <div class="skeleton"></div>
            </div>
        </details>
    `;
    let template = document.createElement("template");
    template.innerHTML = templateHtml.trim();
    template = template.content.firstChild;

    let url = '/admin/affiliates/json/hosts';

    let urlParams = {};
    let filter = document.querySelector("#affilliate-clicks .filter").value;
    if (filter) {
        urlParams.filter = filter;
    }

    urlParams.count = clicksCount;
    urlParams.skip = clicksSkip;
    console.log(urlParams);

    if (Object.entries(urlParams).length > 0) {
        let queryString = "?";
        Object.entries(urlParams).forEach(entry => {
            const [key, value] = entry;
            queryString += key + "=" + encodeURIComponent(value) + "&";
        });
        queryString = queryString.slice(0, queryString.length - 1)
        url += queryString;
    }

    fetch(url)
        .then(response => response.json())
        .then(hostlist => {
            document.querySelector("#affilliate-clicks .skeleton").style.display = "none";
            document.querySelector("#affilliate-clicks .pagination").style.display = "flex";
            document.querySelector("#affilliate-clicks .host-list").innerHTML = "";
            // Update total counts
            let totalHosts = new Intl.NumberFormat("de-DE").format(hostlist.total_hosts);
            let totalClicks = new Intl.NumberFormat("de-DE").format(hostlist.total_clicks);
            document.querySelector("#affilliate-clicks > .heading > .host-count").innerHTML = totalHosts + " hosts";
            document.querySelector("#affilliate-clicks > .heading > .click-count").innerHTML = totalClicks + " clicks";
            let pages = Math.ceil(hostlist.total_hosts / clicksCount);
            let currentPage = Math.floor(clicksSkip / 10) + 1
            document.querySelector("#affilliate-clicks > .pagination > .current-page").innerHTML = currentPage + "/" + pages;
            console.log(hostlist);
            hostlist.hosts.forEach((value, index) => {
                let newHost = template.cloneNode(true);
                newHost.querySelector(".number").innerHTML = (hostlist.skip + index + 1) + ".";
                newHost.querySelector(".host").innerHTML = value.hostname;
                newHost.querySelector(".clicks").innerHTML = value.clicks;
                newHost.querySelector(".add-to-blacklist").dataset.host = value.hostname;
                newHost.querySelector(".add-to-blacklist").addEventListener("click", addBlacklistItem);
                newHost.querySelector(".add-to-whitelist").dataset.host = value.hostname;
                newHost.querySelector(".add-to-whitelist").addEventListener("click", addWhitelistItem);
                newHost.dataset.hostname = value.hostname;
                newHost.addEventListener("toggle", loadHostClicks);
                document.querySelector("#affilliate-clicks .host-list").appendChild(newHost);
            });
        })
        .catch(error => console.log(error));
}

function loadHostClicks(element) {
    let templateHtml = `
        <div class="click">
            <a href="#" target="_blank" class="affiliate">Affiliate Link</a>
            <a href="#" target="_blank" class="link">Link</a>
            <div class="created-at"></div>
        </div>
    `;
    let template = document.createElement("template");
    template.innerHTML = templateHtml.trim();
    template = template.content.firstChild;

    if (!element.target.open || element.target.dataset.loaded) {
        // Element was closed or is already loaded do nothing
        return;
    }

    fetch("/admin/affiliates/json/hosts/clicks?hostname=" + encodeURIComponent(element.target.dataset.hostname))
        .then(response => response.json())
        .then(hostlist => {
            element.target.querySelector(".clicks .skeleton").remove();
            hostlist.results.forEach((value, index) => {
                let newElement = template.cloneNode(true);
                newElement.querySelector(".affiliate").href = value.affillink;
                newElement.querySelector(".link").href = value.link;
                let date = new Date(value.created_at);
                date = date.toLocaleString();
                date = date.replace(/\b(\d{1})\b/g, '0$1');
                newElement.querySelector(".created-at").innerHTML = date;
                element.target.querySelector("details > .clicks").appendChild(newElement);
                element.target.dataset.loaded = true;
            });
        })
        .catch(error => console.log(error));
}