# `metager-chat` — Architecture

> This document describes the **new, not-yet-created** `metager-chat` service. It's written to be
> copied into that service's own repo once created (see the parent
> [`README.md`](../README.md)'s phasing). Where it refers to "this service," that means
> `metager-chat`; "the main MetaGer app" refers to the existing Laravel monolith.

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

## Repo shape (mirrors `metager-keymanager`/`SafeBrowse`)

```
metager-chat/
  app.js / server entrypoint       — mirrors metager-keymanager/pass/app.js
  routes/                          — HTTP + streaming endpoints, model catalog endpoint
  lib/providers/                   — AI SDK model registry / adapter wiring
  lib/billing/                     — Redis claim + keymanager discharge logic
  lib/mcp/                         — MCP client/tool-registry seam (empty tool list in v1)
  config/models.json               — per-model pricing table (see billing.md)
  database/                        — Postgres schema/migrations, only if storage is opted into
                                      (see storage-and-privacy.md)
  chart/                           — Helm chart, modeled on metager-keymanager/chart
  .gitlab-ci.yml                   — modeled on SafeBrowse/.gitlab-ci.yml
  docker/app/Dockerfile             — Node LTS image, modeled on SafeBrowse/docker/app/Dockerfile
```

This service serves **both its own frontend and backend** — not just an API. The frontend is the
chat UI (composer, streaming message rendering, manual model picker with plain-language
explanations) that the main MetaGer app embeds via an iframe (see
[`../metager-integration/foki-integration.md`](../metager-integration/foki-integration.md)).
Recommend a framework like Next.js, since it pairs directly with the Vercel AI SDK's React chat
hooks and serves frontend+backend from one deployable.

## Local dev wiring (in the main MetaGer app's repo)

Add a `chat` service block to the main MetaGer app's `docker-compose.yml`, on the same `metager`
Docker network so it can reach the shared Redis by hostname (see billing.md), following how
`metager-keymanager`/`SafeBrowse` are wired in today (static dev IPs in that subnet, referenced
from `build/nginx/configuration/nginx-default-dev.conf`).

## Provider abstraction: Vercel AI SDK

`lib/providers/registry.ts` defines a `modelId → { provider, providerModelId }` mapping (backed by
`config/models.json`, see billing.md) and wraps `streamText({ model, messages, tools })` from the
`ai` package, using `@ai-sdk/openai`, `@ai-sdk/anthropic`, `@ai-sdk/mistral` for the three initial
providers. This is the actual "switch between providers" mechanism: one call shape, one
streaming/usage-reporting contract (`result.usage.promptTokens`/`completionTokens` normalized
across providers) regardless of which provider is selected. Adding a fourth provider later means
adding one adapter package + one registry entry, not touching call sites.

**Provider set**: OpenAI + Anthropic (required) + **Mistral** as the EU/privacy-friendly third
option — EU-domiciled (France), official TS SDK, first-class AI SDK support, competitive
function-calling/tool-use support (relevant once MCP tools are added). Aleph Alpha (German,
Heidelberg, arguably even more values-aligned) was considered but has thinner tooling/AI-SDK
support today — flagged as a follow-up spike, not a v1 blocker.

**Model switching is manual**: the frontend shows a model picker listing available models with
plain-language capability/cost/speed explanations. There is no server-side logic that
automatically selects or routes between models on the user's behalf.

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

## Streaming design

**Direct HTTP chunked/SSE from this service straight to the browser** — not routed through the
main MetaGer app's Laravel Reverb websocket server. Reverb is used today for low-frequency,
small, fire-and-forget notifications (a login happened, a balance changed), broadcast over a
public per-key channel. Token-by-token LLM streaming is a different traffic shape entirely: high
frequency, ordered, session-scoped, needs backpressure/cancellation — and is exactly what every
provider SDK already emits natively as SSE/chunked HTTP via the AI SDK's `streamText`. Routing it
through Reverb would add an extra Redis pub/sub + websocket hop per token for no benefit.

This service's chat/completion endpoint needs the same nginx treatment the main app's existing
long-lived-connection routes get (`proxy_read_timeout` raised well beyond typical request
timeouts, e.g. to match the VNC-streaming precedent's `900s`) **plus `proxy_buffering off`**
(not currently needed anywhere else in the main app's nginx config, since nothing else there
streams SSE/chunked text) so nginx doesn't batch the token stream before flushing it to the
browser. This nginx configuration lives in the main app's repo — see
[`../metager-integration/foki-integration.md`](../metager-integration/foki-integration.md).

Reverb is still reused, unchanged, for what it's already good at: after a message settles, this
service calls the main app's existing `POST /api/event/key/update` endpoint to push a balance
update — see foki-integration.md §3.

## PHP-FPM bypass rationale

The iframe's page route and this service's streaming API route are both proxied directly to
`metager-chat`, never through the main app's `fpm` container. A chat SSE stream held open for tens
of seconds per message would otherwise tie up a PHP-FPM worker (a pool sized for fast
search-request/response cycles) for the whole generation.
