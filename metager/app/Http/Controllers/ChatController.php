<?php

namespace App\Http\Controllers;

use App\MetaGer;
use App\Services\ChatBackend;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Proxies chat messages to the metager-chat service.
 *
 * One route serves both the JS and the no-JS experience — the difference is purely the response
 * encoding, negotiated on Accept. That is deliberate: MetaGer is built so client JS is optional,
 * and having the streaming path be "the same code path with buffering turned on" is what keeps the
 * two from drifting. See docs/llm/metager-integration/native-frontend.md.
 *
 * This route runs on its own PHP-FPM pool (:9001) because it holds a worker for the whole
 * generation — see build/fpm/configuration/fpm/www_02_chat_*.conf.
 */
class ChatController extends Controller
{
    public function __construct(private ChatBackend $backend)
    {
    }

    public function message(Request $request)
    {
        $wantsStream = str_contains((string) $request->header("Accept"), "text/event-stream");

        $validated = $request->validate([
            "modelId" => "required|string",
            // Prior turns. Absent on the very first message of a conversation.
            "messages" => "sometimes|array",
            "messages.*.role" => "required|in:user,assistant",
            "messages.*.content" => "required|string",
            // The new turn, as the composer's textarea sends it. The JS path may instead fold it
            // into `messages` itself; either shape is accepted.
            "message" => "sometimes|string",
        ]);

        $transcript = $validated["messages"] ?? [];
        if (filled($validated["message"] ?? null)) {
            $transcript[] = ["role" => "user", "content" => $validated["message"]];
        }

        if (count($transcript) === 0) {
            return $this->fail($wantsStream, __("chat.error.generic"), 400, [], $validated["modelId"]);
        }

        $user = Auth::guard("key")->user();
        if ($user === null) {
            return $this->fail($wantsStream, __("chat.error.no_key"), 401, $transcript, $validated["modelId"]);
        }

        try {
            $upstream = Http::withHeaders($this->backend->headers($user->getAuthIdentifier()))
                ->withOptions(["stream" => true])
                ->connectTimeout(config("metager.chat.connect_timeout"))
                // No total timeout: generation length is unbounded by design. The guardrails are
                // the pool's request_terminate_timeout and nginx's fastcgi_read_timeout, both of
                // which sit far above any legitimate generation.
                ->timeout(0)
                ->post($this->backend->url("/api/chat"), [
                    "modelId" => $validated["modelId"],
                    "messages" => $transcript,
                ]);
        } catch (\Throwable $e) {
            Log::warning("chat: upstream unreachable", ["exception" => $e->getMessage()]);
            return $this->fail($wantsStream, __("chat.error.unavailable"), 503, $transcript, $validated["modelId"]);
        }

        // Rejections (401 no key, 402 insufficient balance, 400 bad model) arrive as a normal
        // status with a JSON body, precisely because the service emits them before opening the
        // stream. Pass the status through rather than flattening it — the UI distinguishes
        // "top up your balance" from "something broke".
        if (!$upstream->successful()) {
            $body = json_decode($upstream->body(), true);
            return $this->fail(
                $wantsStream,
                $body["message"] ?? __("chat.error.generic"),
                $upstream->status(),
                $transcript,
                $validated["modelId"]
            );
        }

        return $wantsStream
            ? $this->streamResponse($upstream)
            : $this->bufferedResponse($upstream, $transcript, $validated["modelId"]);
    }

    /**
     * Forwards the upstream SSE stream to the browser, augmenting the terminal `done` event with
     * the settled message rendered to HTML.
     *
     * The rendering happens here, in PHP, on purpose: it is the *only* Markdown renderer in the
     * system, so a JS user and a no-JS user see byte-identical output. During streaming the client
     * shows raw text; on `done` it swaps in this HTML.
     */
    private function streamResponse($upstream): StreamedResponse
    {
        $body = $upstream->toPsrResponse()->getBody();

        $response = new StreamedResponse(function () use ($body) {
            // The chat FPM pool sets output_buffering = 0, but Laravel/PHP may still have an
            // output buffer open from earlier in the request lifecycle.
            while (ob_get_level() > 0) {
                ob_end_flush();
            }

            $answer = "";
            $this->readEvents($body, function (string $event, array $data) use (&$answer) {
                if ($event === "delta") {
                    $answer .= $data["text"] ?? "";
                } elseif ($event === "done") {
                    $data["html"] = $this->renderMarkdown($answer);
                }

                echo "event: {$event}\ndata: " . json_encode($data) . "\n\n";
                flush();
            });
        });

        $response->headers->add([
            "Content-Type" => "text/event-stream; charset=utf-8",
            "Cache-Control" => "no-cache, no-transform",
            // Belt and braces alongside `fastcgi_buffering off` in the nginx chat location.
            "X-Accel-Buffering" => "no",
        ]);

        return $response;
    }

