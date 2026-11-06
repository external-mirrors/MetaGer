<?php

namespace App\Http\Controllers;

use App\Search\BlocklistSync;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * POST /api/blocklist-changed: the blocklist service saying MetaGer's list
 * changed (open-source/blocklist, docs/consumers.md, "Change notice").
 *
 * Answers 202 at once and syncs after the response has gone out, so the
 * service is never kept waiting. The body is not read: MetaGer fetches the
 * list and checks it itself, and the notice only means "now rather than at
 * the next minute". With the scheduled sync every minute, a lost or refused
 * notice delays a change by that minute and loses nothing.
 *
 * `app()->terminating()` and not `defer()`: bootstrap/app.php replaces the
 * global middleware stack, and InvokeDeferredCallbacks is not in it.
 */
class BlocklistChangedController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $expected = config("metager.metager.blocklist.webhook_token");
        $given = $request->bearerToken();
        if (!is_string($expected) || $expected === "" || !is_string($given) || !hash_equals($expected, $given)) {
            return response()->json(["error" => "Unauthorized"], 401);
        }

        app()->terminating(function () {
            [$status, $message] = app(BlocklistSync::class)->sync();
            if ($status === BlocklistSync::FAILED) {
                Log::warning($message);
            }
        });

        return response()->json(["status" => "accepted"], 202);
    }
}
