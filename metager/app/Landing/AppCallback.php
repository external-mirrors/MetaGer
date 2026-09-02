<?php

namespace App\Landing;

use Illuminate\Http\Request;

/**
 * Der Rückweg in die MetaGer-App.
 *
 * Die App öffnet zum Anmelden einen Custom Tab (docs/10-open-decisions.md#d52
 * im Repository app-en). Der Tab ist ein gewöhnlicher Browser: er bekommt das
 * Cookie, aber die App bekommt nichts davon mit. Damit der Schlüssel dort
 * ankommt, muss die Seite, an der die Anmeldung endet, ihn über einen von
 * Android *verifizierten* App Link zurückgeben — und das ist seit dem Umzug
 * des Kontos diese Anwendung und nicht mehr der Keymanager.
 *
 * Zwei Hälften, und sie gehören zusammen, weil beide denselben zwei Markern
 * folgen:
 *
 *   {@see markers()}     `keystore` und `variant` von einer Anfrage
 *                        weiterreichen, damit sie den Weg über mehrere Seiten
 *                        überleben.
 *   {@see handbackUrl()} sie einlösen: der App Link, der den Schlüssel trägt.
 *
 * **Nichts hiervon ist Zierrat, und nichts davon darf ungeprüft durchgereicht
 * werden.** `keystore` wählt den Host, an den ein Schlüssel geht; `variant`
 * steht unmittelbar neben `?key=` im Pfad des Weiterleitungsziels. Ein nicht
 * geprüfter Wert an einer der beiden Stellen ist eine offene Weiterleitung,
 * die eine Zugangsberechtigung mitgibt. Deshalb sind beide Aufzählungen und
 * keine Zeichenketten, und deshalb fällt ein unbekannter Wert auf den festen
 * Präfix zurück, statt abgewiesen zu werden — ein App-Build von vor der
 * Einführung von `variant` schickt keinen mit, und der soll sich weiter
 * anmelden können.
 *
 * Aus pass/routes/key.js übernommen, wo diese Logik zusammen mit dem Konto
 * stand. Was dort blieb, ist das Weiterreichen der Marker in der
 * Weiterleitung von /keys/key/enter hierher.
 */
final class AppCallback
{
    /**
     * Die Signaturzertifikate, die eine Anfrage als Anmeldung aus dem Custom
     * Tab der App ausweisen — und nicht als gewöhnlichen Browserbesuch.
     *
     * `release` kam dazu, als die echten Play-/manual-/F-Droid-Fingerabdrücke
     * in der assetlinks.json von metager.de eingetragen waren (2026-08-01);
     * davor konnten nur Debug-Builds, die immer `development` schicken, diesen
     * Weg überhaupt zu Ende gehen.
     */
    private const KEYSTORES = ["development", "release"];

    /**
     * Aus welchem Vertriebsweg der Rückruf kommt — passend zu den
     * `callbackVariant*`-Werten in android/app/build.gradle der App.
     *
     * Das ist es, was mehrere nebeneinander installierte Kanäle auf einem Host
     * unterscheidbar macht: debug, debug_manual und debug_playstore zielen auf
     * denselben App Link von metager3.de, und ohne unterscheidenden Pfad hat
     * Android keine Möglichkeit, den Rückruf dem gerade getesteten Build
     * zuzustellen — es sucht sich stillschweigend einen aus. Deshalb steckt
     * der Wert im *Pfad* und nicht nur in der Query: der Pfad ist das, worauf
     * der intent-filter der App tatsächlich passt.
     */
    private const VARIANTS = ["playstore", "manual", "fdroid"];

