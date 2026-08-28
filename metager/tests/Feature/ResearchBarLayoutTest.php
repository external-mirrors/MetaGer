<?php

namespace Tests\Feature;

use Tests\Concerns\ReadsBuiltCss;
use Tests\TestCase;

/**
 * The result page's top row, and the two ways it runs out of room.
 *
 * It carries the query, and around the query it carries the logo, the language
 * badge, the submit button, the account pill and the menu. On a wide window
 * none of that is in tension. At the two ends it is:
 *
 *  - Just above 920px the pill and the menu floated top right as a fixed
 *    cluster while the results column stayed centred, so the two closed on each
 *    other as the window narrowed. At 1000px the pill sat 40px *inside* the
 *    research bar, on top of the search field.
 *  - On a 390px phone the five things that are not the query took about 160px
 *    of the row, and the query — the thing the page is about, the thing people
 *    came to read and edit — got what was left. It did not fit.
 *
 * Both fixes are arithmetic, and the numbers sit in two LESS files that cannot
 * see each other: parts/account.less decides where the cluster floats,
 * pages/resultpage/result-page.less decides where the bar takes over. They have
 * to be exact complements, and the threshold has to be wide enough to hold the
 * cluster. Neither is visible from either file alone.
 *
 * Asserted on the compiled stylesheet, and not in Dusk, for the reason the rest
 * of the result page is not in Dusk either: in CI the browser suite drives the
 * deployed review environment, where nothing can sign the test session in, and
 * an unauthorised search never reaches a result page at all.
 */
class ResearchBarLayoutTest extends TestCase
{
    use ReadsBuiltCss;

    private const METAGER = "resources/less/metager/metager.less";

    /** The width of the results column the bar sits on top of. */
    private const RESULTS_COLUMN = 800;

    /**
     * What the floating cluster needs beside that column: its own width — the
     * mark, the balance and the menu button, measured at 127px — plus the 20px
     * it sits in from the window edge.
     */
    private const CLUSTER_AND_OFFSET = 147;

    /**
     * The innermost `@media` condition wrapping the first occurrence of a piece
     * of the built stylesheet, and the block it introduces.
     *
     * Deliberately a brace walk rather than a regex: media blocks nest, and the
     * needles here are rules *inside* one, so there is no pattern that pairs an
     * opening `@media` with the right closing brace.
     *
     * @return array{0: string, 1: string} the condition, and the block's text
     */
    private function enclosingMedia(string $css, string $needle): array
    {
        $target = strpos($css, $needle);
        $this->assertNotFalse(
            $target,
            "The built stylesheet contains no `$needle`. If the rule was renamed or dropped, this test has to "
            . "move with it — and if it was dropped, read the class docblock first."
        );

        preg_match_all('/@media[^{}]*\{|\{|\}/', $css, $tokens, PREG_OFFSET_CAPTURE);

        $open = [];
        foreach ($tokens[0] as [$token, $at]) {
            if ($at >= $target) {
                break;
            }

            if ($token === "}") {
                array_pop($open);
                continue;
            }

            $open[] = str_starts_with($token, "@media")
                ? ["condition" => trim(substr($token, strlen("@media"), -1)), "body" => $at + strlen($token)]
                : null;
        }

        for ($i = count($open) - 1; $i >= 0; $i--) {
            if ($open[$i] === null) {
                continue;
            }

            return [$open[$i]["condition"], $this->block($css, $open[$i]["body"])];
        }

        return ["", ""];
    }

    /** The text of a block, given the offset just past its opening brace. */
    private function block(string $css, int $start): string
    {
        $depth = 1;
        for ($i = $start; $i < strlen($css); $i++) {
            $depth += ($css[$i] === "{") - ($css[$i] === "}");
            if ($depth === 0) {
                return substr($css, $start, $i - $start);
            }
        }

        return substr($css, $start);
    }

    /**
     * The pixel width a media condition switches at. lightningcss rewrites
     * `(max-width: 1150px)` to `(width<=1150px)` on the way out, so this reads
     * the number and ignores the spelling.
     */
    private function breakpoint(string $condition): int
    {
        $this->assertSame(1, preg_match('/(\d+)px/', $condition, $match), "No pixel width in `$condition`.");

        return (int) $match[1];
    }

