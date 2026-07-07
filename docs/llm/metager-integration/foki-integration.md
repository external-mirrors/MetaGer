# Foki / UI Integration (MetaGer Laravel app)

Scope: this document only covers changes to the existing MetaGer Laravel monolith
(`/home/dominik/code/arbeit/MetaGer/metager`). It assumes `metager-chat` (the new service, see
[`../metager-chat-service/`](../metager-chat-service/)) exists and serves its own frontend at some
URL. It does **not** cover that service's internals.

## 1. Register `chat` as a real Foki entry

Foki are data-driven from `config/sumas.json`'s `foki` object (each entry: `display-name`, `main`,
`sumas`), filtered per-locale into `available_foki` by `app/Searchengines.php::parse_available_foki()`,
and resolved per-request by `app/SearchSettings.php` (`fokus` property).

There are two existing precedents for a non-standard focus:
- **`bilder` (images)** stays inside the config-driven system and the normal page chrome, just
  branching to different templates.
- **`maps`** opts out of the config-driven system entirely — it has no `sumas.json` entry, is
  hardcoded into the nav loops (`resources/views/index.blade.php` lines ~23-26,
  `app/SearchSettings.php` line ~71's whitelist, `parts/foki.blade.php`), and immediately
  redirects to an entirely separate external app.

**Recommendation: follow the `bilder` precedent, not `maps`.** Chat should stay on `metager.de`,
share login/header state, and appear/disappear per-locale via ops config without code changes —
exactly what the config-driven path already gives every other focus for free. `maps` opted out
specifically because it redirects to a wholly separate domain; chat does not need to.

Add to `config/sumas.json`'s `foki` object:
```json
"chat": { "display-name": "index.foki.chat", "sumas": [] }
```
And add a translated label to each `lang/{locale}/index.php`'s `foki` array, e.g.
`'chat' => 'Chat'` (mirroring the existing `'web' => 'Web'`, `'bilder' => 'Images'` entries).

### Required fix: `parse_available_foki()` silently drops foci with no engines

Verified by reading `app/Searchengines.php` (lines 23-53):

```php
private function parse_available_foki()
{
    ...
    $foki = [];
    foreach ($this->sumas->foki as $fokus => $fokus_data) {
        foreach ($fokus_data->sumas as $fokus_engine) {
            ...
            $foki[] = $fokus;
            break;
        }
    }
    return $foki;
}
```

The `$foki[] = $fokus` append only happens **inside** the per-engine inner loop. A focus with an
empty `sumas` array (like the `chat` entry above) produces zero iterations of the inner loop, so
it is silently excluded from `available_foki` — and therefore never appears in either Foki
switcher (`resources/views/index.blade.php`'s `foki-switcher` loop, `resources/views/parts/foki.blade.php`),
despite being correctly declared in `sumas.json`.

Fix (small, reusable — benefits any future non-search focus, not just chat):
```php
foreach ($this->sumas->foki as $fokus => $fokus_data) {
    if (empty($fokus_data->sumas)) {
        $foki[] = $fokus;
        continue;
    }
    foreach ($fokus_data->sumas as $fokus_engine) {
        ...
    }
}
```

No change needed in `app/SearchSettings.php` (line ~71's whitelist is
`array_merge(array_keys($sumasJson->foki), ["maps"])`, which already covers any real
`sumas.json` key including `chat`), and no change needed in the Blade switcher templates
themselves — once `chat` is a real, available focus, it appears in those existing loops
automatically, unlike `maps` which needed literal hardcoded blocks in two templates because it
deliberately isn't a real `sumas.json` entry.

## 2. Layout: iframe inside shared chrome, not a bespoke Blade view

Two layout mechanisms already exist:
- The `bilder` pattern: custom Blade view + own JS bundle, still rendered inside the shared
  header/searchbar/foki-nav/sidebar/footer chrome (`layouts/resultPage.blade.php` /
  `layouts/researchandtabs.blade.php`).
- `$suspendheader` (`layouts/resultPage.blade.php` ~line 94): strips the entire
  header/searchbar/foki-nav block, used today for embed/widget output modes.

**Recommendation: keep the shared chrome (reject `$suspendheader`), but instead of a bespoke Blade
view + JS bundle, render a thin wrapper containing an `<iframe>`.** The focus switcher and the
live key-balance widget in the header are *more* relevant during chat than during search (money
is being spent per message), not less — stripping them via `$suspendheader` would remove the very
things the feature needs visible.

Concretely:
- Add a `fokus === "chat"` branch (next to the existing `bilder` branch, `app/MetaGer.php`
  `createView()` ~lines 159-267) or a small dedicated route/controller (see
  [`../open-questions.md`](../open-questions.md) — this choice is deliberately left open) that
  renders a new, mostly-static Blade view, e.g. `resources/views/resultpages/resultpage_chat.blade.php`,
  containing just an `<iframe src="...">` pointed at the chat service's frontend path, inside the
  normal `layouts/resultPage.blade.php` chrome.
- Add an early branch in `app/Http/Controllers/MetaGerSearch.php::search()` (next to the existing
  `fokus === "maps"` special-case around line ~41-43) for `fokus === "chat"` — unlike `maps`,
  this renders the wrapper view directly rather than issuing a redirect.
- **This branch must gate on authentication before rendering the iframe**, not rely on
  `metager-chat` to reject an unauthenticated request after the fact. The rest of MetaGer already
  hides the entire Foki switcher on the startpage for logged-out users
  (`Auth::guard("key")->user() !== null` in `resources/views/index.blade.php`), so a user without a
  key has no discoverable path to `?focus=chat` today — but direct URL navigation would still hit
  it. The chat branch should check `Auth::guard("key")->user()` the same way and, if absent, show
  MetaGer's existing "get a key" prompt/flow instead of an iframe that will only fail once loaded.
  A logged-in key with **zero or very low balance** is a softer version of the same problem: worth
  surfacing as a banner above the iframe (using the existing `KeyState` tiering,
  `app/Authentication/KeyState.php`) so the user sees "your balance is low, top up here" before
  typing a message that will just get rejected — rather than only failing at send-time inside the
  iframe (see [`../metager-chat-service/billing.md`](../metager-chat-service/billing.md) for that
  rejection).
- `<body class="{{ fokus }}">` (already present in `layouts/resultPage.blade.php` ~line 80) gives
  free CSS scoping (`body.chat { ... }`) for iframe sizing (e.g. full-height layout) without a new
  JS bundle.
- **No new entry needed in `webpack.mix.js`** — this is the main simplification vs. the `bilder`
  pattern. The chat service owns its entire frontend independently; Laravel's job shrinks to
  rendering the iframe wrapper and nothing more.

### Same-origin routing (why auth "just works")

The iframe's `src` must be same-origin (e.g. `https://metager.de/chat-app/...`) via an nginx route
proxying straight to the chat service — following the exact existing pattern used for
`/keys` → `metager-keymanager` and `/proxy` → `SafeBrowse`
(`build/nginx/configuration/nginx-default-dev.conf` lines ~62-67 and ~113-120):
```
location ~ "^(/[^/]+)?/keys(/.*)?$"  { proxy_pass http://192.168.5.100:3000; }
location ~ "^(/[^/]+)?/proxy(/.*)?$" { proxy_pass http://192.168.5.200:3001; }
```
A new block, e.g. `location ~ "^(/[^/]+)?/chat-app(/.*)?$" { proxy_pass http://<chat-service>; }`,
should be added analogously. Because it's same-origin, the browser automatically sends the
existing `key` cookie (and any session state) into the iframe with no new cross-origin or
token-passing mechanism required — the chat service reads the same cookie/header MetaGer's own
guard stack already relies on (see
[`../metager-chat-service/billing.md`](../metager-chat-service/billing.md) for how the chat
service uses this for billing).

This same nginx file already has precedent for long-lived streaming connections needing special
timeout/buffering treatment (the VNC-streaming blocks, `proxy_read_timeout 900s`); the chat
service's own streaming API route (not the iframe page route itself, but its underlying
chat/completion endpoint) will need the same treatment — see
[`../metager-chat-service/architecture.md`](../metager-chat-service/architecture.md).

## 3. Reused, unmodified: live balance updates via Reverb

No new Laravel code is needed for the balance widget to update live as a user chats. The chat
service calls the **existing** `POST /api/event/key/update` route
(`app/Http/Controllers/EventController.php`, protected by the `auth.events` middleware and the
`event_authorization` bearer secret already in `config/metager/metager.php`) after each message
settles, exactly as any other external app is already expected to do per that endpoint's existing
purpose. The browser's existing balance widget, already subscribed to the per-key Reverb channel,
picks up the `KeyChanged` event automatically.

## 4. Interaction model (different from every other focus)

Every existing focus is stateless request/response: `?eingabe=...&focus=X` → one page render.
Chat is a stateful multi-turn conversation, so the model differs:

- The **first** message is typed into the normal search box with `focus=chat` selected — exactly
  like starting a search in any other focus — and triggers the page load described in §2 (the
  iframe wrapper). The initial query can be passed into the iframe's `src` as a parameter so the
  chat app can auto-send it as the first turn. Navigating to the chat focus **without** typing a
  query first (e.g. clicking "Chat" directly in the Foki switcher/startpage nav) must also work
  cleanly — it should land on an empty composer inside the iframe, not error out or require a
  query param to be present.
- **All subsequent turns** are handled entirely within the iframe, by the chat service's own
  frontend talking directly to its own streaming API — no further full-page Laravel requests.

This should be called out explicitly when the interface is planned in detail, since it's a
meaningfully different navigation/state model than the rest of MetaGer.

## 5. Model picker UX expectation

The model picker itself (list of available models with plain-language capability/cost/speed
explanations, manual switching, no auto-routing) lives entirely inside the chat service's own
frontend (inside the iframe) — it is not a Laravel/Blade concern. From the MetaGer-integration
side, the only expectation to carry into interface planning is that the iframe's look and feel
(fonts, color scheme, light/dark mode) should be made to feel consistent with the surrounding
chrome rather than like an embedded foreign app — likely via theme information passed into the
iframe `src` as a query parameter. Left for the interface-planning phase to work out in detail.

### Seamlessness checklist (must be addressed in interface planning, not assumed for free)

A naive `<iframe src="...">` will feel like a bolted-on widget, not part of MetaGer, unless these
are deliberately designed for:

- **First-message latency**: a full Laravel page load followed by the iframe app's own boot and
  *then* its first request to start generating is at least two sequential load steps before
  anything streams — noticeably slower than a native chat app's instant response to the first
  message. Worth measuring and possibly optimizing (e.g. pre-warming the iframe, skeleton states)
  once real UI exists.
- **Visual drift over time**: this is a genuinely separate frontend codebase from MetaGer's
  Blade/vanilla-JS stack. Matching fonts/colors/spacing/dark-mode at launch (via passed-in theme
  params) doesn't keep them in sync afterward — a MetaGer redesign won't automatically propagate,
  and vice versa. Needs an explicit ongoing process (shared design tokens, or a recurring review),
  not a one-time effort.
- **No visible iframe "box"**: iframes have their own scroll context and fixed size by default.
  Needs `postMessage`-based auto-resize to content height and no internal scrollbar, so the whole
  page scrolls together rather than the chat area reading as an embedded widget.
- **URL / back-button / bookmarking**: navigating within the chat app (switching conversations,
  opening settings) should be reflected in the browser's address bar and support back/forward,
  via the parent page listening for `postMessage` and using the History API — otherwise refresh
  and back-button behavior will feel broken relative to the rest of MetaGer.
- **Locale**: the iframe `src` must carry the current locale explicitly (MetaGer uses
  `mcamara/laravel-localization`); left unaddressed, the chat app will default to one language
  regardless of what the rest of the page shows. **Decided** (not yet implemented — no chat UI
  exists yet to translate): pass it as a `?locale=` query param using Laravel's already-resolved
  locale, deliberately *not* derived from the URL path the way `metager-keymanager` does it — see
  `metager-chat/docs/planning/locale-awareness.md` for the reasoning (iframe context means the
  iframe's own URL is never bookmarked, unlike keymanager's directly-navigated pages).
- **Mobile**: a separately responsive app inside an iframe needs its own explicit mobile testing
  pass (virtual-keyboard resize behavior, viewport height quirks are known iframe trouble spots),
  not just reliance on the outer page already being mobile-friendly.

Same-origin routing (auth) and keeping the outer chrome visible (§2 above) are already handled by
this design — the items above are the remaining gap between "technically embedded" and "feels like
one product."

Since billing is transparent per-model (see
[`../metager-chat-service/billing.md`](../metager-chat-service/billing.md)'s pricing table), the
picker should show a real ballpark cost indicator per model (not just qualitative
"cheap"/"expensive" language) so the "explains available models" requirement actually lets users
make an informed cost/quality tradeoff — e.g. an approximate MetaGer-token cost per typical
message, derived from `models.json`. Each assistant message in the transcript should also be
tagged with which model actually answered it, since a conversation may span several model
switches.
