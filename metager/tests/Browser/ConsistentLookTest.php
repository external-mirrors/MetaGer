<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\FakesSearchEngines;
use Tests\DuskTestCase;

/**
 * Things that are one thing on the site and have to be drawn as one thing,
 * wherever they appear. Each of these drifted apart once, and a palette or
 * card change made in one stylesheet is exactly how they drift again.
 *
 * A browser because every colour here is a var(), and only a rendering engine
 * resolves it. Result pages are rendered in-process from the engine fixtures
 * and written into a document of the app's origin, as AccessibilityTest does.
 */
class ConsistentLookTest extends DuskTestCase
{
    use FakesSearchEngines;

    public static function themes(): array
    {
        return [
            "light" => ["light"],
            "dark" => ["dark"],
        ];
    }

    /** What makes a result card look like one (pages/resultpage/result.less, .result). */
    private const CARD = [
        "border-top-color", "border-bottom-color", "border-top-width", "border-bottom-width",
        "border-top-left-radius", "border-top-right-radius", "border-bottom-left-radius", "border-bottom-right-radius",
        "background-color",
    ];

    protected function tearDown(): void
    {
        $this->forgetSearchUserClaims();
        parent::tearDown();
    }

    /**
     * Rendered in-process. Once per test: a second request in the same
     * application instance answers the locale-prefixed path with a 404 (the
     * load-more test in OpenAnonymouslyTest runs into the same thing).
     */
    private function resultPage(string $query, array $engines): string
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses($engines);

        return $this->get("/de-DE/meta/meta.ger3?" . $query)->assertOk()->getContent();
    }

    private function show(Browser $browser, string $html): Browser
    {
        $browser->visit("/de-DE/impressum");
        $browser->driver->executeScript("document.open(); document.write(arguments[0]); document.close();", [$html]);

        return $browser->waitUntil("document.readyState === 'complete'");
    }

    /** @return array<string, string> */
    private function style(Browser $browser, string $selector, array $properties): array
    {
        // Dusk's script() takes no arguments, so this goes through the driver.
        $resolved = $browser->driver->executeScript(
            "const e = document.querySelector(arguments[0]); if (!e) return null;"
                . " const s = getComputedStyle(e); return Object.fromEntries(arguments[1].map(p => [p, s.getPropertyValue(p)]));",
            [$selector, $properties]
        );
        $this->assertNotNull($resolved, "Nothing on the page matches {$selector}.");

        return $resolved;
    }

    /**
     * The region code beside the logo ("DE") was grey on the start page and
     * the accent colour on the result page.
     */
    #[DataProvider("themes")]
    public function testTheLanguageCodeIsTheSameColourOnTheStartAndTheResultPage(string $theme): void
    {
        $html = $this->resultPage("eingabe=kaffee&focus=web&dark_mode={$theme}", [
            "brave" => $this->engineFixture("brave-web.json"),
        ]);

        $this->browse(function (Browser $browser) use ($theme, $html) {
            $browser->visit("/de-DE?dark_mode={$theme}&zitate=off")->assertPresent("#startpage-logo a.lang");
            $start = $this->style($browser, "#startpage-logo a.lang", ["color"]);

            $this->show($browser, $html)->assertPresent("#results");
            $results = $this->style($browser, "a.lang", ["color"]);

            $this->assertSame($start, $results, "The language code changes colour between the start page and the results.");
        });
    }

    /**
     * A result's address is a link, in the link colour, in both themes. Dark
     * had it (#ff9933, the link and the accent there are the same orange) and
     * light had the accent text, a grey (#555): the one colour on a result
     * card was there in one theme and missing in the other. Light's link
     * colour is blue, not orange, by decision — no orange is both legible on
     * white and not brown.
     */
    #[DataProvider("themes")]
    public function testTheResultAddressIsInTheLinkColour(string $theme): void
    {
        $html = $this->resultPage("eingabe=kaffee&focus=web&dark_mode={$theme}", [
            "brave" => $this->engineFixture("brave-web.json"),
        ]);

        $this->browse(function (Browser $browser) use ($html) {
            $this->show($browser, $html)->assertPresent("#results .result .result-subheadline .result-link");
            $link = $browser->driver->executeScript(
                "const p = document.createElement('a'); p.style.color = 'var(--link-color)';"
                    . " document.body.append(p); const c = getComputedStyle(p).color; p.remove(); return c;"
            );

            $this->assertSame(
                ["color" => $link],
                $this->style($browser, "#results .result .result-subheadline .result-link", ["color"]),
                "The result's address is not in the link colour."
            );
        });
    }

    /**
     * Image results are cards like web results: the same frame, rounded on
     * every corner — the tiles were rounded at the top only and unframed, and
     * the detail panel had a grey ground of its own.
     *
     * Measured against the card tokens a web result is drawn with (resolved by
     * a probe on the same page, since one test can render only one result
     * page — see resultPage()), plus the radius .result uses.
     */
    #[DataProvider("themes")]
    public function testImageResultsAreCardsLikeWebResults(string $theme): void
    {
        $images = $this->resultPage("eingabe=kaffee&focus=bilder&dark_mode={$theme}", [
            "brave_images" => $this->engineFixture("brave-images.json"),
            "serper_images" => "no-result",
        ]);

        $this->browse(function (Browser $browser) use ($images) {
            $this->show($browser, $images)->assertPresent(".image-details .details.default");
            $card = $browser->driver->executeScript(
                "const p = document.createElement('div');"
                    . " p.style.cssText = 'border:1px solid var(--result-card-border-color);background:var(--result-card-color);border-radius:10px';"
                    . " document.body.append(p); const s = getComputedStyle(p);"
                    . " const r = Object.fromEntries(arguments[0].map(k => [k, s.getPropertyValue(k)])); p.remove(); return r;",
                [self::CARD]
            );

            $this->assertSame($card, $this->style($browser, ".image-container .images > .image", self::CARD), "An image tile is not drawn like a result card.");
            $this->assertSame($card, $this->style($browser, ".image-details > .details.default", self::CARD), "The image details are not drawn like a result card.");
        });
    }

    /**
     * "Bild herunterladen" was a Bootstrap button beside the "Seite anonym
     * öffnen" action; now both are actions and look it.
     */
    #[DataProvider("themes")]
    public function testTheImageDownloadLooksLikeTheActionBesideIt(string $theme): void
    {
        $images = $this->resultPage("eingabe=kaffee&focus=bilder&dark_mode={$theme}", [
            "brave_images" => $this->engineFixture("brave-images.json"),
            "serper_images" => "no-result",
        ]);
        $action = [
            "color", "background-color", "font-size", "min-height", "border-top-width",
            "border-top-left-radius", "padding-left", "text-decoration-line",
        ];

        $this->browse(function (Browser $browser) use ($images, $action) {
            $this->show($browser, $images)->assertPresent(".image-details .details.default");

            $this->assertSame(
                $this->style($browser, ".image-details > .details.default .actions > a.result-open-proxy", $action),
                $this->style($browser, ".image-details > .details.default .actions > a.result-image-download", $action),
            );
        });
    }
}
