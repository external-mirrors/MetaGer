# MetaGer — working notes

Laravel app for the MetaGer metasearch engine. The Laravel project lives in `metager/`;
`build/` holds the Docker images, `chart/` the Helm chart.

## Every change ships with tests

Not aspirational — it is the rule. New behaviour gets a test, a bugfix gets a regression test, and
a refactor gets characterization tests written *before* the refactor starts. If you are about to
change code that nothing covers, write the covering test first and commit it separately.

When you pin behaviour you believe is wrong, say so in the test: name it a characterization test,
explain what is wrong, and let it fail loudly when someone later fixes it on purpose. There are
existing examples in `tests/Unit/SearchSettingsBlacklistTest.php` and
`tests/Feature/StaticPagesTest.php`.

## Running things

There is **no local PHP or Composer toolchain**. Everything runs through the compose services:

```bash
docker compose build fpm                     # build the image first
docker compose run --rm --no-deps -T --entrypoint /usr/local/bin/php fpm artisan test
docker compose run --rm --entrypoint /usr/bin/composer composer install
```

For the browser suite you need the app and Selenium running:

```bash
docker compose up -d nginx fpm valkey reverb selenium_standalone_firefox
docker compose run --rm --no-deps -T --entrypoint /usr/local/bin/php fpm artisan dusk
```

The app is then on `http://localhost:8080`, and Selenium's noVNC view on `http://localhost:7900`.

Frontend assets go through the `node` service:

```bash
docker compose run --rm --no-deps -T --entrypoint /usr/local/bin/npm node install
docker compose run --rm --no-deps -T --entrypoint /usr/local/bin/npm node run build
docker compose run --rm --no-deps -T --entrypoint /usr/local/bin/npm node test
```

**The feature suite needs a build.** `Vite::asset()` throws without `public/build/manifest.json`,
so every page-rendering test 500s on a fresh checkout until `npm run build` has run once.

**Tooling services sit behind compose profiles**, so `docker compose up` starts the application
and nothing else. `selenium_standalone_firefox` is in the `test` profile, `composer` and `node` in
`tools`. Neither `docker compose run` nor naming the service explicitly on `up` needs the profile
flag — both enable it — so every command above works as written. `docker compose --profile tools up
node` is the way to get the asset watcher.

**The cache service is `valkey`**, matching what the Helm chart deploys. It keeps a `redis` network
alias, so an existing gitignored `metager/.env` saying `REDIS_HOST=redis` still resolves;
`.env.example` names `valkey` for fresh checkouts. After the rename, one
`docker compose up --remove-orphans` clears the old `redis` container.

## Test layout

| suite | config | needs | run with |
|---|---|---|---|
| `tests/Unit` | `phpunit.xml` | nothing | `artisan test` |
| `tests/Feature` | `phpunit.xml` | a Vite build | `artisan test` |
| `tests/Browser` | `phpunit.dusk.xml` | Selenium + running app | `artisan dusk` |
| `resources/js/**/*.test.js`, `vite.config.test.js` | `vite.config.js` | nothing | `npm test` |

`phpunit.xml` deliberately excludes `tests/Browser` — keep it that way, so the default run never
depends on a browser container. CI mirrors the split: the `test` job has no Selenium service, only
`browsertest` does.

Prefer a Feature test. Reach for Dusk only when a real rendering engine is genuinely required —
today that means the no-JavaScript behaviour, the locale-prefixed URLs (see below) and the theme
palette, where only a browser resolves `var()`.

`tests/Browser/ThemeColorsTest` snapshots every colour declaration in every loaded stylesheet,
resolved through the browser, into `tests/Browser/snapshots/theme-colors-{light,dark}.json` — 808
declarations per theme. If a change is meant to alter a colour, regenerate and read the diff:

```bash
docker compose run --rm --no-deps -T -e UPDATE_THEME_SNAPSHOTS=1 \
    --entrypoint /usr/local/bin/php fpm artisan dusk --filter ThemeColors
```

Regenerating to make a red test green throws the safety net away. Every changed line is a colour
that changed on the site.

## Constraints that bite

**Progressive enhancement is mandatory.** The site must work with client JS disabled; JS only
enhances. This rules out client-rendered UI frameworks. `tests/Browser/ProgressiveEnhancementTest`
runs Firefox with `javascript.enabled=false` and is what keeps this honest.

**Web routes have no session, so Laravel CSRF does not apply there.** `StartSession` is removed
from the `web` group in `bootstrap/app.php`. A same-origin check stands in for CSRF.

**Never let an HTML formatter touch `.blade.php` files.** It splits `{{--` markers and silently
kills the page. Edit blades by hand.

