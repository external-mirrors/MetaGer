<?php

namespace App\Http\Controllers;

use App\Localization;
use App\Models\Configuration\SearchEngineRegistry;
use App\Models\parserSkripte\Tootnews;
use Illuminate\Support\Facades\Vite;
use Illuminate\View\View;

/**
 * /entfernen — where a search result can actually be removed.
 *
 * MetaGer shows other engines' results, so the only removals that work are at
 * the website and at those engines. This page says where; the providers and
 * their forms are in config/removal_providers.php, keyed by the engine names
 * in config/foki.json. suma-crm links to the anchors on this page.
 *
 * SUMA-EV's own searches keep no index of their own either: TootNews searches
 * Mastodon servers, Nolm the Open Web Index. Their cards say where their
 * results are removed; there is no form of ours. suma-crm still has its
 * removal form, switched off; nothing here links it.
 *
 * Only providers MetaGer still gets results from get a card: an engine its
 * parser switches off (CONFIG_OVERLOAD `disabled`) sends nobody
 * anywhere. The mapping in the config keeps such engines, so a provider comes
 * back by itself when its engine is switched on again.
 */
class RemovalController extends Controller
{
    /** The request kinds every provider entry carries a link (or null) for. */
    public const KINDS = ["personal_data", "outdated", "legal"];

    public function show(SearchEngineRegistry $registry): View
    {
        $providers = config("removal_providers.providers");
        $providers["suma"]["name"] = self::tootNewsName();
        $foki = $this->fokiPerProvider($registry);

        return view("removal.index", [
            "title" => __("removal.title"),
            "metaDescription" => __("removal.meta_description"),
            "navbarFocus" => "kontakt",
            "css" => [Vite::asset("resources/less/metager/pages/removal.less")],
            "js" => [Vite::asset("resources/js/removal.js")],
            "kinds" => self::KINDS,
            "website" => $providers["website"],
            "providers" => array_filter(
                $providers,
                fn(array $provider, string $id) => $provider["type"] !== "website" && isset($foki[$id]),
                ARRAY_FILTER_USE_BOTH
            ),
            "foki" => $foki,
            "hotlines" => config("removal_providers.hotlines"),
            "own" => config("removal_providers.own"),
            "tootnews" => self::tootNewsUrl(),
        ]);
    }

    /**
     * The name a visitor finds under a TootNews result: TroetNews for German,
     * TootNews for everyone else (Tootnews::__construct). They are one search
     * over the same Mastodon servers, so the guide names only this one.
     */
    public static function tootNewsName(): string
    {
        return Localization::getLanguage() === "de"
            ? Tootnews::GERMAN_DISPLAY_NAME
            : Tootnews::CONFIG_OVERLOAD["tootnews"]["infos"]["display_name"];
    }

    /**
     * TootNews' start page, which lists the Mastodon servers it searches, on
     * the host the parser searches for this visitor: TroetNews for German,
     * TootNews for everyone else (Tootnews::__construct).
     */
    public static function tootNewsUrl(): string
    {
        $host = Localization::getLanguage() === "de"
            ? Tootnews::GERMAN_HOST
            : Tootnews::CONFIG_OVERLOAD["tootnews"]["host"];

        return "https://$host/";
    }

    /**
     * provider id => the foki (as display names) it supplies results to, so a
     * card can say "MetaGer shows its results under Web and Images". Only
     * engines that are not switched off count; a provider missing here gets
     * no card.
     *
     * Mojeek, Staan and Polias are off only *by default* (`disabledByDefault`): a
     * user can opt in, so their results can be on MetaGer and they count.
     *
     * @return array<string, string[]>
     */
    private function fokiPerProvider(SearchEngineRegistry $registry): array
    {
        $engines = config("removal_providers.engines");
        $perProvider = [];

        foreach ($registry->foki as $key => $fokus) {
            foreach ($fokus->sumas as $engine) {
                if (!isset($engines[$engine]) || ($registry->sumas->{$engine}->disabled ?? false)) {
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
