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

### Schema (7 tables, `assoc_` prefix — deliberately not `Crm*`)

`Contact` (people — `display_name` covers a donor whose name only ever arrived as one unparsed
string, what CiviCRM's third contact type, "Household", was actually for here; see phase 5),
`Company`, `Membership`, `Debit`, `RecurContribution` (dues + donations share these two via a
`source` enum, matching CiviCRM), `BankStatementLine`, `DonationReceipt`. Payer references are two
nullable FK columns (`contact_id`/`company_id`, exactly one set, enforced at the app layer) — not
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
- Local suite: 1446 passed, 1 skipped, one known pre-existing failure unrelated to this branch
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
6. **Cutover** — in progress, being built as five separable pieces (see below): (a) ~~flip phase
   4's matcher live~~ — done, see below; (b) ~~`assoc:create-debits`~~ — done, see below,
   porting the two `CreateDebits` cron jobs; (c) the SEPA-generation port (`de.suma-ev.donation-
   debit`'s pain.008.001.02 logic); (d) `assoc:charge-keys`, wiring `ChargeKeys`-equivalent
   keymanager charging onto this schema (reuse the existing production keymanager credential —
   already decided, no new credential needed); (e) derived (not stored) payment-status reminder
   emails and the `Membership.Renew` equivalent, **now scoped to also cover chargebacks** (see
   below). `ChargeKeys.php`'s hardcoded bearer token needs rotating once (d) replaces it (see
   extension inventory above).
7. **Mass email** — deliberately last, per explicit prior instruction. Still undecided between
   keeping/improving the WordPress+Newsletter-plugin setup or adopting listmonk.

Phase 6 was scoped into the five pieces above via a research pass re-reading
`de.suma-ev.donation-debit`/`de.suma-ev.bescheinigungen` from the Trash copy (still intact as of
this writing, but see the same "not guaranteed to survive" caveat as phase 4). Findings worth
knowing before touching any of (b)-(e):

- The SEPA XML itself is built with `digitick/sepa-xml` (composer package), not hand-rolled —
  `CRM_DonationDebit_Form_ExecuteDebits::createPaymentInfos()`. The creditor name/IBAN/BIC/
  Gläubiger-ID are hardcoded literals there; the port needs these as `.env`-backed config (real
  values not captured anywhere in this repo — whoever does (c) needs to source them).
- `ChargeKeys.php`'s hardcoded bearer token is a *second*, separate credential from this app's own
  `config("metager.metager.keymanager.access_token")`/`KEY_SERVER` (used today by
  `VRPaymentChargeIssuer`/`ManualChargeIssuer`). (d) should call the keymanager's `/key/create` and
  `/key/{key}/charge` endpoints through that existing config/HTTP pattern, not reintroduce
  `ChargeKeys.php`'s own token.
- For direct-debit members, CiviCRM never wrote `Zahlungsstatus` back to "Okay" after their first
  billing cycle — only `end_date`/debit status were ever the live signal for them. Confirms (rather
  than requires changing) the existing decision that `assoc_memberships.standing` is derived from
  `end_date` + `assoc_debits`, not a stored mirror of the 8-value CiviCRM enum.
- The doc's earlier "9 cron jobs" figure only turned up 8 in the extension source
  (`RecurContribution.CreateDebits`, `IncomingPayment.Auto`, `Membership.UpdatePaymentStatus`,
  `Membership.Chargekeys`, `Membership.Renew`, `Membership.UpdatePublicMemberlist`,
  `Membership.Mastodonpendingaccounts`, `Membership.CreateDebits`) — the 9th may have been a core
  CiviCRM job (e.g. `UpdateMembershipStatuses`) counted alongside the two extensions'. Not chased
  further; flagging rather than guessing which one.
- `assoc_debits.status` today is `pending`/`executed`/`failed`. The legacy flow has a fourth,
  intermediate state — "included in a generated SEPA file, submitted to the bank, awaiting
  confirmation" — that this schema doesn't have yet; (c) will need it (so `Membership.CreateDebits`'
  ~26-day and (c)'s admin-confirmed batch don't both keep re-offering the same still-pending debits).
- Confirming a payment has exactly one side effect in the legacy flow: the debit flips to executed.
  `ChargeKeys` and the payment-status reminders are separate, independently-scheduled jobs reading
  the resulting state afterward, not triggered by the match itself — matches how (a) below and phase
  5's receipt generation are already split apart.