**Locale routing is resolved once per boot, not per request.** `RouteServiceProvider::mapWebRoutes`
registers routes under `prefix => Localization::setLocale()`, which reads `request()->segment(1)`.
Under `artisan test` the console kernel's `SetRequestForConsole` builds that request from
`config('app.url')`, so the whole feature suite runs as a single locale with unprefixed routes:
`$this->get('/about')` works, `$this->get('/de-DE/about')` 404s. Per-locale URL coverage has to be
a Dusk test.

**Never leave routes cached — `php artisan optimize` must always be followed by `route:clear`.**
Because the locale is resolved *while registering* routes, a route cache means `mapWebRoutes` never
runs and `app.locale` stays the literal `'default'` from `config/app.php`. `entrypoint_production.sh`
has done this since long before anyone wrote it down:

```bash
php artisan optimize
php artisan route:clear # Do not cache routes; Interferes with Localization
```

It does not fail as a routing error, which is what makes it expensive to diagnose. Engines whose
language map has no entry for the current locale are disabled, and the web engines declare
`languages => []` with only exact regional keys — so *every* engine is disabled, `MetaGerSearch`
answers "no enabled engines" with a redirect to `settings#engines`, and the whole search suite fails
with `expected 200, got 302` and nothing in the log. The CI test job was the one place that ran
`optimize` without the `route:clear`; that cost three pipeline round trips to find.
`EngineReachabilityTest::testAWebSearchHasEnginesToQuery` now fails once and names the locale and
the per-engine `DisabledReason`, instead of eighty tests failing identically.

**`App\Support\Browser` is the only device-detection service.** It wraps `matomo/device-detector`
and normalises names to the short forms the views branch on (`Edge`, not `Microsoft Edge`). It
reads the Laravel Request, so `withHeader('User-Agent')` works from a test — unlike the three
libraries it replaced, one of which read the `$_SERVER` superglobal directly.

## Assets

Vite, not laravel-mix, and **no dev server**: `npm run watch` runs `vite build --watch` and writes
real files. Vite's dev server would deliver stylesheets through a JS module that injects them at
runtime, which breaks the no-JS requirement in development and hides the regressions
`ProgressiveEnhancementTest` exists to catch. Because nothing writes `public/hot`, `Vite::asset()`
always reads the manifest.

Entries are referenced **by source path**, not by output path and not with `@vite`:

```php
Vite::asset('resources/less/metager/metager.less')   // a URL
Vite::content('resources/less/…/widget-template.less') // the file's contents, for inlining
```

`@vite` is unusable here because pages pass asset URLs into layouts as `$css` / `$js` / `$darkcss`
arrays, and the dark theme is applied by putting `media="(prefers-color-scheme:dark)"` on the link
tag — which the directive cannot express and which has to work without JS.

Anything added to `input` in `vite.config.js` becomes reachable; anything removed stops being
built. `tests/Feature/AssetPipelineTest` cross-checks the `Vite::asset()` calls, that input list
and the built manifest against each other, and fails on any `public_path()` reaching for a `.css`
or `.js` — build output has hashed filenames, so it can only be reached through the manifest.

Asset URLs are forced root-relative in `AppServiceProvider`; the same app answers on `metager.de`,
`metager3.de` and a `.onion` address, so a host-qualified URL is only ever a way to get it wrong.

## Configuration

- `config/foki.json` is the authoritative list of which search engines belong to which fokus.
  Engines themselves are discovered by scanning `app/Models/parserSkripte/*.php` for
  `CONFIG_OVERLOAD` (see `SearchEngineRegistry`), so a parser file that exists is loaded and
  instantiated whether or not any fokus uses it.
- `config/sumas.json` is **gitignored and holds real API secrets**. It only ever carries
  credentials; everything non-secret lives on the parser classes. A fresh checkout has none —
  `cp config/sumas.json.example config/sumas.json` to boot.

## Search request flow

FPM pushes fetch jobs onto a Redis list → the `requests:fetcher` worker (`artisan requests:fetcher`,
multi-curl) fetches upstream → results go back onto Redis → FPM blocks on `Redis::brpop` in
`MetaGer::waitForMainResults` (up to 6s) and renders.

Note `brpop` is called with a **multi-key array**, which rules out Redis/Valkey *cluster* mode
(CROSSSLOT). HA has to be sentinel-based.

## Commits

Conventional style, as used in the repo: `feat(scope):`, `fix(scope):`, `refactor(scope):`,
`test:`, `build(scope):`, `docs:`. Keep one concern per commit.
