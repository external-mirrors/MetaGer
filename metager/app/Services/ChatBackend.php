<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Addressing and non-streaming calls for the metager-chat service.
 *
 * The browser never talks to metager-chat: MetaGer renders the chat UI itself and proxies every
 * call from PHP (see docs/llm/metager-integration/native-frontend.md). This class is the single
 * place that knows where the service lives and how to authenticate to it.
 *
 * Streaming is deliberately *not* here — see ChatController, which needs the raw PSR-7 body to
 * forward chunks as they arrive rather than a materialised response.
 */
class ChatBackend
{
    private const HEALTH_CACHE_KEY = "chat:backend:available";
    private const MODELS_CACHE_KEY = "chat:backend:models";

    public function url(string $path = ""): string
    {
        return rtrim(config("metager.chat.url"), "/") . $path;
    }

    /**
     * Headers every request to the chat service carries.
     *
     * The key is resolved by MetaGer's own guard and forwarded — the chat service does no cookie
     * or query parsing of its own, so this is the only way a key reaches it. The shared secret
     * makes "only MetaGer may call this" enforced rather than merely intended.
     */
    public function headers(?string $key = null): array
    {
        $headers = ["X-MetaGer-Secret" => config("metager.chat.shared_secret")];
        if ($key !== null) {
            $headers["X-MetaGer-Key"] = $key;
        }
        return $headers;
    }

    /**
     * Whether the chat service is reachable right now.
     *
     * Fails closed: a timeout, a connection error or a non-200 all count as unavailable. Hiding a
     * working feature for a few seconds is much cheaper than the failure this exists to prevent —
     * rendering a chat UI that cannot possibly work.
     *
     * Cached because this is consulted while building available_foki, i.e. on the search request
     * path. On a cache hit the cost is one Redis read; the HTTP call happens at most once per
     * cache window per node.
     */
    public function isAvailable(): bool
    {
        return Cache::remember(
            self::HEALTH_CACHE_KEY,
            config("metager.chat.health_cache_seconds"),
            function () {
                try {
                    return Http::withHeaders($this->headers())
                        ->timeout(config("metager.chat.health_timeout"))
                        ->get($this->url("/api/health"))
                        ->successful();
                } catch (\Throwable $e) {
                    return false;
                }
            }
        );
    }

    /**
     * Parks an attachment's text with the chat service and returns its id.
     *
     * The transcript is stateless — it round-trips through hidden form fields — so a document has
     * to live somewhere that a field can *point* at. The service keeps it in Redis for an hour,
     * sliding from last use; nothing durable is written. Returns null if the upload failed, which
     * the caller turns into a message rather than losing the user's turn over.
     *
     * MetaGer does the decoding and validation because it owns the multipart form and the user's
     * locale; what crosses the wire is a plain string.
     */
    public function uploadFile(string $key, string $name, string $content): ?string
    {
        try {
            $response = Http::withHeaders($this->headers($key))
                ->timeout(config("metager.chat.connect_timeout"))
                ->post($this->url("/api/files"), ["name" => $name, "content" => $content]);

            if (!$response->successful()) {
                Log::warning("chat: attachment upload rejected", ["status" => $response->status()]);
                return null;
            }

            return $response->json("id");
        } catch (\Throwable $e) {
            Log::warning("chat: attachment upload failed", ["exception" => $e->getMessage()]);
            return null;
        }
    }

    /**
     * The model catalog, including per-model cost in MetaGer tokens.
     *
     * Returns an empty array when the service is unreachable, so callers render "no models" rather
     * than fataling — the availability gate should have caught that case first anyway.
     */
    public function models(): array
    {
        return Cache::remember(
            self::MODELS_CACHE_KEY,
            config("metager.chat.models_cache_seconds"),
            function () {
                try {
                    $response = Http::withHeaders($this->headers())
                        ->timeout(config("metager.chat.connect_timeout"))
                        ->get($this->url("/api/models"));

                    if (!$response->successful()) {
                        return [];
                    }

                    return $response->json("models", []);
                } catch (\Throwable $e) {
                    Log::warning("chat: model catalog unavailable", ["exception" => $e->getMessage()]);
                    return [];
                }
            }
        );
    }
}
