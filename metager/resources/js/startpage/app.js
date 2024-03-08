// Register Keyboard listener for quicklinks on startpage
(() => {
    let sidebar_toggle = document.querySelector("#sidebarToggle");
    document.addEventListener("keyup", e => {
        if (e.key == "Escape") {
            // Disable sidebar if opened
            if (sidebar_toggle && sidebar_toggle.checked) {
                sidebar_toggle.checked = false;
            }
            let skip_links_container = document.querySelector(".skiplinks");
            if (skip_links_container.contains(document.activeElement)) {
                document.activeElement.blur();
            } else {
                document.querySelector(".skiplinks > a").focus();
            }
        }
    })
})();