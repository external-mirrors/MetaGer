<?php

namespace Tests\Unit\Authentication;

use App\Authentication\KeyIdenticon;
use PHPUnit\Framework\TestCase;

/**
 * The account mark.
 *
 * Its whole value is that the same key always produces the same picture, on
 * every surface that computes it — this app, the /keys dashboard, and the
 * webextension, which has to port the derivation rather than share it. So the
 * derivation itself is what is pinned here, not the SVG string: reformatting
 * the markup should not fail a test, changing what gets drawn must.
 */
class KeyIdenticonTest extends TestCase
{
    public function testTheSameFingerprintAlwaysProducesTheSameMark(): void
    {
        $this->assertSame(KeyIdenticon::hue("a1b2c3"), KeyIdenticon::hue("a1b2c3"));
        $this->assertSame(KeyIdenticon::cells("a1b2c3"), KeyIdenticon::cells("a1b2c3"));
        $this->assertSame(
            KeyIdenticon::render("a1b2c3")->toHtml(),
            KeyIdenticon::render("a1b2c3")->toHtml()
        );
    }

    /**
     * Twelve hues, thirty degrees apart, from the low bits of the fingerprint.
     *
     * Hard-coded rather than recomputed from the same expression the class uses,
     * which would only assert that PHP's modulo works.
     */
    public function testTheHueIsOneOfTwelveDerivedFromTheFingerprint(): void
    {
        // 0xa1b2c3 = 10597059; 10597059 % 12 = 3; 3 * 30 = 90
        $this->assertSame(90, KeyIdenticon::hue("a1b2c3"));
        $this->assertSame(0, KeyIdenticon::hue("000000"));
        // 0xffffff = 16777215; % 12 = 3
        $this->assertSame(90, KeyIdenticon::hue("ffffff"));

        foreach (["000000", "0000ff", "abcdef", "123456", "7f3e91", "ffffff"] as $fingerprint) {
            $hue = KeyIdenticon::hue($fingerprint);
            $this->assertGreaterThanOrEqual(0, $hue);
            $this->assertLessThan(360, $hue);
            $this->assertSame(0, $hue % 30, "Hue $hue for $fingerprint is off the twelve-step wheel.");
        }
    }

    /**
     * The pattern is symmetric. Not decoration: a mirrored shape is what makes
     * it read as a mark rather than as noise, at the 18px it is usually seen at.
     */
    public function testThePatternIsMirroredAcrossTheVerticalAxis(): void
    {
        $cells = KeyIdenticon::cells("a1b2c3");
        $this->assertNotEmpty($cells);

        foreach ($cells as [$x, $y]) {
            $this->assertContains([3 - $x, $y], $cells, "Cell [$x, $y] has no mirror.");
        }
    }

    /**
     * Eight independent cells: 256 patterns across 12 hues. Enough that two of
     * one person's own keys are very unlikely to collide, which is the only
     * distinctness that matters here — this is not a hash.
     */
    public function testDifferentFingerprintsGenerallyProduceDifferentMarks(): void
    {
        $seen = [];
        foreach (["000000", "0000ff", "abcdef", "123456", "7f3e91", "0042ab", "e91d5c", "bada55"] as $fingerprint) {
            $seen[] = KeyIdenticon::hue($fingerprint) . ":" . json_encode(KeyIdenticon::cells($fingerprint));
        }

        $this->assertCount(count($seen), array_unique($seen), "Two of these fingerprints draw the same mark.");
    }

    /**
     * No fingerprint is a real state, not an error: a webextension visitor has
     * none by design (KeyUser::getKeyFingerprint returns null for a temporary
     * user), and a legacy non-UUID key has none we could still be showing next
     * request. Drawing something from it would look deliberate and be a lie.
     */
    public function testAnUndrawableFingerprintGetsTheAnonymousPlaceholder(): void
    {
        foreach ([null, "", "abc", "abcdefg", "ABCDEF", "gggggg", "12 456"] as $fingerprint) {
            $this->assertNull(KeyIdenticon::hue($fingerprint), "hue() drew something for " . var_export($fingerprint, true));
            $this->assertSame([], KeyIdenticon::cells($fingerprint));

            $html = KeyIdenticon::render($fingerprint)->toHtml();
            $this->assertStringContainsString("account-mark--anonymous", $html);
            $this->assertStringNotContainsString("<svg", $html);
        }
    }

    /**
     * The hue is the only thing that crosses into the markup, and it arrives as
     * a class rather than an inline `style`: metager.de's CSP is
     * `style-src-attr 'self'` (build/nginx/configuration/nginx.conf), which has
     * no `'unsafe-inline'` for style attributes and drops one silently — an
     * inline `--account-mark-hue` here would render every mark on the fallback
     * hue, one colour regardless of key. Saturation, lightness and both theme
     * palettes live in variables.less. If this starts emitting a colour or an
     * inline style, the dark theme (or the CSP) has silently stopped working.
     */
    public function testOnlyTheHueReachesTheMarkupAndAsAClassNotAnInlineStyle(): void
    {
        $html = KeyIdenticon::render("a1b2c3")->toHtml();

        $this->assertStringContainsString("account-mark--hue-90", $html);
        $this->assertStringNotContainsString("style=", $html);
        $this->assertStringNotContainsString("hsl(", $html);
        $this->assertStringNotContainsString("fill=", $html);
        $this->assertStringContainsString('aria-hidden="true"', $html, "The mark is decorative; the label lives on the pill.");
    }

    public function testAnExtraClassIsCarriedOntoTheElement(): void
    {
        $this->assertStringContainsString(
            'class="account-mark sidebar-mark account-mark--hue-90"',
            KeyIdenticon::render("a1b2c3", "sidebar-mark")->toHtml()
        );
    }
}
