<?php

namespace App\Http\Controllers;

use App\Localization;
use App\Models\Result;
use Crypt;
use Exception;
use Illuminate\Http\Request;

class SuggestionController extends Controller
{
    public function partner(Request $request)
    {
        if (!$this->verifySignature($request) && 1 == 0) {
            abort(401);
        }
        $query = $request->input("query");
        if (empty($query)) {
            abort(404);
        }

        $region = strtolower(Localization::getRegion());
        $public_key = config("metager.metager.admitad.suggest_public_key");

        $request_data = [
            "keywords" => [
                $query
            ],
            "market" => $region,
            "provider" => "bing-search"
        ];
        $context = stream_context_create([
            "http" => [
                "method" => "POST",
                "header" => [
                    "Content-Type: application/json",
                    "Authorization: Bearer $public_key"
                ],
                "user_agent" => "MetaGer",
                "content" => json_encode($request_data)
            ]
        ]);
        $response = file_get_contents("https://apisuggests.com/api/v1/resolve", false, $context);
        $response = json_decode($response, true);
        if (array_key_exists("resolutions", $response) && is_array($response["resolutions"])) {
            for ($i = 0; $i < sizeof($response["resolutions"]); $i++) {
                $response["resolutions"][$i]["data"]["imageUrl"] = Pictureproxy::generateUrl($response["resolutions"][$i]["data"]["imageUrl"]);
            }
            return response()->json($response["resolutions"]);
        } else {
            return response()->json([]);
        }
    }

    public function suggest(Request $request)
    {
        if (!$this->verifySignature($request) && 1 == 0) {
            abort(401);
        }
        $query = $request->input("query");
        if (empty($query)) {
            abort(404);
        }

        $region = strtolower(Localization::getRegion());
        $public_key = config("metager.metager.admitad.suggest_public_key");

        $request_data = [
            "query" => $query,
            "market" => $region,
            "provider" => "bing-search"
        ];
        $context = stream_context_create([
            "http" => [
                "method" => "GET",
                "header" => [
                    "Content-Type: application/json",
                    "Authorization: Bearer $public_key"
                ],
                "user_agent" => "MetaGer",
                "content" => null
            ]
        ]);
        $url = "https://apisuggests.com/api/v1/suggest?" . http_build_query($request_data);
        $response = file_get_contents($url, false, $context);
        $response = json_decode($response, true);
        if (array_key_exists("suggestions", $response) && is_array($response["suggestions"]) && array_key_exists("items", $response["suggestions"]) && is_array($response["suggestions"]["items"])) {
            return response()->json($response["suggestions"]["items"]);
        } else {
            return response()->json([]);
        }
    }

    private function verifySignature(Request $request): bool
    {
        $key = $request->input("key", "");
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