- **New requirement (not in the original 5-piece scoping), status: design not started, deliberately
  deferred — see below.** A returned SEPA collection (Rücklastschrift) needs both an automatic and a
  manual detection path, and the fee the bank charges the association for it needs to be collected
  back from the member and reflected in the payment-status reminders. Raised mid-implementation of
  (b), after discussing it: **`assoc_debits.status` (`pending`/`executed`/`failed`) is the wrong
  shape for this, not just missing a value.** A collection isn't only paid-or-not — a member can
  wire an amount that doesn't match what's owed (under/overpayment, entirely outside any bank-return
  mechanism) with no notice, and a chargeback adds a fee on top of the original amount rather than
  simply failing it. CiviCRM's own model for this is `civicrm_contribution` +
  `civicrm_financial_trxn`: a contribution carries a status (Completed/Partially Paid/Pending/
  Failed/Cancelled) *derived from* however many transaction rows have been applied against it, not a
  single flag written once. The likely shape here is the same — a small ledger of transactions per
  `Debit` (the bank-statement amount actually received, a fee, a correction) that `status` gets
  derived from, replacing today's single write in `BankStatementMatcher::confirm()` — rather than
  another enum value or a bolted-on fee column. This is a real schema change (new table, likely
  changes to how phase 5's receipts and (a)'s matcher read "is this paid"), not a small addition to
  (a)/(e), so it needs its own design pass before any of it is built. Everywhere in phases 4-6 that
  currently treats a debit as binary pending/executed/failed will need revisiting once this lands:
  `BankStatementMatcher::confirm()`, `DebitCreator`'s "does a pending debit already exist" guards,
  phase 5's receipt generation, and (e)'s reminder text.
  - (e): the derived payment-status reminder text must mention a `failed` debit and its fee (once fee
    tracking exists) — this was always going to read `assoc_debits.status`, so a `failed` value is a
    new branch in existing logic, not new plumbing.
  - **Refinement after further discussion — this changes the unit the ledger attaches to.** Real
    member behaviour from the association's history: people change what they pay without telling
    anyone (a quiet underpayment, needs a reminder — silently accepting a short payment as "paid" is
    wrong), pay out of order relative to due dates, or send amounts that match no single due charge
    but whose running total still covers what's owed overall. **The source of truth for what's owed
    is always the fee stored on the membership (`amount`/`interval`), never the amount of any
    incoming payment** — a payment doesn't redefine the charge, it's applied against it. That last
    case (out-of-order, non-matching-but-sufficient-in-aggregate amounts) doesn't fit "a ledger of
    transactions per `Debit`" as sketched above: it needs a running balance kept per membership
    (received-to-date minus owed-to-date), with individual `Debit`/expected-charge rows as accrual
    line items the balance is checked against, not as the specific thing each payment must match one
    to one. `BankStatementMatcher::matchByMandate()`'s current design — pick the pending debit whose
    amount matches exactly, else the earliest-due one — is a best-effort guess at which debit a
    payment was "for"; it should keep doing that for SEPA-collected debits (still useful for
    per-collection SEPA reporting) without being the thing payment-status/reminders actually trust.
  - Overpayment is carried forward, not refunded automatically — refunding only happens if the member
    asks. Two more manual, admin-triggered actions the new interface needs to support (not
    automation): accepting a cancellation after a charge was already due (waiving what's outstanding),
    and refunding the most recently paid fee together with a retroactive cancellation. Both are
    ledger-adjustment entries an admin makes, not derived states, and the ledger design needs an entry
    "kind" for them (waiver, refund) alongside the automatic ones (received amount, chargeback fee).
- **New requirement (not in the original 5-piece scoping), status: design not started, deliberately
  deferred.** Raised separately from the chargeback discussion above: a lapsed member who later
  rejoins must not require deleting their old membership, and every record tied to a person/company
  — memberships, debits, receipts, and (not yet modelled at all) messages sent to them — must stay
  intact and viewable in the admin UI for the statutory 10-year retention period, with a cron doing
  the actual deletion once that window passes rather than an admin action.
  - **Correction after discussion: CiviCRM itself always supported multiple memberships per
    contact — this was never a CRM schema limit.** The actual failure was in the custom automation
    built on top of it (`de.suma-ev.donation-debit`/`de.suma-ev.bescheinigungen`): it couldn't
    reliably tell which of a contact's memberships an incoming contribution belonged to once an old,
    inactive one was also on file, so deleting the lapsed membership was the workaround staff used to
    disambiguate for the automation, not something CiviCRM required. That's the bar this system needs
    to clear — not "allow a second row" (trivial) but "keep automatic contribution/debit
    attribution unambiguous once a second row exists."
  - Where this schema already stands relative to that bar: `Contact::membership()`/
    `Company::membership()` are `hasOne` today (`app/Models/Assoc/Contact.php`, `Company.php`), read
    as a single row by both admin views (`resources/views/admin/assoc/{members,member}.blade.php`),
    so a second membership isn't even visible yet, let alone disambiguated — nothing in
    `assoc_memberships` (civicrm_id aside) stops a second row from existing for the same contact,
    the relation just can't see it. On the automation side, `BankStatementMatcher` already narrows
    its candidates to `pending` debits and `active` recur contributions only
    (`pendingDebits()`/`activeRecurContributions()` in `app/Assoc/BankStatementMatcher.php`), so a
    lapsed direct-debit membership's old mandate has no pending debit left to be confused with a new
    one — that specific case is already safe by construction, not by design intent. The gap is
    banktransfer: `Membership.payment_reference` is imported for every payment method
    (`CiviCrmImporter::importMemberships()`, `zahlungsreferenz_36`) but the matcher never reads it —
    banktransfer memberships get no `assoc_debits` row at all (`DebitCreator` only handles
    `directdebit`) and aren't in `knownMandates()`, so there is currently no automatic
    contribution-matching path for them, safe or not. Building one is exactly where this failure mode
    would resurface: unlike a SEPA mandate, a banktransfer reference is free text a payer may type
    inconsistently or not at all, so two memberships (one lapsed, one current) for the same person
    could easily produce references that don't disambiguate cleanly — the same ambiguity the old
    custom automation had, not yet solved because the matching itself doesn't exist yet.
  - **Resolved after discussion: the sequence is cancel-then-rejoin, not concurrent memberships.**
    Once a member genuinely cancels, that `assoc_memberships` row stays exactly as it is (terminated,
    not deleted) for the 10-year record; a later rejoin is a new row, not a resurrection of the old
    one. So a contact is never meant to hold two `standing => active` rows at the same time — "the
    current membership" is simply "the one row (if any) with `standing => active`", which is what
    `hasOne` already got right for someone on their first or only membership. What `hasOne` got wrong
    was only the historical rows.
  - **Done (`5b7f4a186`).** `Contact::membership()`/`Company::membership()` are now `memberships():
    HasMany`, with `currentMembership(): HasOne` (scoped to `standing = "active"`) as the accessor
    everything that only ever cared about "the" membership keeps using —
    `AssocController`/`resources/views/admin/assoc/{members,member}.blade.php` were the only call
    sites, both switched over; `DebitCreator::createForDueMemberships()` was already querying
    `Membership` directly with its own `where` clauses, not through the relation, so it needed no
    change. The member detail page now also lists terminated memberships as history underneath the
    current one, rather than a rejoin making the old row invisible.
  - The retention side is a second, distinct piece: nothing here today is soft-deleted, nothing
    tracks when a record's retention window started or ends, and there is no messages/communication-
    log entity in the `assoc_*` schema at all yet — that part isn't "add a retention flag to an
    existing table," it's a net-new entity. A general retention/purge mechanism (soft-delete date +
    scheduled cron reading it) likely wants designing once, applied consistently across memberships,
    debits, donation receipts, and whatever the messages entity ends up being, rather than bolted onto
    each table separately.
  - Likely touches, once designed: a soft-delete/retention-window column (or a new migration) on
    `assoc_memberships`, `assoc_debits`, `assoc_donation_receipts` and whatever the messages entity
    ends up being, and a new scheduled command alongside `assoc:create-debits`/`assoc:import-civicrm`
    for the actual purge. The `hasOne`→`hasMany` relation change this depended on is already done
    (see above).

