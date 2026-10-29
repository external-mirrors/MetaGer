/**
 * The menu toggle, made to behave like the button it looks like.
 *
 * Without JavaScript the menu is a checkbox and a sibling combinator, and a
 * keyboard reaches it the way it reaches any checkbox: focus, then Space. That
 * is all a checkbox promises, and it is not what anyone tries. Enter did
 * nothing, a screen reader said "Kontrollkästchen, nicht aktiviert", and the
 * skip link "Zur Navigation springen" left focus on that closed checkbox — from
 * which Tab skips the whole menu, because a closed menu is visibility: hidden.
 *
 * So, once JavaScript runs: the checkbox announces itself as a button that
 * expands the menu, answers Enter as well as Space, and the skip links to it
 * open the menu and put focus on its first link instead.
 *
 * The role is what makes aria-expanded possible: a checkbox may not carry it,
 * and axe reports that as a WCAG failure. role="button" on a checkbox is itself
 * outside what ARIA in HTML allows without aria-pressed, which axe reports only
 * as best practice — and "erweitert"/"reduziert" is what a menu button is
 * expected to say, where "gedrückt" is not.
 */
export function enhanceMenuToggle(toggle, menu) {
  if (!menu.id) {
    menu.id = "sidebar-menu";
  }
  toggle.setAttribute("role", "button");
  toggle.setAttribute("aria-controls", menu.id);
  reflect(toggle);

  // Space changes a checkbox on its own and fires change; Enter does neither.
  toggle.addEventListener("change", () => reflect(toggle));
  toggle.addEventListener("keydown", (e) => {
    if (e.key === "Enter") {
      e.preventDefault();
      setMenuOpen(toggle, !toggle.checked);
    }
  });

  document.querySelectorAll(`.skiplinks a[href="#${toggle.id}"]`).forEach((link) => {
    link.addEventListener("click", (e) => {
      e.preventDefault();
      setMenuOpen(toggle, true);
      let first = menu.querySelector("a[href]");
      (first || toggle).focus();
    });
  });
}

/**
 * Open or close the menu from script. Setting `checked` fires no change event,
 * so everything that does it goes through here to keep aria-expanded honest.
 */
export function setMenuOpen(toggle, open) {
  toggle.checked = open;
  reflect(toggle);
}

function reflect(toggle) {
  if (toggle.hasAttribute("aria-controls")) {
    toggle.setAttribute("aria-expanded", toggle.checked ? "true" : "false");
  }
}
