# `metager-chat` — Chat Storage & Privacy

> See the note at the top of [`architecture.md`](architecture.md) about this document's home
> once `metager-chat` becomes its own repo.

## Default-off, opt-in persistence

Persistent chat history storage is **disabled by default**, for every user class. "Opt in" is a
single boolean flag toggled from a setting in the chat UI.

## The identity problem: no stable server-visible identity for some users

The main MetaGer app's "Anonymous Token" webextension flow mints ephemeral, blind-signed tokens
client-side and rewrites requests to send a rotating `Anonymous-Token-Key` instead of a stable
`Key` — the backend (`KeyAuthGuard.php`) marks these `temporary = true`. This means **there is no
stable server-visible identity to key chat storage off for these users** — the billing key itself
changes from one request to the next.

## Chosen approach: client-generated UUID, decoupled from the billing key

Apply the same pattern already validated for an analogous problem in a different MetaGer product
(SafeBrowse): when facing "how do we identify a user across requests without a stable server
identity," that team tried a cookie-based session identity, reverted it, and landed on a **plain
client-generated UUID stored in localStorage, used purely as a storage-scoping key, with no
server-side tracking**.

Applied here: on first chat use, client JS checks for a `metager_chat_client_id` in `localStorage`;
if absent, generates one (`crypto.randomUUID()`) and stores it. This id is sent on chat API calls
as a header (e.g. `X-Chat-Client-Id`), used **only** as a database partition key for
`chats`/`messages` rows — it carries no billing authority and is entirely decoupled from the
MetaGer key used for billing (see [`billing.md`](billing.md)).

**No separate mechanism is needed for webextension users.** The Anonymous Token webextension only
rewrites outgoing request *headers* (via `declarativeNetRequest`, swapping `Key` for
`Anonymous-Token-Key`) — it doesn't render its own UI or intercept page JS. Webextension users
browse plain `metager.de` in their normal browser tab exactly like anyone else, so the chat
iframe's `localStorage` (same-origin, per §"Same-origin routing" in
[`../metager-integration/foki-integration.md`](../metager-integration/foki-integration.md)) is
already the same storage a non-extension user gets. The billing key rotates per request; the
client id does not — that's the whole point of keeping them decoupled, and no extension-specific
code path is required to achieve it.

This works **uniformly** for both logged-in and anonymous/webextension users — no special-casing
needed per user class, and no coupling between `metager-chat`'s storage layer and the billing
key's rotation behavior. The opt-in flag itself is also stored scoped by this same client id, for
the same reason: it keeps the mechanism identical across both user classes and avoids giving
`metager-chat` any privileged relationship to the billing key beyond the one-off
reserve/settle calls.

## Schema (if persistence is opted into)

`metager-chat` gets its own Postgres — none of the sibling services share a database;
`metager-keymanager` has its own Postgres for its own concerns, and this service should follow
the same isolation:

```
chats:      id (uuid pk), client_id (text, indexed), title, model_id, created_at, updated_at
messages:   id (uuid pk), chat_id (fk), role (user|assistant|system), content,
            token_usage (jsonb: prompt/completion/cost), created_at
```

## If not opted in

The current conversation's prior turns still need to be sent as context on each follow-up
message — handled purely **client-side**: the browser JS keeps the running conversation in
memory/`sessionStorage` and re-sends the full transcript each turn, with nothing written
server-side. This is a clean default that fully satisfies "disabled by default" without
special-casing the LLM call path itself based on opt-in status — the difference between opted-in
and not is purely whether messages get written to Postgres after the fact, not how a given turn
is generated.

## Retention / deletion

No automatic expiry is recommended for opted-in history — treat it like the user's own data,
deleted only on explicit user action (a "delete chat" / "delete all my chat history" action in the
UI, e.g. `DELETE /chat-api/chats/:id` scoped to the caller's `client_id`).

**Open question, not resolved here** (tracked in [`../open-questions.md`](../open-questions.md)):
whether a maximum retention period should be imposed anyway as a privacy-by-design measure
independent of user action, given MetaGer's stated privacy positioning. This is a legal/product
call, not an architecture one.
