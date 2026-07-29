import t from "./strings";

/**
 * The per-message affordances: copy, regenerate, and per-code-block copy/download.
 *
 * All of these are pure JS additions with no no-JS equivalent, which is exactly the kind of thing
 * the enhancement layer is for — a user without JS loses convenience here, never capability. They
 * are therefore built in JS rather than rendered by Blade and hidden: markup for a button that can
 * never work is just a broken button in someone's screen reader.
 */

/** Extensions for the languages a chat answer realistically produces. Anything else downloads as .txt. */
const EXTENSIONS = {
  bash: "sh",
  c: "c",
  cpp: "cpp",
  csharp: "cs",
  css: "css",
  diff: "diff",
  go: "go",
  html: "html",
  java: "java",
  javascript: "js",
  js: "js",
  json: "json",
  kotlin: "kt",
  less: "less",
  markdown: "md",
  php: "php",
  python: "py",
  ruby: "rb",
  rust: "rs",
  shell: "sh",
  sh: "sh",
  sql: "sql",
  swift: "swift",
  typescript: "ts",
  ts: "ts",
  xml: "xml",
  yaml: "yaml",
  yml: "yaml",
};

function canCopy() {
  // Absent on insecure origins. Rather than a button that silently fails, offer nothing.
  return !!(navigator.clipboard && navigator.clipboard.writeText);
}

/**
 * A button that copies `getText()` and briefly says so.
 *
 * The confirmation is the label swapping to "Copied", not a toast: it is attached to the thing that
 * was acted on, and it needs no extra styling to be legible in either theme.
 */
function copyButton(className, label, getText) {
  const button = document.createElement("button");
  button.type = "button";
  button.className = className;
  button.textContent = label;

  button.addEventListener("click", () => {
    navigator.clipboard.writeText(getText()).then(
      () => {
        button.textContent = t("copyDone");
        button.classList.add("success");
        window.setTimeout(() => {
          button.textContent = label;
          button.classList.remove("success");
        }, 2000);
      },
      () => {
        button.classList.add("failure");
        window.setTimeout(() => button.classList.remove("failure"), 2000);
      }
    );
  });

  return button;
}

/**
 * Adds the actions bar to one message node.
 *
 * `entry` is the live transcript object, not a copy, so the copy button keeps working after
 * streaming replaces its content — the closure reads it at click time.
 */
export function decorateMessage(node, entry) {
  if (node.querySelector(":scope > .chat-message-actions")) {
    return;
  }

  const actions = document.createElement("div");
  actions.className = "chat-message-actions";

  if (canCopy()) {
    // The Markdown source, not the rendered text: that is what is useful to paste anywhere else,
    // and it is what the user would get from the no-JS page by selecting the text.
    actions.appendChild(
      copyButton("chat-action chat-action--copy", t("copy"), () => entry.content)
    );
  }

  if (entry.role === "assistant") {
    const regenerate = document.createElement("button");
    regenerate.type = "button";
    regenerate.className = "chat-action chat-action--regenerate";
    regenerate.textContent = t("regenerate");
    regenerate.addEventListener("click", () => {
      node.dispatchEvent(new CustomEvent("chat:regenerate", { bubbles: true }));
    });
    actions.appendChild(regenerate);
  }

  if (actions.children.length > 0) {
    node.appendChild(actions);
  }
}

/**
 * Adds copy/download controls to every code block in a rendered answer.
 *
 * Called after the server's HTML lands, since that is the first moment `<pre><code>` exists — the
 * streaming view is a single plain-text node with no structure to hang controls off.
 */
export function decorateCodeBlocks(body) {
  body.querySelectorAll("pre").forEach((pre) => {
    if (pre.parentNode.classList.contains("chat-code")) {
      return;
    }

    const code = pre.querySelector("code");
    const text = (code || pre).textContent;
    const language = languageOf(code);

    const wrapper = document.createElement("div");
    wrapper.className = "chat-code";
    pre.parentNode.insertBefore(wrapper, pre);

    const toolbar = document.createElement("div");
    toolbar.className = "chat-code-toolbar";

    if (language) {
      const badge = document.createElement("span");
      badge.className = "chat-code-language";
      badge.textContent = language;
      toolbar.appendChild(badge);
    }

    if (canCopy()) {
      toolbar.appendChild(
        copyButton("chat-action chat-action--copy", t("copy"), () => text)
      );
    }

    toolbar.appendChild(downloadButton(text, language));

    wrapper.appendChild(toolbar);
    wrapper.appendChild(pre);
  });
}

function languageOf(code) {
  if (!code) {
    return null;
  }

  // CommonMark writes the info string as `language-xyz` on the <code> element.
  const match = code.className.match(/(?:^|\s)language-([\w+-]+)/);
  return match ? match[1].toLowerCase() : null;
}

/**
 * Downloads a code block as a file.
 *
 * A Blob and an object URL, not a `data:` URI: the page's CSP has no reason to allow `data:` in
 * navigations, and object URLs are same-origin blobs that we revoke immediately afterwards.
 */
function downloadButton(text, language) {
  const button = document.createElement("button");
  button.type = "button";
  button.className = "chat-action chat-action--download";
  button.textContent = t("download");

  button.addEventListener("click", () => {
    const extension = (language && EXTENSIONS[language]) || "txt";
    const blob = new Blob([text], { type: "text/plain;charset=utf-8" });
    const url = URL.createObjectURL(blob);

    const link = document.createElement("a");
    link.href = url;
    link.download = `metager-chat.${extension}`;
    document.body.appendChild(link);
    link.click();
    link.remove();

    URL.revokeObjectURL(url);
  });

  return button;
}
