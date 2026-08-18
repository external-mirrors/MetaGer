<?php

namespace App\Localization;

use Mcamara\LaravelLocalization\LaravelLocalization;

/**
 * LaravelLocalization, with the route scan done once per URL instead of once
 * per call.
 *
 * Every localized link on a page goes through getLocalizedURL, and a result
 * page has about fifty of them. Each one calls extractAttributes, which walks
 * the entire route collection — 132 routes — asking each route for its URI and,
 * for reasons lost to the package's history, calling method_exists on it first.
 * That came to some 3,900 route lookups and 3,900 method_exists calls for one
 * page, and made it the largest single item in a profile of a warm result page
 * once device detection had been dealt with.
 *
 * The answer is a pure function of the URL, the target locale, the current
 * locale and the route collection. The first three are the key; the fourth
 * cannot change within a request, because routes are registered at boot.
 *
 * Kept as a subclass rather than a patch to the blades: the fifty call sites
 * are spread across templates and are individually reasonable — it is asking
 * fifty times and recomputing fifty times that is not.
 *
 * The binding lives in AppServiceProvider and is asserted in
 * tests/Feature/LocalizedUrlMemoizationTest, because if a package upgrade ever
 * drops it the only symptom is that the pages get slower again.
 */
class MemoizingLaravelLocalization extends LaravelLocalization
{
    /** @var array<string, array<string, mixed>> */
    private array $attributes = [];

    /**
     * @param string|false $url
     * @param string $locale
     * @return array<string, mixed>
     */
    protected function extractAttributes($url = false, $locale = '')
    {
        $key = ($this->currentLocale ?? "") . "\0" . $locale . "\0" . (is_string($url) ? $url : "");

        // ??= and not isset(): the answer is very often an empty array, and an
        // empty array is a perfectly good cached answer.
        return $this->attributes[$key] ??= parent::extractAttributes($url, $locale);
    }
}
