import { beforeEach, describe, expect, it } from "vitest";
import { markAsCombobox, reflectActive, reflectExpanded } from "./combobox";

// The markup parts/searchbar.blade.php renders, trimmed to two suggestions.
function render() {
  document.body.innerHTML = `
    <input type="search" id="eingabe" name="eingabe">
    <div class="suggestions">
      <div class="suggestion" tabindex="0"><a href=""><img src="/img/icon-lupe.svg" alt=""></a><span></span></div>
      <div class="suggestion" tabindex="0"><a href=""><img src="/img/icon-lupe.svg" alt=""></a><span></span></div>
    </div>`;
  return {
    input: document.querySelector("input"),
    listbox: document.querySelector(".suggestions"),
    options: [...document.querySelectorAll(".suggestion")],
  };
}

describe("the search suggestions as a combobox", () => {
  let input, listbox, options;

  beforeEach(() => {
    ({ input, listbox, options } = render());
    markAsCombobox(input, listbox);
  });

  it("ties the field to its list of options", () => {
    expect(input.getAttribute("role")).toBe("combobox");
    expect(input.getAttribute("aria-autocomplete")).toBe("list");
    expect(input.getAttribute("aria-controls")).toBe(listbox.id);
    expect(listbox.getAttribute("role")).toBe("listbox");
    options.forEach((option) => {
      expect(option.getAttribute("role")).toBe("option");
      expect(option.id).not.toBe("");
    });
  });

  it("takes the options out of the tab order, links and all", () => {
    options.forEach((option) => {
      expect(option.hasAttribute("tabindex")).toBe(false);
      expect(option.querySelector("a").getAttribute("tabindex")).toBe("-1");
      expect(option.querySelector("a").getAttribute("aria-hidden")).toBe("true");
    });
  });

  it("starts closed and says when it opens", () => {
    expect(input.getAttribute("aria-expanded")).toBe("false");
    reflectExpanded(input, true);
    expect(input.getAttribute("aria-expanded")).toBe("true");
  });

  it("points the field at the option the arrow keys are on", () => {
    reflectExpanded(input, true);
    reflectActive(input, options[1]);

    expect(input.getAttribute("aria-activedescendant")).toBe(options[1].id);
    expect(options[1].getAttribute("aria-selected")).toBe("true");
    expect(options[0].getAttribute("aria-selected")).toBe("false");
  });

  it("lets go of the option when the list closes", () => {
    reflectExpanded(input, true);
    reflectActive(input, options[0]);
    reflectExpanded(input, false);

    expect(input.hasAttribute("aria-activedescendant")).toBe(false);
    expect(options[0].getAttribute("aria-selected")).toBe("false");
  });
});
