# `metager-chat` — Architecture

> The service exists (repo scaffolded, provider adapter and billing implemented and verified
> end to end). This document describes its **target** shape, which differs from what is currently
> checked in: the service is being reduced to a headless API, and its Next.js frontend removed. Where
> it refers to "this service," that means `metager-chat`; "the main MetaGer app" refers to the
> existing Laravel monolith.

## Why a new, independent service (not Laravel code)

No MCP or LLM client ecosystem exists in the main MetaGer app's PHP dependency tree, and no
mature PHP MCP SDK exists in the broader ecosystem. The wider MetaGer system is already polyrepo:
two sibling Node/Express microservices exist as direct precedent —
`metager-keymanager` (the key/billing system of record) and `SafeBrowse` (remote browser
sessions) — each with its own repo, Dockerfile(s), Helm chart, GitLab CI pipeline, and Postgres,
connected to the main app over a shared Docker network locally / k8s service discovery in
production. `metager-chat` follows this exact shape.

**Node/TypeScript** is the implementation language: the Vercel AI SDK (provider adapter, see
below) and MCP client/tool tooling are most mature in TypeScript/Python, and TypeScript keeps the
service in the same ecosystem as its two sibling services.

## Headless: this service has no frontend

The original design had this service serve its own React/Next.js chat UI, which the main MetaGer app
embedded in an iframe. **That is abandoned** — see
[`../metager-integration/native-frontend.md`](../metager-integration/native-frontend.md) for the
reasoning (invisible failure modes when the backend is down, a second hand-maintained copy of
MetaGer's auth precedence, and permanent two-codebase visual drift). The main MetaGer app now renders
the entire chat interface itself, as server-rendered HTML with a JS enhancement layer.

So this service is a pure API: provider abstraction, streaming, billing, and an opaque store for
encrypted history. It is not reachable from a browser at all — MetaGer proxies every call.

## Repo shape (mirrors `metager-keymanager`/`SafeBrowse`)

```
metager-chat/
  src/app.ts                       — Express entrypoint, mirrors metager-keymanager/pass/app.js
  src/routes/                      — chat (SSE), models, health, files, conversations
  src/lib/providers/               — AI SDK model registry / adapter wiring
  src/lib/billing/                 — Redis claim + keymanager discharge logic
  src/lib/mcp/                     — MCP client/tool-registry seam (empty tool list in v1)
  config/models.json               — per-model pricing table (see billing.md)
  config/billing.json              — margin/FX constants (see billing.md)
  database/                        — Postgres schema/migrations for the encrypted history store
  chart/                           — Helm chart, modeled on metager-keymanager/chart
  .gitlab-ci.yml                   — modeled on SafeBrowse/.gitlab-ci.yml
  docker/app/Dockerfile            — Node LTS image, modeled on SafeBrowse/docker/app/Dockerfile
```

TypeScript stays; `lib/providers` and `lib/billing` carry over essentially unchanged, since neither
ever depended on Next.js. What goes is the framework wrapper around them.

### Migration from the current Next.js codebase

| Current | Target |
|---|---|
| `src/app/page.tsx`, `src/app/layout.tsx`, `src/app/globals.css`, `src/components/ChatApp.tsx` | Deleted — MetaGer owns the UI |
| `src/lib/strings.ts`, `src/lib/theme.ts`, `src/lib/locale.ts` | Deleted — become Laravel `lang/{locale}/chat.php` and MetaGer's existing theme handling |
| `src/app/api/*/route.ts` | Express route handlers |
| `next.config.mjs` (`basePath: "/chat"`) | Deleted — MetaGer owns the public URL space, this service serves at its own root |
| `next`, `react`, `react-dom`, `react-markdown`, `remark-gfm`, `@ai-sdk/react` | Dropped from `package.json` |
| `src/lib/billing/auth.ts` | Deleted — see Authentication below |
| `src/lib/{providers,billing,mcp}/*` (rest), `config/*.json` | Unchanged |

Dropping `basePath` is worth calling out: it existed purely so the Next app's assets resolved under
the `/chat` prefix nginx proxied to. With no assets and no browser-facing routes, the service should
serve at `/` and let MetaGer decide what the public URL looks like.

## API surface

| Endpoint | Purpose |
|---|---|
| `GET /api/health` | Liveness. MetaGer polls this (1s timeout, ~15s Redis cache) to decide whether the chat focus appears at all, so a broken backend hides the feature instead of rendering a dead UI. |
| `GET /api/models` | Model catalog **including pricing**. The earlier decision to withhold pricing predates billing; the per-model cost indicator in MetaGer's picker needs it. |
| `POST /api/chat` | SSE token stream. The only long-lived endpoint. |
| `POST /api/files` | Attachment upload: content into Redis under a random id, 1-hour TTL, id returned. Lets MetaGer keep its transcript stateless while follow-up turns still reference an attachment. **Takes JSON `{name, content}`, not multipart** — MetaGer owns the form, the decoding and the user's locale, so this service needs no multipart parser for its one upload route. The record is bound to the uploading key, so a leaked id is useless to anyone else, and the TTL slides on every read: a conversation that keeps citing a document keeps it alive. `lib/attachments.ts` also folds attachment text into the message *before* the token estimate — reserving against the un-expanded message would under-book a large file by orders of magnitude. An expired reference is a 410 emitted before the stream opens, where a status code still means something. |
| `GET\|PUT\|DELETE /api/conversations[/:id]` | Opt-in encrypted history — a dumb ciphertext blob store. This service can no more read stored conversations than MetaGer can. See [`storage-and-privacy.md`](storage-and-privacy.md). |

## Authentication: trust MetaGer, not the browser

The browser cannot reach this service — MetaGer proxies every call, and the nginx `/chat` route that
previously exposed it is removed. So `lib/billing/auth.ts`'s re-implementation of
`KeyAuthGuard.php`'s cookie/header/query precedence **is deleted**. MetaGer resolves the key with its
own guard — the single source of truth for what a valid key is, including the `Anonymous-Token-Key`
webextension case — and forwards it as a header, alongside a shared secret this service checks, the
same pattern `event_authorization` already uses in the main app's `config/metager/metager.php`.

Rejecting requests without that shared secret is what makes "not browser-reachable" enforced rather
than merely intended, and it should be middleware on every route except `/api/health`.

Billing is unaffected: the keymanager calls in `lib/billing/` stay exactly as they are, since balance
checks and discharge were never derivable from the cookie anyway.

**The key is not an identity.** It identifies a payer, not a person — a key may be shared between
several people, and rotates per-request for webextension users. Nothing in this service may key
user-visible state off it; that is why encrypted history uses a separate, client-held secret.

## Stream format

With no `useChat` client left, the AI SDK's UI message stream protocol has no consumer, and its
versioning becomes a liability rather than a convenience. This service defines its **own minimal SSE
contract** instead — easier to proxy through PHP, and stable across `ai` package upgrades:

```
event: delta   data: {"text": "..."}
event: done    data: {"model": "...", "cost": 12}
event: error   data: {"message": "Your MetaGer balance is too low for this message."}
```

MetaGer's proxy layer augments `done` with the settled message rendered by its own PHP Markdown
pipeline before forwarding it to the browser, so JS and no-JS users see byte-identical output from a
single renderer (see the native-frontend doc's "two-renderer trap"). This service neither renders nor
knows about HTML.

`error` deserves care: once the first `delta` has been flushed the HTTP status is already sent, so a
mid-stream failure can only be reported as an event. Billing rejections must therefore happen
*before* any token is emitted — which they already do, since access control precedes the provider
call (see [`billing.md`](billing.md)).

## Streaming design: SSE, not Reverb

Token streaming does **not** go through the main app's Laravel Reverb websocket server. Reverb is
used today for low-frequency, small, fire-and-forget notifications (a login happened, a balance
changed), broadcast over a public per-key channel. Token-by-token LLM streaming is a different
traffic shape entirely: high frequency, ordered, session-scoped, needing backpressure and
cancellation — and is exactly what every provider SDK already emits natively as SSE/chunked HTTP via
the AI SDK's `streamText`. Routing it through Reverb would add an extra Redis pub/sub + websocket hop
per token for no benefit.

Reverb is still reused, unchanged, for what it is good at: after a message settles, this service
calls the main app's existing `POST /api/event/key/update` endpoint to push a balance update — see
[`../metager-integration/foki-integration.md`](../metager-integration/foki-integration.md) §3.

### Buffering must be disabled at every hop

The stream now crosses two hops (browser → nginx → PHP-FPM → this service), and **any buffering
anywhere collapses streaming into a single delayed flush**. Each hop needs explicit treatment:
`fastcgi_buffering off` on the chat location in nginx, output buffering disabled and an explicit
`flush()` per chunk in Laravel, and no response buffering in Express. This is worth verifying
end to end with a slow generation rather than assumed — a buffered stream still *works*, it just
silently stops being a stream, which is easy to miss until someone notices first-token latency.

## PHP-FPM: deliberately not bypassed

The original design routed the stream directly to this service specifically to avoid tying up a
PHP-FPM worker for the length of a generation. **That is reversed**: chat requests now go through
Laravel, so the worker cost is paid on purpose.

The concern behind the original rationale was real, but the progressive-enhancement requirement makes
it unavoidable rather than optional — a no-JS user's buffered POST occupies a worker for the full
generation whether or not anything streams. Two production settings make this a blocker to solve
rather than a cost to absorb: `request_terminate_timeout = 30` would hard-kill any generation past 30
seconds, and the 100-worker pool is shared with all search traffic.

The mitigation is a **dedicated, bounded FPM pool** for the chat route with its own timeout, so chat
can never starve search. Exact nginx/FPM configuration lives in
[`../metager-integration/native-frontend.md`](../metager-integration/native-frontend.md).

## Local dev wiring

The `chat` service block already exists in the main MetaGer app's `docker-compose.yml`, on the shared
`metager` network with a static dev IP (192.168.5.202), following how `metager-keymanager` (…5.100)
and `SafeBrowse` (…5.200-201) are wired. That stays.

What changes: the `location ~ "^(/[^/]+)?/chat(/.*)?$"` proxy blocks in
`build/nginx/configuration/nginx-default{,-dev}.conf` are **removed**, since the browser no longer
talks to this service. Laravel addresses it directly instead, from a single config entry —
`chat-master.chat:80` in production (k8s service DNS), the compose address in development. Same
discovery mechanisms as before, moved one layer up.

## Provider abstraction: Vercel AI SDK

`lib/providers/registry.ts` defines a `modelId → { provider, providerModelId }` mapping (backed by
`config/models.json`, see billing.md) and wraps `streamText({ model, messages, tools })` from the
`ai` package, using `@ai-sdk/openai`, `@ai-sdk/anthropic`, `@ai-sdk/mistral` for the three initial
providers. This is the actual "switch between providers" mechanism: one call shape, one
streaming/usage-reporting contract (`result.usage.promptTokens`/`completionTokens` normalized
across providers) regardless of which provider is selected. Adding a fourth provider later means
adding one adapter package + one registry entry, not touching call sites.

Note this survives the frontend removal untouched — only `toUIMessageStreamResponse()` at the very
edge is replaced by writes into the SSE contract above. The AI SDK remains the right choice for the
server half; it was only its React half that the redesign discards.

**Provider set**: OpenAI + Anthropic (required) + **Mistral** as the EU/privacy-friendly third
option — EU-domiciled (France), official TS SDK, first-class AI SDK support, competitive
function-calling/tool-use support (relevant once MCP tools are added). Aleph Alpha (German,
Heidelberg, arguably even more values-aligned) was considered but has thinner tooling/AI-SDK
support today — flagged as a follow-up spike, not a v1 blocker.

**Model switching is manual**: MetaGer's picker lists available models with plain-language
capability/cost/speed explanations and a real per-message cost estimate. There is no server-side
logic that automatically selects or routes between models on the user's behalf. This service's only
role is to expose the catalog and pricing that make an informed choice possible.

## Where MCP genuinely fits (and where it doesn't)

MCP standardizes how a client connects to **tools and context sources** — it does not replace or
implement the provider adapter above, and should not be used to talk to OpenAI/Anthropic/Mistral
directly. Its real fit here is as the mechanism for exposing **tools** to the LLM, most notably a
future `web_search` tool wrapping the main MetaGer app's own search pipeline — explicitly out of
scope for v1, but this is the reason MCP is part of the architecture at all.

Concretely: the Vercel AI SDK ships `experimental_createMCPClient`, which converts an MCP server's
tool list into the same `tools` parameter shape `streamText` already accepts — so attaching an MCP
tool server later requires no rearchitecture of the provider layer.

**For v1: no MCP server is deployed.** Standing up a transport for zero tools is pure overhead.
`lib/mcp/toolRegistry.ts` defines the seam as an empty/typed `tools: Record<string, Tool>` object
passed into `streamText`. When `web_search` is added later, it can be implemented either as a
small in-process MCP server (stdio transport — simplest, no new network surface) or as a plain
AI-SDK-native tool function without MCP at all. **Left open** (see
[`../open-questions.md`](../open-questions.md)) whether the first tool should use real MCP
transport or just a native tool function — a toss-up until there's a second/third tool, or a
desire for tools to be independently versioned/deployed.

One consequence of the SSE contract above: tool calls have no representation in it yet. When the
first tool lands, the contract needs a `tool` event (or equivalent) and MetaGer needs UI for it. Not
a problem to solve now, but worth knowing the contract is deliberately minimal rather than complete.
