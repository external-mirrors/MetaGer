# Blocklist API (MetaGer ⇄ suma-crm)

MetaGer owns the blocklist — which results it will not show, and whose descriptions it will
not print. It lives in MetaGer's database (`blocklist_entries`). suma-crm edits it live through
this API and builds removal-request cases on top of it. This document is the contract both sides
test against: MetaGer's feature tests assert these shapes, suma-crm's `Http::fake` responses copy
them. Change both sides and this file together.

## Transport

- Base path: `/api/blocklist` on MetaGer's public origin (`https://metager.de/api/blocklist`). No
  locale prefix; `ResolveLocale` strips one if it is sent, so both forms work.
- Authentication: `Authorization: Bearer <token>`. MetaGer reads the token from env
  `CRM_ADMIN_TOKEN` (config `metager.metager.crm.admin_token`), suma-crm sends env
  `METAGER_ADMIN_TOKEN`. An unset token on MetaGer's side refuses every request.
- Request and response bodies are JSON (`Content-Type: application/json`,
  `Accept: application/json`). Server-to-server: no cookies, no session, no `Origin` header —
  MetaGer's same-origin check must not apply to these routes.
- Timestamps are ISO 8601 with offset (`2026-11-03T14:05:00+01:00`); dates are `YYYY-MM-DD`.

## The entry

```json
{
  "id": 42,
  "kind": "url_terms",
  "value": "example.org/people/max-mustermann",
  "terms": ["max", "mustermann"],
  "reason": "removal_request",
  "note": "Art. 17 request, balancing in favour of requester",
  "external_ref": "crm:removal:17",
  "created_by": "jane@suma-ev.de",
  "review_at": "2027-11-03",
  "created_at": "2026-11-03T14:05:00+01:00",
  "updated_at": "2026-11-03T14:05:00+01:00",
  "deleted_at": null
}
```

