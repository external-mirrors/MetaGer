/**
 * ‹ › over the edges of the news and video rows.
 *
 * Each row scrolls sideways (layouts/resultpage/news.blade.php), which a phone
 * does with a swipe and a mouse barely does at all: shift and the wheel, or a
 * thin scrollbar. The buttons are for the mouse. The stylesheet only shows them
 * where the pointer is one (hover and a fine pointer), so a phone keeps its
 * swipe and nothing in its way.
 *
 * A button is hidden at its end of the row, and both are when the row fits.
 * They are out of the tab order: a keyboard already moves through the cards'
 * links, and the row scrolls each one into view.
 *
 * Safe to call again: a frame that has its buttons is left alone, so it can run
 * after load-more has replaced a row.
 * @param {ParentNode} root
 * @param {Window} win
 */
export function enableInlineScrollers(root = document, win = window) {
    root.querySelectorAll(".inline-scroller").forEach((frame) => enhance(frame, win));
}

/** How much of the visible row one click moves: most of it, so a card stays in view for orientation. */
export const PAGE = 0.85;

function enhance(frame, win) {
    const row = frame.querySelector(":scope > .inline-results");
    if (row === null || frame.querySelector(":scope > .inline-scroll-button") !== null) {
        return;
    }

    const back = button(frame.dataset.scrollBack, "back", "M15 6l-6 6 6 6");
    const forward = button(frame.dataset.scrollForward, "forward", "M9 6l6 6-6 6");
    frame.append(back, forward);

    const update = () => {
        const end = row.scrollWidth - row.clientWidth;
        // A pixel of slack: zoomed pages scroll in fractions.
        back.hidden = row.scrollLeft <= 1;
        forward.hidden = row.scrollLeft >= end - 1;
    };

    const scroll = (direction) => {
        const reduce = win.matchMedia?.("(prefers-reduced-motion: reduce)").matches;
        row.scrollBy({ left: direction * row.clientWidth * PAGE, behavior: reduce ? "auto" : "smooth" });
    };
    back.addEventListener("click", () => scroll(-1));
    forward.addEventListener("click", () => scroll(1));

    row.addEventListener("scroll", update, { passive: true });
    if ("ResizeObserver" in win) {
        new win.ResizeObserver(update).observe(row);
    }
    update();
}

function button(label, direction, path) {
    const element = document.createElement("button");
    element.type = "button";
    element.className = "inline-scroll-button " + direction;
    element.tabIndex = -1;
    element.setAttribute("aria-label", label ?? "");
    element.innerHTML = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="${path}"/></svg>`;
    return element;
}
