# `metager-chat` — Billing / Metering

> See the note at the top of [`architecture.md`](architecture.md) about this document's home
> once `metager-chat` becomes its own repo.
>
> **Implemented and verified locally** — see `metager-chat`'s own `docs/planning/billing.md` (same
> content, that repo's copy carries the current status note) and its `AGENT.md`.

## Why this can't reuse the main app's `KeyUser.php` claim mechanism

Verified by reading `app/Authentication/KeyUser.php` in the main MetaGer app:

```php
public function __construct(string $key)
{
    $this->id = uniqid('key_user', true);
    $this->key = $key;
    ...
}
```

`KeyUser`'s claim id (`$this->id`) is a random `uniqid()` generated fresh **per PHP object**, i.e.
per request. Today's websearch billing works because `authorize()` (reserve) and `makePayment()`
(commit) are always called on the *same* `KeyUser` instance within a *single* Laravel request
(`AuthenticationValidation::handle()`: `$user->authorize(...) && $user->makePayment(...)`).

Chat billing needs **reserve-now / settle-later**, potentially many seconds apart (however long
generation takes) and necessarily across *separate* HTTP calls/processes — a naive reuse of
`KeyUser` across two requests would silently create two unrelated claim ids, and settlement would
find no claim to release. Fixing this would require refactoring `KeyUser`'s constructor to accept
an explicit claim id, plus building a new authenticated protocol for `metager-chat` to call two
new Laravel endpoints. **Decision: don't do this.** Instead, `metager-chat` implements the same
Redis claim *convention* directly in Node, and talks to the keymanager service directly for the
actual money movement — **no changes needed anywhere in `metager-keymanager` or the main Laravel
app's `KeyUser.php`.**

This mirrors an existing precedent exactly: `SafeBrowse/app/ws/AuthWrapper.js` already does
`checkBalance()` (`GET /key/:key`) before starting a paid session, then `authenticateAndPay()`
(`POST /key/:key/discharge`) once the session actually starts — a minimal check-then-commit
contract against the keyserver that any service can call directly, entirely independent of the
main app's Laravel guard/middleware stack.

## Two-phase per-message billing

Unlike websearch's flat, known-in-advance cost (computed once, authorized-and-paid in a single
request), LLM cost is only known **after** the provider responds, and varies a lot per message.

1. **Pre-flight reservation** (before starting the stream): take the requesting key from the header
   MetaGer forwards — MetaGer resolves it with its own guard, so this service no longer parses
   cookies or query parameters itself (see
   [`architecture.md`](architecture.md) §"Authentication") — compute a conservative worst-case
   ceiling for *this message* —
   `(estimated_prompt_tokens × price_in + max_output_tokens × price_out) × margin`, converted to
   MetaGer decitokens (see pricing formula below) — and reserve that amount as a Redis claim.
2. **Settlement** (after the stream ends): the provider's reported `usage`
   (prompt/completion tokens, normalized by the AI SDK across providers) is run through the same
   pricing formula for the *actual* cost, which is discharged for real; the difference between the
   reservation and the actual charge is released as part of settlement.

### Reservation mechanics

Implement the same convention `KeyUser.php` uses, directly in `lib/billing/claims.ts`, against the
**same shared Redis instance** the main MetaGer app already uses (reachable via the shared Docker
network / same `REDIS_HOST`/`REDIS_PORT`/`REDIS_PASSWORD` config) — this matters so chat
reservations correctly interact with concurrent websearch claims on the same key, avoiding
double-spend across the two billing paths:

- Reserve: `HINCRBYFLOAT keyserver:claims:{key} {message_uuid} {amount}` +
  `HEXPIREAT keyserver:claims:{key} <ttl> {message_uuid}`.
- Release/settle: `HINCRBYFLOAT keyserver:claims:{key} {message_uuid} -{amount}`.

The claim id is simply a UUID `metager-chat` generates itself at reservation time and keeps for
the duration of that one message — there is no cross-process id-matching problem since the same
Node process/request context holds it from reservation through settlement.

**TTL as a safety net**: reservation claims get a Redis TTL sized to the worst-case generation
time for the largest supported model/`max_output_tokens` (e.g. 5 minutes, padded). If
`metager-chat` crashes or the connection drops mid-stream before settlement, the claim simply
expires — MetaGer forgoes that one message's revenue rather than risking a stuck reservation or a
double charge.