    /**
     * One control, one copy. The cluster stops floating at exactly the width
     * where the bar starts carrying the pill; a gap between the two thresholds
     * renders no pill at all, an overlap renders two.
     */
    public function testTheClusterStandsDownExactlyWhereTheBarPicksTheControlsUp(): void
    {
        $css = $this->builtCss(self::METAGER);

        [$clusterHides] = $this->enclosingMedia($css, ".navigation-cluster--wide-only{display:none}");
        [$pillAppears] = $this->enclosingMedia($css, ".account-pill--in-bar{display:inline-flex}");

        $this->assertSame(
            $this->breakpoint($clusterHides),
            $this->breakpoint($pillAppears),
            "The floating cluster stands down at `$clusterHides` and the bar picks the pill up at `$pillAppears`. "
            . "Those are the same swap and have to name the same width, or there is a band of window sizes with "
            . "two account pills on screen, or none."
        );
    }

    /**
     * And it only floats where it fits. The cluster is `position: fixed` and the
     * results column is centred, so the gap between them closes as the window
     * narrows — with nothing in the layout to stop them touching. This is the
     * width at which they stop touching.
     */
    public function testTheClusterOnlyFloatsWhereThereIsRoomBesideTheResultsColumn(): void
    {
        $css = $this->builtCss(self::METAGER);

        [$condition] = $this->enclosingMedia($css, ".navigation-cluster--wide-only{display:none}");
        $breakpoint = $this->breakpoint($condition);
        $needed = self::RESULTS_COLUMN + 2 * self::CLUSTER_AND_OFFSET;

        $this->assertGreaterThanOrEqual(
            $needed,
            $breakpoint,
            "The cluster is allowed to float from {$breakpoint}px up. The results column is "
            . self::RESULTS_COLUMN . "px wide and centred, and the cluster needs " . self::CLUSTER_AND_OFFSET
            . "px of the gutter on the right, so below {$needed}px it overlaps the research bar — which is how "
            . "the pill came to sit on top of the search field at 1000px."
        );
    }

    /**
     * On a phone the row gives its width to the query, and what steps out is
     * what the menu also answers: the language badge (the sidebar's last entry)
     * and the account pill (the sidebar's account block, which says the key code
     * and the balance rather than just a colour). Neither is something anyone
     * reaches for while reading results.
     */
    public function testThePhoneBarDropsWhatTheMenuAlsoAnswers(): void
    {
        $css = $this->builtCss(self::METAGER);

        [$condition, $block] = $this->enclosingMedia($css, ".account-pill--in-bar{display:none}");

        $this->assertGreaterThanOrEqual(
            430,
            $this->breakpoint($condition),
            "The bar stops shedding at `$condition`, which leaves out the widest phones — 430px is an iPhone "
            . "Pro Max. Those are exactly the screens this is for."
        );

        $this->assertStringContainsString(
            "#header-logo a.lang",
            $block,
            "The account pill steps out of the phone bar but the language badge does not. Both are one tap away "
            . "in the menu, and both are worth more to the query than to this row."
        );
    }

    /**
     * Editing gets the whole bar as soon as the caret lands in the field.
     *
     * There is a second, older rule that does the same thing once
     * `data-suggest="active"` is set — but suggest.js only sets that when a
     * suggestion request has come back, so the row stayed crowded for a network
     * round trip, and stayed crowded for good with suggestions switched off or
     * scripting disabled. On a phone that is the difference between editing a
     * query in 240px and editing it in the whole bar, so there it cannot be
     * conditional on anything.
     */
    public function testEditingTheQueryOnAPhoneDoesNotWaitForSuggestions(): void
    {
        $css = $this->builtCss(self::METAGER);

        $collapse = "#research-bar:has(#header-searchbar .search-input:focus-within)";
        [$condition] = $this->enclosingMedia($css, $collapse);

        $this->assertNotSame(
            "",
            $condition,
            "The unconditional focus collapse is not inside a media query. It is meant for phones, where the bar "
            . "has no room for anything else; on a wide window the logo should not vanish the moment someone "
            . "clicks the search field."
        );
        $this->assertGreaterThanOrEqual(430, $this->breakpoint($condition));
    }
}
