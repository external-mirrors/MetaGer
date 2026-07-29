# Chat Storage & Privacy

> **Rewritten.** The earlier design (a plain localStorage UUID as a partition key, transcripts stored
> readable in Postgres) is superseded. Two facts that emerged later invalidated its core assumption
> that an opaque id is sufficient protection: **a MetaGer key may be shared between several people**,
> and users legitimately want to recover history on a second device. What survives unchanged is the
> central decision that storage identity must be **fully decoupled from the billing key** — that is
> now more strongly motivated, not less.

## Default-off, opt-in persistence

Persistent chat history remains **disabled by default**, for every user class. Opting in is a single
toggle in the chat UI. Everything below describes what happens only after that toggle is on.

Not opted in, the current conversation's prior turns are still needed as context for each follow-up
turn — handled without any server-side storage. With JS the browser holds the transcript in memory
and re-sends it each turn; without JS MetaGer round-trips it through hidden form fields (see
[`../metager-integration/native-frontend.md`](../metager-integration/native-frontend.md)).

## Why the billing key cannot be the identity

Three independent reasons, any one of which is disqualifying:

1. **A key may be shared between several people.** Keying chat history off the key would show one
   person's private conversations to everyone else holding that key. This is the decisive one.
2. **For webextension users the key rotates.** The Anonymous Token extension mints ephemeral,
   blind-signed tokens and sends a changing `Anonymous-Token-Key` that is cryptographically
   unlinkable to the original key. There is simply no stable value to key off.
3. **Paying for something and owning its content are different relationships.** Tying them creates
   a link between spend records and conversation content that nothing about the feature requires.

So storage identity is a secret the *user* holds, generated client-side, never derived from and
never stored next to the key.

## Chosen approach: end-to-end encrypted, keyed by a user-held recovery code

On opt-in, client JS generates a 128-bit random **recovery code** (base32, presented as words for
the rare occasion it must be read or typed). HKDF splits it into two halves with distinct info
strings:

```
recovery code (128-bit, generated client-side, never transmitted whole)
  ├── HKDF(code, "id")  → set id      ──→ sent to the server as the lookup handle
  └── HKDF(code, "enc") → AES-GCM key ──✕ never leaves the browser
```

The server stores opaque ciphertext under the set id. It cannot read titles, message text, or which
models were used. Recovery on any device is: supply the code, re-derive both halves, decrypt.

**What this protects, precisely.** Stored history is unreadable to MetaGer and to anyone who obtains
the database. It does **not** make the conversation itself private from the provider — answering a
message inherently requires sending it in plaintext to OpenAI/Anthropic/Mistral, and the client must
decrypt prior turns to supply them as context. The protection is *at rest*, and the UI should say so
in exactly those terms rather than implying more.

**What the server still observes**: set id, ciphertext size, timestamps, and request timing. In
principle a set id could be correlated with a billing key seen on the same request. The mitigation is
discipline, not cryptography — never persist or log the two together — and it should be documented
as a known limitation rather than papered over.

**The unavoidable cost**: lose the code with no device still holding it, and the history is
permanently unrecoverable. There is no reset, by construction. This must be stated plainly at the
opt-in moment, not buried.

## Why the extension's sync channel must not be used for this

The Anonymous Token extension stores and syncs the user's cookies and settings across their browsers,
re-attaching them to MetaGer requests as headers of the same name. That looks like a free
cross-device sync mechanism for the recovery code, and it is worth writing down explicitly why it
cannot be used:

**Anything the extension syncs is, by design, re-sent to MetaGer as a request header.** A synced
value is therefore a server-visible value. Putting the encryption half of the recovery code into a
cookie or setting would transmit it to the server on every request, which is not "end-to-end
encrypted with a caveat" — it is not end-to-end encrypted at all.

The same reasoning rules out a cookie for the *id* half, for a different reason: cookies are sent on
**every** request to `metager.de`, including ordinary searches. That would attach a stable chat
identifier to search traffic and create exactly the cross-request linkability the rest of MetaGer is
built to avoid. (This also matches the precedent from SafeBrowse, where cookie-based session identity
was tried, reverted, and replaced with a client-generated localStorage id.)

**Therefore: localStorage only, sent as an explicit header on chat API calls and nowhere else.**

## Making the code nearly invisible in practice

The design goal is that a normal user never sees or types the recovery code. Four mechanisms, in
descending order of how often they apply:

1. **Silent generation, local persistence.** Opting in generates the code and stores it in
   localStorage. On that browser, history simply works, indefinitely, with no prompt. This covers
   almost all real usage.
2. **QR transfer for a second device.** "Use my history on another device" renders a QR encoding a
   URL whose **fragment** carries the code — `https://metager.de/chat#h=<code>`. Fragments are never
   sent to the server, so the code stays client-side even though it travels through a URL. Scanning
   on the second device imports and stores it. No typing.
3. **Word-list fallback.** For the case where scanning is not possible, the same code is rendered as
   a short word sequence, which is far less error-prone to type or read aloud than base32.
