# Open Questions

Genuinely unresolved items, deliberately not decided during the initial design pass. Each is
tagged with which project/doc it belongs to so it can be tracked and closed out over time.

## `metager-chat-service`

- **FX-rate staleness** — `usd_to_eur_rate` (used in the billing formula, see
  [`metager-chat-service/billing.md`](metager-chat-service/billing.md)) is a periodically-reviewed
  config constant, not fetched live. A stale rate under-margins slightly during EUR/USD swings;
  acceptable given the 2x margin's buffer, but worth monitoring once real usage data exists.
- **MCP transport for the first tool** — when the future `web_search` tool is added (see
  [`metager-chat-service/architecture.md`](metager-chat-service/architecture.md) §"Where MCP
  genuinely fits"), should it be implemented as a real MCP server (stdio transport) or as a plain
  AI-SDK-native tool function without MCP at all? A toss-up until there's a second/third tool, or
  a desire for tools to be independently versioned/deployed.
- **Mandatory retention ceiling** — should opted-in chat history have a maximum retention period
  regardless of user choice, as a privacy-by-design measure independent of explicit deletion (see
  [`metager-chat-service/storage-and-privacy.md`](metager-chat-service/storage-and-privacy.md))?
  A legal/product call, not an architecture one.

## Accepted, not open (listed here only to avoid re-litigating)

- **`createView()` vs. a dedicated controller** — **resolved**: woven into the existing
  `app/MetaGer.php::createView()` alongside the `bilder` branch (plus an early-return branch in
  `MetaGerSearch.php::search()`), not a dedicated controller. See
  [`metager-integration/foki-integration.md`](metager-integration/foki-integration.md) §2.

- **Concurrent balance drain** between reservation and settlement (see
  [`metager-chat-service/billing.md`](metager-chat-service/billing.md) "Accepted risk") is a
  known, accepted risk — not something to design a mitigation for in v1.
