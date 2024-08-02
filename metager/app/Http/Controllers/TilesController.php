<?php

namespace App\Http\Controllers;

use App\Localization;
use App\Models\Authorization\Authorization;
use DeviceDetector\Cache\LaravelCache;
use DeviceDetector\ClientHints;
use DeviceDetector\DeviceDetector;
use Exception;
use Illuminate\Support\Facades\Redis;
use App\Models\Tile;
use App\SearchSettings;
use Cache;
use Log;
use Request;

class TilesController extends Controller
{
    const CACHE_DURATION_SECONDS = 300;

    /**
     * Generate Tiles for a given request
     * This includes static TIles and SUMA Tiles
     * Take Tiles are generated asynchroniously
     * 
     * @param string $ckey
     * @return array
     */
    public static function TILES(string $ckey): array
    {
        // Check if the user has disabled tiles
        if (!app(SearchSettings::class)->tiles_startpage)
            return [];
        $tiles = self::STATIC_TILES();
        $tiles = array_merge($tiles, self::SUMA_TILES());
        return $tiles;
    }

    /**
     * Generates Static Tiles
     * @return Tile[]
     */
    private static function STATIC_TILES(): array
    {
        $tiles = [];

        $dd = new DeviceDetector(Request::header("user-agent"), ClientHints::factory($_SERVER));
        $dd->setCache(new LaravelCache());
        $dd->parse();
        $plugin_url = route("plugin");
        $browser = $dd->getClient("name");
        $version = $dd->getClient("version");
        $os = $dd->getOs("name");
        $target = "__self";
        $classes = "";
        if (!$dd->isMobile() && $browser === "Firefox" && version_compare($version, "115.0", "ge")) {
            $plugin_url = "https://addons.mozilla.org/firefox/downloads/latest/metager-suche";
            $classes .= "orange";
        } elseif (!$dd->isMobile() && $browser === "Chrome" && $os === "Windows") {
            $plugin_url = "https://chromewebstore.google.com/detail/metager-suche/gjfllojpkdnjaiaokblkmjlebiagbphd";
            $target = "__BLANK";
            $classes .= "orange";
        } elseif ($browser === "Microsoft Edge") {
            $plugin_url = "https://microsoftedge.microsoft.com/addons/detail/fdckbcmhkcoohciclcedgjmchbdeijog";
            $target = "__BLANK";
            $classes .= "orange";
        }
        $tiles[] = new Tile(title: __('index.plugin'), image: "/img/svg-icons/plug-in.svg", url: $plugin_url, image_alt: "MetaGer Plugin Logo", classes: $classes, target: $target, id: "plugin-btn");

        if (Localization::getLanguage() === "de")
            $tiles[] = new Tile(title: "Unser Trägerverein", image: "/img/tiles/sumaev.png", url: "https://suma-ev.de", image_alt: "SUMA_EV Logo");
        $tiles[] = new Tile(title: "Maps", image: "/img/tiles/maps.png", url: "https://maps.metager.de", image_alt: "MetaGer Maps Logo");
        $tiles[] = new Tile(title: __('sidebar.nav28'), image: "/img/icon-settings.svg", url: route("settings", ["focus" => app(SearchSettings::class)->fokus, "url" => url()->full()]), image_alt: "Settings Logo", image_classes: "invert-dm");

        return $tiles;
    }

    /**
     * Generates dynamic Tiles booked through SUMA-EV
     * 
     * @return array
     */
    private static function SUMA_TILES(): array
    {
        $tiles = [];
        return $tiles;
    }
}
