# CiviCRM replacement — moved

The CiviCRM-replacement backoffice (contacts, companies, memberships, debits, the donation-
receipt/bank-statement-matching/SEPA-generation machinery this file used to describe in full) has
been extracted out of this repo into two separate projects. See the "Extract the Assoc/CRM
backoffice into its own project(s)" plan for why and how the split happened; the code itself was
removed from `metager/` once both new projects reached full test parity with the `Assoc` suite
this repo used to carry.

- **Business/ledger/membership/donation-receipt design** — `suma-crm`'s own
  `docs/civicrm-replacement.md`.
- **Payment-mechanics design** — SEPA/pain.008 generation, bank-statement matching, chargeback
  detection, and the still-open Hibiscus/Jameica Payment-Server integration spike —
  `suma-payments`' own `docs/civicrm-replacement.md`.

`app/Http/Controllers/MembershipController`/`DonationController`, `app/Models/Membership/*`, and
their PayPal integration code are untouched here and were never part of this move — see the
extraction plan's Phase B for their eventual fate (thin pages calling into suma-crm/suma-payments
instead of doing everything locally).

This file's own git history (up to and including the commit that removed the ported `Assoc` code
from this repo) still holds the original, single-app version of this document if the detailed
narrative of how the design evolved is ever needed from this side.
