# CiviCRM replacement — status and next steps

Handoff snapshot as of 2026-09-04. Written so work can resume on a different machine without
replaying this session; the durable, cross-session narrative also lives in Claude's own memory
under `civicrm-replacement-project`, `civicrm-extension-inventory` and `checkout-payment-flow` —
if you're picking this up with a fresh assistant, point it at this file first.

## The decision (settled, don't re-litigate)

suma-ev (the non-profit behind MetaGer) is replacing CiviCRM entirely, not incrementally improving
it. CiviCRM today is a database + mass-email tool wrapped around two custom, git-history-less
extensions living only on the production WordPress/CiviCRM pod
(`suma-ev-b84d6487-...` in namespace `suma-ev-78`):

- **`de.suma-ev.donation-debit`** — SEPA direct-debit generation (pain.008.001.02), the full
  membership billing lifecycle via 9 cron jobs, and a live call into the MetaGer keymanager
  (`ChargeKeys.php`) that creates/recharges a search key per active member. Its creditor
  IBAN/BIC and a keymanager bearer token are hardcoded literals in extension source — **rotate
  that token once the replacement takes over this call.**
- **`de.suma-ev.bescheinigungen`** — donation tax receipts (Zuwendungsbestätigung, German legal
  formatting requirements) *and*, despite the name, Hibiscus bank-statement import + PayPal
  polling + manual/automatic income-matching triage. Matching is exact (mandate ref, then a
  `/([mM]\d{14})/` regex, then substring), not fuzzy.

Full extension read-through, dead code, and the Zahlungsweise/Zahlungsstatus option-value mappings
are in the `civicrm-extension-inventory` memory — re-copy the extensions from the pod above if that
needs re-reading, the local copy was session-scratch and is gone.

**Rebuild-then-cutover, not incremental migration.** CiviCRM is a stateful system of record
(contacts, membership history, email consent) — dual-running two sources of truth isn't viable the
way the stateless keymanager-checkout migration (see `checkout-payment-flow` memory) was. Needs a
tested data-migration/dry-run against real production data before cutover.

**Open, not yet decided:**
1. Mass email replacement — user dislikes the current WordPress+Newsletter-plugin setup, floated
   **listmonk** as a candidate, not evaluated.
2. Exact scope of the rebuilt module vs. what MetaGer's `membership_applications`/`Membership`
   code already does.
3. When to schedule extending self-hosted SEPA collection to keymanager key purchases (replacing
   Micropayment for bank-transfer/direct-debit) — deliberately deferred, chargeback risk already
   sits with suma-ev today either way, this is pure scope sequencing.

## Where the code lives