### Payment-ledger design pass

Requested explicitly as its own design pass rather than folded into (c)/(e), on the condition that
anywhere the design was unclear it should ask rather than guess how the association actually
handles it — four questions were asked and answered; this section is the resulting shape. Nothing
below is implemented yet.

**Grounding: what legacy actually does today for banktransfer members**, read from
`Civi\Api4\Action\Membership\{UpdatePaymentStatus,Renew}.php`, because "should follow how we handle
things perfectly" only holds for banktransfer — direct-debit members' standing already comes from
`end_date`/`assoc_debits` (see the phase 6 findings above), untouched by any of this:

- `Renew.php` advances a membership's `end_date` by one period the moment *any* `Completed`
  Member-Dues contribution is recorded against it — it never checks whether the amount matches the
  fee. This is very likely how underpayments went unnoticed in the first place.
- `UpdatePaymentStatus.php` runs independently and stages purely off how far in the past `end_date`
  is: Okay → 1st reminder at `end_date + 2 weeks` → 2nd reminder at `end_date + 4 weeks` (which also
  attaches the SEPA-mandate PDF, nudging a switch to direct debit) → "Unterbrochen" past
  `end_date + 4 weeks`. It never reads the received amount either. Nothing in the read code actually
  terminates a membership automatically past that point — "Unterbrochen" is a status value, not a
  standing change; if legacy ever force-cancelled for non-payment, it happened manually.

**Resolved decisions:**

1. **Coverage only advances once it's actually paid for.** Unlike `Renew.php`, a membership's
   covered-through date does not move forward until the cumulative amount received actually covers
   what's owed for that period. A partial payment is credited to the balance immediately (it isn't
   held in limbo) but does not by itself extend coverage — the shortfall is what a reminder is for.
2. **Reminders stay staged, but the trigger becomes the balance, not the calendar.** The nudge-
   toward-SEPA-then-eventually-cancel escalation is worth keeping — it's a real, working part of how
   the association gets people to switch payment methods — but gating it on "how long since
   `end_date`" is exactly the mechanism that let underpayments through unremarked, since it never
   looked at whether anything was actually short. The new trigger is "the ledger balance has been in
   shortfall for N weeks," measured from the oldest unpaid charge's due date rather than `end_date`,
   reusing legacy's own staging (assumption, not re-confirmed: the same 2/4-week intervals, 1st
   reminder → 2nd reminder with the mandate PDF attached → termination) since only the trigger
   condition was asked about, not the specific intervals. If a payment clears the balance at any
   point, the escalation resets — the reason for it is gone. Termination-for-non-payment past the
   final stage is a real standing change here (`active` → `terminated`), which is new: legacy's own
   code never went that far automatically as far as this read shows.
