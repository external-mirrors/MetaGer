<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

/**
 * MetaGer once offered search with ads for free and without ads for a key.
 * That tier is gone: every search needs a key now. Copy that says the key,
 * or membership, makes the search "ad-free" promises a difference that no
 * longer exists, and for membership it reads like a return for the fee, which
 * a non-profit may not give.
 *
 * What stays is listed nowhere here on purpose: the tagline and the landing
 * page's "no ads" block and the feature list on /about say MetaGer has no ads
 * at all, which is true; the
 * /about timeline records when the ad-free search was introduced; the price
 * page explains that upstream engines are paid for ad-free results.
 */
class NoAdFreeTierWordingTest extends TestCase
{
    /** "ad-free" and its relatives in each of the twelve languages. */
    private const ADS_PATTERN = '/werbe|werbung|ad-free|ad free|\bads\b|publicit|publicidad|pubblicit|reklam|reclame|advertentie|annons|annonce|mainok|mainos|anúnc|anunci|membres est gratuite|membri è gratuita/iu';

    /** @var array<string, list<string>> file => keys that once sold the tier */
    private const KEYS = [
        "index.php" => ["key.tooltip.nokey", "key.tooltip.full", "adfree", "landing.howitworks.steps.1.description"],
        "settings.php" => ["key", "metager-key.no-key"],
        "spende.php" => ["amount.membershiphint.description"],
        "campaigns.php" => ["redeem.teaser.description"],
        "mg-story.php" => ["privacy.p", "ngo.p"],
        "price.php" => ["texts.0"],
        "help/key.php" => ["faqs.0.description"],
        "help/anonymous-token.php" => ["description.text"],
        "help/help-mainpages.php" => ["searchfield.memberkey", "settings.1", "startpage.info"],
        "help/easy-language/help-functions.php" => ["key.1", "key.5.3", "key.7", "key.9", "key.10"],
        "help/easy-language/help-mainpages.php" => [
            "searchfield.memberkey",
            "searchfield.memberkey.3",
            "settings.metagerkey.title",
            "settings.metagerkey.1",
        ],
    ];

    public function testNoStringSellsTheKeyOrMembershipAsAdFree(): void
    {
        $problems = [];
        $checked = 0;

        foreach (glob($this->langPath() . "/??", GLOB_ONLYDIR) as $dir) {
            $locale = basename($dir);
            foreach (self::KEYS as $file => $keys) {
                if (!is_file("$dir/$file")) {
                    continue; // The easy-language pages exist in five languages only.
                }
                $strings = $this->flatten(require "$dir/$file");
                foreach ($keys as $key) {
                    if (!isset($strings[$key])) {
                        continue;
                    }
                    $checked++;
                    if (preg_match(self::ADS_PATTERN, $strings[$key])) {
                        $problems[] = "$locale/$file $key: " . mb_substr($strings[$key], 0, 120);
                    }
                }
            }
        }

        // Against passing because the paths stopped matching anything.
        $this->assertGreaterThan(200, $checked, "checked almost no strings");
        $this->assertSame([], $problems);
    }

    private function langPath(): string
    {
        return dirname(__DIR__, 2) . "/lang";
    }

    /** @return array<string, string> */
    private function flatten(array $translations, string $prefix = ""): array
    {
        $flat = [];
        foreach ($translations as $key => $value) {
            $dotted = $prefix === "" ? (string) $key : "$prefix.$key";
            if (is_array($value)) {
                $flat += $this->flatten($value, $dotted);
            } else {
                $flat[$dotted] = (string) $value;
            }
        }
        return $flat;
    }
}
