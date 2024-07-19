(async () => {
    let tile_container = document.querySelector("#tiles");
    let tile_count = tile_container.querySelectorAll("a").length;

    let advertisements = [];
    let fetch_timeout = null;
    fetchAdvertisements().then(() => udpateInterface());

    async function fetchAdvertisements() {
        let desired_tile_count = calculateDesiredTileCount();
        let regular_tile_count = getRegularTileCount();
        console.log(desired_tile_count, advertisements.length)
        if (advertisements.length >= desired_tile_count - regular_tile_count) return;
        let update_url = document.querySelector("meta[name=tiles-update-url]").content;
        update_url += "&count=" + (desired_tile_count - tile_count);
        return new Promise((resolve, reject) => {
            if (fetch_timeout != null) return resolve();
            fetch_timeout = setTimeout(() => {
                fetch(update_url).then(response => response.json()).then(response => {
                    advertisements = response;
                    fetch_timeout = null;
                    resolve();
                }).catch(e => reject(e));
            }, 500);
        });
    }
    function udpateInterface() {
        let desired_tile_count = calculateDesiredTileCount();
        let regular_tile_count = getRegularTileCount();

        if (document.querySelectorAll("#tiles > a").length == desired_tile_count) return;
        document.querySelectorAll("#tiles >a.advertisement").forEach(element => {
            console.log("remove");
            element.remove();
        });
        for (let i = 0; i < desired_tile_count - regular_tile_count; i++) {
            if (advertisements.length < i + 1) continue;
            let container = document.createElement("div");
            container.innerHTML = advertisements[i].html;

            tile_container.appendChild(container.firstChild);
        }
    }
    function getRegularTileCount() {
        return tile_container.querySelectorAll("a:not(.advertisement)").length;
    }
    function calculateDesiredTileCount() {
        let max_tiles = 8;
        let native_tile_count = getRegularTileCount();
        let min_advertisements = 2;

        let tile_width = parseFloat(window.getComputedStyle(document.querySelector("#tiles > a")).width.replace("px", ""));
        let tile_gap = parseFloat(window.getComputedStyle(document.querySelector("#tiles"))["column-gap"].replace("px", ""));
        let client_width = document.querySelector("html").clientWidth;
        let client_height = document.querySelector("html").clientHeight;

        let desired_tile_count = 8;

        if (client_width > 9 * tile_width + 8 * tile_gap) {
            // Largest Screen Size => Up to 8 Tiles in one row
            desired_tile_count = max_tiles;
        } else if (client_width > 8 * tile_width + 7 * tile_gap) {
            // Large Screen => Up to 7 Tiles in one row => Just Fill up
            desired_tile_count = 7;
        } else if (client_width > 6 * tile_width + 5 * tile_gap) {
            desired_tile_count = 5;
        } else if (client_width > 4 * tile_width + 3 * tile_gap) {
            desired_tile_count = 3;
        } else {
            desired_tile_count = 2;
        }

        console.log(client_width, tile_width, tile_gap, desired_tile_count);
        console.log("Six Tiles", tile_width * 6 + 5 * tile_gap);
        if (native_tile_count + min_advertisements > desired_tile_count) {
            // Allow 2x3 Tiles on small displays
            if (desired_tile_count == 2 && client_height > 850) {
                desired_tile_count *= 3;
            } else {
                desired_tile_count *= 2;
            }
        }


        return desired_tile_count;
    }
    window.addEventListener("resize", e => {
        fetchAdvertisements().then(() => udpateInterface());
    })
})();

