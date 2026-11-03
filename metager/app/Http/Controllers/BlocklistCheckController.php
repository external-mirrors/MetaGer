<?php

namespace App\Http\Controllers;

use App\Models\Configuration\SearchEngineRegistry;
use App\Search\ListingCheck;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * POST /api/blocklist/check — see App\Search\ListingCheck and
 * docs/blocklist-api.md.
 *
 * Every check runs real searches against paid engines, so it is throttled
 * here: ten per minute per token. Done by hand rather than with the
 * `throttle` middleware, whose 429 body is not the contract's.
 */
class BlocklistCheckController extends Controller
{
    public const PER_MINUTE = 10;

    public const DEFAULT_FOKI = ["web", "bilder", "nachrichten"];

    public function check(Request $request, ListingCheck $check): JsonResponse
    {
        $key = "blocklist-check:" . hash("sha256", (string) $request->bearerToken());
        if (RateLimiter::tooManyAttempts($key, self::PER_MINUTE)) {
            return response()->json(["error" => "Too many requests"], 429, [
                "Retry-After" => RateLimiter::availableIn($key),
            ]);
        }

        $foki = array_keys((array) app(SearchEngineRegistry::class)->foki);

        $input = $request->validate([
            "urls" => ["required", "array", "min:1", "max:20"],
            "urls.*" => ["required", "string", "max:4096"],
            "queries" => ["required", "array", "min:1", "max:5"],
            "queries.*" => ["required", "string", "max:2048"],
            "foki" => ["nullable", "array", "min:1"],
            "foki.*" => ["required", "string", Rule::in($foki)],
        ]);

        foreach ($input["urls"] as $index => $url) {
            if (\App\Search\BlocklistNormalizer::host($url) === "") {
                throw ValidationException::withMessages(["urls.$index" => ["The url does not contain a host."]]);
            }
        }

        // Counted once the request is valid: a malformed body costs nothing
        // upstream, so it should not cost a slot either.
        RateLimiter::hit($key, 60);

        $result = $check->run(
            array_values($input["urls"]),
            array_values($input["queries"]),
            array_values(array_unique($input["foki"] ?? self::DEFAULT_FOKI))
        );

        return response()->json([
            "data" => [
                "checked_at" => now()->toIso8601String(),
                "scope" => "first_page",
                "results" => $result["results"],
                "engines_failed" => $result["engines_failed"],
            ],
        ]);
    }
}
