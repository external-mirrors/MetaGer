/**
 * The ⋯ menu of each result, closed the way a menu is expected to close.
 *
 * The menu is a checkbox (layouts/result.blade.php), so without JavaScript ⋯
 * opens and closes it and nothing else does. With JavaScript a click anywhere
 * outside it closes it, Escape closes it and puts focus back on ⋯, and opening
 * one result's menu closes any other.
 * @param {Document} doc
 */
export function enableResultMenus(doc = document) {
    doc.addEventListener("click", (event) => {
        doc.querySelectorAll(".result-toggle:checked").forEach((toggle) => {
            if (!toggle.closest(".result-menu").contains(event.target)) {
                toggle.checked = false;
            }
        });
    });

    doc.addEventListener("change", (event) => {
        const opened = event.target;
        if (!opened.matches?.(".result-toggle") || !opened.checked) {
            return;
        }
        doc.querySelectorAll(".result-toggle:checked").forEach((toggle) => {
            if (toggle !== opened) {
                toggle.checked = false;
            }
        });
    });

    doc.addEventListener("keydown", (event) => {
        if (event.key !== "Escape") {
            return;
        }
        const open = doc.querySelector(".result-toggle:checked");
        if (open) {
            open.checked = false;
            open.focus();
        }
    });
}