| field | type | meaning |
|---|---|---|
| `kind` | `domain` \| `url` \| `url_terms` \| `description` | `domain`: every result on the host is dropped. `url`: that page is dropped. `url_terms`: that page is dropped when **every** term appears in the search query. `description`: the result stays, its description is blanked. |
| `value` | string | `domain`: stripped host (`example.org` — scheme, `www.`, port removed, lowercased). Other kinds: stripped host + path (`example.org/a/b`), no query string, no trailing slash. MetaGer normalises what it is sent with the same rules `Result::getStrippedHost/Link` apply to results, and returns the normalised form. |
| `terms` | string[] \| null | Only for `url_terms` (required there, at least one; `null` for every other kind). Normalised: lowercased, diacritics folded (`ä`→`ae`, `ß`→`ss`), split on whitespace and punctuation, de-duplicated, sorted. A query matches when its normalised tokens contain all of them, in any order, among other words. |
| `reason` | `spam` \| `legal` \| `removal_request` \| `other` | Why the entry exists. |
| `note` | string \| null | Free text, max 2000 chars. |
| `external_ref` | string \| null | Where the entry came from outside MetaGer. suma-crm writes `crm:removal:<case id>`. Max 191 chars. |
| `created_by` | string \| null | Who created it (suma-crm sends the agent's email). |
| `review_at` | date \| null | When someone should look at the entry again. |
| `deleted_at` | timestamp \| null | Set on soft-deleted entries (only returned with `trashed=1`). |

A `(kind, value, terms)` combination is unique among non-deleted entries.

## Endpoints

### `GET /api/blocklist`

Query parameters, all optional:

| param | meaning |
|---|---|
| `kind` | one of the kinds |
| `reason` | one of the reasons |
| `q` | substring of `value`, `note` or `external_ref` (case-insensitive) |
| `external_ref` | exact match |
| `review_due` | `1`: only entries whose `review_at` is today or earlier |
| `trashed` | `1`: include soft-deleted entries |
| `page` | 1-based, default 1 |
| `per_page` | default 50, max 200 |

`200`:

```json
{
  "data": [ { "id": 42, "kind": "url_terms", "...": "..." } ],
  "meta": { "current_page": 1, "last_page": 3, "per_page": 50, "total": 123, "review_due": 4 }
}
```

`meta.review_due` is the count of non-deleted entries due for review, independent of the filters
— suma-crm's nav badge reads it with `per_page=1`. Order: newest first (`id` descending).

### `GET /api/blocklist/{id}`

`200` `{ "data": <entry> }` (soft-deleted entries included), `404` when no such id.

### `POST /api/blocklist`

Body: `kind`, `value`, `terms` (for `url_terms`), `reason`, and optionally `note`,
`external_ref`, `created_by`, `review_at`.

`201` `{ "data": <entry> }` — the stored, normalised entry.
`422` on validation failure, including a duplicate of a non-deleted entry.

### `PATCH /api/blocklist/{id}`

Body: any of `reason`, `note`, `review_at`, `external_ref`. `kind`, `value` and `terms` are not
editable — delete and recreate, so the history says what changed.

`200` `{ "data": <entry> }`, `404`, `422`.

### `DELETE /api/blocklist/{id}`

Soft-deletes. Body (optional): `{ "deleted_by": "jane@suma-ev.de" }`.

`200` `{ "data": <entry> }` with `deleted_at` set; `404` when no such (non-deleted) id.

### `POST /api/blocklist/test`

Would this result be blocked for this query, right now?

Body: `{ "url": "https://www.example.org/people/max-mustermann?x=1", "query": "Max Mustermann Anwalt" }`

`200`:

```json
{
  "data": {
    "url": "example.org/people/max-mustermann",
    "query": "Max Mustermann Anwalt",
    "blocked": true,
    "description_blanked": false,
    "matches": [ <entry>, ... ]
  }
}
```

`url` is the stripped form. `matches` lists every entry that applies (blocking and description).

### `POST /api/blocklist/check`

Is this result listed on MetaGer at all, and which engine returns it? Runs real searches against
the upstream engines (paid), with the blocklist filter **bypassed** so it reports what upstream
returns. Throttled: 10 requests per minute per token.

Body:

```json
{
  "urls": ["https://example.org/people/max-mustermann"],
  "queries": ["Max Mustermann"],
  "foki": ["web", "bilder", "nachrichten"]
}
```

`urls`: 1–20. `queries`: 1–5. `foki`: optional, default `["web", "bilder", "nachrichten"]`,
each must be a fokus in `config/foki.json`.

`200`:

```json
{
  "data": {
    "checked_at": "2026-11-03T14:05:00+01:00",
    "scope": "first_page",
    "results": [
      {
        "url": "example.org/people/max-mustermann",
        "query": "Max Mustermann",
        "fokus": "web",
        "found": true,
        "rank": 3,
        "engines": [
          { "name": "brave", "display_name": "Brave" },
          { "name": "serper_web", "display_name": "Google" }
        ],
        "blocked_now": false,
        "blocking_entry_ids": []
      }
    ],
    "engines_failed": [ { "fokus": "web", "name": "yandex" } ]
  }
}
```

- One row per url × query × fokus.
- `scope: "first_page"`: only each engine's first result page was searched. A `found: false` is
  "not in the first page", not "not on MetaGer"; replies must say so.
- `engines[].name` is the engine key from `config/foki.json` (`brave`, `serper_web`,
  `brave_images`, …). It is the key into MetaGer's `config/removal_providers.php`, whose anchors
  on `/entfernen` suma-crm links to.
- `rank` is the 1-based position in MetaGer's merged result list, `null` when not found.
- `blocked_now` / `blocking_entry_ids`: whether today's blocklist hides it for this query.
- `engines_failed`: engines that errored or timed out, so "not found" can be qualified.

## Errors

| status | body | when |
|---|---|---|
| `401` | `{ "error": "Unauthorized" }` | missing or wrong bearer token, or no token configured |
| `404` | `{ "error": "Not found" }` | unknown id |
| `422` | `{ "message": "...", "errors": { "field": ["..."] } }` | Laravel's validation shape |
| `429` | `{ "error": "Too many requests" }` | `check` throttle |
