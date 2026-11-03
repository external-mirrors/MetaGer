<?php

namespace App\Http\Controllers;

use App\Localization;
use Illuminate\Support\Facades\Vite;
use Illuminate\Translation\Translator;
use Illuminate\View\View;

/**
 * /entfernen — where a search result can actually be removed.
 *
 * MetaGer shows other engines' results, so the only removals that work are at
 * the website and at those engines. This page says where; the providers and
 * their forms are in config/removal_providers.php, keyed by the engine names
 * in config/foki.json. suma-crm links its replies to the anchors on this page.
 */
class RemovalController extends Controller
{
    /** The request kinds every provider entry carries a link (or null) for. */
    public const KINDS = ["personal_data", "outdated", "legal"];

    public function show(Translator $translator): View
    {
        // The page is written in de, en and ca. Everywhere else the app falls
        // back to `de` (config/app.php), and a German page is the wrong
        // answer for a French visitor asking how to get a result removed:
        // English is the one they are likelier to read.
        if (!file_exists(lang_path(Localization::getLanguage() . "/removal.php"))) {
            $translator->setFallback("en");
        }

        $providers = config("removal_providers.providers");

        return view("removal.index", [
            "title" => __("removal.title"),
            "navbarFocus" => "kontakt",
            "css" => [Vite::asset("resources/less/metager/pages/removal.less")],
            "kinds" => self::KINDS,
            "website" => $providers["website"],
            "providers" => array_filter(
                $providers,
                fn(array $provider) => $provider["type"] !== "website"
            ),
            "foki" => $this->fokiPerProvider(),
            "hotlines" => config("removal_providers.hotlines"),
        ]);
    }

    /**
     * provider id => the foki (as display names) it supplies results to, so a
     * card can say "MetaGer shows its results under Web and Images".
     *
     * @return array<string, string[]>
     */
    private function fokiPerProvider(): array
    {
        $engines = config("removal_providers.engines");
        $foki = json_decode(file_get_contents(config_path("foki.json")), true);
        $perProvider = [];

        foreach ($foki as $key => $fokus) {
            foreach ($fokus["sumas"] as $engine) {
                if (!isset($engines[$engine])) {
                    continue;
                }
                // Not $fokus["display-name"]: science's is a literal
                // "Wissenschaft" rather than a key.
                $name = __("index.foki.$key");
                $perProvider[$engines[$engine]][$name] = $name;
            }
        }

        return array_map("array_values", $perProvider);
    }
}