3. **A chargeback's fee is parsed from the bank statement, never entered manually or read from
   config** — the association's own bank charges these fees and they vary per bank/case, so there is
   no fixed amount to configure and no reliable way for an admin to know the figure except by reading
   it off the same Hibiscus export the return itself arrives in. This is new `BankStatementImporter`/
   `BankStatementMatcher` work — recognising a Rücklastschrift return line, linking it back to the
   original `executed` `Debit` (mandate/end-to-end reference, same as any other match), and
   separately identifying the fee the bank charged the association for it — and, like the IBAN-field
   uncertainty already flagged in `BankStatementImporter`'s docblock, the exact shape a real
   Hibiscus Rücklastschrift export takes isn't nailed down yet; flagging rather than guessing a
   field name.
4. **A chargeback fee is money the member owes, never a donation.** It becomes its own ledger entry
   that adds to what's owed, but must never be summed into a donation-receipt total even when the
   underlying collection it's attached to was a donation — a bank fee isn't tax-deductible. This
   means the ledger needs to distinguish entries by kind for receipt purposes, not just net everything
   into one "amount received" figure (see the entry kinds below).
5. **Refunds and outgoing payments route by how the money actually arrived**, because different
   channels need different rails: banktransfer/direct-debit-sourced payments get refunded as an
   outgoing SEPA credit transfer, PayPal-sourced payments get refunded through PayPal's API. This
   requires that whatever channel funded a ledger entry stays attached to it, not just an aggregate
   balance. It also surfaces a real new dependency: **the association will run the Hibiscus
   Payment-Server**, which becomes the channel for both submitting generated SEPA files (collections
   *and* refund credit transfers) and pulling bank statements automatically. See "Jameica/Hibiscus
   integration design" below for the researched shape of that dependency — it directly grows (c)'s
   scope beyond "generate a pain.008 file" and supersedes the VNC-based sketch this doc originally had
   here.

**Jameica/Hibiscus integration design.** The original sketch here assumed running desktop Jameica
headless and remoting into its GUI (VNC/X11) whenever a TAN was needed. A design-pass spike into
Jameica/Hibiscus's actual capabilities found a materially better mechanism, so this replaces that
sketch rather than sitting alongside it:

- **The right target is the Hibiscus Payment-Server** (`willuhn/hibiscus.server`), a separate
  headless distribution with its own scheduler and REST/XML-RPC APIs — not desktop Jameica's
  `--server` mode, which is a headless daemon but has neither. Confirmed via Payment-Server's own
  docs and source (`StartupParams.java`, `de.willuhn.jameica.hbci.payment.Settings`).
- **TAN handling is an application-level callback, not a remote desktop.** Hibiscus supports a
  configurable **TAN-Handler**: when it needs a TAN, it makes a blocking XML-RPC call *out to a
  service you provide* — `(text, accountId, tanType, payload)` — and waits for the response to
  contain the TAN string (`payload` is the chipTAN flicker code, or a `data:image/…` image for
  photoTAN/QR-TAN). This means Laravel implements one XML-RPC endpoint; Hibiscus calls into it. No
  VNC, no exposed remote GUI, no second interactive surface — the earlier plan's main piece of
  infrastructure turns out to be unnecessary. The association's own GLS Sparda-style TAN procedure is
  currently QR-TAN (an image challenge, straightforward to render in an admin page) and may move to
  push-TAN, which Hibiscus can also handle as *decoupled* pushTAN — the handler just waits while the
  member approves in their banking app, no code/image to display at all.
- **Statement fetches are TAN-free most of the time for a concrete, PSD2-shaped reason, not an
  arbitrary bank mood.** A TAN is required on first account access and on any fetch reaching more
  than 90 days back; inside that window a fetch can stay PIN-only, but only if a TAN-verified fetch
  already happened within the last 90 days, and banks may grant that exemption at most 4×/day. The
  Payment-Server's scheduler defaults to a 180-minute interval (8×/day), which is too aggressive
  against that cap and needs to go to 6+ hours. This matches what's seen operationally against GLS in
  local Jameica today (a TAN is rarely asked for) — but it's a periodic, bank-policy-governed
  requirement, not a one-off, so the same TAN-Handler callback above has to cover the statement side
  too, not just outgoing transfers.
- **The statement side needs a second adapter, not a replacement of the existing one.** Hibiscus's
  read APIs (REST/JSON, or XML-RPC) return transactions in a different shape than the "Umsätze
  exportieren" XML `BankStatementImporter::importHibiscusXml()` already parses. The plan is a second
  adapter feeding the same `BankStatementMatcher`, triggered by Hibiscus's own notify-URL webhook
  after each sync (a plain HTTP POST once new data is in) rather than a poll — keeping the existing
  XML importer as the manual/fallback path, not deleting it.
