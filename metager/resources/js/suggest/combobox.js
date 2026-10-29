/**
 * The search field and its suggestions, as an ARIA combobox.
 *
 * Without this a screen reader heard nothing when suggestions opened, and
 * arrowing through them only swapped the text in the field — there was no way
 * to tell a suggestion had been picked rather than typed. The roles go on only
 * once suggestions are enabled: without JavaScript there are none, and a field
 * announcing a popup that never opens is worse than a plain one.
 *
 * Each suggestion is an option, and options are not tab stops: the list is
 * reached with the arrow keys from the field, which keeps focus — and the caret —
 * where the person is typing. The magnifier link inside each one duplicates the
 * option and leaves both the tab order and the accessibility tree.
 */
export function markAsCombobox(input, listbox) {
  if (!listbox.id) {
    listbox.id = "search-suggestions";
  }
  listbox.setAttribute("role", "listbox");
  input.setAttribute("role", "combobox");
  input.setAttribute("aria-autocomplete", "list");
  input.setAttribute("aria-controls", listbox.id);
  input.setAttribute("aria-expanded", "false");

  listbox.querySelectorAll(".suggestion").forEach((option, index) => {
    option.id = listbox.id + "-" + index;
    option.setAttribute("role", "option");
    option.setAttribute("aria-selected", "false");
    option.removeAttribute("tabindex");
    let link = option.querySelector("a");
    if (link) {
      link.setAttribute("tabindex", "-1");
      link.setAttribute("aria-hidden", "true");
    }
  });
}

/**
 * Open or closed, as the suggestions container shows it.
 */
export function reflectExpanded(input, expanded) {
  input.setAttribute("aria-expanded", expanded ? "true" : "false");
  if (!expanded) {
    reflectActive(input, null);
  }
}

/**
 * The option the arrow keys are on, or null for the field itself.
 */
export function reflectActive(input, option) {
  let listbox = document.getElementById(input.getAttribute("aria-controls"));
  if (listbox) {
    listbox.querySelectorAll("[role=option]").forEach((candidate) => {
      candidate.setAttribute("aria-selected", candidate === option ? "true" : "false");
    });
  }
  if (option) {
    input.setAttribute("aria-activedescendant", option.id);
  } else {
    input.removeAttribute("aria-activedescendant");
  }
}
