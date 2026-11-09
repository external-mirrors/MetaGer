import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";
import { currentStep, enhanceContents, enhanceRequestKinds } from "./enhance";

// The markup resources/views/removal/index.blade.php renders, trimmed.
function render({ open = "personal_data" } = {}) {
  const kind = (name) => `
    <details class="removal-kind" id="kind-${name}" data-removal-kind="${name}" ${open === name ? "open" : ""}>
      <summary>${name}</summary>
    </details>`;
  const row = (name) => `<div data-removal-kind="${name}"><dt>${name}</dt><dd><a href="https://example.org">x</a></dd></div>`;

  document.body.innerHTML = `
    <div id="removal">
      <nav class="removal-toc"><ol>
        <li><a href="#one">1</a></li>
        <li><a href="#two">2</a></li>
      </ol></nav>
      <section id="one"></section>
      <section id="two"></section>
      ${kind("personal_data")}${kind("outdated")}
      <details class="removal-kind" id="illegal"><summary>illegal</summary></details>
      <dl class="removal-provider-links">${row("personal_data")}${row("outdated")}</dl>
      <dl class="removal-provider-links">${row("personal_data")}${row("outdated")}</dl>
    </div>`;
  return document.querySelector("#removal");
}

function markedRows(root) {
  return [...root.querySelectorAll(".removal-row-active")].map((row) => row.dataset.removalKind);
}

function toggle(root, selector, open) {
  const details = root.querySelector(selector);
  details.open = open;
  details.dispatchEvent(new Event("toggle"));
}

describe("the request kinds", () => {
  it("mark the rows of the kind that is open when the page loads, on every card", () => {
    const root = render();
    enhanceRequestKinds(root);
    expect(markedRows(root)).toEqual(["personal_data", "personal_data"]);
  });

  it("mark nothing when no kind is open", () => {
    const root = render({ open: null });
    enhanceRequestKinds(root);
    expect(markedRows(root)).toEqual([]);
  });

  it("follow the kind opened last", () => {
    const root = render();
    enhanceRequestKinds(root);
    toggle(root, "#kind-outdated", true);
    expect(markedRows(root)).toEqual(["outdated", "outdated"]);
  });

  it("clear the mark when the marked kind closes", () => {
    const root = render();
    enhanceRequestKinds(root);
    toggle(root, "#kind-personal_data", false);
    expect(markedRows(root)).toEqual([]);
  });

  it("keep the mark when a different kind closes", () => {
    const root = render();
    enhanceRequestKinds(root);
    toggle(root, "#kind-outdated", true);
    toggle(root, "#kind-personal_data", false);
    expect(markedRows(root)).toEqual(["outdated", "outdated"]);
  });

  it("mark nothing for a kind no card has a row for", () => {
    const root = render();
    enhanceRequestKinds(root);
    toggle(root, "#illegal", true);
    expect(markedRows(root)).toEqual([]);
  });
});

describe("currentStep", () => {
  it("is the last step at or above the reading line", () => {
    expect(currentStep([-900, -100, 150, 900], 200)).toBe(2);
  });

  it("is the first step before any has reached the line", () => {
    expect(currentStep([400, 900], 200)).toBe(0);
  });
});

describe("the table of contents", () => {
  let root, view, tops;

  const currentLinks = () => [...root.querySelectorAll("[aria-current]")].map((link) => link.getAttribute("href"));

  beforeEach(() => {
    root = render();
    tops = { one: 300, two: 900 };
    for (const id of Object.keys(tops)) {
      root.querySelector(`#${id}`).getBoundingClientRect = () => ({ top: tops[id] });
    }
    view = new EventTarget();
    view.innerHeight = 800;
    view.requestAnimationFrame = vi.fn((callback) => callback());
  });

  afterEach(() => {
    vi.restoreAllMocks();
  });

  it("names the first step before the reader has scrolled", () => {
    enhanceContents(root, view);
    expect(currentLinks()).toEqual(["#one"]);
    expect(root.querySelector("[aria-current]").getAttribute("aria-current")).toBe("location");
  });

  it("moves with the scroll position, leaving one link current", () => {
    enhanceContents(root, view);
    tops.one = -500;
    tops.two = 100;
    view.dispatchEvent(new Event("scroll"));
    expect(currentLinks()).toEqual(["#two"]);
  });

  it("does nothing when an anchor has no step, rather than naming the wrong one", () => {
    root.querySelector("#two").remove();
    enhanceContents(root, view);
    expect(currentLinks()).toEqual([]);
  });
});