    /**
     * Die Marker dieser Anfrage, so wie sie an einen Link oder ein Formular
     * weitergereicht werden.
     *
     * Ein leeres Feld heißt: gewöhnlicher Browserbesuch, es gibt nichts
     * weiterzureichen. Hier wird bewusst *nicht* gegen {@see KEYSTORES}
     * geprüft — weitergereicht wird, was kam, und geprüft wird erst dort, wo
     * ein Wert etwas bewirkt ({@see handbackUrl()}). Sonst würde ein künftiger
     * Keystore-Name unterwegs verlorengehen, statt an einer Stelle abgewiesen
     * zu werden.
     *
     * @return array<string, string>
     */
    public static function markers(?Request $request = null): array
    {
        $request ??= request();

        // input() und nicht query(): auf der Anmeldeseite stehen die beiden als
        // versteckte Felder im Formular, weil sie einen Fehlversuch überleben
        // müssen und es keine Session gibt. Beim Abschicken kommen sie also im
        // Body an. query() sah dort nichts, und die Marker gingen genau in dem
        // Schritt verloren, für den sie mitgeführt werden.
        $keystore = $request->input("keystore");
        if (!is_string($keystore) || trim($keystore) === "") {
            return [];
        }

        $callback = ["keystore" => $keystore];

        $variant = $request->input("variant");
        if (is_string($variant) && trim($variant) !== "") {
            $callback["variant"] = $variant;
        }

        return $callback;
    }

    /**
     * Ob diese Anfrage aus dem Custom Tab der App kommt und der Schlüssel
     * deshalb zurückgegeben werden muss, statt eine Seite zu rendern.
     */
    public static function isHandback(?Request $request = null): bool
    {
        $request ??= request();

        return in_array($request->input("keystore"), self::KEYSTORES, true);
    }

    /**
     * Der App Link, über den der Schlüssel zurückgeht.
     *
     * `$needsCharge` sagt der App, dass der zurückgegebene Schlüssel noch
     * nichts bezahlen kann, sie den Benutzer also gleich wieder auf den
     * Aufladen-Abschnitt setzen soll, statt den Custom Tab nur zu schließen
     * (docs/10-open-decisions.md#d55 in app-en). Hier entschieden und nicht in
     * der App, weil diese Seite die Zahl ohnehin schon hat — die App müsste
     * sonst den Schlüssel speichern, die API nach seiner Ladung fragen und
     * erst dann weiterspringen, eine Runde nachdem der Tab bereits zu ist.
     *
     * Es ist ausdrücklich *nicht* „wurde dieser Schlüssel gerade erstellt“.
     * Ein frisch erstellter Schlüssel hat Ladung 0 und fällt ohnehin unter
     * diese Regel — und ein Besucher, der sich mit einem alten, leergesuchten
     * Schlüssel anmeldet, braucht denselben nächsten Schritt aus demselben
     * Grund. Eine Regel, abgelesen an der einzigen Tatsache, die darüber
     * entscheidet, ob jemand schon suchen kann.
     */
    public static function handbackUrl(
        string $key,
        ?string $keystore,
        ?string $variant,
        bool $needsCharge
    ): string {
        $url = self::host($keystore)
            . self::path($variant)
            . "?key=" . urlencode($key);

        if ($needsCharge) {
            $url .= "&flow=charge";
        }

        return $url;
    }

    /**
     * Welchen Host der App Link eines Builds anspricht.
     *
     * `debug.keystore` liegt im offenen App-Repository, sein privater
     * Schlüssel ist also öffentlich: jeder kann eine APK mit demselben
     * Paketnamen und demselben Zertifikat signieren. Android verhindert nur,
     * dass ein solcher Build eine bereits installierte, anders signierte App
     * stillschweigend ersetzt — frisch installieren lässt er sich, und für den
     * Host, der sein Zertifikat in seiner assetlinks.json führt, verifiziert
     * er dann genau wie die echte App. Debug-Builds dürfen deshalb nie
     * erfahren, dass metager.de diesem Zertifikat traut; sie bekommen
     * metager3.de, dessen assetlinks.json den Debug-Fingerabdruck als einzige
     * führt (siehe routes/web.php, `.well-known/assetlinks.json`).
     */
    private static function host(?string $keystore): string
    {
        if ($keystore === "development") {
            return config("metager.metager.app.callback_dev_url");
        }

        return config("metager.metager.app.callback_url");
    }

    /** @see VARIANTS — alles Unbekannte fällt auf den festen Präfix zurück. */
    private static function path(?string $variant): string
    {
        return in_array($variant, self::VARIANTS, true)
            ? "/app/callback/" . $variant
            : "/app/callback";
    }
}
