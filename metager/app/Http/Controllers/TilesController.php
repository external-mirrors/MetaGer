<?php

namespace App\Http\Controllers;

use App\Localization;
use App\Models\Authorization\Authorization;
use Exception;
use Illuminate\Support\Facades\Redis;
use App\Models\Tile;
use App\SearchSettings;
use Cache;
use Illuminate\Http\Request;
use Log;

class TilesController extends Controller
{
    const CACHE_DURATION_SECONDS = 300;

    public function loadTakeTiles(Request $request)
    {
        if (!$request->filled("ckey") || !Cache::has($request->input("ckey"))) {
            abort(404);
        }
        $ckey = $request->input("ckey");
        $count = $request->input("count", 4);
        $tiles = [];
        $tiles = self::TAKE_TILES($ckey, $count);
        StatisticsController::LOG_STATISTICS([
            "e_c" => "Take Tiles",
            "e_a" => "Load",
            "e_n" => "Take Tiles",
            "e_v" => sizeof($tiles),
        ]);
        return response()->json($tiles);
    }

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
        $tiles[] = new Tile(title: "SUMA-EV", image: "/img/tiles/sumaev.png", url: "https://suma-ev.de", image_alt: "SUMA_EV Logo");
        //$tiles[] = new Tile(title: "Maps", image: "/img/tiles/maps.png", url: "https://maps.metager.de", image_alt: "MetaGer Maps Logo");
        $tiles[] = new Tile(title: __('sidebar.nav28'), image: "/img/icon-settings.svg", url: route("settings", ["focus" => app(SearchSettings::class)->fokus, "url" => url()->full()]), image_alt: "Settings Logo", image_classes: "invert-dm");
        $tiles[] = new Tile(title: __('index.plugin'), image: "/img/svg-icons/plug-in.svg", url: route("plugin"), image_alt: "MetaGer Plugin Logo");
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

    /**
     * Generates Tile ads from Takeads
     * 
     * @return array
     */
    private static function TAKE_TILES(string $ckey, int $count): array
    {
        $tiles = [];
        $result_cache_key = "taketiles:fetch:$ckey:$count";

        $result = Cache::get($result_cache_key);
        if ($result === null) {
            $supported_countries = ["US", "GB", "DE", "AT", "CH", "TR"];
            if (!config("metager.taketiles.enabled") || !in_array(Localization::getRegion(), $supported_countries)) {
                return $tiles;
            }
            if (app(Authorization::class)->canDoAuthenticatedSearch(false))
                return $tiles;

            $endpoint = config("metager.taketiles.endpoint");
            $params = [
                "count" => $count,
                "deviceId" => $ckey,
                "countryCode" => Localization::getLanguage()
            ];
            $mission = [
                "resulthash" => $result_cache_key,
                "url" => $endpoint . "?" . http_build_query($params),
                "useragent" => "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:81.0) Gecko/20100101 Firefox/81.0",
                "headers" => [
                    "Content-Type" => "application/json",
                    "Authorization" => "Bearer " . config("metager.taketiles.public_key"),
                ],
                "cacheDuration" => ceil(self::CACHE_DURATION_SECONDS / 60),
                "name" => "Take Tiles",
            ];
            $mission = json_encode($mission);
            Redis::rpush(\App\MetaGer::FETCHQUEUE_KEY, $mission);
            Cache::put($ckey, "1", now()->addSeconds(self::CACHE_DURATION_SECONDS));
            $result = Redis::blpop($result_cache_key, 0);
            if (sizeof($result) === 2) {
                $result = $result[1];
            }
        }

        if ($result !== null) {
            try {
                $result = json_decode($result);
                foreach ($result->data as $result_tile) {
                    $tiles[] = new Tile(title: $result_tile->title, image: $result_tile->image, image_alt: $result_tile->title . " Image", url: $result_tile->url, advertisement: true);
                }
            } catch (Exception $e) {
                Log::error($e);
            }

        }

        return $tiles;
    }
}
