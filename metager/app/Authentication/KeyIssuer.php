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
}
