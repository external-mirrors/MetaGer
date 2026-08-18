<?php

namespace App\Search;

use App\MetaGer;
use App\SearchSettings;
use Illuminate\Contracts\View\View;

/**
 * Picks the shape a finished search is answered in.
 *
 * Seven of them, chosen by `out=` and — for two — by the fokus:
 *
 *   json                 the documented API schema, built by MetaGer::toApiArray
 *   results              a fragment, for the page to paste in as more arrive
 *   results-with-style   the same fragment inside a bare document, for an iframe
 *   rss20 / atom10 / api two feeds, of which `api` is the atom one
 *   result-count         a count and a duration, for monitoring
 *   (anything else)      the result page
 *
 * `json` is answered before the fokus is looked at because its schema is the
 * same either way — an image search only fills a field the others leave empty.
 * The two HTML shapes are not: an image search renders from its own blades, so
 * `results` and the page have an image version each.
 *
 * ## Why this was worth moving
 *
 * MetaGer::createView was a switch inside a switch, and the eight branches were
 * eight ->with() chains repeating the same seven or eight variables. That
 * arrangement hid a real asymmetry: the two branches that render
 * `resultpages.resultpage` — the default page and `results-with-style` — pass
 * *different* variables to the same blade. One passes `focusPages`, `quicktips`
 * and `resultcount` and no results; the other passes results and
 * `suspendheader` and calls the fokus `fokus` rather than `focus`. Both are
 * preserved here and are visible now that the shared variables are named once.
 *
 * The formats themselves are pinned in tests/Feature/Search/OutputFormatsTest.
 */
class ResponseFactory
{
    /**
     * @param array<int, mixed> $quicktips
     * @return View|string a view for the markup formats, a string for json and result-count
     */
    public function make(MetaGer $metager, array $quicktips = []): View|string
    {
        $out = $metager->getOut();

        if ($out === "json") {
            return json_encode($metager->toApiArray(), MetaGer::JSON_API_FLAGS);
        }

        if ($out === "result-count") {
            // The elapsed time is part of the answer: this format exists so a
            // monitor can tell "MetaGer returned nothing" from "MetaGer took too
            // long", and a count alone cannot.
            return count($metager->getResults()) . ";" . round(microtime(true) - $metager->starttime, 2);
        }

        $fokus = app(SearchSettings::class)->fokus;

        if ($fokus === "bilder") {
            return $out === "results"
                ? $this->view($metager, "resultpages.results_images", ["imagesearch" => true])
                : $this->view($metager, "resultpages.resultpage_images", [
                    "imagesearch" => true,
                    "quicktips" => $quicktips,
                    "focus" => $fokus,
                    "resultcount" => count($metager->getResults()),
                ]);
        }

        return match ($out) {
            "results" => $this->view($metager, "resultpages.results", ["fokus" => $fokus]),
            "results-with-style" => $this->view($metager, "resultpages.resultpage", [
                "suspendheader" => "yes",
                "fokus" => $fokus,
            ]),
            "rss20" => $this->view($metager, "resultpages.metager3resultsrss20", [
                "resultcount" => count($metager->getResults()),
                "fokus" => $fokus,
            ]),
            // `api` is not a format of its own. It is the atom feed under the
            // name the old API documentation used.
            "atom10", "api" => view("resultpages.metager3resultsatom10", [
                "eingabe" => $metager->getEingabe(),
                "resultcount" => count($metager->getResults()),
                "key" => $metager->getApiKey(),
                "metager" => $metager,
            ]),
            default => $this->resultPage($metager, $quicktips, $fokus),
        };
    }

    /**
     * The result page proper.
     *
     * Alone among the HTML shapes in not being handed the results: the blade
     * reads them off $metager instead. Kept that way — it is what the page does
     * today, and the two are the same list.
     *
     * @param array<int, mixed> $quicktips
     */
    private function resultPage(MetaGer $metager, array $quicktips, string $fokus): View
    {
        return view("resultpages.resultpage", $this->shared($metager) + [
            // Which engines the user ticked by hand, so the settings boxes come
            // back the way they were sent.
            "focusPages" => $this->tickedEngines($metager),
            "quicktips" => $quicktips,
            "resultcount" => count($metager->getResults()),
            "focus" => $fokus,
        ]);
    }

    /**
     * @param array<string, mixed> $extra
     */
    private function view(MetaGer $metager, string $blade, array $extra): View
    {
        return view($blade, $this->shared($metager) + [
            "results" => array_map(fn($result) => get_object_vars($result), $metager->getResults()),
        ] + $extra);
    }

    /**
     * What every markup format is given.
     *
     * @return array<string, mixed>
     */
    private function shared(MetaGer $metager): array
    {
        return [
            "eingabe" => $metager->getEingabe(),
            "mobile" => $metager->isMobile(),
            "warnings" => $metager->warnings,
            "htmlwarnings" => $metager->htmlwarnings,
            "errors" => $metager->errors,
            "apiAuthorized" => $metager->isApiAuthorized(),
            "metager" => $metager,
        ];
    }

    /**
     * @return list<string>
     */
    private function tickedEngines(MetaGer $metager): array
    {
        $ticked = [];
        foreach ($metager->getRequest()->all() as $key => $value) {
            if (stripos($key, "engine_") === 0 && $value === "on") {
                $ticked[] = $key;
            }
        }

        return $ticked;
    }
}
