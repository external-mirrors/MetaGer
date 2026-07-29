import Transcript from "./transcript";
import { decorateCodeBlocks, decorateMessage } from "./affordances";
import { readEventStream, supportsStreaming } from "./sse";
import { setupAttachment } from "./attachment";
import { setupPicker } from "./picker";
import t, { loadStrings } from "./strings";

/**
 * The chat enhancement layer.
 *
 * MetaGer's chat works entirely without JS: the composer is a real form, it POSTs the transcript,
 * and the server re-renders the page with the answer appended. Everything in this bundle is an
 * upgrade on top of that baseline and nothing here is load-bearing — if it fails to load, fails a
 * feature test, or throws, the form is left exactly as the server rendered it and submits normally.
 *
 * The upgrade is worth having because the baseline's weak spot is real: a long answer means tens of
 * seconds of blank page. Asking the *same* route for `text/event-stream` instead turns that into
 * tokens as they arrive, without a second endpoint or a second idea of what a conversation is.
 *
 * See docs/llm/metager-integration/native-frontend.md.
 */

/** Distance from the bottom, in px, within which we consider the user "following along". */
const PIN_THRESHOLD = 48;

function boot() {
  const form = document.getElementById("chat-composer");
  const list = document.getElementById("chat-messages");

  // Absent on the unavailable / not-logged-in branches of results_chat.blade.php.
  if (!form || !list) {
    return;
  }

  loadStrings(document.getElementById("chat-strings"));

  const transcript = new Transcript(form, list);
  const input = form.querySelector(".chat-input");
  const sendButton = form.querySelector(".chat-send");
  const stopButton = document.getElementById("chat-stop");

  // Answers the server already rendered — after a no-JS POST, or an error page. They get the same
  // affordances as anything streamed in this session.
  transcript.nodes().forEach((node, index) => {
    const entry = transcript.entries[index];
    if (entry) {
      decorateMessage(node, entry);
      if (entry.role === "assistant") {
        decorateCodeBlocks(node.querySelector(".chat-message-body"));
      }
    }
  });

  // Both are pure presentation over markup that already works, so they run regardless of whether
  // this browser can stream — a turn that falls back to a plain form submit still gets them.
  setupPicker(form);
  const attachment = setupAttachment(form);

  if (!supportsStreaming()) {
    // Copy buttons and textarea growth still work; the turn itself keeps using the plain submit.
    autoGrow(input);
    return;
  }

  let controller = null;

  const isPinned = () =>
    list.scrollHeight - list.scrollTop - list.clientHeight < PIN_THRESHOLD;
  const pin = () => {
    list.scrollTop = list.scrollHeight;
  };

  function setBusy(busy) {
    sendButton.disabled = busy;
    input.disabled = busy;
    if (stopButton) {
      stopButton.hidden = !busy;
    }
  }

  function showError(message) {
    const error = document.createElement("div");
    error.className = "chat-error";
    error.setAttribute("role", "alert");
    error.textContent = message;
    list.appendChild(error);
    pin();
  }

  function selectedModel() {
    const checked = form.querySelector('input[name="modelId"]:checked');
    return checked ? checked.value : null;
  }

  function selectedModelName() {
    const checked = form.querySelector('input[name="modelId"]:checked');
    const name = checked && checked.closest("label").querySelector(".chat-model-option-name");
    return name ? name.textContent.trim() : "";
  }

  /**
   * Streams one turn. The transcript already contains everything to send.
   */
  async function send(file) {
    const modelId = selectedModel();
    if (modelId === null || transcript.length === 0) {
      return;
    }

    list.querySelectorAll(".chat-error").forEach((node) => node.remove());

    // Not in the transcript yet — see Transcript#attach.
    const entry = { role: "assistant", content: "", model: selectedModelName() };
    const node = transcript.attach(entry);
    const body = node.querySelector(".chat-message-body");
    node.classList.add("chat-message--streaming");

    controller = new AbortController();
    setBusy(true);
    pin();

    try {
      const headers = { Accept: "text/event-stream" };
      if (!file) {
        // Deliberately unset for a multipart body: only the browser knows the boundary it is about
        // to generate, and naming the type ourselves would leave the server unable to parse it.
        headers["Content-Type"] = "application/x-www-form-urlencoded; charset=UTF-8";
      }

      const response = await fetch(form.action, {
        method: "POST",
        credentials: "same-origin",
        signal: controller.signal,
        headers: headers,
        body: transcript.toBody(modelId, file),
      });

      // The file is on the server now, or the request failed outright; either way the composer
      // should not offer to send it a second time.
      if (file && attachment) {
        attachment.clear();
      }

      if (!response.ok) {
        // The server rejects before opening the stream (no key, empty balance, unknown model), so
        // the body is plain JSON with a message already localised for this user.
        const payload = await response.json().catch(() => ({}));
        throw new Error(payload.message || t("errorGeneric"));
      }

      await consume(response, entry, node, body);
    } catch (error) {
      if (error.name === "AbortError") {
        // Stopped on purpose. Whatever arrived is kept — the user paid for those tokens — but it
        // never went through the server's Markdown renderer, so it stays plain text and is marked
        // as such. Any later server round trip renders it properly from the same source.
        node.classList.add("chat-message--unrendered");
        finish(entry, node, body);
      } else {
        node.remove();
        showError(error.message || t("errorGeneric"));
      }
    } finally {
      controller = null;
      setBusy(false);
      input.focus();
    }
  }

  /**
   * Drains the event stream into the placeholder.
   *
   * During streaming the answer is plain text: rendering Markdown here would mean a second renderer
   * with its own quirks and its own security posture for the exact same content. On `done` the
   * server hands over the HTML it rendered from the same source, and that replaces the text.
   */
  async function consume(response, entry, node, body) {
    let failed = null;

    await readEventStream(response, (name, data) => {
      const pinned = isPinned();

      if (name === "delta") {
        entry.content += data.text || "";
        body.textContent = entry.content;
      } else if (name === "done") {
        if (data.model) {
          entry.model = data.model;
        }
        if (typeof data.html === "string") {
          body.innerHTML = data.html;
          decorateCodeBlocks(body);
        }
      } else if (name === "attachments") {
        // Sent before the first token: the file is stored, and these are the ids the conversation
        // will refer to it by from now on.
        recordAttachments(data.attachments || []);
      } else if (name === "error") {
        failed = data.message || t("errorGeneric");
      }

      if (pinned) {
        pin();
      }
    });

    if (failed !== null && entry.content === "") {
      node.remove();
      showError(failed);
      return;
    }

    finish(entry, node, body);

    if (failed !== null) {
      // Partial answer plus a failure: keep both, so the user can see what they got and retry.
      showError(failed);
    }
  }

  /**
   * Files the just-uploaded attachments onto the user turn they were sent with.
   *
   * They have to land in the hidden fields, not just on screen: those fields are the conversation,
   * and an id that never reaches them is an upload the next turn cannot see.
   */
  function recordAttachments(attachments) {
    if (attachments.length === 0) {
      return;
    }

    const index = transcript.length - 1;
    const entry = transcript.entries[index];
    if (!entry || entry.role !== "user") {
      return;
    }

    entry.attachments = (entry.attachments || []).concat(attachments);
    transcript.syncHiddenFields();

    const node = transcript.nodes()[index];
    if (!node) {
      return;
    }

    let list = node.querySelector(".chat-message-attachments");
    if (!list) {
      list = document.createElement("ul");
      list.className = "chat-message-attachments";
      node.insertBefore(list, node.querySelector(".chat-message-body"));
    }

    attachments.forEach((attachment) => {
      const chip = document.createElement("li");
      chip.className = "chat-attachment-chip";
      chip.textContent = attachment.name;
      list.appendChild(chip);
    });
  }

  /** Turns the placeholder into a real, recorded turn. */
  function finish(entry, node, body) {
    node.classList.remove("chat-message--streaming");

    if (entry.content === "") {
      node.remove();
      return;
    }

    const model = node.querySelector(".chat-message-model");
    if (model) {
      model.textContent = entry.model || "";
    } else if (entry.model) {
      const label = document.createElement("div");
      label.className = "chat-message-model";
      label.textContent = entry.model;
      node.insertBefore(label, body);
    }

    transcript.commit(entry);
  }

  form.addEventListener("submit", (event) => {
    const message = input.value.trim();
    if (message === "") {
      return;
    }

    event.preventDefault();

    const file = attachment ? attachment.file() : null;

    input.value = "";
    resize(input);
    transcript.append({ role: "user", content: message });
    send(file);
  });

  list.addEventListener("chat:regenerate", (event) => {
    const nodes = Array.prototype.slice.call(transcript.nodes());
    const index = nodes.indexOf(event.target);
    if (index === -1) {
      return;
    }

    transcript.truncate(index);
    send();
  });

  if (stopButton) {
    stopButton.addEventListener("click", () => {
      if (controller) {
        controller.abort();
      }
    });
  }

  // Enter sends, Shift+Enter breaks the line. `isComposing` keeps IME users from sending half a
  // word when they confirm a candidate with Enter.
  input.addEventListener("keydown", (event) => {
    if (event.key !== "Enter" || event.shiftKey || event.isComposing) {
      return;
    }

    event.preventDefault();
    if (typeof form.requestSubmit === "function") {
      form.requestSubmit();
    } else {
      form.dispatchEvent(new Event("submit", { bubbles: true, cancelable: true }));
    }
  });

  autoGrow(input);
  pin();
}

/**
 * Grows the textarea with its content, up to the cap set in chat.less.
 *
 * `rows="1"` in the markup is the no-JS compromise: one line is the honest starting size, and
 * without JS the textarea's own scrollbar takes over. With JS it grows instead, which is what
 * anyone writing a paragraph-long prompt expects.
 */
function autoGrow(input) {
  input.addEventListener("input", () => resize(input));
  resize(input);
}

function resize(input) {
  input.style.height = "auto";
  input.style.height = `${input.scrollHeight}px`;
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", boot);
} else {
  boot();
}