### Real money movement: existing, unmodified keymanager endpoints

- `GET /key/:key` — balance check (used to size the pre-flight reservation and to reject
  insufficient-balance requests, see below).
- `POST /key/:key/discharge` — the real charge, called once at settlement with the actual computed
  amount.

Both already exist in `metager-keymanager/pass/routes/api.js` and require no changes.

**Setup detail not covered elsewhere**: `metager-chat` needs its own bearer credential to call
these endpoints, provisioned the same way the main app's `KeyUser.php` has one
(`config("metager.metager.keymanager.access_token")`) and the same way `SafeBrowse` must already
have one for its own direct `AuthWrapper.js` calls. This is a keymanager-side credential/config
step to do during phase 1 scaffolding, not a design question — flagging it here only because none
of the other docs mention it explicitly.

## Access control before any billing happens

`metager-chat` must reject any request that doesn't carry a valid key or `Anonymous-Token-Key`
outright — there is no "serve it anyway, unauthenticated" case to design for. The remaining case
that must be handled explicitly, not left as an unhandled failure, is an **authenticated key with
insufficient or zero balance**: reject with a clear, user-facing message before starting a stream,
conceptually mirroring the existing `KeyState` full/low/empty tiering concept used elsewhere in
the main app (`app/Authentication/KeyState.php`) even though `metager-chat` implements this check
itself rather than depending on that PHP class.

## Pricing table and margin formula

`config/models.json`, one entry per model:
```json
{
  "id": "openai/gpt-4o-mini",
  "provider": "openai",
  "provider_model": "gpt-4o-mini",
  "display_name": "GPT-4o mini",
  "max_output_tokens": 16384,
  "price_usd_per_1m_input": 0.15,
  "price_usd_per_1m_output": 0.60,
  "margin_multiplier": 2.0,
  "enabled": true
}
```

Formula:
```
cost_eur = (prompt_tokens / 1e6 × price_in + completion_tokens / 1e6 × price_out)
           × margin_multiplier × usd_to_eur_rate

metager_tokens = ceil(cost_eur / 0.01 × 10) / 10   // round UP to nearest 0.1 (decitoken)
```

1 MetaGer token = €0.01 (this rate lives in `metager-keymanager/pass/config/default.json`,
`price.per_token` — `metager-chat` does not hardcode or re-derive it, it's used here only to
express the rounding target). Rounding is always in MetaGer's favor (ceiling), consistent with
the margin's protective intent, and reuses the fractional-decitoken concept the main app already
has for the anonymous-token flow — most individual chat messages cost a small fraction of a cent,
so coarse whole-token rounding would be too imprecise.

`usd_to_eur_rate` is a config constant, reviewed/updated periodically (e.g. quarterly) rather than
fetched live per request, avoiding a new external dependency and latency in the billing hot path.
Flagged in [`../open-questions.md`](../open-questions.md) as an accepted tradeoff, not a blocker
(a stale rate under-margins slightly during EUR/USD swings; the 2x margin provides buffer).

`models.json` is git-committed as the default/fallback (mirrors how the main app's `sumas.json` is
the source-of-truth engine catalog). Following the precedent of how `sumas.json` is actually
injected as a deploy-time file from a CI variable in the main app's deployment scripts, this
service's Helm chart should support mounting an override `models.json` via ConfigMap at deploy
time, so pricing/margins can be retuned without a code deploy. A ConfigMap, not a Secret — pricing
isn't sensitive.

## Accepted risk: concurrent balance drain

A key's balance could theoretically be drained by concurrent activity (parallel chat messages, or
a chat message racing a websearch spend on the same key) between reservation and end-of-stream
settlement, in rare cases making settlement fail after generation already happened (i.e. MetaGer
eats the cost of that one message). **This is explicitly accepted as a risk worth taking** — no
incremental/partial settlement mechanism is planned to mitigate it. Conservative reservation
sizing (based on `max_output_tokens`) is the only safeguard in place, and it exists primarily to
protect against runaway TTL-based claim leakage, not to solve this race.
