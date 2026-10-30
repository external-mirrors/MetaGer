<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

/**
 * The privacy policy ends with the date of its current version, and that date
 * is a literal in the view — nothing moves it when the text changes, so it
 * was left behind more than once. This pins a fingerprint of the text next to
 * the date it was published under: change the text and this fails until
 * someone has looked at the date.
 *
 * The fingerprint covers the view and the German and English text, the ones
 * written by hand. The other locales come from Weblate and only follow them;
 * a translation fix there should not fail the build.
 *
 * A plain TestCase for the reason {@see AccountTranslationsTest} gives.
 */
class PrivacyPolicyDateTest extends TestCase
{
    private const DATE = "2026-10-30";
    private const FINGERPRINT = "ea0f2e2ba3c8becfd455c7d9aa1bdd1cf07c17d036d966588814dcc8ae81ace1";

    private const DATE_PATTERN = "/@lang\('privacy\.changes\.date', \['date' => '([0-9-]+)'\]\)/";

    public function testThePolicyShowsThePinnedDate(): void
    {
        $this->assertSame(1, preg_match(self::DATE_PATTERN, $this->view(), $match), "privacy.blade.php no longer shows privacy.changes.date in the expected form");
        $this->assertSame(self::DATE, $match[1]);
    }

    public function testTheTextHasNotChangedSinceThatDate(): void
    {
        $fingerprint = $this->fingerprint();

        $this->assertSame(
            self::FINGERPRINT,
            $fingerprint,
            "The privacy policy's text changed. If what it says changed, set the date in privacy.blade.php "
                . "and DATE here to the day it goes live. Either way, set FINGERPRINT to '$fingerprint'."
        );
    }

    private function fingerprint(): string
    {
        $base = dirname(__DIR__, 2);
        // The date itself is left out, so bumping it alone does not ask for a new fingerprint.
        $parts = [
            preg_replace(self::DATE_PATTERN, "", $this->view()),
            file_get_contents("$base/lang/de/privacy.php"),
            file_get_contents("$base/lang/en/privacy.php"),
        ];
        return hash("sha256", implode("\0", $parts));
    }

    private function view(): string
    {
        return file_get_contents(dirname(__DIR__, 2) . "/resources/views/privacy.blade.php");
    }
}
