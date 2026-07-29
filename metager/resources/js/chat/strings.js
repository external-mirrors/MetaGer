/**
 * Localised strings for the enhancement layer.
 *
 * They come from `data-` attributes that Blade fills from lang/{de,en}/chat.php, rather than from
 * a JS-side dictionary (resources/js/translations.js style). That keeps exactly one translation
 * source for the chat feature: a string used by both the no-JS markup and the JS enhancement is
 * written once, and a translator adding a language does not have to know that some of the interface
 * lives in a bundle.
 */

let strings = {};

export function loadStrings(element) {
  if (element) {
    // dataset keys are camelCased by the browser: data-copy-done -> copyDone.
    strings = Object.assign({}, element.dataset);
  }
}

export default function t(key) {
  return Object.prototype.hasOwnProperty.call(strings, key) ? strings[key] : "";
}
