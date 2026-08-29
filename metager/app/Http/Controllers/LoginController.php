<?php

namespace App\Http\Controllers;

use App\Landing\KeymanagerLinks;
use App\Support\Browser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Vite;

/**
 * Die Anmeldeseite — /anmelden.
 *
 * Lag als /keys/key/enter im Keymanager. Was hierher gezogen ist, ist die
 * Seite; das Formular schickt weiterhin dorthin ab
 * ({@see KeymanagerLinks::submitKey()}), und die Gründe dafür stehen an der
 * Methode. Die Naht dazwischen ist der `redirect_error`-Parameter: den kannte
 * routes/key.js schon, weil die Startseite ihn brauchte, und er ist jetzt der
 * Weg, auf dem ein abgewiesener Versuch wieder auf dieser Seite landet statt
 * auf einer zweiten Fassung derselben Seite im anderen Repository.
 *
 * Der Keymanager bekommt damit von dieser Seite drei Dinge mit, die er
 * zurückgeben muss und nicht selbst kennt: wohin bei Erfolg
 * (`redirect_success`), wohin bei Fehler (`redirect_error`) und die beiden
 * Callback-Marker der MetaGer-App. Alle vier sind versteckte Felder im
 * Formular, nicht Serverzustand — es gibt auf Webrouten keine Session.
 */
final class LoginController extends Controller
{
    /**
     * Die Fehlercodes, die der Keymanager als `key_error` zurückschicken kann.
     *
     * Eine Aufzählung und keine freie Zeichenkette: der Wert kommt aus der
     * Query und wird zu einem Übersetzungsschlüssel. Ohne diese Liste wäre
     * `?key_error=irgendwas` ein Weg, „login.errors.irgendwas“ nachzuschlagen —
     * Laravel gibt dann den Schlüssel selbst aus, und auf der Seite stünde
     * fremder Text.
     */
    private const ERRORS = [
        "invalid_key",
        "invalid_login_code",
        "invalid_key_payment_id",
        "no_input",
        "file_unreadable",
        // Sechs Zeichen, die kein Schlüssel sind — fast immer das Kürzel neben
        // dem Guthaben, das der Keymanager seit der Prüfung in POST /key/enter
        // abweist statt daraus ein leeres Phantomkonto zu machen.
        "key_mark",
    ];

    /**
     * Was von einem abgewiesenen Versuch wieder ins Feld darf.
     *
     * Der Keymanager hängt die Eingabe als `invalid_key` an, damit ein
     * Tippfehler sichtbar bleibt. Ein Schlüssel hat 36 Zeichen, ein Anmeldecode
     * sechs; alles darüber ist keine Eingabe mehr, die jemand korrigieren will.
     */
    private const MAX_PREFILL = 64;

