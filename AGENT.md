# AGENT.md — MetaGer

This file provides guidance to AI coding agents working in this repository.

**Self-update rule**: when you learn something new and relevant about this project's architecture,
configuration, or conventions that isn't already captured here, update this file before finishing
the task.

## Commands

```bash
# Full local stack (nginx :8080, fpm, scheduler, queue, reverb, redis, node, selenium)
docker compose up --build

# Artisan / composer / npm all run inside containers, not on the host
docker compose run --rm composer install
docker exec -it metager-fpm-1 php artisan migrate
docker exec -it metager-fpm-1 php artisan tinker

# Frontend build (webpack-mix)
docker compose run --rm node npm run development   # or watch / production

# Lint a single PHP file (no host PHP CLI — use the fpm container)
docker exec metager-fpm-1 php -l app/SomeFile.php

# Browser/e2e tests (Laravel Dusk, tests/Browser/) run against the selenium_standalone_firefox
# service already in docker-compose.yml
docker exec -it metager-fpm-1 php artisan dusk
```

There is no host-installed PHP — always exec into `metager-fpm-1` for anything PHP-related
(`php -l`, `artisan`, composer). `phpunit.xml` + `tests/Browser/` exist; CI has a dedicated
`integrationtest` stage (`.gitlab/ci/integrationtest.yml`).

## Architecture

### The Foki system (search "focuses")

Foki (search focuses: web, bilder/images, nachrichten/news, produkte/products, science, chat, ...)
are data-driven from `config/sumas.json`'s `foki` object — each entry has `display-name`, `main`
(default-enabled engines), and `sumas` (all engines for that focus).

- `app/Searchengines.php::parse_available_foki()` filters `sumas.json`'s foki down to
  `available_foki`, per-locale (an engine must support the current language/region to count). A
  focus with an **empty `sumas` array** (e.g. `chat`, which has no engines at all) is included
  unconditionally rather than going through the per-engine locale filter.
- `app/SearchSettings.php` (`$fokus` property, ~line 69-78) resolves the focus from the `focus`
  query param, falling back to `web` if not a real `sumas.json` key (whitelist:
  `array_merge(array_keys($sumasJson->foki), ["maps"])` — `maps` is the one focus that
  deliberately has **no** `sumas.json` entry at all, since it redirects to an entirely separate
  external app rather than participating in this system).
- Both Foki switchers (`resources/views/index.blade.php`'s `#foki-switcher` on the startpage,
  `resources/views/parts/foki.blade.php` on result pages) iterate `available_foki` — adding a new
  focus to `sumas.json` (+ a `'foki' => [...]` label per `lang/{base-lang}/index.php`, see below)
  makes it appear in both automatically, no template changes needed.

**`config/sumas.json` is gitignored** (`metager/config/.gitignore`) and in review/production is
mounted from a Kubernetes `secrets` volume at `subPath: SUMAS_JSON`
(`chart/templates/deployment.yaml`) — **not** from anything in this repo. Local dev only:
`build/fpm/entrypoint/validate_laravel.sh` copies the tracked `config/sumas.json.example` (a
minimal stub, just a `web` entry with empty `main`/`sumas` — not a full mirror of the real foki
list) if no `sumas.json` exists yet. **Editing `metager/config/sumas.json` directly only affects
your local environment** — a change meant to reach review/production has to go wherever the real
`SUMAS_JSON` secret content is maintained (outside this repo).

### Locale/lang files

`lang/{locale}/` — only 11 base languages have their own files (`da, de, en, es, fi, fr, it, nl,
pl, pt, sv`); regional variants (`de-DE`, `de-AT`, `en-US`, ...) fall back to the base language
automatically. When adding a foki label or any other translated string, edit all 11 base-language
files, not the regional ones.

### Search request flow

`app/Http/Controllers/MetaGerSearch.php::search()` — entry point for the `resultpage` route
(`GET|POST /meta/meta.ger3`). Special-cased foci get an early branch here, before the normal
pipeline (`checkSpecialSearches` → `createQuicktips` → `startSearch` → `waitForMainResults` →
`retrieveResults` → `$metager->createView()`):

