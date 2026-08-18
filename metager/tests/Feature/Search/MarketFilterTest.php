<?php

namespace Tests\Feature\Search;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\FakesSearchEngines;
use Tests\TestCase;

/**
 * That every interface locale can also be searched as a market.
 *
 * The market filter's option list lives in `config/filters.json`; the interface
 * locales live in `config/laravellocalization.php`. Nothing connects the two,
 * but `SearchSettings::loadParameterFilter()` joins them at runtime: it
 * overwrites the filter's `default-value` with
 * `LaravelLocalization::getCurrentLocaleRegional()`, and `parts/filter.blade.php`
 * marks an option `selected` only when it equals that default.
 *
 * A locale with no matching market therefore renders a `<select>` in which
 * **no** option is selected, and that is not cosmetic. A browser showing such a
 * select displays and submits its *first* option — `de_AT`, for everybody. So
 * the next query submitted from the result page carries a market the user never
 * chose, and because `web_setting_m` is still the interface locale as well, it
 * also moves them out of their language. `ca-ES` was in exactly that state on
 * the day it was added as a supported locale: the locale list gained an entry,
 * the market list did not.
 *
 * These tests are the join those two configuration files do not have.
 */
class MarketFilterTest extends TestCase
{
    use FakesSearchEngines;

    /**
     * Locales whose result page cannot be reached, and why.
     *
     * `en-MY` is a supported interface locale that no search engine can serve:
     * `en_MY` appears in `filters.json` as a market, but in **no** parser's
     * `regions` map, so `SearchengineConfiguration::applyLocale()` disables
     * every engine and the search redirects to the settings page. That is a
     * real defect and a separate one — the fix is a decision about what to send
     * Brave and Serper for Malaysia, not about which markets to offer — so it
     * is recorded here rather than silently passed over.
     */
    private const UNSEARCHABLE_LOCALES = [
        "en-MY" => "no parser's regions map contains en_MY, so every engine is disabled",
    ];

    protected function tearDown(): void
    {
        $this->forgetSearchUserClaims();
        parent::tearDown();
    }

    /**
     * Every supported locale, as `[locale => [locale, regional]]`.
     *
     * `default` is skipped: it is the "no locale decided" entry and has no
     * regional form, so there is no market it could ask for.
     */
    public static function supportedLocales(): array
    {
        $config = require __DIR__ . "/../../../config/laravellocalization.php";
        $cases = [];
        foreach ($config["supportedLocales"] as $locale => $properties) {
            if (empty($properties["regional"])) {
                continue;
            }
            $cases[$locale] = [$locale, $properties["regional"]];
        }
        return $cases;
    }

    #[DataProvider("supportedLocales")]
    public function testEveryInterfaceLocaleIsAlsoAnOfferedMarket(string $locale, string $regional): void
    {
        $markets = (array) json_decode(file_get_contents(config_path("filters.json")))
            ->{"parameter-filter"}->language->values;

        $this->assertArrayHasKey(
            $regional,
            $markets,
            "$locale resolves to the market $regional, which config/filters.json does not offer. "
                . "A search in $locale would show no market selected and submit the list's first entry instead.",
        );
    }

    #[DataProvider("supportedLocales")]
    public function testEveryInterfaceLocaleIsPreselectedOnItsOwnResultPage(string $locale, string $regional): void
    {
        if (array_key_exists($locale, self::UNSEARCHABLE_LOCALES)) {
            $this->markTestSkipped("$locale cannot search at all: " . self::UNSEARCHABLE_LOCALES[$locale]);
        }

        $this->actingAsSearchUser();
        $this->fakeEngineResponses([]);

        // Redirects are followed because a locale that is hidden from the URL
        // (en-US) is canonicalised to the unprefixed path and still renders the
        // page under test.
        $html = $this->followingRedirects()
            ->get("/$locale/meta/meta.ger3?eingabe=cafe")
            ->assertOk()
            ->getContent();

        $this->assertSame(
            1,
            preg_match('/<select name="m".*?<\/select>/s', $html, $select),
            "The $locale result page has no market selector.",
        );

        $this->assertSame(
            [$regional],
            $this->selectedMarketsIn($select[0]),
            "The $locale result page should preselect $regional. With nothing selected a browser "
                . "submits the list's first option instead, changing both the market and the interface language.",
        );
    }

    /**
     * The same assertion for the settings screen, which renders the same filter
     * through a different blade and so can drift from the result page.
     */
    #[DataProvider("supportedLocales")]
    public function testEveryInterfaceLocaleIsPreselectedOnItsOwnSettingsPage(string $locale, string $regional): void
    {
        $this->actingAsSearchUser();

        $html = $this->followingRedirects()
            ->get("/$locale/meta/settings?focus=web")
            ->assertOk()
            ->getContent();

        $this->assertSame(
            1,
            preg_match('/<select[^>]*name="m"[^>]*>.*?<\/select>/s', $html, $select),
            "The $locale settings page has no market selector.",
        );

        $this->assertSame(
            [$regional],
            $this->selectedMarketsIn($select[0]),
            "The $locale settings page should show $regional as the current market.",
        );
    }

    /**
     * The values of the `selected` options in one rendered `<select>`.
     *
     * The control is rendered across several lines with its attributes in no
     * fixed order, so each tag is matched whole and then read.
     */
    private function selectedMarketsIn(string $select): array
    {
        preg_match_all('/<option\b[^>]*>/s', $select, $options);
        $selected = [];
        foreach ($options[0] as $option) {
            if (preg_match('/\bselected\b/', $option) && preg_match('/value="([^"]*)"/', $option, $value)) {
                $selected[] = trim($value[1]);
            }
        }
        return $selected;
    }
}
