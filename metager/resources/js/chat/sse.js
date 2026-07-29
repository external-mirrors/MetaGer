/**
 * Minimal Server-Sent-Events reader over `fetch`.
 *
 * Deliberately not `EventSource`: that only does GET, and a chat turn is a POST carrying the whole
 * transcript. The wire format is the same three-event contract the server speaks
 * (`delta` / `done` / `error`), and this parser mirrors ChatController::readEvents() line for line
 * so the two never drift.
 */

/**
 * Whether this browser can stream a fetch response body.
 *
 * Everything here is a hard requirement, not a nicety: without any one of them the enhancement
 * cannot work and the page must keep using the plain form submit. That is the whole progressive
 * enhancement contract — we check instead of assuming, and we never break the baseline.
 *
 * Firefox <65 is the interesting case: it has `fetch`, but `Response.prototype.body` is missing, so
 * a naive feature test on `window.fetch` alone would hand those users a broken chat.
 */
export function supportsStreaming() {
  return (
    typeof window.fetch === "function" &&
    typeof window.AbortController === "function" &&
    typeof window.TextDecoder === "function" &&
    typeof window.ReadableStream === "function" &&
    typeof window.Response === "function" &&
    "body" in window.Response.prototype
  );
}

/**
 * Parses one raw event block (the text between two blank lines).
 *
 * Only `event:` and `data:` are recognised, which is all our contract uses. A block missing either
 * one is not an event we know how to act on, so it is skipped rather than guessed at.
 */
function parseEvent(raw) {
  let name = null;
  let data = null;

  raw.split("\n").forEach((line) => {
    if (line.indexOf("event: ") === 0) {
      name = line.slice(7);
    } else if (line.indexOf("data: ") === 0) {
      data = line.slice(6);
    }
  });

  if (name === null || data === null) {
    return null;
  }

  try {
    return { name: name, data: JSON.parse(data) };
  } catch (e) {
    return null;
  }
}

/**
 * Consumes an SSE response, invoking `onEvent(name, data)` per event.
 *
 * Chunk boundaries are meaningless — one read can contain half an event, several events, or both —
 * so bytes are accumulated until a blank-line boundary appears. `{ stream: true }` on the decoder
 * matters for the same reason one level down: a multi-byte UTF-8 character can be split across two
 * reads, and decoding each chunk independently would corrupt it.
 */
export async function readEventStream(response, onEvent) {
  const reader = response.body.getReader();
  const decoder = new TextDecoder();
  let buffer = "";

  for (;;) {
    const { done, value } = await reader.read();
    if (done) {
      break;
    }

    buffer += decoder.decode(value, { stream: true });

    let boundary;
    while ((boundary = buffer.indexOf("\n\n")) !== -1) {
      const event = parseEvent(buffer.slice(0, boundary));
      buffer = buffer.slice(boundary + 2);

      if (event) {
        onEvent(event.name, event.data);
      }
    }
  }
}