Branch **`crm-replacement-schema`** (off `development`, in `metager/`), MR
[!2478](https://gitlab.metager.de/open-source/MetaGer/-/merge_requests/2478). Pipeline is green as
of the last commit below.

```
metager/database/migrations/2026_09_04_09*_create_assoc_*_table.php   8 tables, assoc_ prefix
metager/app/Models/Assoc/{Contact,Company,Household,Membership,Debit,
                          RecurContribution,BankStatementLine,DonationReceipt}.php
metager/app/Assoc/CiviCrmImporter.php                                  the import logic
metager/app/Console/Commands/ImportCiviCrm.php                         artisan command wrapping it
metager/app/Http/Controllers/AssocController.php                       read-only admin UI
metager/app/Http/Middleware/AdminAuthenticate.php                      per-request /admin auth gate
metager/resources/views/admin/assoc/*.blade.php                       the admin views
metager/tests/Unit/Assoc/*                                             model + importer unit tests
metager/tests/Feature/Assoc/AssocAdminTest.php                         admin UI feature tests
metager/tests/Feature/AdminRouteAuthenticationTest.php                 pins the two CI bugs below
```

### Schema (8 tables, `assoc_` prefix — deliberately not `Crm*`)

`Contact`, `Company`, `Household` (donation-only payer, never a member), `Membership`, `Debit`,
`RecurContribution` (dues + donations share these two via a `source` enum, matching CiviCRM),
`BankStatementLine`, `DonationReceipt`. Payer references are three nullable FK columns
(`contact_id`/`company_id`/`household_id`, exactly one set, enforced at the app layer) — not
`morphTo`, nothing else here uses polymorphic relations.

Decisions worth knowing before touching this schema:

- Money columns are `decimal(10,2)` **with an explicit `decimal:2` Eloquent cast** — the migration
  type alone isn't enough; SQLite's NUMERIC affinity silently turns a decimal string back into a
  float on read otherwise.
- `assoc_memberships.standing` (`active`/`terminated`/`deceased`) plus `payment_method` gaining a
  fifth value `exempt` **replaced** CiviCRM's 8-value `Beitrag.Zahlungsstatus` mirror on purpose —
  billing/collection progress is meant to be derived live from `end_date` + `assoc_debits` in a
  later phase, not re-stored as CiviCRM's own already-derived state.
- `$table->uuid('x')->references('id')->on('table')` in these migrations is **decorative, not a
  real FK constraint** (matches an existing codebase convention) — `references()`/`on()` without
  `->foreign()`/`->constrained()` are silently dropped by Laravel. Don't assume it's enforced.
- `membership_applications.crm_contact`/`crm_membership` are still `integer` (CiviCRM's numeric
  IDs) while `assoc_*` uses `uuid` PKs — deliberately not reconciled yet. That's a decision for
  when `CiviCrm.php` itself gets repointed at this schema.
- Production's actual default DB connection is **postgres**; `.env.example`'s `sqlite` default is
  a fresh-checkout convenience only. Every schema choice here needs to stay portable across both.
- Tests can't rely on `php artisan migrate` having run (CI's `test` job never calls it, and
  `RefreshDatabase` would wipe a developer's real local sqlite). Use the
  `Tests\Concerns\UsesInMemorySqlite` trait — every `Assoc*Test` does.

### Importer (`App\Assoc\CiviCrmImporter::importMemberships()`)

Column names for `Beitrag.*`/`Mastodon.*`/`MetaGer_Key.*` (CiviCRM custom-value tables
`civicrm_value_beitrag_8`/`_mastodon_10`/`_metager_key_14`) were confirmed against a real
production dump via a throwaway local `mariadb:11` container, not guessed — a naive text-parse of
the dump's multi-row `INSERT`s misaligns columns whenever a text field contains a comma. Skips 4
household memberships (no `household_id` on `Membership`) and 38 pre-Zahlungsweise expired
memberships (no reliable payment method) rather than guessing. The production dump used for this
(`/tmp/civicrm-dump-20260904.sql` on the machine this was developed on) **contains real donor/member
PII — never publish or quote it verbatim; only schema/structure/aggregate counts are shareable.**
That dump was session-scratch and needs re-pulling from the production pod if the importer needs
re-verifying against real data.

### Admin UI (`AssocController` + `resources/views/admin/assoc/*`)

Read-only, for verifying the import: `/admin/assoc/members` (contacts + companies, each paginated,
with their membership), `/admin/assoc/members/{type}/{id}` (detail: membership, debits, recurring
contributions), `/admin/assoc/households` + detail. Routed under the existing `/admin` prefix in
`routes/session.php`.

`Membership::standingLabel()`/`paymentMethodLabel()`/`intervalLabel()` are deliberately hardcoded
German on the model, not `@lang()` lookups — `ResolveLocale` runs on every route including
`admin/*`, so a translation lookup would render e.g. "Bank transfer" next to otherwise-German admin
labels for an English-negotiating visitor. This is this codebase's first `paginate()` use anywhere.

### The two CI bugs this surfaced (both fixed, both worth knowing about for any future admin route)

The admin UI's own tests failed in CI while passing locally — two separate, stacked config-caching
bugs, both instances of "cached config/routes never re-read `env()`/`App::environment()`", a class
of bug this codebase has hit before (see `CLAUDE.md`'s locale-caching paragraph).

1. **Route registration timing** (`7636dea21`) — `routes/session.php` used to decide whether
   `/admin` needed `keycloak-web` by checking `App::environment()` *while registering routes*,
   baking that decision into the route cache `php artisan optimize` builds in CI. Fixed by moving
   the check into `App\Http\Middleware\AdminAuthenticate`, evaluated per request, attached to the
   group unconditionally.
2. **Config caching itself** (`fc7ccbab4`) — even after (1), CI still failed the same way. Root
   cause one level up: `php artisan optimize`'s `config:cache` bakes `config('app.env')` — and
   therefore `App::environment()` — into `bootstrap/cache/config.php` as a build-time literal,
   which `LoadConfiguration` reads back on every request without calling `env()` again.
   `phpunit.xml`'s `<env name="APP_ENV" value="testing"/>` was therefore silently ignored, and
   `App::environment()` stayed `"production"` (from the copied-in production `.env`) for the whole
   CI test job. Fixed with a job-level `APP_ENV: testing` variable on `.gitlab-ci.yml`'s `test` job
   specifically (not `.test_base` — `browsertest` depends on that same config cache for its own
   `app.locale` resolution and gets nothing from this fix, since its assertions run against the
   deployed review pod, not its own container).

`tests/Feature/AdminRouteAuthenticationTest.php` pins both invariants going forward — any future
admin route should stay covered by it automatically. **If a next admin route's tests fail in CI
with unexpected redirects, this file's docblock explains the exact mechanism before you re-derive
it.**

## Where it stands right now

- MR !2478 pipeline green as of commit `fc7ccbab4`.
- Local suite: 1369 passed, 1 skipped, no known failures.
- Nothing has been merged to `development` yet — this is all still on `crm-replacement-schema`,
  under active review/iteration.

## Upcoming work — 6-phase roadmap

1. ~~Data model~~ — done (`6d4cca588` .. `3803884fc`).
2. ~~CiviCRM importer~~ — done (`4308c8e64`).
3. ~~Read-only admin UI~~ — done (`9ed4a26e9`, CI fixed by `7636dea21`/`fc7ccbab4`).
4. **Shadow-mode bank-statement matching** — next up. Port the Hibiscus/PayPal matching logic from
   `de.suma-ev.bescheinigungen` (`FetchBankAccount.php`, `FetchPayPal.php`, the triage UI) against
   `assoc_bank_statement_lines`, running alongside CiviCRM without writing back to it yet, to
   validate the matcher against real traffic before it has to be trusted.
5. **Donation receipts** — port `de.suma-ev.bescheinigungen`'s Zuwendungsbestätigung generation
   (mpdf, address-completeness gating, the German number-to-words converter) onto
   `assoc_donation_receipts`.
6. **Cutover** — the SEPA-generation port (`de.suma-ev.donation-debit`'s pain.008.001.02 logic),
   wiring `ChargeKeys`-equivalent keymanager charging onto this schema (reuse the existing
   production keymanager credential — already decided, no new credential needed), and switching
   `Zahlungsstatus`-derived writes over from CiviCRM. `ChargeKeys.php`'s hardcoded bearer token
   needs rotating at this point (see extension inventory above).
7. **Mass email** — deliberately last, per explicit prior instruction. Still undecided between
   keeping/improving the WordPress+Newsletter-plugin setup or adopting listmonk.

None of phases 4-7 have been started. The natural next step on resume is phase 4, but confirm with
whoever picks this up before starting — the mass-email and keymanager-payment-phase-timing
questions above are still open and may reorder things.
