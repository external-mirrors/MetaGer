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
docker compose up -d nginx fpm redis reverb selenium_standalone_firefox
docker compose run --rm --no-deps -T --entrypoint /usr/local/bin/php fpm artisan dusk
```

The app is then on `http://localhost:8080`, and Selenium's noVNC view on `http://localhost:7900`.

## Test layout

| suite | config | needs | run with |
|---|---|---|---|
| `tests/Unit` | `phpunit.xml` | nothing | `artisan test` |
| `tests/Feature` | `phpunit.xml` | nothing | `artisan test` |
| `tests/Browser` | `phpunit.dusk.xml` | Selenium + running app | `artisan dusk` |

`phpunit.xml` deliberately excludes `tests/Browser` — keep it that way, so the default run never
depends on a browser container. CI mirrors the split: the `test` job has no Selenium service, only
`browsertest` does.

Prefer a Feature test. Reach for Dusk only when a real rendering engine is genuinely required —
today that means the no-JavaScript behaviour and the locale-prefixed URLs (see below).

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

**`new Agent()` reads the `$_SERVER` superglobal**, not the Laravel Request — so `withHeader('User-Agent')`
alone does not reach it from a test.

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
