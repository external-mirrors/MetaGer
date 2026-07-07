# MetaGer LLM Chat Feature — Planning Docs

This folder documents the plan for a new LLM chat feature: letting MetaGer's key-authenticated
users chat with LLMs from multiple providers (OpenAI, Anthropic, and an EU/privacy-friendly
option), manually switching between models via a plain-language picker UI. It is deliberately
kept separate from websearch for now.

**Status: design/planning stage. No application code has been written yet.**

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

## Phasing (for later reference — not started yet)

1. Create the `metager-chat` repo (sibling to `metager-keymanager`/`SafeBrowse`) and copy
   `metager-chat-service/*` into it as its own planning docs. Scaffold the service, provider
   adapter (OpenAI + Anthropic), and streaming — no billing, no UI yet.
2. Billing plumbing (Redis claims + keymanager discharge + pricing table) + the
   `parse_available_foki()` fix in the MetaGer repo.
3. Add the Mistral adapter (validates that adding a provider is cheap once the pattern is proven
   with two).
4. Foki/UI integration in the MetaGer repo (iframe wrapper, model picker, live balance push).
5. Chat storage & privacy (opt-in flag, client-id bootstrap, delete flow) — deliberately last.
6. (Future, separate effort) MCP `web_search` tool for search grounding.

Only after these docs are reviewed and solid do we move on to planning the interface in detail,
and only after that does implementation begin.
