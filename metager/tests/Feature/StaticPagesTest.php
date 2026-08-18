<?php

namespace Tests\Feature;

use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Every static page responds and renders its own content.
 *
 * This replaces the per-page Dusk tests that asserted the same thing by driving
 * Firefox through the sidebar. Those needed a Selenium container to learn that a
 * controller returns 200 and a view contains its title, which is not a good
 * trade. What genuinely needs a browser — the CSS-only sidebar — stays in
 * tests/Browser.
 *
 * These pages are requested without a locale prefix, which is a choice rather
 * than a limitation: serving them under one is Tests\Feature\LocalizedRoutingTest's
 * job, and every page here would only assert the same thing again. It used to be
 * a limitation — the locale was a route group prefix evaluated once per boot, so
 * /de-DE/about did not exist in-process at all — and that is why the per-locale
 * coverage was a Dusk test until ResolveLocale replaced it.
 */
class StaticPagesTest extends TestCase
{
    /**
     * @return array<string, array{0: string, 1: string, 2: string|null}>
     *   page name => [path, title translation key, body translation key or null]
     */
    public static function pages(): array
    {
        return [
            "startpage" => ["/", "titles.index", "mg-story.privacy.title"],
            "about" => ["/about", "titles.about", "about.head.3"],
            "app" => ["/app", "titles.app", "app.metager.1"],
            "datenschutz" => ["/datenschutz", "titles.datenschutz", "privacy.title"],
            "hilfe" => ["/hilfe", "titles.help", "help/help.title"],
            "impressum" => ["/impressum", "titles.impressum", "impressum.info.9"],
            "kontakt" => ["/kontakt", "titles.kontakt", "kontakt.form.1"],
            // No body key: the plugin page has no stable body text across
            // browsers. It renders a per-browser, per-version partial, and for
            // an unrecognised browser it discards the shared markup entirely
            // (see testUnlistedBrowserLosesTheSharedPluginMarkup). Its content
            // is covered by the browser-specific tests below instead.
            "plugin" => ["/plugin", "titles.plugin", null],
            "sitesearch" => ["/sitesearch", "titles.sitesearch", "sitesearch.head.2"],
            "spende" => ["/spende", "titles.spende", "spende.headline.1"],
            "team" => ["/team", "titles.team", "team.role.1.0"],
            "websearch" => ["/websearch", "titles.websearch", "websearch.head.2"],
            "widget" => ["/widget", "titles.widget", "widget.body.1"],
        ];
    }

    #[DataProvider("pages")]
    public function testPageRespondsWithItsTitleAndBody(
        string $path,
        string $titleKey,
        ?string $bodyKey
    ): void {
        $response = $this->get($path);

        $response->assertOk();
        $response->assertSee("<title>" . e(trans($titleKey)) . "</title>", false);

        if ($bodyKey !== null) {
            $response->assertSeeText(trans($bodyKey));
        }
    }

    /**
     * The old Dusk suite looped every supported locale per page, which mostly
     * amounted to checking that the translation keys resolve in every language.
     * That part needs neither a browser nor an HTTP request.
     */
    #[DataProvider("pages")]
    public function testEveryLanguageDefinesThePageTranslations(
        string $path,
        string $titleKey,
        ?string $bodyKey
    ): void {
        foreach ($this->translatedLanguages() as $lang) {
            foreach (array_filter([$titleKey, $bodyKey]) as $key) {
                $translated = trans($key, [], $lang);

                $this->assertIsString(
                    $translated,
                    "Translation [$key] for [$lang] resolved to a non-string."
                );
                $this->assertNotSame(
                    $key,
                    $translated,
                    "Translation [$key] is missing for language [$lang]."
                );
            }
        }
    }

    /**
     * @return array<string, array{0: string, 1: string}> [user agent, expected key]
     */
    public static function pluginBrowsers(): array
    {
        return [
            "firefox" => [
                "Mozilla/5.0 (X11; Linux x86_64; rv:128.0) Gecko/20100101 Firefox/128.0",
                "plugin-page.head.1",
            ],
            "chrome" => [
                "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36",
                "plugin-page.head.2",
            ],
            "edge" => [
                "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36 Edg/126.0.0.0",
                "plugin-page.head.5",
            ],
            "safari" => [
                "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.5 Safari/605.1.15",
                "plugin-page.head.6",
            ],
        ];
    }

    /**
     * The plugin headline is the only static page whose content depends on
     * browser detection, so it is the page that notices if that detection
     * changes. Pinned here on purpose: the modernization plan replaces
     * jenssegers/agent (unmaintained since 2020, and already emitting PHP 8.4
     * deprecations via mobiledetectlib) with matomo/device-detector, and this
     * test is what proves the swap kept the same answers.
     *
     * These used to need the $_SERVER superglobal set by hand, because
     * jenssegers/agent read it directly and never saw the Request. App\Support\Browser
     * takes $request->userAgent(), so a plain header is enough now.
     */
    #[DataProvider("pluginBrowsers")]
    public function testPluginHeadlineFollowsTheDetectedBrowser(
        string $userAgent,
        string $expectedKey
    ): void {
        $response = $this->withHeader("User-Agent", $userAgent)->get("/plugin");

        $response->assertOk();
        $response->assertSeeText(trans($expectedKey));
    }

    /**
     * Characterization test, not an endorsement.
     *
     * resources/views/plugin-page.blade.php opens a *nested* @section('content')
     * inside the outer @section('content'), in the @else arm that handles an
     * unrecognised desktop browser. Re-opening the section replaces everything
     * rendered above it, so for an unlisted browser the page silently loses its
     * <h1 class="page-title"> headline and the search-engine card, and shows only
     * the fallback block plus the Firefox v61 instructions.
     *
     * Pinned so the behaviour is visible and so fixing the nesting has to update
     * this test deliberately. The fix belongs with the view work, not here.
     */
    public function testUnlistedBrowserLosesTheSharedPluginMarkup(): void
    {
        $response = $this->withHeader("User-Agent", "SomeBrowserWeDoNotKnow/1.0")->get("/plugin");

        $response->assertOk();
        // The fallback block is rendered...
        $response->assertSeeText(trans("plugin-page.browser-download"));
        // ...but the headline that should have preceded it is gone.
        $response->assertDontSeeText(trans("plugin-page.head.0"));
    }

    /**
     * Languages that actually ship a lang/ directory. LaravelLocalization
     * advertises more locales than lang/ contains (including the "default"
     * pseudo-locale); the old Dusk pages skipped the rest the same way.
     *
     * @return array<int, string>
     */
    private function translatedLanguages(): array
    {
        $languages = [];

        foreach (array_keys(LaravelLocalization::getSupportedLocales()) as $locale) {
            $lang = preg_replace("/^([a-zA-Z]+)-.*/", "$1", $locale);

            if (!file_exists(lang_path($lang))) {
                continue;
            }

            $languages[$lang] = true;
        }

        return array_keys($languages);
    }
}
