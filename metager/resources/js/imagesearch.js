(() => {
    // Polyfill for Browsers without Has selector
    let tries = 0;
    let timeout = setTimeout(() => {
        let supported = getComputedStyle(document.querySelector("body")).getPropertyValue("--supports-selector-has");
        if (supported != "0" && supported != "1") {
            tries++;
            return;
        }
        clearTimeout(timeout);
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
    })
})();