<?php

namespace Tests\Feature;

use Tests\Concerns\ReadsBuiltCss;
use Tests\TestCase;

/**
 * The three things across the top of the startpage sit on one line.
 *
 * The fokus row, the account pill and the menu button all reach into the same
 * strip in the top of the page, and they arrive there by three different routes:
 * the pill and the button are a `position: fixed` cluster at a pixel offset, and
 * the fokus row is a normal-flow flex row at the top of a full-height column.
 * Nothing makes them agree except the numbers, and the numbers live in two
 * different stylesheets — parts/account.less compiles into metager.css, the
 * fokus row into startpage.css — so a change to one of them cannot be seen from
 * the other.
 *
 * That is how the row came to sit 6px above the pill's centre: it was positioned
 * with `padding: 2dvh 0`, a proportion of the window, against a cluster that is
 * pinned in pixels and does not move. The two agreed at no window height in
 * particular and drifted with every one.
 *
 * So the invariant is arithmetic, and it is asserted on the built CSS rather
 * than the LESS: the source can look right while the compiled variables say
 * something else, and the browser only ever reads the compiled file.
 *
 * The geometry itself — that this arithmetic really does put the glyphs on one
 * line — was verified in Firefox at 1280/700px and both window heights; a Dusk
 * test cannot stand in for this one, because in CI the browser suite drives the
 * deployed review environment, where nothing can sign the test session in and
 * neither the fokus row nor the pill renders at all.
 */
class NavigationBandAlignmentTest extends TestCase
{
    use ReadsBuiltCss;

    /**
     * Pull one declaration out of the first rule for a selector in a built
     * stylesheet. Deliberately naive: the build minifies to one rule per line
     * segment with no whitespace, and a parser that copes with more than that
     * would be more machinery than the assertion is worth.
     */
    private function declaration(string $entry, string $selector, string $property): string
    {
        $css = $this->builtCss($entry);

        // The rule first, then the declaration inside it. Matching the property
        // against the whole file would work, but a failure would then print the
        // entire minified stylesheet as its diff — so narrow it before asserting.
        $this->assertSame(
            1,
            preg_match("/" . preg_quote($selector, "/") . "\{([^}]*)\}/", $css, $rule),
            "No rule for [$selector] in the built stylesheet. If it was renamed, this test has to move with it."
        );
        $this->assertSame(
            1,
            preg_match("/(?:^|;)" . preg_quote($property, "/") . ":([^;]+)/", $rule[1], $value),
            "[$selector] declares no $property — it is `{$rule[1]}`."
        );

        return trim($value[1]);
    }

    private function pixels(string $entry, string $selector, string $property): int
    {
        $value = $this->declaration($entry, $selector, $property);
        $this->assertMatchesRegularExpression(
            '/^-?\d+(\.\d+)?px$/',
            $value,
            "[$selector] { $property: $value } is not a plain pixel length. The band is pinned in pixels on purpose — "
            . "the cluster is position:fixed and does not move with the window, so anything sharing its line "
            . "cannot be sized as a proportion of the window."
        );

        return (int) round((float) $value);
    }

    private const METAGER = "resources/less/metager/metager.less";
    private const STARTPAGE = "resources/less/metager/pages/startpage/startpage.less";

    /**
     * The one equation. The fokus row reserves a band symmetric about the
     * cluster — the cluster's own top offset above it, the same again below —
     * and centres in it, so its text lands on the cluster's centre line.
     *
     * Change any of the three numbers in variables.less and this still holds,
     * because all three are read from the compiled output. Change one of them
     * *somewhere else* and it does not.
     */
    public function testTheFokusRowReservesExactlyTheBandTheNavigationClusterOccupies(): void
    {
        $clusterTop = $this->pixels(self::METAGER, ".navigation-cluster", "top");
        $clusterHeight = $this->pixels(self::METAGER, ".navigation-cluster", "min-height");
        $reserved = $this->pixels(self::STARTPAGE, "#foki-switcher", "min-height");

        $this->assertSame(
            $clusterTop * 2 + $clusterHeight,
            $reserved,
            "The fokus row reserves {$reserved}px for a cluster that occupies {$clusterHeight}px at {$clusterTop}px "
            . "from the top. Those have to agree or the row and the pill sit on different lines."
        );
    }

    /**
     * Reserving the band is half of it; the row also has to centre in what it
     * reserved. `align-items: flex-start` would put the labels back at the top
     * of an 68px box, which is further off than where they started.
     */
    public function testTheFokusRowCentresInThatBand(): void
    {
        $this->assertSame("center", $this->declaration(self::STARTPAGE, "#foki-switcher", "align-items"));
        $this->assertSame("center", $this->declaration(self::METAGER, ".navigation-cluster", "align-items"));
    }

    /**
     * The specific regression. box-sizing is border-box globally (Bootstrap), so
     * padding on the fokus row comes *out* of the reserved height rather than
     * adding to it — vertical padding here silently pushes the labels back off
     * the line, which is exactly what the `2dvh` it replaced did.
     *
     * The horizontal padding below 900px is fine and stays; only the block axis
     * is pinned.
     */
    public function testTheFokusRowTakesNoVerticalPaddingOutOfTheBand(): void
    {
        $padding = $this->declaration(self::STARTPAGE, "#foki-switcher", "padding");

        $this->assertSame(
            "0",
            $padding,
            "The fokus row declares `padding: $padding`. Under border-box that comes out of its reserved height, "
            . "so the labels no longer land on the cluster's centre line."
        );
    }
}
