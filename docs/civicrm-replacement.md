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

- MR !2478 pipeline green as of commit `fc7ccbab4`; phases 4 and 5 have since been pushed on top.
- Local suite: 1440 passed, 1 skipped, one known pre-existing failure unrelated to this branch
  (`LogsAdminDeleteTest` — the developer's local `database.sqlite` has drifted from migrations,
  `logs_access_key` is missing `updated_at`; nothing this project touches).
- Nothing has been merged to `development` yet — this is all still on `crm-replacement-schema`,
  under active review/iteration.

## Upcoming work — 6-phase roadmap

1. ~~Data model~~ — done (`6d4cca588` .. `3803884fc`).
2. ~~CiviCRM importer~~ — done (`4308c8e64`).
3. ~~Read-only admin UI~~ — done (`9ed4a26e9`, CI fixed by `7636dea21`/`fc7ccbab4`).
4. ~~Shadow-mode bank-statement matching~~ — done, see below.
5. ~~Donation receipts~~ — done, see below.
6. **Cutover** — next up. The SEPA-generation port (`de.suma-ev.donation-debit`'s pain.008.001.02
   logic), wiring `ChargeKeys`-equivalent keymanager charging onto this schema (reuse the existing
   production keymanager credential — already decided, no new credential needed), switching
   `Zahlungsstatus`-derived writes over from CiviCRM, and turning phase 4's shadow-mode matcher and
   phase 5's manual-only receipt generation into the real, automatic thing (flipping
   `assoc_debits.status` to `executed` on a confirmed match — deliberately not built yet, see phase
   4's section). `ChargeKeys.php`'s hardcoded bearer token needs rotating at this point (see
   extension inventory above).
7. **Mass email** — deliberately last, per explicit prior instruction. Still undecided between
   keeping/improving the WordPress+Newsletter-plugin setup or adopting listmonk.

None of phase 6/7 have been started. The natural next step on resume is phase 6, but confirm with
whoever picks this up before starting — the mass-email and keymanager-payment-phase-timing
questions above are still open and may reorder things.

### Phase 4 — shadow-mode bank-statement matching

Ported from `de.suma-ev.bescheinigungen`'s `FetchBankAccount.php` (Hibiscus XML upload) and
`checkMandates()`/`searchForMandates()`, plus `de.suma-ev.donation-debit`'s
`IncomingPayment/Auto.php` mandate-lookup cascade — read from a session-scratch copy of the
extensions still sitting in `~/.local/share/Trash` at the time (`de.suma-ev.donation-debit` and
`de.suma-ev.bescheinigungen`, under `wp-content/plugins/civicrm/civicrm/ext/`); that copy is not
guaranteed to survive and should be re-pulled from the production pod if it's gone. **Not
re-verified against a real Hibiscus export** — see the IBAN caveat below.

```
metager/app/Assoc/{BankStatementImporter,BankStatementMatcher}.php
metager/app/Console/Commands/ImportBankStatement.php               assoc:import-bank-statement
metager/app/Http/Controllers/BankStatementController.php           the triage UI, not read-only
metager/resources/views/admin/assoc/bank_statement{,s}.blade.php
metager/tests/Unit/Assoc/BankStatement{Matcher,Importer}Test.php, ImportBankStatementCommandTest.php
metager/tests/Feature/Assoc/BankStatementAdminTest.php
```

Fixed in passing: `BankStatementLine.amount` was missing the `decimal:2` cast every other money
column in this schema has (see the SQLite-affinity paragraph above) — CLAUDE.md's own documented
footgun, found by writing this phase's tests rather than by inspection.

**The matching cascade** (`BankStatementMatcher`), run once per line at import time and re-runnable
via `rematchUnresolved()`/the admin "Automatik erneut anwenden" button:

1. `mandate_reference` — an exact match on a structured field the bank itself supplied: the SEPA
   end-to-end reference (`Debit::end_to_end_reference`, unique per collection) if present, else the
   SEPA mandate id against `Debit::mandate`/`RecurContribution::mandate` directly — no CiviCRM API
   round-trip needed, unlike the original, because our own `assoc_debits`/`assoc_recur_contributions`
   already carry these per row (confirmed by re-reading `CiviCrmImporter::importDebits()` — it
   copies `civicrm_debit.mandate` straight across).
2. `regex` — the mandate id isn't in a structured field, but appears as a whole word (`\bMANDATE\b`)
   in the free-text purpose — this is what `searchForMandates()` actually did; the doc-carried
   assumption of a specific `/([mM]\d{14})/`-shaped regex did **not** turn up anywhere in either
   extension on inspection, and isn't what got built. (`M`+14-digit-timestamp *is* the real format
   CiviCRM's membership mandates use — confirmed in `MembershipChangeController.php`/
   `RecurContribution/CreateAll.php` — so this may have been a plausible-looking but wrong inference
   from an earlier session. Not chased further; the whole-word cascade is what's implemented and
   tested.)