    /**
     * The no-JS path: consume the whole stream, then re-render the page with the answer appended.
     *
     * Slow by nature — tens of seconds with no feedback — but complete, and it keeps the full
     * MetaGer chrome (header, foki switcher, live balance widget) around the conversation.
     */
    private function bufferedResponse($upstream, array $transcript, string $modelId)
    {
        $body = $upstream->toPsrResponse()->getBody();

        $answer = "";
        $meta = [];
        $error = null;

        $this->readEvents($body, function (string $event, array $data) use (&$answer, &$meta, &$error) {
            if ($event === "delta") {
                $answer .= $data["text"] ?? "";
            } elseif ($event === "done") {
                $meta = $data;
            } elseif ($event === "error") {
                $error = $data["message"] ?? __("chat.error.generic");
            }
        });

        if ($answer !== "") {
            $transcript[] = [
                "role" => "assistant",
                "content" => $answer,
                "model" => $meta["model"] ?? $modelId,
            ];
        }

        return $this->renderPage($transcript, $modelId, $error);
    }

    /**
     * Error handling for both paths: JSON for the streaming client, a re-rendered page otherwise.
     *
     * The no-JS path must not lose the user's conversation just because one turn failed, so the
     * transcript is rendered back out along with the message.
     */
    private function fail(bool $wantsStream, string $message, int $status, array $transcript, ?string $modelId)
    {
        if ($wantsStream) {
            return response()->json(["message" => $message], $status);
        }

        return $this->renderPage($transcript, $modelId, $message)->setStatusCode($status);
    }

    /**
     * Renders the chat focus page.
     *
     * Deliberately does *not* go through MetaGer::createView(), even though that is how the focus
     * renders on a normal GET: createView() writes a QueryLogger entry, which would file every
     * chat prompt into MetaGer's search query log. Constructing the view directly keeps chat
     * prompts out of it entirely.
     */
    private function renderPage(array $transcript, ?string $modelId, ?string $error = null)
    {
        return response()->view("resultpages.resultpage_chat", [
            // The shared result-page chrome expects the same view data createView() supplies —
            // parts/errors.blade.php in particular does sizeof($errors) unguarded, and MetaGer
            // removes ShareErrorsFromSession (no sessions), so nothing populates these for free.
            "eingabe" => "",
            "mobile" => false,
            "warnings" => [],
            "htmlwarnings" => [],
            "errors" => [],
            "apiAuthorized" => false,
            "quicktips" => [],
            "metager" => app(MetaGer::class),

            "chatTranscript" => $transcript,
            "chatModelId" => $modelId,
            "chatError" => $error,
        ]);
    }

    /**
     * Minimal SSE reader over a PSR-7 stream.
     *
     * Events are separated by a blank line, so bytes are accumulated until a "\n\n" boundary is
     * seen — a single read can contain a partial event, several events, or both. Only `event:` and
     * `data:` are recognised, which is all our contract uses.
     */
    private function readEvents($body, callable $onEvent): void
    {
        $buffer = "";

        while (!$body->eof()) {
            $chunk = $body->read(8192);
            if ($chunk === "") {
                // Guzzle can hand back an empty string on a slow upstream without signalling EOF;
                // spinning on that would burn CPU for the whole generation.
                usleep(10000);
                continue;
            }

            $buffer .= $chunk;

            while (($boundary = strpos($buffer, "\n\n")) !== false) {
                $raw = substr($buffer, 0, $boundary);
                $buffer = substr($buffer, $boundary + 2);

                $event = null;
                $data = null;
                foreach (explode("\n", $raw) as $line) {
                    if (str_starts_with($line, "event: ")) {
                        $event = substr($line, 7);
                    } elseif (str_starts_with($line, "data: ")) {
                        $data = substr($line, 6);
                    }
                }

                if ($event !== null && $data !== null) {
                    $onEvent($event, json_decode($data, true) ?? []);
                }
            }
        }
    }

    /**
     * The single canonical Markdown renderer for chat output.
     *
     * Model output is untrusted text, so raw HTML in it is stripped rather than escaped-and-shown,
     * and unsafe link schemes (javascript:, data:) are refused. This is also what keeps the page's
     * `default-src 'self'` CSP story simple: no client-side HTML construction from model output.
     */
    private function renderMarkdown(string $markdown): string
    {
        return Str::markdown($markdown, [
            "html_input" => "strip",
            "allow_unsafe_links" => false,
        ]);
    }
}