- **Outgoing money movement (SEPA collection batches, refund credit transfers) is submitted over
  XML-RPC**, using Hibiscus's `sepasammellastschrift`/`sepaueberweisung` services — there's an
  official PHP client (`willuhn/hibiscus.php`) usable directly from this app. It always needs a TAN,
  routed through the same handler; confirmation that it actually went out is only known once the next
  statement pull reflects it, the same "hand off, then confirm via import" shape already designed for
  (a).
- **Deployment stays single-instance by construction** — Jameica's datastore is lock-file-guarded
  against concurrent access, so this is a `replicas: 1` StatefulSet (or Deployment with
  `strategy: Recreate`), one persistent volume holding the workdir (keystore, encrypted wallet, the
  Hibiscus database), master password supplied non-interactively via a mounted secret file rather
  than typed at startup, started in `--server --noninteractive` mode so a TAN request deterministically
  reaches the handler instead of blocking on a console nobody's watching, and port 8080 kept
  `ClusterIP`-only behind a `NetworkPolicy` scoped to this app's pods — it's both the admin API and,
  under HTTP Basic auth, effectively the master password, so it must never be ingressed.
- **Not yet verified — a hands-on spike is needed before building this**: whether GLS actually grants
  the 90-day PIN-only exemption in practice and how often; the exact XML-RPC method signatures for
  the SEPA batch services; whether a generated `pain.008` file can be handed to Hibiscus directly or
  has to be rebuilt as XML-RPC calls order-by-order; the real time budget a human has to answer a TAN
  callback before the bank-side HBCI dialog times out (the reference PHP client sets no timeout of its
  own); and Verification-of-Payee behaviour on outgoing transfers, which could silently block the
  refund path under the EU Instant Payments Regulation. None of these block the shape above, but all
  of them are implementation-time, not design-time, unknowns.

**Shape done (`22df6625e`); directdebit collection now writes to it (`9a578f253`).** A new
`assoc_ledger_entries` table: one row per accrual/payment/adjustment event, tied to a
`Membership` and, where applicable, to the `Debit`/`BankStatementLine` it came from — with an
entry `kind`: `charge` (accrued from the membership's own stored `amount`/`interval`, the source
of truth for what's owed — never a payment's amount), `payment` (received, from whatever
channel), `chargeback_fee` (owed, excluded from receipt totals), `waiver` (admin write-off on an
accepted late cancellation), `refund` (admin-recorded outgoing amount, tagged with the channel it
went out through: SEPA credit transfer vs. PayPal — a `channel` column, shared with `payment`, so
a later refund can route the same way the money arrived). `App\Models\Assoc\LedgerEntry` +
`Membership::ledgerEntries()`/`ledgerBalance()`. `ledgerBalance()` sums in integer cents, not
floats or bcmath — `ext-bcmath` isn't in the fpm image, and `decimal(10,2)` amounts never carry
more than two fractional digits, so cents are exact. The sign convention (a decision made
building this, not itself asked about in the design pass): `charge`/`chargeback_fee` add to what's
owed, `payment`/`waiver` reduce it, `refund` adds back what a `payment` had reduced, since that
money is no longer with the association — positive balance is owed, negative is a credit carried
forward. `assoc_debits` keeps its existing role as "a specific SEPA collection attempt," but
stops being the thing payment-status is read from; that becomes the membership's ledger balance
instead. Donation-receipt generation sums only `payment`-kind entries tied to a donation source,
explicitly excluding `chargeback_fee` — not yet wired up, see below.

`Debit` gained a nullable `membership_id`, decorative FK to `assoc_memberships` — the mandate
string alone isn't a reliable enough link back to the Membership a `"membership"`-source `Debit`
collects for (it isn't unique across memberships, see `assoc_debits`' own migration comment), and
`DebitCreator` already has the `Membership` in hand when it creates that row. `DebitCreator` now
writes a `charge` `LedgerEntry` there too — the amount becoming due — and
`BankStatementMatcher::confirm()` writes the matching `payment` entry (`channel: "directdebit"`)
when it flips that `Debit` from `pending` to `executed`. Guarded the same way the status flip
already was: only a `Debit` actually found still `pending` gets either. A `"donation"`-source
`Debit` (from `RecurContribution`) has no `membership_id` and so gets no ledger entries — the
ledger stays membership-only for now.

`LedgerEntryController` (`ee036814e`) now gives admins the two manual actions from the chargeback
refinement above — a `waiver` write-off and a `refund` record — on `/admin/assoc/members/*`, shown
against the current membership and, since both scenarios described there (a waiver on a late
cancellation, a refund with a retroactive cancellation) target a membership that's just been
terminated, against each past one too. Both are record-keeping only: a `refund` entry means "this
amount went back out," not itself moving money — the actual outgoing transfer still happens by hand
until phase 6c's SEPA generation (and, per the design doc, the Hibiscus Payment-Server) exists. A
`refund`'s `channel` is restricted to `sepa_credit_transfer`/`paypal` (a waiver isn't a transfer of
money and carries none); nothing else about `kind`/`channel`'s validity has changed.

