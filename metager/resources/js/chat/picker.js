/**
 * Upgrades the model picker from a disclosure to a popover.
 *
 * The baseline is `<details>` plus one radio per model, which is entirely functional without JS —
 * it opens, it selects, it submits. What it does not do is behave like a menu: it stays open after
 * a choice, it ignores Escape, and it ignores clicks elsewhere on the page. All three are added
 * here, and all three are pure behaviour: nothing below changes what the picker *is*, so losing
 * this file costs a little polish and nothing else.
 */
export function setupPicker(form) {
  const picker = form.querySelector(".chat-model-picker");
  if (!picker) {
    return;
  }

  const current = picker.querySelector(".chat-model-picker-current");

  // Only meaningful once it can be closed again by something other than a second click.
  picker.classList.add("chat-model-picker--enhanced");

  const close = () => {
    picker.open = false;
  };

  picker.addEventListener("change", (event) => {
    if (event.target.name !== "modelId") {
      return;
    }

    if (current) {
      const name = event.target.closest("label").querySelector(".chat-model-option-name");
      current.textContent = name ? name.textContent.trim() : "";
    }

    close();
    // Without this the summary keeps focus somewhere invisible after the popover collapses.
    picker.querySelector("summary").focus();
  });

  document.addEventListener("click", (event) => {
    if (picker.open && !picker.contains(event.target)) {
      close();
    }
  });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && picker.open) {
      close();
      picker.querySelector("summary").focus();
    }
  });
}