4. **Backup prompt, deferred and rare.** Suggest backing up the code only once a user has enough
   accumulated history to care — never at opt-in, when the prompt carries no meaning and trains
   people to dismiss it.

QR generation must happen **client-side**; MetaGer's server-side `endroid/qr-code` must not be used
here, since that would mean sending the code to the server. A small npm QR library through the
existing Laravel Mix pipeline is the right tool.

## Retention: user-chosen length, but everything expires on disuse

**There is no "keep forever" option.** Every stored set carries an expiry, because the failure mode
worth designing against is not large data volumes — the data is small — but *zombie sets*:
history belonging to users who stopped using MetaGer chat years ago, retained indefinitely by
nobody's active decision. Data that no one will ever look at again is pure liability.

The mechanism is a **sliding window measured from last use, not from creation**:

- The user picks the window length: **30 / 90 days / 1 year / 3 years**, defaulting to 90 days.
  Because the window slides, generous options are safe to offer — a long window doesn't mean data
  lingers after abandonment, only that abandonment is judged more patiently.
- **Any access to the set refreshes the whole set**, not just the conversation touched. Listing
  conversations counts, so simply opening chat with history enabled is enough. An active user
  therefore never silently loses an old conversation, which is the behaviour people actually expect;
  "unused" means the user stopped coming back, not that one conversation got old.
- A set nobody has touched for the whole window is deleted by a scheduled purge.

### Implementation under E2E

Store a computed **`expires_at` timestamp, not the chosen period.** The client computes it and sends
it on write and on refresh. Three benefits: the purge job is a single indexed query over a plain
timestamp, it needs no access to plaintext, and the server never holds the retention setting as an
enum it could use as a weak per-user fingerprint.

Two details that keep this honest and cheap:

- **The server clamps `expires_at` to a hard maximum** (the longest offered window plus slack).
  Otherwise a buggy or hostile client could write a year-3000 expiry and reintroduce exactly the
  forever-storage case this section exists to prevent. The no-forever rule has to be enforced
  server-side, not merely offered client-side.
- **Refresh at most once per day per set.** A naive implementation rewrites every row in the set on
  every page load; skipping the update when `expires_at` has moved by less than a day makes the
  sliding window essentially free, at the cost of up to 24h of imprecision in when a set expires —
  irrelevant at a 30-day-minimum granularity.

Deletion is otherwise entirely user-driven: delete one conversation, delete all history, or forget
the code on this device (local only, leaving the server data intact for other devices). The history
settings panel should show the current expiry date plainly, so the behaviour is visible rather than
something users discover by losing data.

## Schema

`metager-chat` gets its own Postgres, following the isolation the sibling services already have. The
store is deliberately dumb — it holds blobs and timestamps and knows nothing about chat:

```
conversations:
  id                uuid primary key
  set_id            text not null, indexed   -- HKDF(code, "id")
  title_ciphertext  bytea not null           -- separate, so listing needn't fetch transcripts
  body_ciphertext   bytea not null           -- AES-GCM over the whole transcript
  iv                bytea not null
  created_at, updated_at, last_access_at     -- timestamptz
  expires_at        timestamptz not null, indexed  -- sliding; server-clamped to a hard maximum
```

`expires_at` is `not null` by design — it is the schema-level expression of "nothing is stored
forever". The index on it is what makes the purge job a cheap scheduled `DELETE`.

**One blob per conversation, rewritten on each turn**, rather than a row per message. Slightly more
write amplification (a long conversation is still well under 100KB), in exchange for two real
benefits: the server cannot infer message counts from row counts, and there is no per-message
metadata to leak. Titles are encrypted separately so the conversation list can render without
downloading every transcript.

Titles are derived client-side from the first user message. The server cannot generate them, and
asking a model to would cost the user money for no benefit.

## Endpoints

All proxied through MetaGer, all non-streaming, all operating on ciphertext:

| Endpoint | Purpose |
|---|---|
| `GET /api/conversations` | List for a set id: `(id, title_ciphertext, iv, updated_at, expires_at)`. Refreshes the sliding window across the whole set (at most daily). Rate-limited — a set id is a bearer credential, so brute-force enumeration must be expensive. |
| `GET /api/conversations/:id` | Fetch one blob. Bumps `last_access_at`. |
| `PUT /api/conversations/:id` | Create or replace, with a client-computed `expires_at` the server clamps. |
| `DELETE /api/conversations/:id` | Delete one. |
| `DELETE /api/conversations` | Delete everything under a set id. |

## Interface

Detailed in the native-frontend doc; the storage-relevant surface is:

- A conversation list (drawer on mobile, panel on wide viewports) with rename and delete.
- A history settings panel: the opt-in toggle, retention selector, "use on another device"
  (QR + word list), "forget on this device", "delete all history", and **export** to Markdown or
  JSON — data portability is part of taking this positioning seriously.
- **No-JS: no history panel at all.** Encryption, key derivation and QR rendering all require JS, so
  stored history is necessarily a JS-only enhancement. The default, non-persistent chat experience
  remains fully functional without JS, which is the right place to draw that line — the baseline is
  complete, and the enhancement is genuinely impossible to deliver otherwise.
