import { beforeEach, describe, expect, it } from "vitest";
import { enhanceMenuToggle, setMenuOpen } from "./menu-toggle";

// The markup parts/sidebar.blade.php and a layout's skip links render, trimmed.
function render() {
  document.body.innerHTML = `
    <div class="skiplinks"><a href="#sidebarToggle">Zur Navigation springen</a></div>
    <input id="sidebarToggle" type="checkbox">
    <nav class="sidebar"><ul><li><a href="/">Startseite</a></li></ul></nav>`;
  return {
    toggle: document.querySelector("#sidebarToggle"),
    menu: document.querySelector("nav"),
    skipLink: document.querySelector(".skiplinks a"),
  };
}

describe("the menu toggle", () => {
  let toggle, menu, skipLink;

  beforeEach(() => {
    ({ toggle, menu, skipLink } = render());
    enhanceMenuToggle(toggle, menu);
  });

  it("is a button that controls the menu, closed to begin with", () => {
    expect(toggle.getAttribute("role")).toBe("button");
    expect(toggle.getAttribute("aria-controls")).toBe(menu.id);
    expect(toggle.getAttribute("aria-expanded")).toBe("false");
  });

  it("opens and closes on Enter", () => {
    toggle.dispatchEvent(new KeyboardEvent("keydown", { key: "Enter", bubbles: true }));
    expect(toggle.checked).toBe(true);
    expect(toggle.getAttribute("aria-expanded")).toBe("true");

    toggle.dispatchEvent(new KeyboardEvent("keydown", { key: "Enter", bubbles: true }));
    expect(toggle.checked).toBe(false);
    expect(toggle.getAttribute("aria-expanded")).toBe("false");
  });

  it("follows a change the checkbox made itself, as Space does", () => {
    toggle.checked = true;
    toggle.dispatchEvent(new Event("change"));
    expect(toggle.getAttribute("aria-expanded")).toBe("true");
  });

  it("follows a change made from script", () => {
    setMenuOpen(toggle, true);
    expect(toggle.getAttribute("aria-expanded")).toBe("true");
    setMenuOpen(toggle, false);
    expect(toggle.getAttribute("aria-expanded")).toBe("false");
  });

  it("opens the menu from its skip link and focuses the first link in it", () => {
    skipLink.click();
    expect(toggle.checked).toBe(true);
    expect(document.activeElement).toBe(menu.querySelector("a"));
  });
});
