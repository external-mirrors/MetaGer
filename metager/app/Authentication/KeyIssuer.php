<?php

namespace App\Authentication;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Ein Schlüssel, den es noch nicht gibt.
 *
 * Die Seite zum Erstellen liegt hier ({@see \App\Http\Controllers\KeyCreationController}),
 * und alles, was mit dem Schlüssel geschieht, geschieht hier: anzeigen, als
 * QR-Code ausgeben, das Cookie setzen. Gewürfelt wird er trotzdem drüben, und
 * zwar wegen der Prüfung, nicht wegen der Zufallszahl.
 *
 * `Str::uuid()` gäbe dieselben 122 Bit. Was es nicht gäbe, ist die Antwort auf
 * die Frage, ob die gewürfelte UUID schon jemandem gehört — und die ist hier
 * nicht rhetorisch: der Keyserver faltet jeden alten Nicht-UUID-Schlüssel per
 * MD5 in denselben Raum und setzt Version und Variante von Hand (`Key.GET_KEY`
 * dort). In dem Raum, in dem gewürfelt wird, liegen also fremde Konten, und wer
 * sie kennt, ist nur er. `POST /api/json/key/new` ist genau diese eine Frage.
 *
 * Angelegt wird dabei nichts. Der Schlüssel steht erst dann in Redis, wenn er
 * zum ersten Mal aufgeladen wird — ein Schlüssel, den am Ende niemand nimmt,
 * hinterlässt nichts, und zwei Aufrufe kurz hintereinander stören einander
 * nicht.
 *
 * Der Zwilling ist {@see KeyResolver}: dieselbe Naht, die andere Frage.
 */
final class KeyIssuer
{
    private string $keyserver;

    public function __construct()
    {
        $keyserver = config("metager.metager.keymanager.server") ?: config("app.url") . "/keys";
        $this->keyserver = $keyserver . "/api/json";
    }

    /**
     * Ein frischer Schlüssel, oder null, wenn der Keyserver nicht antwortet.
     *
     * Null und keine Ausnahme: die Seite kann das erklären, und sie muss es
     * auch — ohne Schlüssel gibt es hier nichts zu zeigen, aber ein Besucher,
     * der lesen kann, dass es an uns liegt, versucht es in einer Minute noch
     * einmal, statt zu glauben, es gehe nicht.
     */
    public function issue(): ?string
    {
        try {
            $response = Http::timeout(5)
                ->withHeaders(["Authorization" => "Bearer " . config("metager.metager.keymanager.access_token")])
                ->post($this->keyserver . "/key/new");
        } catch (\Throwable $e) {
            Log::warning("keymanager key/new unreachable: " . $e->getMessage());

            return null;
        }

        if (!$response->successful()) {
            Log::warning("keymanager key/new answered " . $response->status());

            return null;
        }

        $body = $response->json();
        $key = Arr::get(is_array($body) ? $body : [], "key");

        // Geprüft und nicht geglaubt: was hier herauskommt, wird gleich als
        // Konto in ein Cookie geschrieben und in einen QR-Code gemalt. Eine
        // Antwort, die keine UUID ist, ist keine Antwort.
        if (!is_string($key) || !self::isKey($key)) {
            Log::warning("keymanager key/new answered with something that is not a key");

            return null;
        }

        return strtolower($key);
    }

    /**
     * Ob eine Zeichenkette ein Schlüssel ist.
     *
     * Dieselbe Form, die der Keyserver in `Key.IS_VALID_UUID` prüft — UUID der
     * Version 4. Steht hier, weil auch der Weg zurück sie braucht: das
     * Formular schickt den Schlüssel als verstecktes Feld ab, und was von dort
     * kommt, ist Eingabe wie jede andere.
     */
    public static function isKey(string $key): bool
    {
        return preg_match(
            "/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i",
            $key
        ) === 1;
    }

    /**
     * The first key-shaped UUID found anywhere inside arbitrary file
     * contents, or null.
     *
     * Built for {@see \App\Authentication\KeyResolver::resolveImage()}:
     * suma-crm's membership confirmation page now offers a plain-text
     * download of a freshly-minted key alongside its QR code, and the sign-in
     * form's file input — built for a QR-code screenshot — has no way to
     * tell the two apart before reading them. Unlike isKey(), deliberately
     * unanchored (a search within a whole file, not a check of one
     * already-isolated string) and tolerant of surrounding text.
     *
     * The UTF-8 check is what keeps this from ever running the search
     * against an actual QR-code image at all: real image bytes are all but
     * certain to contain an invalid UTF-8 sequence somewhere across the
     * whole file, so this returns null for one before the regex does any
     * work, rather than relying on the regex alone to simply find nothing.
     */
    public static function findInText(string $contents): ?string
    {
        if (!mb_check_encoding($contents, "UTF-8")) {
            return null;
        }

        if (preg_match("/[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}/i", $contents, $matches) !== 1) {
            return null;
        }

        return strtolower($matches[0]);
    }
}