- `maps` — redirects to the external maps.metager.de app (no `sumas.json` entry, see above).
- `chat` — renders `$metager->createView()` directly (no redirect, no engines to query), and does
  this **before** the "empty query → redirect to startpage" and "no enabled engines → redirect to
  settings" checks that every other focus hits, since chat must work with no query typed
  (lands on an empty composer) and has zero engines by design.

`app/MetaGer.php::createView()` branches on `fokus` (`bilder` has its own image-result templates;
`chat` returns a thin wrapper view, `resultpages/resultpage_chat.blade.php` /
`results_chat.blade.php`, ignoring `$this->out` entirely since it's a single static page, not a
paginated/AJAX result set like everything else). Protected properties (`$this->eingabe`,
`$this->mobile`, etc.) are populated by `parseFormData()` in the constructor — available even when
no actual search has run yet (this is what lets `chat` and the "empty query" case call
`createView()` immediately).

### Auth gating happens at the view level, not route middleware, for per-focus checks

`app/Http/Middleware/AuthenticationValidation.php` (route middleware on `resultpage`) gates the
**whole route** on being able to afford at least `Searchengines::getSearchCost()` (floored to a
minimum of 1, even if the focus has zero real engines — this floor applies identically to `chat`,
`maps`, and `web`; it's pre-existing behavior, not focus-specific). This redirects unauthenticated/
unfunded requests to the startpage before the controller ever runs.

Additional, focus-specific auth/balance UI (e.g. chat's "get a key" prompt vs. a low-balance
banner) is handled **inside the Blade view itself** via `\Auth::guard("key")->user()` and
`->getKeyState()` (`App\Authentication\KeyState` enum: `FULL`/`LOW`/`EMPTY`/`NO_KEY`), mirroring
`index.blade.php`'s startpage pattern — not in the controller or middleware. This is
defense-in-depth (the middleware above already blocks the fully-unauthenticated case for any
focus) rather than the primary gate.

Legacy `app(Authorization::class)->loggedIn` (anonymous-token webextension flow, being phased out)
is still checked alongside `Auth::guard("key")->user()` in most "am I logged in" checks across the
app — match both, don't drop the legacy check without verifying it's actually gone first.

### Sibling services (polyrepo)

`metager-keymanager` (key/billing system of record) and `SafeBrowse` (remote browser sessions,
`/proxy`) are separate repos/deployments, connected to this app only via:

- A shared Docker network locally (`metager_net`, `192.168.5.0/24`, static dev IPs per service —
  keymanager `.100`, SafeBrowse `.200`/`.201`). None of the sibling `docker-compose.yml` files mark
  this network `external: true`; whichever project's `docker compose up` runs first creates it.
- nginx reverse-proxy routes in `build/nginx/configuration/nginx-default-dev.conf` (dev, IPs) /
  `nginx-default.conf` (production, k8s service DNS like `keymanager-main.keymanager`) — regex
  `location` blocks matching an optional leading locale segment, e.g.
  `^(/[^/]+)?/keys(/.*)?$`.

`metager-chat` (LLM chat, `/chat`) follows the exact same pattern (dev IP `.202`) — see
`docs/llm/metager-chat-service/` for its design docs and
`docs/llm/metager-chat-service/bootstrap-guide.md` for how its repo/CI/proxy route came to exist.
Unlike keymanager/SafeBrowse, its nginx block **strips** any locale prefix before proxying
(`rewrite "^/[^/]+(/chat.*)$" $1 break;`) rather than forwarding it — see
`metager-chat/docs/planning/locale-awareness.md` for why (it's iframe-embedded, not a
directly-navigated bookmarkable portal like keymanager's key-management pages).

### Deployment

Helm chart (`chart/`) + `.gitlab-ci.yml` including `.gitlab/ci/*.yml` stage files. Secrets/config
(including `SUMAS_JSON`, see above) are mounted from a k8s `secrets` volume populated by
`.gitlab/deployment_scripts/update_secret.sh` in CI, not stored in this repo.
`.gitlab/agents/metager/config.yaml` grants this project `ci_access` to its own `metager` GitLab
Kubernetes agent — the same pattern `metager-chat` copies for its own agent.

## Docs

- `docs/llm/` — planning docs for the LLM chat feature (`metager-chat-service/` = design docs for
  the sibling repo, `metager-integration/` = the Foki/UI wiring in *this* repo, both described
  above are already implemented; `open-questions.md` / `future-considerations.md` for what isn't).
