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
    /**
     * Attachment size ceiling, matched by MAX_CONTENT_BYTES in metager-chat's lib/attachments.ts.
     *
     * The real constraint is not storage but billing: the file's text becomes prompt tokens the
     * user pays for on every subsequent turn of the conversation, so a generous limit here is a
     * bill, not a convenience.
     */
    private const MAX_ATTACHMENT_BYTES = 256 * 1024;

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
            // Attachments already parked with the chat service on an earlier turn. Only the id and
            // the display name travel; the content never re-enters the browser.
            "messages.*.attachments" => "sometimes|array",
            "messages.*.attachments.*.id" => "required|string",
            "messages.*.attachments.*.name" => "required|string",
            // The new turn, as the composer's textarea sends it. The JS path may instead fold it
            // into `messages` itself; either shape is accepted.
            "message" => "sometimes|string",
            // `attachment` is deliberately absent: a failed validation rule throws, and without a
            // session Laravel can only answer that with a redirect back — which would drop the
            // entire conversation on the floor because the transcript lives in this request's
            // fields. storeAttachment() checks the same things and returns a message instead, so a
            // rejected file costs the user one sentence rather than their whole chat.
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

        // A file belongs to the turn it was sent with. Uploading it here — before anything is
        // billed or streamed — means a rejected file costs nothing and can still be reported as a
        // status code.
        // file() rather than hasFile(): the latter is false for a *failed* upload, which would turn
        // "your file was too big for PHP" into silently answering as if nothing had been attached.
        $attached = [];
        $upload = $request->file("attachment");
        if ($upload !== null) {
            $result = $this->storeAttachment($upload, $user->getAuthIdentifier());
            if (is_string($result)) {
                return $this->fail($wantsStream, $result, 422, $transcript, $validated["modelId"]);
            }

            $attached = [$result];
            $last = count($transcript) - 1;
            $transcript[$last]["attachments"] = array_merge($transcript[$last]["attachments"] ?? [], $attached);
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

            // 410 means an attachment's hour ran out mid-conversation. That is the one upstream
            // rejection a user can act on, so it gets MetaGer's own localised wording rather than
            // the service's English.
            $message = $upstream->status() === 410
                ? __("chat.error.attachment_gone")
                : ($body["message"] ?? __("chat.error.generic"));

            return $this->fail($wantsStream, $message, $upstream->status(), $transcript, $validated["modelId"]);
        }

        return $wantsStream
            ? $this->streamResponse($upstream, $attached)
            : $this->bufferedResponse($upstream, $transcript, $validated["modelId"]);
    }

    private function attachmentLimitLabel(): string
    {
        return round(self::MAX_ATTACHMENT_BYTES / 1024) . " KB";
    }

    /**
     * Validates one uploaded file and parks it with the chat service.
     *
     * Returns `["id" => …, "name" => …]` on success, or a localised error string.
     *
     * The check is "is this text?" rather than an extension whitelist: what the model can actually
     * use is text, whatever it is called, and a list of allowed suffixes would reject a perfectly
     * readable file for having the wrong name while happily accepting a renamed binary. Invalid
     * UTF-8 or an embedded NUL is the honest signal, and it needs no maintenance.
     */
    private function storeAttachment($file, string $key)
    {
        if (!$file->isValid()) {
            // PHP rejected the upload before we ever saw it. Size is the one cause worth naming —
            // it is the only one the user can do anything about.
            return in_array($file->getError(), [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)
                ? __("chat.error.file_too_large", ["size" => $this->attachmentLimitLabel()])
                : __("chat.error.file_failed");
        }

        $content = @file_get_contents($file->getRealPath());
        if ($content === false) {
            return __("chat.error.file_failed");
        }

        if (strlen($content) > self::MAX_ATTACHMENT_BYTES) {
            return __("chat.error.file_too_large", ["size" => $this->attachmentLimitLabel()]);
        }

        if ($content === "" || str_contains($content, "\0") || !mb_check_encoding($content, "UTF-8")) {
            return __("chat.error.file_not_text");
        }

        // getClientOriginalName() is attacker-controlled — it is only ever echoed as escaped text
        // and sent onward as a label, never used as a path.
        $name = $file->getClientOriginalName();
        $id = $this->backend->uploadFile($key, $name, $content);

        return $id === null ? __("chat.error.file_failed") : ["id" => $id, "name" => $name];
    }

    /**
     * Forwards the upstream SSE stream to the browser, augmenting the terminal `done` event with
     * the settled message rendered to HTML.
     *
     * The rendering happens here, in PHP, on purpose: it is the *only* Markdown renderer in the
     * system, so a JS user and a no-JS user see byte-identical output. During streaming the client
     * shows raw text; on `done` it swaps in this HTML.
     */
    private function streamResponse($upstream, array $attached = []): StreamedResponse
    {
        $body = $upstream->toPsrResponse()->getBody();

        $response = new StreamedResponse(function () use ($body, $attached) {
            // The chat FPM pool sets output_buffering = 0, but Laravel/PHP may still have an
            // output buffer open from earlier in the request lifecycle.
            while (ob_get_level() > 0) {
                ob_end_flush();
            }

            // The ids the just-uploaded files were parked under, so the client can put them in its
            // hidden fields and keep referring to them on later turns. Sent first and unconditionally:
            // the upload already happened and is already stored, whether or not generation succeeds.
            if (count($attached) > 0) {
                echo "event: attachments\ndata: " . json_encode(["attachments" => $attached]) . "\n\n";
                flush();
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
        // SearchSettings reads the focus from the request, and the composer sends `focus=chat` for
        // exactly that reason. It is load-bearing well beyond the foki switcher, though: the body
        // class it produces is what scopes the whole chat stylesheet, and it now also decides
        // whether the search chrome renders (layouts/researchandtabs.blade.php). Pinning it here
        // means a lost field degrades to "no highlight" rather than to an unstyled page wrapped in
        // a search bar for a search that never ran.
        app(\App\SearchSettings::class)->fokus = "chat";

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
