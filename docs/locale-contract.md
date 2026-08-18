# The locale contract

Four codebases have to answer the same question — *what language is this user reading in?* — and
until now each answered it differently. MetaGer forced German on `metager.de`, the keymanager
stripped English locales on that same host, SafeBrowse worked in two-letter codes and read a cookie
nobody else wrote, and the app threw away the device's region before asking anything. The four
disagreed most often for exactly the users whose browser language and chosen language differ, which
is the population this document exists for.

This is the contract. It is short on purpose: one order, one cookie, one fallback.

## 1. Three concepts, three storages

| Concept | What it decides | Where it lives |
| --- | --- | --- |
| **Interface locale** | Which translation, which URL prefix, `<html lang>` | URL path prefix (canonical), `mg_locale` cookie (persistence), `?lang=` / `MG-Locale` (explicit clients) |
| **Result market** | Which region search engines are asked about | `{fokus}_setting_m`, one per fokus |
| **Host** | Branding, nothing else | — |

The interface locale and the result market used to be the same cookie, `web_setting_m`, which is why
changing the language of your *results* changed the language of the *page* and moved you to another
domain. They are separate settings and this document keeps them separate.

**The host decides nothing.** `metager.de` and `metager.org` each serve every locale. The host
appears exactly once below, as the fallback for a request that stated no preference at all, and
reaching that fallback never produces a redirect.

## 2. Resolution order

Take the first that yields a supported locale:

1. an explicit `?lang=` query parameter, or an `MG-Locale` request header
2. the URL path prefix — `/es-ES/…`
3. the `mg_locale` cookie (or, while the migration window is open, `web_setting_m`)
4. `Accept-Language`, negotiated against every locale the project supports
5. the host: `de-DE` on `metager.de`, `en-US` everywhere else

A client that keeps its own settings rather than accepting cookies — the WebExtension, the app —
may send any of these as a request header under the same name.

### Notes that are easy to get wrong

- **Resolution is independent of whether a translation exists.** `ca-ES` resolves to `ca-ES` in every
  project, including the ones that ship no Catalan catalogue; those fall back when *loading* the
  catalogue, not when deciding the locale. Mixing the two is what produced the keymanager's
  "supported locales" list that quietly differed from MetaGer's.
- **Tags are BCP-47 with a hyphen** — `de-DE`, not `de_DE`. The *market* keeps the underscore form
  (`de_DE`), because that is what `config/filters.json` and the search engines use. Any
  implementation that stores both must not let one shape leak into the other.
- **A bare language gets its home region** (§4). `Accept-Language: de` is a legal request and has to
  end up somewhere specific, because both the URL prefix and the market need a region.
- **`Vary: Accept-Language, Cookie`** on any response whose content depended on steps 3–5.
- **Two-letter path prefixes are not locales.** `/uk`, `/ie`, `/es` and `/at` were what we handed
  out before July 2023, and a redirect kept them working for three years afterwards. They were
  retired in August 2026: those segments are ordinary path components now, in every project. Do not
  reintroduce them in one implementation alone — a prefix that means Austria to one half of the site
  and nothing to the other is worse than a dead link.

## 3. No non-navigation request is ever redirected for locale

An `XMLHttpRequest`, a `fetch`, a WebSocket upgrade, or anything else without
`Sec-Fetch-Mode: navigate` gets **answered**, never `302`'d to another origin. A page's own CSP is
`connect-src 'self'`, so a cross-origin redirect on an XHR is not a detour — it is a failure the
calling code sees as a network error, and that is how a language switch used to leave the start
page's search box permanently unsubmittable.

This rule holds regardless of what the resolution order decides. It is the reason the order can be
changed safely.

## 4. Home regions

The region a bare language stands for:

