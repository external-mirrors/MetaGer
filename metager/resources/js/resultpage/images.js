/**
 * A result whose picture does not load is shown as a result without one.
 *
 * Engines send thumbnail URLs that answer 404 often enough — Brave in
 * particular — and the image proxy can only pass that on. Left alone, the
 * browser draws its broken-image icon in the 64px slot. Whether a picture
 * loads is only known once the browser has tried, so this is the one place
 * that can decide; without JavaScript the slot keeps the broken picture.
 *
 * A news or video card has a picture slot whatever happens, so there the
 * placeholder it uses for "no picture" takes the broken one's place.
 */

export const PLACEHOLDER = "/img/news-placeholder.svg";

const RESULT_PICTURE = ".result-body .result-image img, .result-body .result-logo img";
const CARD_PICTURE = ".inline-result .result-image img";

/**
 * Deal with one picture that failed to load.
 * @param {HTMLImageElement} img
 */
export function dropBrokenImage(img) {
    if (img.matches(CARD_PICTURE)) {
        // Guarded: should the placeholder itself fail, it must not loop.
        if (!img.src.endsWith(PLACEHOLDER)) {
            img.src = PLACEHOLDER;
        }
        return;
    }

    if (img.matches(RESULT_PICTURE)) {
        const slot = img.closest(".result-image, .result-logo");
        slot.closest(".result-body")?.classList.remove("with-image");
        slot.remove();
    }
}

/**
 * Watch every result picture on the page, including those load-more brings in
 * later, and deal with the ones that already failed before this ran.
 * @param {Document} doc
 */
export function watchBrokenImages(doc = document) {
    // `error` does not bubble; a capturing listener still sees it, for every
    // picture the page will ever have.
    doc.addEventListener(
        "error",
        (event) => {
            if (event.target instanceof HTMLImageElement) {
                dropBrokenImage(event.target);
            }
        },
        true
    );

    doc.querySelectorAll(`${RESULT_PICTURE}, ${CARD_PICTURE}`).forEach((img) => {
        if (img.complete && img.naturalWidth === 0 && img.getAttribute("src")) {
            dropBrokenImage(img);
        }
    });
}
