<?php

namespace App\Authentication;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Der Anmeldecode, mit dem ein zweites Gerät an dasselbe Konto kommt.
 *
 * Sechs Ziffern statt sechsunddreißig Zeichen, zehn Sekunden gültig, für genau
 * eine Anmeldung. Wer sein Telefon anmelden will, tippt sie ab, statt eine UUID
 * abzuschreiben oder den Schlüssel durch einen Messenger zu schicken.
 *
 * Die dritte Naht zum Keyserver in dieser Reihe, und sie ist aus demselben
 * Grund dort geblieben wie {@see KeyIssuer} und {@see KeyResolver}: der Code
 * lebt in *seinem* Redis. `logincode:<code>` → Schlüssel und
 * `logincode:<schlüssel>` → Code, beide mit zehn Sekunden Frist, und die
 * Kollisionsprüfung beim Würfeln ist ein `SETNX` gegen denselben Speicher.
 * Nachgebaut werden könnte das hier nur mit einem zweiten Redis, den die
 * Anmeldung dann auch lesen müsste — und die läuft drüben.
 *
 * Neu ist nur, dass danach gefragt wird: solange das Konto im Keymanager lag,
 * holte seine Seite den Code über `GET /keys/key/<uuid>/logincode`, eine
 * Webroute mit dem Schlüssel im Pfad. Jetzt fragt der Server für den
 * angemeldeten Besucher ({@see \App\Http\Controllers\AccountController::loginCode()}),
 * und der Schlüssel steht in keiner Adresse mehr.
 */
final class LoginCodeIssuer
{
    private string $keyserver;

    public function __construct()
    {
        $keyserver = config("metager.metager.keymanager.server") ?: config("app.url") . "/keys";
        $this->keyserver = $keyserver . "/api/json";
    }

    /**
     * Der aktuell gültige Code für diesen Schlüssel, oder null.
     *
     * Der Keyserver gibt einen bestehenden Code zurück und verlängert seine
     * Frist, statt jedes Mal einen neuen zu würfeln — deshalb darf die Seite
     * im Sekundentakt fragen, ohne dem Besucher die Ziffern unter den Fingern
     * wegzuziehen. Wechselt der Code trotzdem, dann ist der alte verbraucht:
     * jemand hat sich damit angemeldet.
     */
    public function issue(string $key): ?string
    {
        try {
            $response = Http::timeout(5)
                ->withHeaders(["Authorization" => "Bearer " . config("metager.metager.keymanager.access_token")])
                ->post($this->keyserver . "/key/" . urlencode($key) . "/logincode");
        } catch (\Throwable $e) {
            Log::warning("keymanager logincode unreachable: " . $e->getMessage());

            return null;
        }

        if (!$response->successful()) {
            Log::warning("keymanager logincode answered " . $response->status());

            return null;
        }

        $body = $response->json();
        $code = Arr::get(is_array($body) ? $body : [], "code");

        // Sechs Ziffern, und nur die. Der Wert wird in eine Seite geschrieben,
        // von der ihn jemand abtippt — was keine sechs Ziffern sind, ist keine
        // Antwort, und ein durchgereichter Fremdtext wäre eine Meldung, die wir
        // nicht formuliert haben.
        if (!is_string($code) && !is_int($code)) {
            return null;
        }

        $code = (string) $code;

        return preg_match("/^[0-9]{6}$/", $code) === 1 ? $code : null;
    }
}
