import t from "./strings";

/**
 * Upgrades the composer's file input.
 *
 * Without JS the browser's own file control is what the user gets: it works, it just looks like a
 * form field in the middle of a chat composer. With JS the input moves out of sight behind a button
 * and the chosen file becomes a removable chip — the same interaction, dressed as part of the
 * conversation rather than part of a form.
 *
 * The input element itself stays in the DOM and stays the source of truth, so a JS failure anywhere
 * leaves a working file picker behind.
 */
export function setupAttachment(form) {
  const row = form.querySelector(".chat-attach-row");
  const input = form.querySelector(".chat-attach-input");
  if (!row || !input) {
    return null;
  }

  row.classList.add("chat-attach-row--enhanced");

  const button = document.createElement("button");
  button.type = "button";
  button.className = "chat-attach-button";
  button.textContent = t("attachmentChoose");
  button.addEventListener("click", () => input.click());
  row.appendChild(button);

  const chip = document.createElement("div");
  chip.className = "chat-attachment-chip chat-attachment-chip--pending";
  chip.hidden = true;

  const label = document.createElement("span");
  chip.appendChild(label);

  const remove = document.createElement("button");
  remove.type = "button";
  remove.className = "chat-attachment-remove";
  remove.textContent = "×";
  remove.setAttribute("aria-label", t("attachmentRemove"));
  chip.appendChild(remove);
  row.appendChild(chip);

  function render() {
    const file = input.files && input.files[0];
    chip.hidden = !file;
    button.hidden = !!file;
    label.textContent = file ? file.name : "";
  }

  function clear() {
    // `input.value = ""` is the only cross-browser way to empty a file input; `files = null` is not
    // assignable in older engines.
    input.value = "";
    render();
  }

  input.addEventListener("change", render);
  remove.addEventListener("click", clear);
  render();

  return {
    /** The file to send with the next turn, or null. */
    file: () => (input.files && input.files[0]) || null,
    clear,
  };
}
