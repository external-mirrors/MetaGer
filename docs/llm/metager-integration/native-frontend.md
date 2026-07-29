# Native Chat Frontend in MetaGer (supersedes the iframe design)

**Status: steps 1–5 implemented and locally verified.** The backend is headless, the chat route runs
on its own FPM pool (a 39-second generation verified surviving), the availability gate hides the
focus when the backend is down, the no-JS path works end to end — form POST, multi-turn context via
hidden fields, server-rendered Markdown — and the JS layer streams over that same route, verified in
a real Firefox against the running stack. Remaining: step 6 (model picker polish, file upload),
step 7 (encrypted conversation history).

This document replaces the iframe-embedding approach described
in [`foki-integration.md`](foki-integration.md) §2 and §5. Those sections are kept for history but
are marked superseded; §1 (Foki registration), §3 (Reverb balance updates) and §4 (interaction
model) still apply unchanged.

## Why the iframe design is being abandoned

Three separate problems, all rooted in the same decision:

1. **Failure modes are invisible until too late.** When `metager-chat` is unreachable, the iframe
   renders *something* — in practice MetaGer's own 404 page nested inside MetaGer's chrome, logo,
   footer and all. MetaGer has no way to know the backend is down before committing to render the
   chat focus, because the only party that talks to the backend is the browser, after the page has
   already shipped.
2. **Doubled authentication logic.** `metager-chat/src/lib/billing/auth.ts` re-implements the
   precedence rules of `app/Authentication/KeyAuthGuard.php` (cookie < header < query, plus the
   `Anonymous-Token-Key` webextension fallback) from scratch, in a second language. Every change to
   MetaGer's auth semantics has to be mirrored there by hand or the two silently diverge.
3. **Permanent visual drift.** Two frontends, two design systems, two sets of strings, two
   responsive breakpoint sets. `foki-integration.md` §5 already flagged this as needing "an explicit
   ongoing process" — which is a way of saying the architecture creates work that never ends.

The iframe also makes the UI clunky in ways users can see: a nested scroll context, a second boot
sequence before the first token, and a chat surface that visibly is not part of the page around it.

## The new shape

```
                    ┌─────────────────────────────────────────┐
  browser ────────► │ MetaGer (Laravel)                       │
                    │  · renders the whole chat UI as HTML    │
                    │  · authenticates via the key guard      │
                    │  · proxies chat calls onward            │──┐
                    │  · gates the focus on backend health    │  │
                    └─────────────────────────────────────────┘  │
                                                                 │ k8s service DNS (prod)
                                                                 │ compose network (dev)
                                                                 ▼
                                                  ┌──────────────────────────────┐
                                                  │ metager-chat (headless API)  │
                                                  │  · provider adapter          │
                                                  │  · billing (Redis+keymanager)│
                                                  │  · SSE token stream          │
                                                  └──────────────────────────────┘
```

`metager-chat` keeps everything it is genuinely good at — the provider abstraction, streaming, and
the billing reserve/settle cycle — and loses its frontend entirely. See
[`../metager-chat-service/architecture.md`](../metager-chat-service/architecture.md) for that side.

## Progressive enhancement is the governing constraint

MetaGer is built so client JS is **optional**: it extends the experience but never replaces it. Chat
benefits enormously from streaming, so the design has to deliver streaming to JS users without
making JS a requirement. This is feasible, and it drives nearly every decision below.

**Baseline (no JS).** The composer is an ordinary `<form method="POST">`. Submitting it hits a
Laravel route, which calls the backend, waits for the complete answer, and re-renders the full page
with the extended transcript. Slow — tens of seconds with no feedback — but correct, and every
feature that matters still works: sending, model selection, file upload, reading the answer.

**Enhanced (JS).** The same form, the same route. JS intercepts `submit`, POSTs with
`Accept: text/event-stream`, and the route streams tokens back instead of buffering. The DOM is
updated incrementally; no navigation happens.

One route, one auth path, one billing path, two response encodings chosen by content negotiation.
The no-JS path is not a bolted-on fallback — it is the same code path with buffering turned on.

