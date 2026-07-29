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
- **Retention window lengths** — **resolved in shape, open in detail.** There is deliberately no
  keep-forever option: every set expires on a sliding window measured from last use, so nothing is
  retained after a user stops coming back. The offered lengths (30 / 90 days / 1 year / 3 years,
  defaulting to 90 days) are a first proposal and worth a product review — the sliding window makes
  long options much safer than they would otherwise be, so there is room to be generous. See
  [`metager-chat-service/storage-and-privacy.md`](metager-chat-service/storage-and-privacy.md).
  This supersedes the original "mandatory retention ceiling" question, which asked whether to
  override user choice with a hard cap; the sliding window answers it more precisely, since active
  users are never cut off and inactive ones always are.
- **Where the encrypted history store lives** — the schema is currently placed in `metager-chat`'s
  own Postgres, following the sibling-service isolation precedent. But under E2E it is a dumb blob
  store with no chat knowledge whatsoever, and MetaGer already has a database, migrations and
  backups, while `metager-chat` would be standing up Postgres for this one feature. Worth a
  deliberate second look before the migration is written; the counter-argument is that MetaGer's DB
  sits on the search hot path.
- **Claim hashes grow without bound** — found while verifying step 5. `releaseClaim()`
  (`src/lib/billing/claims.ts`) settles with `hincrbyfloat(hash, claimId, -amount)`, which leaves the
  field behind at `0` rather than deleting it, and `keyserver:claims:<key>` has no TTL. So every chat
  message a key ever sends adds one dead field, permanently, and `getClaimsTotal()` reads the whole
  hash on every request — a heavy user's pre-flight balance check gets slower forever. `HDEL` on
  settle (or a TTL on the hash) fixes it; correctness is unaffected either way, which is why this is
  a note rather than a blocker.
- **Set-id enumeration hardening** — a set id is a bearer credential derived from 128 bits of
  entropy, so guessing is infeasible, but `GET /api/conversations` still needs rate limiting and
  probably some monitoring for scanning behaviour. The specific limits are unset.

## `metager-integration` (native frontend)

All from [`metager-integration/native-frontend.md`](metager-integration/native-frontend.md).

- **Chat FPM pool size** — the design bounds the dedicated chat pool at 20 workers so chat can't
  starve the 100-worker search pool. That number is a guess: too low and concurrent chats queue
  behind each other, too high and the isolation is theatre. Needs real concurrency data, and a
  Prometheus gauge on pool saturation from day one to get it. Cheap to change (config only).
- **Attachment TTL** — **narrowed, not closed.** Uploads live in Redis for 1 hour so the stateless
  transcript can reference them by id across turns. Two of the three worries are now handled: the
  TTL *slides from last use*, so an active conversation never loses its document, and an expired
  reference produces a specific, localised "please attach it again" (a 410 emitted before the stream
  opens) rather than a silent failure. What remains is the genuinely open part — whether an hour of
  inactivity is the right window, and whether a user who does hit it should be prompted to re-upload
  in place instead of retyping the turn. Best answered against real behaviour.
- **Progressive HTML for the no-JS path** — deliberately out of v1 (once the first byte is flushed
  the status code is fixed, so a billing rejection can no longer be a 402). Worth revisiting if the
  no-JS blocking wait proves to be a real complaint rather than a theoretical one.

## Accepted, not open (listed here only to avoid re-litigating)

- **`createView()` vs. a dedicated controller** — **resolved**: woven into the existing
  `app/MetaGer.php::createView()` alongside the `bilder` branch (plus an early-return branch in
  `MetaGerSearch.php::search()`), not a dedicated controller. See
  [`metager-integration/foki-integration.md`](metager-integration/foki-integration.md) §2. The
  native-frontend redesign does not reopen this: the *page render* stays in `createView()`; the new
  `ChatController` exists only for the `POST /chat/message` streaming proxy, which is a genuinely
  separate concern from rendering a result page.

- **Frontend framework for the chat UI** — **resolved**: no framework. Forced by the
  progressive-enhancement requirement — a component framework would mean a second transcript
  renderer alongside the Blade one, recreating the exact two-codebase drift the redesign exists to
  eliminate. Losing `useChat` is the accepted cost. Note this rules out *frameworks*, not npm
  dependencies: MetaGer's existing Laravel Mix/webpack pipeline already bundles packages
  (`chart.js`, `macy`, `js-cookie`), so focused libraries — a QR generator for device transfer, for
  instance — are fair game.

- **Concurrent balance drain** between reservation and settlement (see
  [`metager-chat-service/billing.md`](metager-chat-service/billing.md) "Accepted risk") is a
  known, accepted risk — not something to design a mitigation for in v1.
