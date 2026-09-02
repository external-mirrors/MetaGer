<?php

namespace Tests\Unit\Authentication;

use App\Authentication\KeyUser;
use App\Authentication\KeyState;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Was {@see KeyUser::refresh()} wegwirft — und dass es alles ist.
 *
 * Der Kontostand wird zehn Sekunden zwischengespeichert, weil eine Suche ihn
 * mehrfach braucht und der Keyserver nicht dreimal pro Anfrage gefragt werden
 * soll. Genau einmal ist das falsch: direkt nachdem gutgeschrieben wurde. Wer
 * von einer Zahlung zurückkommt, ist schneller als zehn Sekunden, und dann
 * steht der Stand von vorher neben „Aufladen abgeschlossen".
 *
 * Der Stand liegt an drei Stellen gleichzeitig: im Cache (geteilt zwischen
 * allen Prozessen), in $key_data (dieses Objekt, vom Guard schon gefüllt) und
 * abgeleitet in $state. Ein refresh(), das eine davon stehen lässt, sieht in
 * einem Test grün aus und zeigt trotzdem die alte Zahl — deshalb hat jede
 * ihren eigenen Test.
 */
class KeyUserRefreshTest extends TestCase
{
    private const KEY = "0d1e2f3a-4b5c-4d6e-8f90-1a2b3c4d5e6f";

    /**
     * Ein KeyUser mit vorgemerktem Stand, ohne Netz — genau der Zustand, in
     * dem der Guard ihn an einen Controller übergibt.
     */
    private function cachedKeyUser(float $charge): KeyUser
    {
        Cache::put("keyserver:key:" . self::KEY, [
            "key" => self::KEY,
            "charge" => $charge,
        ], now()->addMinutes(10));

        return new KeyUser(self::KEY);
    }

    /**
     * Was der Keyserver ab jetzt sagt. Erst nach einem refresh() wird er
     * überhaupt gefragt; preventStrayRequests() sorgt dafür, dass ein Test,
     * der versehentlich woanders hinfasst, das auch sagt.
     */
    private function keyserverSays(float $charge): void
    {
        Http::preventStrayRequests();
        Http::fake([
            "*/api/json/key/*" => Http::response([
                "key" => self::KEY,
                "charge" => $charge,
            ]),
        ]);
    }

    /**
     * Die Ausgangslage, ohne die die anderen drei nichts beweisen: solange
     * niemand refresh() ruft, wird der Keyserver nicht gefragt.
     */
    public function testWithoutARefreshTheCachedChargeIsWhatIsRead(): void
    {
        $this->keyserverSays(1000);
        $user = $this->cachedKeyUser(charge: 7);

        $this->assertSame(7.0, $user->getCharge());
        Http::assertNothingSent();
    }

    public function testAfterARefreshTheChargeComesFromTheKeyserverAgain(): void
    {
        $this->keyserverSays(1000);
        $user = $this->cachedKeyUser(charge: 7);
        $user->getCharge(); // füllt $key_data, so wie der Guard es tut

        $user->refresh();

        $this->assertSame(1000.0, $user->getCharge());
    }

    /**
     * Der geteilte Eintrag wird ersetzt, nicht nur umgangen. Sonst stimmte die
     * Seite, auf der gerade bezahlt wurde, und die nächste wieder nicht.
     */
    public function testTheRefreshedChargeIsWrittenBackToTheSharedCache(): void
    {
        $this->keyserverSays(1000);
        $user = $this->cachedKeyUser(charge: 7);

        $user->refresh();
        $user->getCharge();

        $this->assertSame(1000.0, (float) Cache::get("keyserver:key:" . self::KEY)["charge"]);
    }

    /**
     * Der abgeleitete Zustand fällt mit weg. Er bestimmt die Farbe des
     * Kontochips und den Warnhinweis in der Seitenleiste — bliebe er stehen,
     * hätte die Seite nach dem Aufladen die richtige Zahl in der Farbe für
     * „leer".
     */
    public function testTheDerivedStateIsDroppedAlongWithTheCharge(): void
    {
        $this->keyserverSays(1000);
        $user = $this->cachedKeyUser(charge: 1);
        $this->assertSame(KeyState::EMPTY , $user->getKeyState());

        $user->refresh();

        $this->assertSame(KeyState::FULL, $user->getKeyState());
    }
}