    public function show(Request $request): Response|RedirectResponse
    {
        // Wer schon einen Schlüssel hat, will nicht das Anmeldeformular,
        // sondern sein Konto — genau die Fallunterscheidung, die vorher in
        // routes/key.js stand. Die Reihenfolge ist die des KeyAuthGuard, und
        // geprüft wird nur, *ob* etwas da ist: was davon ein gültiger
        // Schlüssel ist, weiß nur der Keyserver, und der wird beim
        // Weiterleiten ohnehin gefragt.
        if (
            $request->filled("key")
            || $request->hasHeader("key")
            || $request->cookie("key") !== null
        ) {
            return redirect()->away(KeymanagerLinks::dashboard($request));
        }

        $callback = KeymanagerLinks::appCallback($request);
        $redirectSuccess = $request->query("redirect_success");
        $redirectSuccess = is_string($redirectSuccess) && trim($redirectSuccess) !== ""
            ? $redirectSuccess
            : null;

        $error = $request->query("key_error");
        $error = is_string($error) && in_array($error, self::ERRORS, true) ? $error : null;

        $prefill = $request->query("invalid_key");
        $prefill = is_string($prefill) ? mb_substr(trim($prefill), 0, self::MAX_PREFILL) : "";

        return response()
            ->view("login", [
                "title" => trans("titles.login"),
                "navbarFocus" => "login",
                "css" => [Vite::asset("resources/less/metager/pages/login.less")],
                "js" => [Vite::asset("resources/js/login.js")],

                "action" => KeymanagerLinks::submitKey(),
                "chargeEndpoint" => KeymanagerLinks::keyApi(),
                // Absolut, und deshalb nicht route('login') mit der halben
                // Query dran: routes/key.js nimmt den Wert nur an, wenn dessen
                // Hostname der der Anfrage ist, und vergleicht dafür einen
                // geparsten URL. Ein Pfad allein wird dort verworfen und der
                // Besucher landet wieder beim Keymanager.
                "redirectError" => $this->selfUrl($callback, $redirectSuccess),
                "redirectSuccess" => $redirectSuccess,
                "callback" => $callback,

                // $keyError und nicht $error: @extends reicht die Variablen der
                // Ansicht per get_defined_vars() ans Layout weiter, und
                // layouts/staticPages rendert ein $error als Fehlerleiste. Der
                // Code stünde dann roh und unübersetzt ein zweites Mal auf der
                // Seite. Dasselbe gilt für $info, $success und $warning.
                "keyError" => $error,
                "prefill" => $prefill,

                "createUrl" => KeymanagerLinks::create($request),
                "tokenUrl" => route("anonymous-token"),
                "extension" => $this->extension(),
            ])
            // Die Seite hängt an Cookie und Query und trägt im Fehlerfall die
            // Eingabe des Besuchers. Nichts davon gehört in einen Cache, weder
            // in einen gemeinsamen noch in den des Browsers.
            ->header("Cache-Control", "no-store, private");
    }

    /**
     * Wohin der Erweiterungs-Knopf zeigt und wie er heißt.
     *
     * Die alte Seite bot drei Knöpfe mit drei Markenlogos an, von denen für
     * jeden Besucher höchstens einer je funktioniert hat. App\Support\Browser
     * weiß, in welchem Browser die Seite gerade steht, also ist es einer — und
     * dann braucht es auch keine fremden Logos in public/img.
     *
     * Ohne erkannten Browser mit Erweiterung führt der Knopf auf MetaGers
     * eigene Plugin-Seite: die beantwortet die Frage für jeden Browser und ist
     * die einzige ehrliche Antwort, wenn wir den Browser nicht kennen. Safari
     * und der Internet Explorer landen deshalb ebenfalls dort — für sie gibt es
     * keine Erweiterung, und ein Store-Link, der ins Leere führt, wäre
     * schlechter als eine Seite, die das erklärt.
     *
     * @return array{url: string, label: string}
     */
    private function extension(): array
    {
        $store = match (app(Browser::class)->name()) {
            "Firefox" => "https://addons.mozilla.org/firefox/addon/metager-suche/",
            "Chrome" => "https://chromewebstore.google.com/detail/metager-suche/gjfllojpkdnjaiaokblkmjlebiagbphd",
            "Edge" => "https://microsoftedge.microsoft.com/addons/detail/metager-suche/fdckbcmhkcoohciclcedgjmchbdeijog",
            default => null,
        };

        if ($store === null) {
            return ["url" => route("plugin"), "label" => trans("login.extension.install_generic")];
        }

        return [
            "url" => $store,
            "label" => trans("login.extension.install", ["browser" => app(Browser::class)->name()]),
        ];
    }

    /**
     * Diese Seite als absoluter URL, mit allem, was einen zweiten Versuch
     * überstehen muss — sonst verliert genau der Besucher die Callback-Marker
     * der App, der sich beim ersten Mal vertippt hat.
     *
     * `key_error` und `invalid_key` stehen bewusst nicht drin: die setzt der
     * Keymanager selbst dazu, und beide gehören zu diesem einen Versuch.
     *
     * @param array<string, string> $callback
     */
    private function selfUrl(array $callback, ?string $redirectSuccess): string
    {
        $query = $callback;

        if ($redirectSuccess !== null) {
            $query["redirect_success"] = $redirectSuccess;
        }

        return route("login", $query);
    }
}