**Banktransfer charge-accrual — done.** `DebitCreator`/`BankStatementMatcher` now cover
`payment_method = banktransfer` too, not just `directdebit` — reusing the existing `Debit`/matcher
machinery rather than a parallel path, since the doc's own framing of the gap ("banktransfer
memberships get no `assoc_debits` row at all") already pointed there. `assoc_debits.iban` is now
nullable (a banktransfer collection has no bank details to snapshot — `bic` was already nullable for
the same reason); `account_holder` falls back to the payer's own name, same pattern
`createForRecurContribution()` already used for donations. `DebitCreator`'s existing
"already-pending" guard needs no new "in arrears" concept to satisfy the agreed behaviour (stop
accruing new charges once someone stops paying, resume forward once they pay again) — a member who
stops paying just leaves their last debit `pending`, which already blocks a new one. `paypal`/`card`
memberships are explicitly excluded, pending the still-open "exact scope of the rebuilt module vs.
what MetaGer's `membership_applications`/`Membership` code already does" decision above — they're
already billed by that separate, pre-existing system.

Fixed in passing: `BankStatementMatcher::confirm()` never advanced `Membership::end_date` on a
confirmed payment, so the *next* `assoc:create-debits` run would offer the exact same just-paid
period again — a latent bug, not yet hit because phase 6a is very new. It now advances by one
interval on confirm, and (per an explicit decision) a debit that sat pending well past its due date
— someone who stopped paying, then resumed — restarts coverage fresh from the day the resuming
payment was booked rather than compounding forward one stale interval from wherever `end_date` had
been stuck; the membership itself is untouched (same row continues, no new `Membership` created) —
also fixed the `payment` `LedgerEntry`'s `channel`, previously hardcoded to `"directdebit"`
regardless of which payment method the underlying membership actually used.

**Rücklastschrift detection — done.** Resolved decision 3's real export shape is now confirmed
(a Hibiscus export supplied for this pass, not fabricated): a chargeback arrives as its own line,
`art` "Retourenbelastung", negative `betrag`, carrying the original collection's end-to-end
reference/mandate exactly. `BankStatementImporter` routes such a line to a new
`BankStatementMatcher::matchChargeback()`/`confirmChargeback()` pair instead of the normal
`match()`/`confirm()`, looked up among `executed` (not `pending`) debits. The fee is *computed*
(`abs(line amount) - original debit amount`), not parsed off the purpose text — the statement nets
the original amount and however many fees applied into one number, and the purpose text itself can
wrap a number's digits mid-string across `zweck`/`zweck2`/`zweck3` (confirmed against the same real
export; fixed the general concatenation, not just for chargebacks, since a long line of any kind was
silently truncated before). A confirmed chargeback flips the `Debit` to `failed`, records a `refund`
entry (reversing the earlier `payment`, tagged with the *original* payment channel rather than
`sepa_credit_transfer` — nothing was sent out, the collection un-happened) and a `chargeback_fee`
entry (new debt, `channel: null`) against its `Membership`, and rolls `Membership::end_date` back to
exactly what it was before that payment's confirmation — a new nullable `assoc_debits.previous_end_date`
snapshot column makes this exact rather than assuming "subtract one interval," which isn't always
right (see the resumption case above). A `"donation"`-source debit still gets no ledger entries,
same asymmetry the normal payment path already has.

Deliberately deferred, not built here:
- **Collecting the fee.** `DebitCreator` still charges exactly `membership->amount`; the
  `chargeback_fee` ledger entry just sits on the balance. Actually collecting it is the still-unbuilt
  balance-driven reminder phase's job (resolved decision 2), not something to bolt onto the next
  regular debit.
- **Manual admin matching for an unmatched chargeback.** `BankStatementController` only searches
  `pending` debits today; finding an `executed` one for a manual reversal match is real, separate
  follow-up work.
- **A `previous_end_date` that's null** (a debit confirmed before this column existed) skips the
  rollback rather than guessing — moot today since nothing has been deployed with the old shape yet.

**Not yet done, in rough dependency order:**
- The balance-driven reminder staging (resolved decisions 1-2 above) and the precise reminder-stage
  intervals/copy (assumed ported from legacy pending actual confirmation).
- Donation-receipt generation (`DonationReceiptGenerator`) still reads `assoc_debits` directly, not
  the ledger — the "sums only `payment`-kind entries" rule above isn't implemented yet, and is
  structurally blocked until it is: `assoc_ledger_entries.membership_id` is `NOT NULL`, so a
  `"donation"`-source `Debit` (no `Membership` at all) can't have a `LedgerEntry` under today's
  schema.

### Phase 6b — `assoc:create-debits`

Ported `Membership.CreateDebits`/`RecurContribution.CreateDebits` as one command (`assoc:create-
debits`), backed by `App\Assoc\DebitCreator`. Both legacy actions used a foreign key
(`Beitrag.Zahlungsstatus`, `civicrm_debit.recur_contribution_id`) neither exists here to avoid
re-offering a due payment that already has a debit in flight — this schema has neither, so both are
derived instead from a pending `assoc_debits` row already sharing the same mandate.

