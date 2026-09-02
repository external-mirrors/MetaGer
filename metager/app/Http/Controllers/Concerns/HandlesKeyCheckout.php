<?php

namespace App\Http\Controllers\Concerns;

use App\Authentication\KeyIssuer;
use App\Authentication\KeyUser;
use App\Landing\KeymanagerLinks;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Der gemeinsame Unterbau der schlüsselgebundenen Kassen- und Bestellseiten
 * ({@see \App\Http\Controllers\ChargeController},
 * {@see \App\Http\Controllers\OrderController}).
 *
 * Beide brauchen dieselben vier Dinge, und vor diesem Trait stand jedes davon
 * wortgleich in beiden Klassen: den angemeldeten Schlüssel auflösen (oder eine
 * Weiterleitung liefern), den kanonischen Schlüsselstring bestimmen, prüfen ob
 * ein Formular von unserer eigenen Seite kam, und ob ein URL auf unseren Host
 * zeigt.
 */
trait HandlesKeyCheckout
{
    /**
     * Meldet den Besucher an, oder liefert eine Weiterleitung, die an seiner
     * statt zurückgegeben werden soll.
     *
     * Dieselbe Reihenfolge wie {@see \App\Http\Controllers\AccountController::show()}:
     * kein Schlüssel geht zur Anmeldung (mit `$loginReturn` als Rücksprungziel),
     * ein anonymes Token zur Erklärungsseite der Erweiterung, und ein Schlüssel,
     * den der Keyserver gerade nicht kanonisch beantworten kann, zurück zum
     * Konto.
     *
     * @return array{0: KeyUser, 1: string, 2: null}|array{0: null, 1: null, 2: RedirectResponse}
     */
    private function resolveKey(Request $request, string $loginReturn): array
    {
        /** @var KeyUser|null $user */
        $user = Auth::guard("key")->user();

        if ($user === null) {
            return [null, null, redirect()
                ->to(KeymanagerLinks::login($loginReturn, $request))
                ->header("Cache-Control", "no-store, private")];
        }

        if ($user->temporary) {
            return [null, null, redirect()
                ->to(route("anonymous-token"))
                ->header("Cache-Control", "no-store, private")];
        }

        $key = $this->keyOf($user);
        if ($key === null) {
            return [null, null, redirect()
                ->to(route("account"))
                ->header("Cache-Control", "no-store, private")];
        }

        return [$user, $key, null];
    }

    /** Wortgleich zu AccountController::keyOf() — siehe dort für das Warum. */
    private function keyOf(KeyUser $user): ?string
    {
        $canonical = $user->getCanonicalKey();
        if ($canonical !== null && KeyIssuer::isKey($canonical)) {
            return strtolower($canonical);
        }

        return KeyIssuer::isKey($user->key) ? strtolower($user->key) : null;
    }

    /**
     * Ob dieses Formular von unserer eigenen Seite abgeschickt wurde.
     *
     * Wortgleich zu {@see \App\Http\Controllers\KeyCreationController::sameOrigin()}
     * und aus demselben Grund; die Begründung steht dort.
     */
    private function sameOrigin(Request $request): bool
    {
        $origin = $request->header("Origin");

        if (is_string($origin) && $origin !== "" && $origin !== "null") {
            return $this->isOurs($request, $origin);
        }

        $site = $request->header("Sec-Fetch-Site");

        if (is_string($site) && $site !== "") {
            return in_array($site, ["same-origin", "same-site", "none"], true);
        }

        return true;
    }

    /** Ob ein URL auf den Host zeigt, unter dem diese Anfrage ankam. */
    private function isOurs(Request $request, string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);

        if ($host === null || $host === false) {
            return str_starts_with($url, "/") && !str_starts_with($url, "//");
        }

        $port = parse_url($url, PHP_URL_PORT);

        return ($host . ($port === null ? "" : ":" . $port)) === $request->getHttpHost();
    }
}
