# MetaGer LLM Chat Feature — Planning Docs

This folder documents the plan for a new LLM chat feature: letting MetaGer's key-authenticated
users chat with LLMs from multiple providers (OpenAI, Anthropic, and an EU/privacy-friendly
option), manually switching between models via a plain-language picker UI. It is deliberately
kept separate from websearch for now.

**Status: implementation under way, ahead of the phasing order below.** The `metager-chat` service
(provider adapter, streaming, a real chat UI, and now billing) and the MetaGer-side Foki/UI
integration (steps 1, 2, and 4) are implemented and locally verified; only the Mistral adapter
(step 3) is not started yet — see the phasing section for the current per-step breakdown.

## Verdict

The feature is feasible with the existing architecture. Two corrections to the original framing
came out of the research:

- **MCP is not a multi-provider abstraction.** MCP (Model Context Protocol) standardizes how an
  LLM client connects to *tools and context sources* — it is not the right tool for switching
  between OpenAI/Anthropic/Mistral. Provider switching needs a separate unified completion-API
  adapter (see [`metager-chat-service/architecture.md`](metager-chat-service/architecture.md)).
  MCP's genuine fit is as the mechanism for exposing *tools* to the LLM later — e.g. a future
  `web_search` tool wrapping MetaGer's own search pipeline — which is explicitly out of scope for
  v1 but the architecture keeps the door open for it.
- **Chat billing cannot reuse `KeyUser.php`'s claim mechanism as-is.** Its claim id is a random
  `uniqid()` generated fresh per PHP object, with no way to persist it across a reserve-now /
  settle-later flow spanning two separate requests. See
  [`metager-chat-service/billing.md`](metager-chat-service/billing.md) for the chosen approach
  (direct-to-keymanager, bypassing Laravel entirely).

## Decisions already made (treat as settled, don't re-litigate)

- An unrelated `Perplexica` clone found on disk during research is **ignored entirely** — not a
  dependency, not reused as code.
- Web-search grounding (LLM calling MetaGer's own search as a tool, Perplexity/Perplexica-style)
  is **out of scope for v1**, but the architecture is designed so it's easy to add later via MCP
  tool-calling.
- Initial providers: **OpenAI + Anthropic + Mistral** (Mistral chosen as the EU-domiciled option —
  fits MetaGer's privacy positioning, has mature TypeScript/AI-SDK support. Aleph Alpha flagged as
  an even more values-aligned but currently less tooling-mature alternative worth a future look).
- **Model switching is manual, not automatic.** The UI shows available models with plain
  explanations of capability/cost/speed; the user explicitly picks and switches. There is no
  auto-routing logic that silently selects a model.
- **Billing goes directly from the new chat service to the keymanager**, not through Laravel.
- **UI integration uses an iframe**, not a bespoke Blade view + JS bundle, so the new chat
  service can own its entire frontend independently.
- **Accepted risk**: a key's balance could theoretically be drained by concurrent activity
  (parallel chat messages, or a chat message racing a websearch spend) between reservation and
  end-of-stream settlement, in rare cases making settlement fail after generation already
  happened. This is accepted as a risk worth taking — no incremental/partial settlement
  mechanism is planned to mitigate it.

## How these docs are organized

This is a two-project feature: changes to the existing MetaGer Laravel app, and a brand new
standalone service that doesn't have its own repo yet. The docs are split accordingly:

- **[`metager-integration/`](metager-integration/)** — documentation that stays in *this* repo
  permanently. Describes the Foki/UI changes needed in the existing Laravel app.
- **[`metager-chat-service/`](metager-chat-service/)** — documentation for the **new, not-yet-created**
  `metager-chat` service. Written so it can be copied wholesale into that repo once it's created
  (see phasing below, step 1).
- **[`open-questions.md`](open-questions.md)** — genuinely unresolved items, each tagged with
  which project/subfolder it belongs to.
- **[`future-considerations.md`](future-considerations.md)** — ideas explicitly out of v1 scope
  (e.g. an "Auto" model-selection mode), captured so they aren't lost.

## Phasing (for later reference)

Note: steps were not executed in the listed order — step 4 (Foki/UI integration) landed before
steps 2 and 3 below. The list itself hasn't been reordered since it's still a reasonable reference
for what remains.

1. Create the `metager-chat` repo (sibling to `metager-keymanager`/`SafeBrowse`) and copy
   `metager-chat-service/*` into it as its own planning docs. Scaffold the service, provider
   adapter (OpenAI + Anthropic), and streaming — no billing, no UI yet.
   **Done**: repo scaffold, CI/CD, Helm chart, the `/chat` basePath, the OpenAI + Anthropic
   provider adapter with streaming (`POST /api/chat`, `GET /api/models`), and now a real chat UI
   (message list, composer, model picker, locale handling for `en`/`de`) all exist and were
   verified locally (a real streaming call reached OpenAI's API end to end through the actual
   `/chat` route) — see `metager-chat`'s own `AGENT.md` for what's actually implemented vs. still
   design-only in `docs/planning/`.
2. **Done.** Billing plumbing (Redis claims + keymanager discharge + pricing table) — implemented
   directly in `metager-chat` (`src/lib/billing/*`, `config/billing.json`), no changes to
   `metager-keymanager` or the main Laravel app needed, per this doc's original verdict. Verified
   locally end to end: a real keymanager-created key was charged the real per-message cost after a
   real streaming completion, its reservation claim was released back to 0, and a low-balance key
   was rejected before any reservation or provider call. See `metager-chat`'s own AGENT.md and
   `docs/planning/billing.md` for details. `parse_available_foki()`'s fix (below) landed early, as
   part of step 4.
3. **Not started.** Add the Mistral adapter (validates that adding a provider is cheap once the
   pattern is proven with two).
4. **Done, done ahead of steps 2–3.** Foki/UI integration in the MetaGer repo — see
   [`metager-integration/foki-integration.md`](metager-integration/foki-integration.md) for the
   per-section implementation status. The `chat` focus is registered, the `parse_available_foki()`
   fix landed, the iframe wrapper with an auth gate and low-balance banner renders, and the nginx
   route exists. Not done: the seamlessness checklist items (`postMessage` auto-resize,
   back-button/URL sync, a passed-in theme handshake) and the per-model cost indicator (blocked on
   billing/step 2, since `/api/models` deliberately doesn't expose pricing yet).
5. **Not started.** Chat storage & privacy (opt-in flag, client-id bootstrap, delete flow) —
   deliberately last.
6. **Not started**, future/separate effort. MCP `web_search` tool for search grounding.