A membership-dues-specific gap surfaced while porting: `assoc_memberships` carries no
iban/bic/account_holder of its own (legacy kept those on Membership custom fields 33-35, which
`CiviCrmImporter::importMemberships()` never pulled in). A new due membership's bank details are
snapshotted from its own most recent `assoc_debits` row sharing the mandate instead — the same
per-row-snapshot pattern every historical imported debit already uses — and a membership with no
debit history at all (a brand-new direct-debit sign-up never yet billed) is skipped rather than
guessed at. In practice this only affects members who joined after cutover and have never been
billed; every migrated CiviCRM member has billing history to snapshot from.

```
metager/app/Assoc/DebitCreator.php
metager/app/Console/Commands/CreateDebits.php                      assoc:create-debits
metager/tests/Unit/Assoc/{DebitCreator,CreateDebitsCommand}Test.php
```

### Phase 6a — bank-statement matcher is live

`BankStatementMatcher::confirm()` (renamed from the former private `assign()`) now flips a matched
Debit from `pending` to `executed` the moment a match is recorded — automatic cascade and manual
match (`BankStatementController::match()`) both go through it, so the flip can't drift between the
two paths. Guarded to only flip a currently-`pending` debit, so a manual match can't silently
downgrade an already-`failed` (bounced/returned) collection back to looking executed. A
`recur_contribution` match still touches nothing — there is no per-collection Debit row to flip in
that case (see the class docblock).

This was shadow-mode's whole reason to exist: phase 5's receipt generation now sees real, live
`executed` debits from confirmed bank-statement matches, not only ones imported already-executed
from CiviCRM. **The two open risks flagged at the end of phase 5's section (PDF persistence, no
CiviCRM-receipted correlation) still apply and are unaffected by this change** — they gate bulk
receipt generation regardless of how a debit became `executed`.

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

**Originally shadow-mode: didn't write to `assoc_debits`/`assoc_recur_contributions`, only
`matched_type`/`matched_id`/`match_method`/`matched_at` onto the `assoc_bank_statement_lines` row
itself — since phase 6a, it's live.** The point of shipping it shadow-mode first was validating the
cascade's hit rate against real traffic before trusting it to drive state anywhere; phase 6a (see
the roadmap section above) turned it on: a confirmed match — automatic or manual — now also flips
the matched Debit to `status = executed`, same as the CiviCRM original did.

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

**Schema.** `assoc_contacts`/`assoc_companies` each gained a nullable `donation_receipt_preference`
enum (`never`/`immediate`/`annual`) — CiviCRM's two independent contact-level settings
(`Bescheinigungen.Spende_bescheinigen` for donations, `Mitgliedsbeitrag_bescheinigen` for dues)
collapsed into one, since nobody asked for the split to survive and German nonprofit law treats both
as the same instrument (a Zuwendungsbestätigung) with only the checkbox on the form differing.
`assoc_debits` gained a nullable `donation_receipt_id` — which receipt (if any) this debit's payment
has already been folded into, the equivalent of CiviCRM's `civicrm_contribution.receipt_date` but as
a link rather than a bare timestamp, since regenerating/reprinting a receipt shouldn't mean the debit
needs receipting again. And `assoc_donation_receipts` gained a `source` enum (`donation`/
`membership`) — a receipt never mixes the two, matching the German certificate's distinct mandatory
wording for each.

**`assoc_households` no longer exists** (folded into `assoc_contacts` — see below); the description
above and the schema section further up already reflect the two-payer-type (`Contact`/`Company`)
shape this settled on, not the original three-type one phase 1 shipped with.

**`DonationReceiptGenerator`** exposes four entry points, all operating only on `status = executed,
donation_receipt_id IS NULL` debits:

1. `generateSingle(Debit $debit)` — on-demand, for one specific debit. Bypasses the payer's
   preference entirely: an admin choosing to generate a receipt right now *is* the decision every
   preference check exists to make. Wired to a "Bescheinigung erstellen" button on the member admin
   page's debits table (`_debits.blade.php`).
2. `generateForPayer(Contact|Company $payer, string $source)` — on-demand, for every outstanding
   debit of one source for one payer, folded into a single receipt. The real-world case this exists
   for: a donor with several unreceipted payments (typically because their preference was
   "never"/unset, the actual default — see below) calls and asks for one now covering everything so
   far. Wired to "Spendenbescheinigung/Beitragsbescheinigung für N offene … erstellen" buttons on the
   member page, shown only when something is outstanding. Same preference bypass as
   `generateSingle()`.
3. `generateImmediate()` — every eligible debit whose effective preference is `immediate`, one
   receipt per debit.
4. `generateAnnualBatch(int $year)` — one receipt per payer+source, covering every eligible debit
   due in `$year` or earlier, for payers whose effective preference is `annual` — the catch-up
   CiviCRM's "Jährlich" performed for anything before the first of January.

