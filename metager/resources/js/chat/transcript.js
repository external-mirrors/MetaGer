import { decorateMessage } from "./affordances";

/**
 * The conversation state, and the one thing that writes it to the DOM.
 *
 * Nothing about the conversation is stored server-side: the composer's hidden fields *are* the
 * state (see parts/chat/composer.blade.php). The enhancement layer therefore does not get to keep
 * a private copy — every change here is mirrored straight back into those fields, so that if JS
 * stops being involved at any point (the user disables it, a bundle fails to load, the tab is
 * restored from bfcache) the very next plain form submit continues the same conversation.
 *
 * Only Markdown *source* is ever held here, never rendered HTML — same reason as on the server:
 * hidden fields are user-editable, so HTML coming back out of them could not be trusted.
 */
export default class Transcript {
  constructor(form, list) {
    this.form = form;
    this.list = list;
    this.entries = readHiddenFields(form);
  }

  get length() {
    return this.entries.length;
  }

  last() {
    return this.entries[this.entries.length - 1] || null;
  }

  nodes() {
    return this.list.querySelectorAll(".chat-message");
  }

  /**
   * Renders a turn without recording it.
   *
   * This is how an answer starts: the placeholder is on screen from the first moment, but it must
   * not reach the hidden fields until it has content, or a turn that fails before a single token
   * arrives would leave an empty assistant message in the conversation forever.
   */
  attach(entry) {
    const emptyNotice = this.list.querySelector(".chat-empty");
    if (emptyNotice) {
      emptyNotice.remove();
    }

    const node = renderMessage(entry);
    this.list.appendChild(node);
    return node;
  }

  /** Records a turn that is already on screen. */
  commit(entry) {
    this.entries.push(entry);
    this.syncHiddenFields();
  }

  /** Renders and records in one go — the normal path for a user turn. */
  append(entry) {
    const node = this.attach(entry);
    this.commit(entry);
    return node;
  }

  /**
   * Cuts the conversation back to `length` turns, DOM included.
   *
   * Used by "regenerate": drop the answer (and anything after it) and re-send the conversation that
   * produced it.
   */
  truncate(length) {
    this.entries = this.entries.slice(0, length);
    this.syncHiddenFields();

    const nodes = this.nodes();
    for (let i = length; i < nodes.length; i++) {
      nodes[i].remove();
    }
  }

  /**
   * Rewrites every `messages[i][…]` input from scratch.
   *
   * Wholesale replacement rather than surgical edits: indices have to stay contiguous from 0 for
   * PHP to parse them back into a list, and removeLast() would otherwise leave a hole.
   */
  syncHiddenFields() {
    this.form
      .querySelectorAll('input[type="hidden"][name^="messages["]')
      .forEach((input) => input.remove());

    // Order matters for the no-JS submit, so insert before the first non-hidden control rather
    // than appending at the end of the form.
    const anchor = this.form.querySelector(".chat-model-picker, .chat-input-row");

    this.entries.forEach((entry, index) => {
      this.form.insertBefore(hiddenField(`messages[${index}][role]`, entry.role), anchor);
      this.form.insertBefore(hiddenField(`messages[${index}][content]`, entry.content), anchor);
    });
  }

  /**
   * The request body for a turn: the whole conversation, every time.
   *
   * The new user message is already part of `entries` by the time this is called, so there is no
   * separate `message` field — the server accepts either shape, and sending only one of them keeps
   * the JS path from being a second, subtly different protocol.
   */
  toBody(modelId) {
    const body = new URLSearchParams();
    body.set("focus", "chat");
    body.set("modelId", modelId);

    this.entries.forEach((entry, index) => {
      body.set(`messages[${index}][role]`, entry.role);
      body.set(`messages[${index}][content]`, entry.content);
    });

    return body;
  }
}

function hiddenField(name, value) {
  const input = document.createElement("input");
  input.type = "hidden";
  input.name = name;
  input.value = value;
  return input;
}

/**
 * Reads the state the server rendered, so a JS session picks up mid-conversation — after a no-JS
 * POST, or after an error page re-rendered the transcript.
 */
function readHiddenFields(form) {
  const entries = [];

  form.querySelectorAll('input[type="hidden"][name^="messages["]').forEach((input) => {
    const match = input.name.match(/^messages\[(\d+)\]\[(role|content)\]$/);
    if (!match) {
      return;
    }

    const index = parseInt(match[1], 10);
    entries[index] = entries[index] || { role: "", content: "" };
    entries[index][match[2]] = input.value;
  });

  return entries.filter((entry) => entry && entry.role !== "");
}

/**
 * Builds the same DOM parts/chat/message.blade.php builds — minus the Markdown.
 *
 * Assistant turns start as plain text on purpose. Rendering Markdown here would mean two renderers
 * with two sets of quirks and two security postures for the same content; instead the client shows
 * the raw stream while it arrives and swaps in the server's HTML when the `done` event carries it.
 */
export function renderMessage(entry) {
  const node = document.createElement("div");
  node.className = `chat-message chat-message--${entry.role}`;

  if (entry.role === "assistant" && entry.model) {
    const model = document.createElement("div");
    model.className = "chat-message-model";
    model.textContent = entry.model;
    node.appendChild(model);
  }

  const body = document.createElement("div");
  body.className = "chat-message-body";
  body.textContent = entry.content;
  node.appendChild(body);

  decorateMessage(node, entry);
  return node;
}
