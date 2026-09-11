<?php

namespace App\Landing;

use App\Authentication\KeyState;
use App\Authentication\KeyUser;
use Illuminate\Http\Request;

/**
 * Ob ein Schlüssel gerade aufladen darf — und wenn nicht, warum.
 *
 * War `AccountController::topupBlocked()`, privat und einmal aufgerufen: nur
 * um zu entscheiden, ob die Kacheln auf /konto überhaupt erscheinen. Die
 * Aufladeseiten selbst (App\Http\Controllers\ChargeController) brauchen
 * dieselbe Prüfung ein zweites Mal — ein Cookie ist nicht so kurzlebig wie
 * eine Bestellzahl, und wer /konto/aufladen/1000 in einem zweiten Tab offen
 * hatte, als der dritte Auftrag im ersten entstand, darf hier keinen vierten
 * anlegen, nur weil die erste Seite ihn schon vorbeigelassen hat.
 *
 * Drei Gründe, alle drei standen schon auf der alten Kontoseite im Keymanager.
 * Der Rückgabewert wird zu einem Übersetzungsschlüssel
 * (`account.page.charge.blocked.*`) und ist deshalb keine freie
 * Zeichenkette und kommt aus keiner Eingabe.
 */
final class ChargeEligibility
{
    /**
     * Ab wie vielen offenen Ladungen der Keyserver keine weitere annimmt.
     *
     * `Key.isChargable()` drüben. Hier gespiegelt, um gar nicht erst Pakete
     * anzubieten, die an der Kasse abgewiesen würden — die alte Seite zeigte
     * dafür einen Satz *statt* der Pakete, und das bleibt so.
     */
    private const MAX_CHARGE_ORDERS = 3;

    /**
     * @param list<array{amount: float, expiration: \Illuminate\Support\Carbon|null}> $orders
     */
    public static function blockedReason(Request $request, KeyUser $user, array $orders): ?string
    {
        // Eine Proxy-Sitzung ist die eine Stelle, an der eine Bezahlseite dem
        // Besucher schaden könnte: sie führt zu einem Zahlungsdienstleister,
        // und der sieht dann eine Sitzung, die gerade anonym sein sollte.
        // Der Header kommt von unserem eigenen Proxy.
        if ($request->header("is-proxy") === "true") {
            return "proxy";
        }

        // Mitglieder suchen ohne weitere Kosten, solange ihre Mitglieds-
        // Ladung reicht; ein Token-Paket wäre bis dahin ein Angebot, für
        // etwas zu zahlen, das sie schon bezahlt haben. Ist sie (fast)
        // aufgebraucht, gilt das nicht mehr — ein paar Restpunkte, für keine
        // ganze Suche mehr gut, sind dasselbe Problem wie null.
        //
        // Dieselbe Schwelle wie überall sonst auf /konto: KeyState::EMPTY
        // (<=3, {@see \App\Authentication\KeyUser::getKeyState()}) ist schon
        // die Angabe "praktisch leer", die den Kontostand rot färbt und die
        // "leer"-Notiz zeigt. NO_KEY (Keyserver antwortet nicht) blockt hier
        // ebenfalls nicht — dieselbe Regel wie sonst im Vorgang: eine
        // unbekannte Ladung gilt als leer, nicht als voll.
        if ($user->isMember() && in_array($user->getKeyState(), [KeyState::FULL, KeyState::LOW], true)) {
            return "member";
        }

        if (count($orders) >= self::MAX_CHARGE_ORDERS) {
            return "full";
        }

        return null;
    }
}
