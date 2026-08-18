<?php

namespace Tests\Feature;

use App\Localization\MemoizingLaravelLocalization;
use Mcamara\LaravelLocalization\LaravelLocalization;
use Illuminate\Routing\RouteCollection;
use Tests\TestCase;

/**
 * Localized URLs are built without re-walking the route table every time.
 *
 * getLocalizedURL is called about fifty times on a result page — once per
 * localized link — and each call used to walk all 132 routes. This pins both
 * halves of the fix: that the memoizing subclass is what the container hands
 * out, and that it really does stop asking the router.
 */
class LocalizedUrlMemoizationTest extends TestCase
{
    /**
     * The binding is the fragile part. Nothing fails if a package upgrade
     * re-registers the original class over it — the pages just get slower
     * again, silently.
     */
    public function testTheContainerHandsOutTheMemoizingLocalization(): void
    {
        $this->assertInstanceOf(
            MemoizingLaravelLocalization::class,
            $this->app->make(LaravelLocalization::class),
            "AppServiceProvider's binding has been lost, so every localized link walks the route table again."
        );
    }

    /**
     * The same URL asked for twice must not consult the router twice.
     *
     * Observed by taking the routes away in between: if the second call still
     * produces the same URL with an empty route collection, it cannot have
     * looked. Blunt, but it measures the thing the change exists for, and it
     * cannot pass by accident the way a timing assertion could.
     */
    public function testTheRouteTableIsWalkedOnlyOncePerUrl(): void
    {
        $localization = $this->app->make(LaravelLocalization::class);
        $url = url("/about");

        $first = $localization->getLocalizedURL("de-DE", $url);

        $router = new \ReflectionProperty(LaravelLocalization::class, "router");
        $original = $router->getValue($localization);
        $router->setValue($localization, tap(clone $original, fn($r) => $r->setRoutes(new RouteCollection())));

        $this->assertSame(
            $first,
            $localization->getLocalizedURL("de-DE", $url),
            "The second call went back to the router, so nothing was memoised."
        );

        $router->setValue($localization, $original);
    }

    /**
     * And two different URLs still get two different answers — the failure a
     * cache keyed too loosely would produce.
     */
    public function testDifferentUrlsStillGetDifferentAnswers(): void
    {
        $localization = $this->app->make(LaravelLocalization::class);

        $this->assertNotSame(
            $localization->getLocalizedURL("de-DE", url("/about")),
            $localization->getLocalizedURL("de-DE", url("/kontakt")),
        );
    }
}
