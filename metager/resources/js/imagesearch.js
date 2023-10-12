(() => {
    // Polyfill for Browsers without Has selector
    let tries = 0;
    let interval = setInterval(polyfillHasSelector, 100);

    function polyfillHasSelector() {
        let supported = getComputedStyle(document.querySelector("body")).getPropertyValue("--supports-selector-has");
        if (supported != "0" && supported != "1") {
            tries++;
            return;
        }
        clearInterval(interval);
        if (supported == "0") {
            console.log("CSS Has Selector not supported by your browser. Enabling JS Polyfill.");
            let maxWidth = getComputedStyle(document.querySelector("body")).getPropertyValue("--full-screen-details-breakpoint");
            document.querySelectorAll("div.image-details > input[name=result]").forEach(input => {
                input.addEventListener("change", e => {
                    if (!window.matchMedia("(max-width: " + maxWidth + ")").matches) {
                        return;
                    }
                    document.querySelector("body").style.overflow = "hidden";
                });
            });
            document.querySelector("form#details").addEventListener("reset", e => {
                if (!window.matchMedia("(max-width: " + maxWidth + ")").matches) {
                    return;
                }
                document.querySelector("body").style.overflow = "auto";
            })
        }
    }
})();

(() => {

    let interval = setInterval(polyfillGrid, 100);

    function polyfillGrid() {
        // Polyfill for Browsers that do not support masonry grid style
        let gridContainer = document.querySelector("#results-container > .image-container > .images");
        if (!gridContainer) {
            return;
        } else {
            clearInterval(interval);
        }
        if (getComputedStyle(gridContainer).gridTemplateRows == 'masonry') {
            return;
        }
        console.log("CSS only Grid masonry is not supported by your browser. Enabling JS Polyfill.");
        gridContainer.classList.add("js-masonry");
        const Macy = require("macy");
        let macy_layout = Macy({
            container: gridContainer,
            columns: 6,
            margin: 10,
            waitForImages: false,
            breakAt: {
                1450: 5,
                1200: 4,
                965: 3,
                550: 2
            }
        });
        macy_layout.runOnImageLoad(() => {
            macy_layout.recalculate(true);
        }, true);

        // Recalculate layout on resize
        window.addEventListener("resize", e => {
            macy_layout.recalculate(true);
        });

        /*
        gridContainer.classList.add("js-masonry");
        const Masonry = require("masonry-layout");
        let masonry = new Masonry(gridContainer, { gutter: 10 });
        console.log(masonry);*/
    }
})();