<?php

namespace App\Providers;

use App\Localization\LocaleContext;
use App\Models\Authorization\LogsAuthGuard;
use App\Models\Authorization\LogsUser;
use App\Models\Logs\LogsAccountProvider;
use App\Localization\MetaGerLocalization;
use App\Queue\FailoverRedisConnector;
use App\Routing\CookieCarryingUrlGenerator;
use App\Support\Browser;
use Mcamara\LaravelLocalization\LaravelLocalization;
use App\Support\UpstreamUserAgent;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        config(["app.locale" => "default"]);

        /**
         * Swap in CookieCarryingUrlGenerator so `route()` and `url()` keep a
         * cookie-blind visitor's key on every generated link. See its
         * docblock for why a generator subclass rather than a formatting
         * hook: there is no query-string equivalent of `formatPathUsing`.
         *
         * First in `boot()`, deliberately, and not next to
         * `formatPathUsing` below where it reads more naturally: the
         * `forceScheme` and `formatPathUsing` calls a few lines down each
         * resolve 'url' through the `\URL` facade the moment they run. Doing
         * that before this registration would build and cache the
         * *original* `UrlGenerator` first, apply `forceScheme` to that
         * instance, and then have this `singleton()` call throw the cached
         * instance away — leaving the fresh CookieCarryingUrlGenerator every
         * later call actually uses never forceScheme'd at all, so
         * `/es-ES/lang` came out `http://` on an environment that requires
         * `https://`. Registering the binding before anything else touches
         * `url` means whichever call resolves it first builds this class.
         *
         * Mirrors `Illuminate\Routing\RoutingServiceProvider::registerUrlGenerator()`'s
         * own singleton factory, class swapped — with one deliberate
         * difference: that factory's `$app->instance('routes', $routes)` is
         * dropped. It exists there to publish the route collection the
         * *first* time 'url' is ever built, during core framework bootstrap.
         * By the time this runs, that has already happened — 'routes' is
         * already bound to this exact object — and calling `instance()`
         * again on an already-bound abstract fires its `rebinding()`
         * listeners. One such listener, registered by that same provider's
         * `extend('url', ...)`, resolves 'url' again to call `setRoutes()`
         * on it — which, while this factory is still on the stack building
         * 'url' for the first time under the new class, sends the container
         * straight back into this same closure. Every request-bound console
         * command hit that as an immediate stack overflow.
         *
         * That provider's `extend('url', ...)` — the session/key resolvers
         * `signedRoute()` needs — still runs afterward regardless: container
         * extenders are keyed by abstract name and are untouched by this
         * rebind.
         */
        $this->app->singleton('url', function ($app) {
            return new CookieCarryingUrlGenerator(
                $app['router']->getRoutes(),
                $app->rebinding('request', function ($app, $request) {
                    $app['url']->setRequest($request);
                }),
                $app['config']['app.asset_url']
            );
        });

        if (Request::getHost() !== "metagerv65pwclop2rsfzg4jwowpavpwd6grhhlvdgsswvo6ii4akgyd.onion" && (app()->environment("production") || app()->environment("development"))) {
            \URL::forceScheme("https");
        }

        // Emit root-relative asset URLs, the way mix() did before Vite replaced it.
        //
        // Vite::asset() otherwise runs through asset(), which builds an absolute URL from the
        // current request. The same application answers on metager.de, metager3.de and the
        // .onion address above, so an absolute URL only ever adds a host that has to match —
        // and a scheme that has to match too, which is why the forceScheme() call above exists
        // at all. A root-relative path cannot get either wrong.
        Vite::createAssetPathsUsing(fn(string $path): string => "/" . ltrim($path, "/"));

        /**
         * Put the locale back on every generated path.
         *
         * The counterpart to `ResolveLocale`, which takes it off the incoming
         * one. Between them, the route table never mentions a locale and every
         * `route()`, `url()` and `redirect()` carries the right prefix without
         * a single call site asking for it — which is the property the old
         * arrangement could not offer, and the reason `route('suggest')` and
         * `route('settings')` produced differently shaped URLs on the same
         * page.
         *
         * `asset()` deliberately does not pass through here: `UrlGenerator`
         * builds asset URLs from the root directly, without calling `format()`.
         * That is the behaviour we want — `/build/app.css` is served by nginx
         * and exists at exactly one path — and it is also why `Vite::asset()`
         * above is left alone.
         */
        \URL::formatPathUsing(function (string $path): string {
            $prefix = app(LocaleContext::class)->urlPrefix();

            return $prefix === "" ? $path : $prefix . $path;
        });

        \Prometheus\Storage\Redis::setDefaultOptions(
            [
                'host' => config("database.redis.default.host"),
                'port' => config("database.redis.default.port"),
                'password' => config("database.redis.default.password"),
                'timeout' => 0.1,
                // in seconds
                'read_timeout' => '10',
                // in seconds
                'persistent_connections' => false
            ]
        );

        /*
         * The Redis queue, built to survive a Valkey failover.
         *
         * Registered over the framework's own `redis` connector rather than
         * under a driver name of its own, so config/queue.php cannot opt out of
         * it by accident. See App\Queue\FailoverRedisQueue for what a failover
         * does to an unwrapped `queue:work`.
         *
         * In boot() rather than register(): every provider's register() has run
         * by now, including QueueServiceProvider's, so this replaces its
         * connector instead of racing it — and nothing resolves a queue
         * connection that early, so no already-built queue survives the swap.
         */
        Queue::extend('redis', function () {
            return new FailoverRedisConnector($this->app['redis']);
        });

        /*
         * Stop the workers polling the application cache in their loop.
         *
         * Worker::daemon reaches the cache twice per iteration even with an
         * empty queue: getNextJob() asks which queues are paused (a many() over
         * the cache store) and stopIfNecessary() reads
         * `illuminate:queue:restart`. Both go to the *cache* connection — the
         * shared, 35s-bounded one, which cannot be tightened because the same
         * connection carries AnonymousToken's 30s payment wait — and neither is
         * covered by App\Queue\FailoverRedisQueue, which only wraps pop(). Two
         * calls per iteration that a failover can hang, on a socket nothing
         * drops.
         *
         * They buy signals nothing here sends: `queue:restart` and
         * `queue:pause` appear nowhere in this repo, in the chart or in CI. The
         * workers are recycled by --max-time inside their supervisor
         * (build/fpm/entrypoint/queue_worker.sh) and replaced by rolling the
         * Deployment. Turning the polling off leaves pop() as the only Redis
         * call the broadcast worker's loop makes, which is what lets that one
         * wrapper be the whole fix, and takes the database worker's loop off
         * Redis entirely.
         *
         * Re-enabling it means those two commands start working again — and the
         * loops start depending on the shared connection again.
         */
        Queue::withoutInterruptionPolling();

        $this->app->bind(LogsUser::class, function ($app) {
            return new LogsUser();
        });
        $this->app->singleton(LogsAccountProvider::class, function ($app) {
            return new LogsAccountProvider();
        });
        // One per request: every engine of the fokus asks for it, and resolving
        // it means parsing the client's User-Agent.
        $this->app->singleton(UpstreamUserAgent::class);

        // One device detection per request. A search asked for it twice — once
        // in MetaGer::__construct for the mobile flag, once in
        // UpstreamUserAgent for the User-Agent it sends upstream — and each
        // ask was a cache read or, on a miss, a full parse. The answer cannot
        // differ between the two: it is the same request and the same
        // User-Agent. A singleton because under FPM a process handles one
        // request; anything resolving this outside a request should use
        // Browser::fromUserAgent directly, as the tests do.
        $this->app->singleton(Browser::class, fn() => Browser::fromRequest());

        // Swap in our own getLocalizedURL(). See App\Localization\MetaGerLocalization:
        // the package's builds its answer through url()->to(), which now carries the
        // locale prefix of its own accord, and located the locale to replace by a
        // config-driven loop that emitted two locales in one path on any prefixed
        // page. Bound against the package's own key, so the facade and every
        // type-hint pick it up.
        $this->app->singleton(LaravelLocalization::class, MetaGerLocalization::class);
        Auth::provider("logs", function ($app, array $config) {
            return new LogsUserProvider($app->make(LogsUser::class));
        });

        Auth::extend('logs', function (Application $app, string $name, array $config) {
            return new LogsAuthGuard(Auth::createUserProvider($config['provider']), $app->make(\Illuminate\Http\Request::class));
        });
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Resolved lazily, and replaced outright by `ResolveLocale` on a real
        // request. This binding is what answers for everything that generates a
        // URL without one: console commands, queued jobs, and the boot sequence
        // before the middleware has run.
        $this->app->singleton(LocaleContext::class, function ($app): LocaleContext {
            return $app->bound("request") ? LocaleContext::resolve($app["request"]) : LocaleContext::neutral();
        });

        // One instance per request, mutated by SettingsController's POST
        // handlers as they queue/forget cookies, and read by both
        // CookieCarryingUrlGenerator::route() and CookieSupport::carryIntoUrl()
        // — see SettingsCarry's own docblock for why a shared singleton
        // rather than each deriving its own list from the request.
        $this->app->singleton(\App\Http\SettingsCarry::class);
    }
}