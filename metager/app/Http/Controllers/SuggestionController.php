<?php

namespace App\Http\Controllers;

use App\Localization;
use App\Models\Authorization\Authorization;
use App\Models\Result;
use App\SearchSettings;
use Cache;
use Crypt;
use Exception;
use Illuminate\Http\Request;

class SuggestionController extends Controller
{
    const CACHE_DURATION_HOURS = 6;
    private $markets = [
        "us" => "us(en)",
        "ch" => "ch(de)",
    ];
    public function partner(Request $request)
    {
        if (!$this->verifySignature($request)) {
            abort(401);
        }
        $query = $request->input("query");
        if (!config("metager.metager.admitad.suggestions_enabled") || empty($query)) {
            abort(404);
        }

        // Disable Partnershops for authorized searches
        if (app(Authorization::class)->canDoAuthenticatedSearch()) {
            return response()->json([], 200, ["Cache-Control" => "no-cache, private"]);
        }

        $region = strtolower(Localization::getRegion());
        if (array_key_exists($region, $this->markets)) {
            $region = $this->markets[$region];
        }
        $public_key = config("metager.metager.admitad.suggest_public_key");

        $request_data = [
            "keywords" => [
                $query
            ],
            "market" => $region,
            "provider" => "bing-search"
        ];

        $cache_key = md5(json_encode($request_data));
        $response = Cache::get($cache_key);
        if ($response === null) {
            $context = stream_context_create([
                "http" => [
                    "method" => "POST",
                    "header" => [
                        "Content-Type: application/json",
                        "Authorization: Bearer $public_key"
                    ],
                    "user_agent" => "MetaGer",
                    "timeout" => 2.0,
                    "content" => json_encode($request_data),
                    "ignore_errors" => true
                ]
            ]);
            $response = file_get_contents("https://apisuggests.com/api/v1/resolve", false, $context);
            $response = json_decode($response, true);
            if (array_key_exists("resolutions", $response) && is_array($response["resolutions"])) {
                Cache::put($cache_key, $response, now()->addHours(self::CACHE_DURATION_HOURS));
            }
        }
        if (array_key_exists("resolutions", $response) && is_array($response["resolutions"])) {
            $result = [];
            for ($i = 0; $i < sizeof($response["resolutions"]); $i++) {
                if ($response["resolutions"][$i]["data"] === null) {
                    continue;
                }
                $response["resolutions"][$i]["data"]["imageUrl"] = Pictureproxy::generateUrl($response["resolutions"][$i]["data"]["imageUrl"]);
                $result[] = $response["resolutions"][$i];
            }
            return response()->json($result, 200, ["Cache-Control" => "max-age=7200"]);
        } else {
            return response()->json([], 200, ["Cache-Control" => "no-cache, private"]);
        }
    }

    public function suggest(Request $request)
    {
        if (!$this->verifySignature($request)) {
            abort(401);
        }
        $query = $request->input("query");
        if (!config("metager.metager.admitad.suggestions_enabled") || empty($query)) {
            abort(404);
        }

        // Do not generate Suggestions if User turned them off
        $settings = app(SearchSettings::class);
        if ($settings->suggestions === "off") {
            return response()->json([], 200, ["Cache-Control" => "no-cache, private"]);
        }
        $suggestion_provider = $settings->suggestions;
        $region = strtolower(Localization::getRegion());
        if (array_key_exists($region, $this->markets)) {
            $region = $this->markets[$region];
        }
        $public_key = config("metager.metager.admitad.suggest_public_key");

        $request_data = [
            "query" => $query,
            "market" => $region,
            "provider" => $suggestion_provider . "-suggest"
        ];

        $cache_key = md5(json_encode($request_data));
        $response = Cache::get($cache_key);
        if ($response === null) {
            $context = stream_context_create([
                "http" => [
                    "method" => "GET",
                    "header" => [
                        "Content-Type: application/json",
                        "Authorization: Bearer $public_key"
                    ],
                    "user_agent" => "MetaGer",
                    "timeout" => 2.0,
                    "content" => null,
                    "ignore_errors" => true
                ]
            ]);
            $url = "https://apisuggests.com/api/v1/suggest?" . http_build_query($request_data);
            $response = file_get_contents($url, false, $context);
            $response = json_decode($response, true);
            if (array_key_exists("suggestions", $response) && is_array($response["suggestions"]) && array_key_exists("items", $response["suggestions"]) && is_array($response["suggestions"]["items"])) {
                Cache::put($cache_key, $response, now()->addHours(self::CACHE_DURATION_HOURS));
            }
        }
        if (array_key_exists("suggestions", $response) && is_array($response["suggestions"]) && array_key_exists("items", $response["suggestions"]) && is_array($response["suggestions"]["items"])) {
            return response()->json($response["suggestions"]["items"], 200, ["Cache-Control" => "max-age=7200"]);
        } else {
            return response()->json([], 200, ["Cache-Control" => "no-cache, private"]);
        }
    }

    private function verifySignature(Request $request): bool
    {
        $key = $request->header("MetaGer-Key", "");
        try {
            $expiration = Crypt::decrypt($key);
            if (now()->isAfter($expiration)) {
                return false;
            } else {
                return true;
            }
        } catch (Exception $e) {
            return false;
        }
    }
}