`generateImmediate()`/`generateAnnualBatch()` are reachable via `assoc:generate-donation-receipts`
(plus `--debit=<id>` repeatable for `generateSingle()`, `--year=YYYY` for the annual batch — mutually
exclusive); `generateForPayer()` is admin-UI-only, since its input is naturally "whichever payer's
page a staff member is looking at", not something a cron would supply. "Effective preference" is
`$payer->donation_receipt_preference ?? config('assoc.donation_receipt_default_preference')`,
**default `never`** (`.env`-overridable) — most donors never ask for a receipt, and generating one
unasked is a worse mistake than not generating one someone later requests via `generateForPayer()`.
The member admin page also carries a small form to set a payer's own preference
(`/admin/assoc/payers/{type}/{id}/donation-receipt-preference`) — the "and immediately in the
future" half of the same real-world request.

### Household removed as a payer type

CiviCRM's "Household" contact type was never an actual multi-person household in this data — the
original phase 1 schema comment already said as much ("Donation-only payer, keyed by the donor's
full name... never becomes a member"), but it took until phase 5's admin-UI work exposed the
three-way `contact_id`/`company_id`/`household_id` polymorphism on every table to notice it wasn't
worth carrying forward. The actual point of "Household" was only ever to hold a donor's name when it
arrived as one unparsed string CiviCRM couldn't safely split into first/last — this org really only
distinguishes people from organizations.

Folded into `Contact`: `assoc_contacts` gained a nullable `display_name`, used in place of
`first_name`/`last_name` (now both nullable) wherever a display name is needed —
`Contact::name()` is the one place that decides which. `assoc_households` is gone, along with its
model, admin pages (`/admin/assoc/households*` — a former household now just shows up on the regular
members list, by its display name), and the `household_id` column on `assoc_debits`/
`assoc_recur_contributions`/`assoc_donation_receipts`. `CiviCrmImporter::importContacts()` imports a
CiviCRM `Household` contact as a `Contact` with `display_name` set; `importMemberships()` still
excludes the 4 production-dump rows where a `Household`-typed contact somehow has a
`civicrm_membership` row (a CiviCRM data quirk, not a real membership), now tracked via a
`civicrm_contact_type` tag carried alongside each resolved payer rather than a `payers` collection
entry of type `"household"`.

Since this schema hadn't been merged or deployed anywhere, the original migrations
(`2026_09_04_090000`/`090040`/`090050`/`090070`/`090080`) were edited in place rather than layered
with a new one — there's no real data anywhere that depended on the old shape.

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
- **`assoc_debits.status` now does flip to `executed` automatically** — phase 6a wired that up on
  top of phase 4's matcher (see the roadmap section above), so a debit can become receiptable either
  from `CiviCrmImporter` (already executed in CiviCRM) or from a live, confirmed bank-statement
  match. `generateSingle()`/the annual batch remain safe to run either way: both paths only ever mark
  a debit executed once a real payment was actually matched to it.

**Two open risks found after the fact, neither resolved yet — do not run bulk generation against
production data until both are:**

- **Generated PDFs are not persisted anywhere durable.** `Storage::disk('local')` writes to
  `storage/app` on the fpm container, and `chart/templates/_helpers.tpl`'s volume mounts back that:
  only `mglogs` (a real PVC), `sqlite-databases` and `fast-logs` (both `emptyDir`) are mounted, none
  at `storage/app`. Autoscaling runs 1–5 fpm replicas by default (`chart/values.yaml`), so a receipt
  generated on one pod can 404 on download if routed to another, and every receipt vanishes outright
  on the next deploy regardless of replica count. `config/filesystems.php` defines an `s3` disk but
  nothing uses it — no object-storage infra exists in the chart to point it at. Needs a decision
  (extend the `mglogs` PVC mount to cover receipts, add a dedicated PVC, or provision object storage
  for the `s3` disk) before any receipt generated is trusted to still exist tomorrow.
- **No correlation exists yet between an imported `assoc_debits` row and whether CiviCRM already
  issued a receipt for it.** CiviCRM itself never persisted receipt PDFs — `DownloadReceipts.php`
  only ever streams one as a `Content-Disposition: attachment` download, regenerated fresh from data
  each time — so there is no historical PDF archive to migrate, only the fact of "already receipted"
  to preserve. That fact lives on `civicrm_contribution.receipt_date`, core CiviCRM schema our
  importer never reads (it only reads `civicrm_debit`/`civicrm_recur_contribution`, extension-specific
  tables), and `civicrm_debit` carries no `contribution_id` linking back to it — `Auto.php` always
  creates a *new* `civicrm_contribution` row per collection rather than referencing an existing one,
  so there's no simple join to resolve this from. Until this is sorted, every `assoc_debits` row
  imported from CiviCRM looks unreceipted regardless of its real history, and `generateAnnualBatch()`/
  `generateForPayer()` run against production data would risk re-issuing a Zuwendungsbestätigung for
  a donation already receipted under the old system.
