<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Human verification is gone, and has to stay gone.
 *
 * MetaGer only serves searches to an authenticated key, so the captcha never
 * stood between a visitor and a result page — it just sat there. How long it
 * had been unreachable is visible in the views themselves: the captcha form
 * posted to a route named `captcha_solve`, and no such route existed any more,
 * so rendering the page would have thrown rather than shown a challenge.
 *
 * This pins the removal rather than the feature, because the failure mode worth
 * catching is a partial revert: a composer entry without its provider, a
 * provider without its package, an alias pointing at a class that is no longer
 * installed. Each of those either breaks the boot or quietly reinstates a
 * dependency nothing calls, and neither is visible from a green page render.
 *
 * Note this is not a "no mention of captchas anywhere" scan. Prose is allowed
 * to discuss them — the privacy pages may well want to say MetaGer does not use
 * one. What is asserted is that nothing resolves.
 */
class HumanVerificationRemovedTest extends TestCase
{
    /**
     * mews/captcha pulled intervention/image and intervention/gif in with it —
     * a whole image-manipulation stack kept alive for one unreachable form.
     */
    public function testTheCaptchaPackageIsNotInstalled(): void
    {
        $this->assertFalse(
            class_exists("Mews\\Captcha\\Captcha"),
            "mews/captcha is installed again. Nothing calls it; if something now does, this test is the wrong thing to change."
        );

        $this->assertFalse(
            class_exists("Intervention\\Image\\ImageManager"),
            "intervention/image is back. It only ever came along as a captcha dependency."
        );
    }

    public function testNothingRegistersTheCaptchaWithLaravel(): void
    {
        $this->assertNull(
            config("captcha"),
            "config/captcha.php is back."
        );

        $this->assertArrayNotHasKey(
            "Captcha",
            config("app.aliases"),
            "The Captcha facade alias is back, and points at a class that is not installed."
        );

        $this->assertNotContains(
            "Mews\\Captcha\\CaptchaServiceProvider",
            require base_path("bootstrap/providers.php"),
            "The captcha service provider is registered again."
        );
    }

    /**
     * @param string $view
     */
    #[DataProvider("removedViews")]
    public function testTheHumanVerificationViewsAreGone(string $view): void
    {
        $this->assertFalse(
            View::exists($view),
            "The view [$view] is back. It belongs to a flow no route reaches."
        );
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function removedViews(): array
    {
        return [
            "captcha" => ["humanverification.captcha"],
            "bot verification" => ["humanverification.bv"],
            "bot overview" => ["humanverification.botOverview"],
        ];
    }

    /**
     * The route the captcha form posted to. Its absence is what proves the flow
     * was already broken rather than merely unused, so it is worth stating.
     */
    public function testNoRouteSolvesACaptcha(): void
    {
        $this->assertFalse(Route::has("captcha_solve"));
    }

    public function testTheCaptchaTranslationsAreGone(): void
    {
        foreach (["1", "2", "3", "4", "5"] as $key) {
            $this->assertFalse(
                Lang::has("captcha.$key"),
                "lang/*/captcha.php is back. Eleven locales carried strings for a form nobody could reach."
            );
        }
    }

    /**
     * Three counters that no code ever incremented, so every one of them
     * reported zero for the whole time the dashboards read them.
     */
    public function testThePrometheusExporterHasNoCaptchaCounters(): void
    {
        $methods = get_class_methods(\App\PrometheusExporter::class);

        $this->assertSame(
            [],
            array_values(array_filter($methods, fn($method) => str_contains(strtolower($method), "captcha"))),
            "PrometheusExporter exposes captcha counters again."
        );
    }
}