### What the enhancement layer is built with

**No framework** — that constraint follows directly from the above, since a component-based
transcript renderer alongside the Blade one is the drift problem restated. It does *not* mean
hand-rolling everything: MetaGer's existing Laravel Mix/webpack pipeline already bundles npm
packages (`chart.js`, `macy`, `js-cookie`), so focused libraries are available and should be used
where they earn their place — a QR generator for cross-device history transfer being the clearest
example. Encryption needs no dependency at all; WebCrypto covers HKDF and AES-GCM natively.

### The two-renderer trap, and how to avoid it

The obvious failure here is rendering Markdown twice: once in PHP for the no-JS path and once in JS
during streaming. Two renderers means two escaping policies, two GFM table behaviours, and output
that subtly differs depending on whether JS is enabled.

**Resolution: PHP is the only canonical renderer.** `league/commonmark` is already in the dependency
tree (transitively, via Laravel's `Str::markdown()`) — promote it to an explicit `require` and
configure it with `html_input => 'strip'` and `allow_unsafe_links => false`, since model output is
untrusted text. During streaming, JS renders arriving deltas as **plain text** in a
`white-space: pre-wrap` container — a live preview, deliberately not Markdown. When the stream
finishes, the final SSE event carries the server-rendered HTML for the settled message, and JS swaps
it in. Both paths therefore display byte-identical final HTML, produced by exactly one renderer.

This also keeps the CSP story simple: `default-src 'self'`, no inline scripts, no client-side HTML
construction from model output beyond `textContent` assignment during the preview phase.

## Conversation state: stateless on both paths

No server-side transcript storage, matching the still-unimplemented opt-in model in
[`../metager-chat-service/storage-and-privacy.md`](../metager-chat-service/storage-and-privacy.md).

- **JS path:** the browser keeps the running transcript in memory and re-sends it each turn (exactly
  what the current implementation already does).
- **No-JS path:** the transcript round-trips through **hidden form fields**, serialized into the
  POST body and re-emitted on every render.

Neither path writes anything server-side, so this design introduces no new retention surface and
requires no decision about session persistence. A user can tamper with the hidden fields, which is
uninteresting: they can only misrepresent their own conversation to themselves, and they pay for the
tokens either way.

The practical limit is POST body size for very long no-JS conversations. `client_max_body_size 30M`
(nginx-default.conf:7) leaves an enormous margin — a 50-turn conversation is well under 100KB.

## Routing: the FPM constraint is real and must be solved explicitly

Proxying the stream through Laravel means a PHP-FPM worker is held for the entire generation. Three
verified constraints in the current config make this a blocker rather than a tradeoff:

| Constraint | Where | Effect on a streamed chat response |
|---|---|---|
| `request_terminate_timeout = 30` | [www_01_production.conf:6](../../../build/fpm/configuration/fpm/www_01_production.conf#L6) | **Hard-kills any generation past 30s in production** |
| `fastcgi_buffer_size 256k` / `fastcgi_buffers 32 256k` | [nginx-default.conf:146-147](../../../build/nginx/configuration/nginx-default.conf#L146-L147) | nginx batches the token stream; no visible streaming |
| `pm.max_children = 100` | [www_01_production.conf:7](../../../build/fpm/configuration/fpm/www_01_production.conf#L7) | Chat streams compete with all search traffic for the same 100 workers |

Note the first constraint applies to the no-JS path too — a buffered 45-second generation dies at 30
seconds just as surely as a streamed one. This is not avoidable by choosing not to stream.

**Required: a dedicated FPM pool for the chat message route.** Add a second pool (e.g. `[chat]`
listening on `:9001`) with `request_terminate_timeout = 0` and a bounded `pm.max_children` — call it
20 — so that long-lived chat requests can never starve the search pool, and so the aggressive
30-second timeout that protects search stays in place for search. Then add a matching nginx location
ahead of the generic `location ~ \.php$`:

```nginx
location ~ "^(/[^/]+)?/chat/message$" {
    try_files /index.php =404;
    fastcgi_pass localhost:9001;      # dedicated chat pool, no terminate timeout
    fastcgi_buffering off;            # required: otherwise nginx batches the SSE stream
    fastcgi_read_timeout 900;
    fastcgi_param SCRIPT_FILENAME $document_root/index.php;
    include fastcgi_params;
}
```

The existing `location ~ "^(/[^/]+)?/chat(/.*)?$"` proxy blocks
([nginx-default.conf:40-49](../../../build/nginx/configuration/nginx-default.conf#L40-L49),
[nginx-default-dev.conf:73-81](../../../build/nginx/configuration/nginx-default-dev.conf#L73-L81))
should be **removed**. With the frontend in MetaGer, the backend has no reason to be reachable from
a browser at all, and removing the route makes that structurally true rather than merely intended.

On the PHP side the proxy is `response()->stream()` wrapping a Guzzle request with
`['stream' => true]`, `flush()`ing each chunk. Guzzle is already available via Laravel's HTTP client.

**Verified.** A 39-second generation streamed end to end through nginx → the chat FPM pool →
`metager-chat` → OpenAI, delivering 1865 incremental delta events and a terminal `done`. Deltas
arrive spread over time rather than in one batched flush, confirming buffering is genuinely off at
all three hops. The same request on the `[www]` pool would have been killed at 30 seconds.

### CSRF: not available, and not needed

The original plan here said to keep this route inside CSRF protection. **That turned out to be
impossible, and unnecessary**, for reasons specific to how MetaGer is built:

- `bootstrap/app.php` removes `StartSession` from the `web` group — MetaGer sets no session cookie
  for ordinary browsing, deliberately. A CSRF token has nowhere to live, and leaving the middleware
  on throws `RuntimeException: Session store not set on request` rather than protecting anything.
  Introducing a session just for chat would be a real privacy cost for no gain.
- The attack it would prevent is already blocked: the `key` cookie is set `SameSite=Lax` by
  `metager-keymanager`, so a cross-site POST doesn't carry it at all. The header and query key paths
  need either JS (CORS-blocked cross-origin) or prior knowledge of the key.

So the route opts out of `ValidateCsrfToken` like every other route in `web.php`, and
`SameOriginRequest` middleware checks `Sec-Fetch-Site`/`Origin` as defense in depth.

Worth noting for anyone reading `bootstrap/app.php`: its `removeFromGroup('web', …)` call names
`VerifyCsrfToken`, which no longer matches the `ValidateCsrfToken` entry Laravel 12 actually
registers — so the group's CSRF middleware is still live despite appearances, which is why the
explicit opt-out is required.

### Do not apply `AuthenticationValidation` to this route

The resultpage route uses it, so it looks like the obvious thing to copy. It is not: that middleware
charges the **search** cost (`Searchengines::getSearchCost()`) and drives the anonymous-token
payment flow. On a chat message that bills a second, unrelated amount on top of the per-token charge
`metager-chat` already makes, and turns auth failures into HTML redirects to the startpage. The
controller authenticates through the key guard directly instead, which already covers the
cookie/header/query precedence *and* the `Anonymous-Token-Key` webextension case.

### Upstream addressing

Identical in shape to how `metager-chat` is addressed by nginx today, just moved into Laravel config:
`chat-master.chat:80` in production (k8s service DNS) and the compose network address in
development, both from a single `config/metager/chat.php` entry driven by an env var. No new
discovery mechanism.

## Health gating: never render a chat UI that cannot work

A small `App\Services\ChatBackend` service with an `isAvailable()` method, backed by the backend's
`GET /api/health`, with a **1-second timeout** and the result cached in Redis for ~15 seconds.

- `parse_available_foki()` ([Searchengines.php:23-60](../../../metager/app/Searchengines.php#L23-L60))
  gains a check so the `chat` focus is dropped from `available_foki` when the backend is down. It
  already special-cases engine-less foki, so this is a small extension of an existing branch — and
  because both Foki switchers loop over `available_foki`, the tab disappears from the nav with no
  template changes.
- The `fokus === "chat"` branch in
  [MetaGerSearch.php:49](../../../metager/app/Http/Controllers/MetaGerSearch.php#L49) renders a plain
  "chat is temporarily unavailable" notice inside normal chrome for anyone arriving by direct URL.

**Fail closed**: a timeout or error means unavailable. Hiding a working feature for 15 seconds is
much cheaper than the failure this design exists to eliminate.

The cost on the search hot path is one Redis read per request. The health call itself only fires on
cache miss, so at most once per 15 seconds per node.

## Interface redesign

The current UI is a bare `<select>`, a textarea, and undifferentiated message bubbles. The redesign
covers what a chat interface is actually expected to have.

### Model picker

Today: a plain `<select>` of display names, with descriptions in `title` attributes that mobile users
can never see, and no cost information at all — `/api/models` deliberately doesn't expose pricing
yet, which was reasonable before billing landed and is now just a gap.

Replacement: a `<details>`/`<summary>` disclosure containing one row per model — name, a
one-line plain-language description, a speed indicator, and **a real per-message cost estimate** in
the same units the header's balance widget uses, derived from the pricing table `/api/models` will
now expose. Selection is a `<input type="radio">` per row, inside the composer form.

`<details>` and radio inputs are pure HTML, so the redesigned picker works fully without JS. JS
upgrades it to a popover that closes on outside-click and reflects the choice in the composer.

This finally satisfies `foki-integration.md` §5's "plain-language capability/cost/speed
explanations" and the per-model cost indicator, both of which have been open since billing landed.

### Message affordances

Per assistant message: **copy**, **regenerate**, and the existing model tag. Per code block: **copy**
and **download**. All JS-only enhancements — without JS the text is still selectable, which is the
correct baseline — so they render only after JS boots, rather than appearing as dead controls.

Copy takes the **Markdown source**, not the rendered text: that is what is useful to paste anywhere
else, and it is what a no-JS user would get by selecting the text. Regenerate truncates the
transcript to just before that answer and re-sends, so it works on any assistant turn rather than
only the last one. Code-block downloads map the CommonMark `language-…` class to a file extension
and use a `Blob` object URL rather than a `data:` URI, which keeps `data:` out of the CSP's
navigation sources.

### File upload and download

Uploads are a genuine feature gap, and they interact with the stateless-transcript decision: hidden
form fields can carry text, but not a file the user attached three turns ago.

**Approach:** the file is uploaded with the message it belongs to (`<input type="file">` in the
composer form, multipart POST — no JS required). The backend stores the content in **Redis under a
random id with a 1-hour TTL** and returns the id. The transcript then carries the *id*, not the
content, so follow-up questions about an attached document work identically on both paths, and
nothing durable is written anywhere.

Downloads are the inverse and much simpler: a download control on code blocks and on the full answer,
implemented client-side as a `Blob`, JS-only, no server round-trip.

### Conversation history

Opt-in, end-to-end encrypted, and identified by a user-held recovery code that is deliberately
independent of the billing key — a key can be **shared between several people**, so keying history
off it would expose one person's conversations to everyone holding that key. Full design in
[`../metager-chat-service/storage-and-privacy.md`](../metager-chat-service/storage-and-privacy.md).

MetaGer-side surface:

- **Conversation list** — a drawer on narrow viewports, a panel on wide ones. Titles are decrypted
  client-side; the server only ever returns ciphertext.
- **History settings** — opt-in toggle, retention selector (worded as "delete if unused for…", since
  the window slides from last use and has no forever option), the current expiry date shown plainly,
  "use on another device" (QR + word list), "forget on this device", "delete all history", and
  export to Markdown/JSON.
- **Fragment import** — arriving at `/chat#h=<code>` imports a code and clears the fragment. URL
  fragments are never sent to the server, which is what makes QR transfer safe.
- **Proxy routes** — the conversation CRUD endpoints are ordinary non-streaming proxy calls, so they
  use the normal FPM pool, not the dedicated chat pool. Only `POST /chat/message` needs that.

This is the one part of the interface that is **JS-only**, and unavoidably so: key derivation,
encryption and QR rendering all require it. The baseline no-JS experience — an ephemeral
conversation that works completely — is unaffected.

Note this also finally resolves the URL/back-button item deferred in `foki-integration.md` §5, since
conversation switching is the navigable state that item was waiting for. `history.pushState` per
conversation, with the conversation id in the path — never the recovery code, which must stay in
localStorage and in fragments only.

### Layout

The viewport-fill model in
[chat.less](../../../metager/resources/less/metager/pages/resultpage/chat.less) is sound and survives —
it just applies to real DOM now instead of an iframe. `#chat-iframe`'s rules become rules on the
transcript container. The comment block in that file explaining the grid interaction with
`#resultpage-container` stays accurate and should be preserved.

Per the 360px requirement that applies to all MetaGer pages, the message list, composer, and model
picker each need an explicit narrow-viewport pass — with the significant advantage that this is now
the outer page's own responsive context, not a separately-responsive app inside an iframe with its
own virtual-keyboard and viewport-height quirks. That entire class of bug disappears with the iframe.

## Backend contract

Full detail in [`../metager-chat-service/architecture.md`](../metager-chat-service/architecture.md).
From MetaGer's side:

- `GET /api/health` — liveness, for the gate above.
- `GET /api/models` — catalog **including pricing** (new), cached in Redis for ~5 minutes.
- `POST /api/chat` — SSE token stream.
- `GET|PUT|DELETE /api/conversations[/:id]` — encrypted history blob store, non-streaming.
- Auth: MetaGer resolves the key via its own guard and forwards it as a header, alongside a shared
  secret so the backend can reject anything that didn't come through MetaGer — the same pattern
  `event_authorization` already uses in `config/metager/metager.php`. The backend's cookie/query
  parsing in `auth.ts` is deleted; it still calls keymanager, because billing needs that regardless.

The SSE event contract is **ours to define**, not the AI SDK's UI message stream protocol, since no
`useChat` client remains. A minimal contract — `delta`, `done` (carrying settled cost, model id, and
the server-rendered HTML), `error` — is easier to proxy through PHP and immune to `ai` package
protocol churn.

## Files touched (MetaGer side)

| File | Change |
|---|---|
| `resources/views/resultpages/results_chat.blade.php` | Replace iframe with real transcript + composer markup |
| `resources/views/resultpages/parts/chat/*.blade.php` | New: message, model picker, composer partials |
| `resources/less/metager/pages/resultpage/chat.less` | Retarget iframe rules onto real DOM; message/composer styling |
| `resources/js/chat/*.js` + `webpack.mix.js` | New enhancement bundle (streaming, copy, download, picker, history crypto + QR) |
| `lang/{locale}/chat.php` → `#chat-strings` data attributes | JS strings come from the same lang files as the markup, not a JS-side dictionary |
| `package.json` | QR generator dependency for cross-device history transfer |
| `app/Http/Controllers/ChatController.php` | New: message route, streaming proxy, Markdown render |
| `app/Http/Controllers/ChatHistoryController.php` | New: proxy for the encrypted conversation store (normal FPM pool) |
| `app/Services/ChatBackend.php` | New: health, model catalog, upstream addressing |
| `app/Searchengines.php` | Health gate in `parse_available_foki()` |
| `app/Http/Controllers/MetaGerSearch.php` | Unavailable-notice branch |
| `routes/web.php` | `POST /chat/message`, CSRF excluded (see below) |
| `app/Http/Middleware/SameOriginRequest.php` | New: session-free CSRF equivalent |
| `lang/{locale}/chat.php` | New: all chat UI strings, replacing `metager-chat/src/lib/strings.ts` |
| `build/fpm/configuration/fpm/*.conf` | Dedicated chat pool |
| `build/nginx/configuration/nginx-default{,-dev}.conf` | Chat FPM location; **remove** the `/chat` proxy blocks |
| `composer.json` | Promote `league/commonmark` to an explicit require |

## Sequencing

1. Backend goes headless (see the service-side doc) — its API surface barely changes, so this is
   mostly deletion plus the pricing addition to `/api/models`.
2. FPM pool + nginx routing. Verify a >30s generation survives in a production-shaped config before
   building UI on top of it, since that constraint invalidates the whole approach if unsolved.
3. `ChatBackend` service + health gating. Independently valuable and testable — it fixes the failure
   mode from the screenshot even before the UI moves.
4. No-JS path end to end: Blade transcript, form POST, PHP Markdown rendering. Ship-ready on its own.
   One constraint found while building it: the buffered path must **not** re-render through
   `MetaGer::createView()`, because that writes a `QueryLogger` entry — which would file every chat
   prompt into MetaGer's search query log. `ChatController::renderPage()` constructs the view
   directly instead, supplying the view data the shared chrome expects (`errors` in particular,
   since `parts/errors.blade.php` calls `sizeof($errors)` unguarded and nothing populates it without
   sessions).
5. JS enhancement layer: streaming, then affordances. `resources/js/chat/` — `index.js` (composer
   interception, streaming, stop, textarea growth), `sse.js`, `transcript.js`, `affordances.js`,
   `strings.js`. Three things were settled while building it:

   - **The hidden fields stay the single source of truth.** `Transcript` does not keep a private
     copy of the conversation; every change is written straight back into `messages[i][…]`. So if JS
     stops being involved at any point — a bundle fails to load, the tab is restored from bfcache,
     the user disables JS mid-conversation — the very next plain form submit continues the same
     conversation rather than starting a new one. It also means the JS path sends exactly the shape
     the no-JS path sends, so there is no second protocol to keep in sync.
   - **A query-string key has to be carried into the form action.** `KeyAuthGuard` accepts a key by
     cookie, header, *or* query parameter. The first two ride along on a POST by themselves; a query
     key lives on the page URL and the form's own action drops it, which left those users unable to
     chat on either path. The composer now appends it, the same way the proxy links in
     `layouts/result.blade.php` do.
   - **Stop keeps the partial answer, marked as unrendered.** The tokens are paid for, so discarding
     them is wrong, but they never went through the server's Markdown renderer and rendering them in
     JS would reintroduce the second renderer. They stay plain text in a `pre-wrap` container; the
     Markdown source goes into the hidden fields, so any later server round trip renders it properly.

   Feature-detection is a hard gate, not a nicety: `sse.js` checks `fetch`, `AbortController`,
   `TextDecoder`, `ReadableStream` *and* `Response.prototype.body` — Firefox <65 has `fetch` but not
   a readable body, so testing `window.fetch` alone would hand those users a broken chat. Copy
   buttons additionally require `navigator.clipboard`, which is absent on insecure origins (so they
   do not appear in local HTTP dev, by design). Whenever a check fails the form is left exactly as
   the server rendered it.
6. Model picker redesign and file upload.
7. Conversation history: the encrypted blob store and its Postgres migration first, then the crypto
   layer, then the list UI, then cross-device transfer. Deliberately last — it is the only part with
   no no-JS story, and everything before it ships without it.

Steps 3 and 4 each deliver user-visible value standalone, so this doesn't have to land as one commit.

## Risks

- **FPM worker exhaustion** is the main one, mitigated but not eliminated by the dedicated pool. The
  pool bounds the blast radius to chat itself: at 20 workers, the 21st concurrent chat message queues
  rather than degrading search. Whether 20 is the right number needs real usage data — it is a config
  value, cheap to change, and worth a Prometheus gauge on pool saturation from day one.
- **Losing the AI SDK's client-side machinery** (`useChat`) means reimplementing stream handling,
  abort, and error surfacing by hand. Bounded — roughly the SSE reader, an append loop, and an
  abort controller — and the no-JS requirement rules out `useChat` regardless, since a React
  transcript renderer alongside a Blade one is precisely the drift problem this redesign exists to
  end.
- **The no-JS experience is genuinely slow**: a 45-second blocking POST with no feedback. Acceptable
  for a fallback. If it needs improving later, chunked progressive HTML would let text appear as it
  generates without any JS — deliberately not in v1, because once the first byte is flushed the HTTP
  status is fixed and billing rejections can no longer be reported as a 402.