3. `substring` — loosest fallback, mandate id appears anywhere in the free text, unbounded.
4. unmatched — queued for manual triage at `/admin/assoc/bank-statements/{id}`.

When several pending debits share a mandate (recurring dues), the one whose own `amount` matches
the payment exactly is preferred; otherwise the earliest-due one, so an over/underpayment still
resolves rather than staying unmatched.

**Deliberately does not write to `assoc_debits`/`assoc_recur_contributions`.** The CiviCRM original
flipped a debit to `status = executed` the moment a payment matched it; this phase only ever writes
`matched_type`/`matched_id`/`match_method`/`matched_at` onto the `assoc_bank_statement_lines` row
itself. That's the shadow-mode contract from the roadmap line above — validate the cascade's hit
rate against real traffic before letting it drive state anywhere. Flipping debit status on a
confirmed match is future work, not yet built.

**The IBAN caveat.** `assoc_bank_statement_lines.iban` (added in phase 1, before this phase existed)
expects the payer's IBAN per line. Neither `FetchBankAccount.php` nor any other file in the
extension copy actually parses an IBAN out of the Hibiscus XML — it was never a field that
extension's matching used. `BankStatementImporter::extractIban()` tries `empfaenger_iban` /
`gegenkonto_iban` / `iban` in that order and falls back to an empty string; **this needs checking
against a real Hibiscus "Umsätze exportieren" export before the importer is trusted operationally**
— if none of those tag names are right, every imported line will carry an empty `iban` and
`BankStatementAdminTest`/production usage would both need revisiting.

No file-upload web form was built — import is CLI-only (`assoc:import-bank-statement {file}
--account=1 --account=2`), matching this codebase's existing `assoc:import-civicrm` pattern. The
admin UI (`/admin/assoc/bank-statements`) is triage-only: list (filterable unmatched/matched/all),
a per-line detail page to search pending debits/recur contributions by account holder or mandate
and assign one manually, and a button to re-run the automatic cascade.

### Phase 5 — donation receipts

Generates `assoc_donation_receipts` (Zuwendungsbestätigung/Beitragsbescheinigung) from executed,
unreceipted `assoc_debits`, ported from `de.suma-ev.bescheinigungen`'s
`Bescheinigungen/Spendenbescheinigung.php`. Confirmed with whoever picked this up (see prior
session) before starting, because the schema `assoc_donation_receipts` already had — one yearly
total per payer, from phase 1, written before the extension source had been read — didn't match
what the original extension actually does (per-contribution, donation vs. dues split, a
Sofort/Jährlich/Niemals preference). The direction taken: extend the schema to keep that real
behaviour, specifically:

- the ability to generate a single receipt for one contribution on demand, not just as part of a
  batch;
- one preference *per payer* that applies to every future donation/dues payment ("global" in the
  sense of "global for that person", not one system-wide value) plus an actual system-wide default
  for payers with no preference of their own — CiviCRM's real behaviour, `shouldCreateReceipt()`,
  generated nothing at all when neither the contribution nor the contact had a preference set; this
  makes "nothing configured" mean something instead of silently never receipting;
- existing CiviCRM preferences correctly migrated in.

```
metager/database/migrations/2026_09_04_090080_add_donation_receipt_tracking.php
metager/app/Models/Assoc/Concerns/HasDonationReceiptPreference.php
metager/app/Assoc/{NumberToGermanWords,DonationReceiptGenerator,DonationReceiptPdf}.php
metager/app/Console/Commands/GenerateDonationReceipts.php               assoc:generate-donation-receipts
metager/app/Http/Controllers/DonationReceiptController.php              the write-side triage UI
metager/resources/views/assoc/donation_receipt_pdf.blade.php            the certificate itself
metager/resources/views/admin/assoc/donation_receipts.blade.php
metager/config/assoc.php
metager/tests/Unit/Assoc/{NumberToGermanWords,DonationReceiptGenerator,GenerateDonationReceiptsCommand}Test.php
metager/tests/Feature/Assoc/DonationReceiptAdminTest.php
```

**Schema.** `assoc_contacts`/`assoc_companies`/`assoc_households` each gained a nullable
`donation_receipt_preference` enum (`never`/`immediate`/`annual`) — CiviCRM's two independent
contact-level settings (`Bescheinigungen.Spende_bescheinigen` for donations,
`Mitgliedsbeitrag_bescheinigen` for dues) collapsed into one, since nobody asked for the split to
survive and German nonprofit law treats both as the same instrument (a Zuwendungsbestätigung) with
only the checkbox on the form differing. `assoc_debits` gained a nullable `donation_receipt_id` —
which receipt (if any) this debit's payment has already been folded into, the equivalent of
CiviCRM's `civicrm_contribution.receipt_date` but as a link rather than a bare timestamp, since
regenerating/reprinting a receipt shouldn't mean the debit needs receipting again. And
`assoc_donation_receipts` gained a `source` enum (`donation`/`membership`) — a receipt never mixes
the two, matching the German certificate's distinct mandatory wording for each.

