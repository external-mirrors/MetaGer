<?php

namespace App\Authentication;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

/**
 * What a string somebody typed into the sign-in form actually is.
 *
 * The page and the sign-in itself live here now; this one question does not,
 * and cannot, because every possible answer depends on state only the
 * keymanager holds. A six-digit login code sits in *its* Redis for ten
 * seconds; a six-character legacy key has to be checked against its key store,
 * or it MD5-folds into a phantom account ({@see self::KEY_MARK}); a voucher
 * code is normalised by its CampaignVoucher; and a QR code inside an uploaded
 * image needs Jimp, which PHP has no equal of that anyone should trust with a
 * credential.
 *
 * So `POST /api/json/key/resolve` answers, and the answer is a fact about the
 * input rather than a step in a flow: a key, a voucher, or a named error.
 * Which sentence a visitor reads and where they go next is this side's
 * decision, and this side can translate it — the strings that used to stand in
 * the keymanager's router could not.
 *
 * The one rule that lives here and not there: how often somebody may ask. The
 * endpoint is bearer-authenticated, so from its side every call is the same
 * caller; only this side knows whose browser is asking. See
 * {@see \App\Http\Controllers\LoginController}.
 */
final class KeyResolver
{
    /** A key, resolved to its canonical UUID. */
    public const KEY = "key";

    /** A campaign voucher, normalised — redeemed on the keymanager's page. */
    public const VOUCHER = "voucher";

    /** Nothing usable. `error` names which of the ways it was nothing. */
    public const ERROR = "error";

    /**
     * The keyserver did not answer at all.
     *
     * Its own answer, and deliberately not folded into {@see self::ERROR}:
     * "your key is wrong" and "we could not ask" are different things to tell
     * somebody, and only one of them is worth retrying.
     */
    public const UNREACHABLE = "unreachable";

    private string $keyserver;

    public function __construct()
    {
        $keyserver = config("metager.metager.keymanager.server") ?: config("app.url") . "/keys";
        $this->keyserver = $keyserver . "/api/json";
    }

    /**
     * @return array{result: string, key?: string, code?: string, error?: string}
     */
    public function resolve(string $input): array
    {
        return $this->ask("/key/resolve", ["input" => $input]);
    }

    /**
     * The key inside an uploaded image, if there is one.
     *
     * The bytes go up raw rather than as multipart: the form is served here,
     * the file is taken apart here, and what crosses is only its content. A
     * second multipart parse would be a second place with a second size limit
     * that could disagree with this one.
     */
    public function resolveImage(UploadedFile $file): array
    {
        $contents = @file_get_contents($file->getRealPath());

        if ($contents === false || $contents === "") {
            return ["result" => self::ERROR, "error" => "file_unreadable"];
        }

        return $this->ask("/key/resolve-image", $contents, raw: true);
    }

    private function ask(string $path, array|string $payload, bool $raw = false): array
    {
        $request = Http::timeout(5)->withHeaders([
            "Authorization" => "Bearer " . config("metager.metager.keymanager.access_token"),
            "X-Forwarded-For" => Request::ip(),
        ]);

        try {
            $response = $raw
                ? $request->withBody($payload, "application/octet-stream")->post($this->keyserver . $path)
                : $request->post($this->keyserver . $path, $payload);
        } catch (\Throwable $e) {
            Log::warning("keymanager resolve unreachable: " . $e->getMessage());

            return ["result" => self::UNREACHABLE];
        }

        if (!$response->successful()) {
            Log::warning("keymanager resolve answered " . $response->status());

            return ["result" => self::UNREACHABLE];
        }

        $body = $response->json();
        $result = Arr::get(is_array($body) ? $body : [], "result");

        // An answer this side does not recognise is not an answer. Passing it
        // on would put an unknown string where a translation key is expected.
        if (!in_array($result, [self::KEY, self::VOUCHER, self::ERROR], true)) {
            Log::warning("keymanager resolve answered with an unknown result");

            return ["result" => self::UNREACHABLE];
        }

        return $body;
    }
}