| | | | | | |
| --- | --- | --- | --- | --- | --- |
| `ca` → `ca-ES` | `da` → `da-DK` | `de` → `de-DE` | `en` → `en-US` | `es` → `es-ES` | `fi` → `fi-FI` |
| `fr` → `fr-FR` | `it` → `it-IT` | `nl` → `nl-NL` | `pl` → `pl-PL` | `pt` → `pt-PT` | `sv` → `sv-SE` |

Catalan's home region is Spain although Catalan is also spoken in Andorra and southern France:
`ca_ES` is the only Catalan market MetaGer offers, so it is the only one a home region could name.

The same table appears as `LocaleContext::HOME_REGION`, as `SettingsController::LANG_TO_LOCALE`, and
as `HOME_MARKET` in the app's `src/search/market.ts`. It is in the fixture (§6) so those three stay
identical.

## 5. What a client should send as its market

A client with no cookie jar — the app — cannot let the backend guess, because the backend's guess
for a request carrying no `Accept-Language` and no cookie is the `default` locale, whose regional
form is the empty string, which applies **no market filter at all**. So the market always travels
explicitly, as a query parameter.

Which market: the device's own region when MetaGer both supports that locale
(`config/laravellocalization.php`) and can actually search it, otherwise the home region of the
device's language, otherwise nothing.

That chain reads the *device*, never the interface language — a `de-AT` phone searches Austria
whichever language it renders in, and a `ca-ES` phone searches Catalonia even where no Catalan
translation exists. A client may fall back to its interface language when the platform will not name
a locale at all, which is a real case on Android (a Hermes build without `Intl`) and the only one.

"Can actually search it" is a real exclusion, not a hypothetical: `en_MY` is offered as a market but
appears in no parser's `regions` map, so a search in it disables every engine and returns nothing.
It is pinned in `MarketFilterTest::UNSEARCHABLE_LOCALES` with that reason. A client that sent the
device's region blindly would break search for exactly the users whose device is set to Malaysian
English.

## 6. The fixture

`metager/tests/Fixtures/locale-cases.json` is the executable form of this document, copied verbatim
into all four repositories and run by each one's own test suite:

| Repo | Copy | Test |
| --- | --- | --- |
| MetaGer | `metager/tests/Fixtures/locale-cases.json` | `tests/Feature/LocaleContractTest.php` |
| app-en | `src/search/__fixtures__/locale-cases.json` | `src/search/market.test.ts` |
| metager-keymanager | `pass/test/fixtures/locale-cases.json` | `pass/test/langdetector.test.js` |
| SafeBrowse | `app/i18n/__fixtures__/locale-cases.json` | `app/i18n/translator.test.js` |

It has three sections. `resolution` is a list of `{host, path, query, cookies, headers} → expect`
cases for the three server-side projects. `home_regions` is §4. `device_markets` is §5, as
`device locale → market or null`; the app asserts what it sends, MetaGer asserts that every market
named is one `config/filters.json` actually offers.

Copying rather than sharing a package is deliberate for now: the four are PHP, PHP, Node and React
Native, and a shared npm package would still leave MetaGer out. A drifted copy is a caught bug — the
diff is the whole point — and a package can follow once the contract has stopped moving.

## 7. Migration

`web_setting_m` held the interface language before this contract. On a request that carries it but
no `mg_locale`, read the language out of it, write `mg_locale`, and **leave `web_setting_m` alone**:
it is still a perfectly good market filter, and deleting it would silently change people's search
results while claiming to fix their language.

The window closes when the read at step 3 is removed. Until then, one hole needs plugging: a browser
that never had `web_setting_m` and then sets a *market* would have that market read back as a
language on its next request. MetaGer closes it by pinning `mg_locale` before writing a market
(`SettingsController::enableFilter()`); any other project that writes markets must do the same.

`LOCALE_DECOUPLED=false` restores the previous host rules wholesale, for as long as the rollout is
being watched. The `metager_locale_decisions{reason}` counter is what to watch: the
`domain_language` series should go to zero and stay there.