**`DonationReceiptGenerator`** exposes three entry points, all operating only on `status = executed,
donation_receipt_id IS NULL` debits:

1. `generateSingle(Debit $debit)` — the on-demand capability. Bypasses the payer's preference
   entirely: an admin choosing to generate a receipt right now *is* the decision every preference
   check exists to make. Wired to a "Bescheinigung erstellen" button on the member/household admin
   page's debits table (`_debits.blade.php`).
2. `generateImmediate()` — every eligible debit whose effective preference is `immediate`, one
   receipt per debit.
3. `generateAnnualBatch(int $year)` — one receipt per payer+source, covering every eligible debit
   due in `$year` or earlier, for payers whose effective preference is `annual` — the catch-up
   CiviCRM's "Jährlich" performed for anything before the first of January.

All three are reachable via `assoc:generate-donation-receipts` (`--debit=<id>` repeatable,
`--year=YYYY`, or no options for the immediate batch — mutually exclusive).
"Effective preference" is `$payer->donation_receipt_preference ?? config('assoc.donation_receipt_default_preference')`
(default `annual`, `.env`-overridable).

**Preference migration.** `CiviCrmImporter::importDonationReceiptPreferences()` (called from
`import()`, so `assoc:import-civicrm` picks it up automatically) reads the "Bescheinigungen" custom
group. Unlike Beitrag/Mastodon/MetaGer_Key, this group's table/column names were never confirmed
against a production dump — it exists only through CiviCRM's admin UI, not shipped extension code,
so nothing in the repository recorded its generated names. Resolved dynamically instead of guessed:
`civicrm_custom_group`/`civicrm_custom_field` are core CiviCRM schema, present and stable regardless
of which numeric suffix a given install generated for the value table. A missing group, missing
fields, or (in a test fixture) a missing `civicrm_custom_group` table entirely all degrade to
"zero preferences imported" rather than failing the whole import — the rest of the data doesn't
depend on it, so a wrong assumption here shouldn't cost contacts/debits/memberships too. **Not yet
run against the real "Bescheinigungen" group** — needs a production dry-run
(`assoc:import-civicrm --dry-run`) to confirm the group/field names actually resolve before this is
trusted. When a contact's two CiviCRM settings disagree, the donation-side one wins arbitrarily and
the conflict is counted in the import summary (`donation_receipt_preference_conflicts`) rather than
silently resolved either way.

**The PDF.** `mpdf/mpdf` (same library the original used) renders
`resources/views/assoc/donation_receipt_pdf.blade.php` — the original's Smarty template ported to
Blade, keeping the legally-mandated boilerplate text verbatim (the §10b EStG reference, the
Finanzamt Hannover-Nord exemption details, the §60a AO note, the liability warning) since altering
it risks invalidating the certificate for German tax purposes. `NumberToGermanWords` is a clean
rewrite of `zahl2wort()`, not a port — the original hand-cased 1–4-digit numbers with duplicated
branches and silently produced wrong output above 9999; this recurses over hundreds/thousands and
supports up to 999999, with 21 test cases including the "eins vs. ein" grammar distinction
(`einhunderteins` but `einundzwanzig`).

**Not ported, deliberately deferred, nobody asked for it in this pass:**
- the thank-you letter (`createDonorThankyou`) — needs a `thankyou` free-text field nothing here
  imports;
- the embedded suma-ev/MetaGer logos and the >2-line-item multi-page layout — visual polish, no
  legal weight;
- PayPal-sourced receipts — phase 4 only covers the Hibiscus/bank-statement side of confirming a
  payment landed.
- **The signee and signature image are configuration, not code.** The original
  (`CRM/Bescheinigungen/Form/DownloadReceipts.php`) hardcoded two board members' names and their
  scanned JPEG signatures directly into extension source — which puts a personal signature image in
  version control. `config('assoc.donation_receipt_signee_name'/'_signature_path')`, both env-only,
  replace that; leaving them unset prints no signature image and the receipt gets signed by hand.
  Nothing is configured yet in any environment.
- **`assoc_debits.status` flipping to `executed` automatically is still not built** — same
  shadow-mode boundary as phase 4. Every debit receiptable today got its `executed` status from
  `CiviCrmImporter`, i.e. from CiviCRM having already executed it. `generateSingle()`/the annual
  batch are safe to run pre-cutover for exactly that reason: nothing here can manufacture a receipt
  for money that was never actually collected.
