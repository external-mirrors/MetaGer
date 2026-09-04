<?php

namespace Tests\Unit;

use App\Support\MembershipFee;
use PHPUnit\Framework\TestCase;

/**
 * Die Beitragsordnung, so wie das Formular sie durchsetzt.
 *
 * Der Test, der vorher nicht möglich war: Mindestbeitrag und Vorschläge lagen
 * in `MembershipController` und `membership/form.blade.php` verstreut, also
 * hinter einem mehrstufigen Formular mit Datenbank. Jetzt sind sie eine
 * Funktion von zwei Angaben — Person oder Firma, und bei einer Firma die
 * Größenklasse — und genau das steht hier.
 *
 * Der Punkt, an dem der Test wehtut, ist {@see self::testTheSmallCompanyNoLongerPaysAPersonsFee}:
 * die 5 € für eine Firma mit bis zu 19 Mitarbeitenden standen so in der
 * Beitragsordnung des Vereins, sind also ein Beschluss und kein Versehen. Wer
 * diesen Betrag hier ändert, ändert eine Vereinsordnung — und soll das an
 * dieser Stelle sehen und nicht nachträglich merken.
 */
class MembershipFeeTest extends TestCase
{
    // ── Natürliche Personen ──────────────────────────────────────────────────

    /**
     * 2,50 € ist nicht der Beitrag, sondern die Untergrenze der Validierung:
     * darunter nimmt das Formular nichts an, dazwischen verlangt es einen
     * Nachweis für die Ermäßigung. Der reguläre Beitrag beginnt bei 5 €.
     */
    public function testAPersonMayApplyForTheReducedFee(): void
    {
        $this->assertSame(2.5, MembershipFee::forPerson()->minimum());
        $this->assertSame(2.5, MembershipFee::PERSON_REDUCED_MINIMUM);
        $this->assertSame(5.0, MembershipFee::PERSON_MINIMUM);
    }

    public function testAPersonKeepsTheThreeFamiliarSuggestions(): void
    {
        $this->assertSame(["10.00", "15.00", "20.00"], MembershipFee::forPerson()->presets());
    }

    // ── Firmen ───────────────────────────────────────────────────────────────

    /**
     * Der Betrag, den die neue Beitragsordnung anhebt.
     *
     * Die alte nannte für 1-19 Mitarbeitende 5 € — denselben Mindestbeitrag wie
     * für eine Einzelperson, bei ausdrücklich bis zu neunzehn Suchenden. Im
     * Controller sah man das nicht: `$min_amount` startete bei 5 und bekam nur
     * für die beiden größeren Klassen einen eigenen Zweig, weil die kleinste
     * ihn nicht brauchte.
     */
    public function testTheSmallCompanyNoLongerPaysAPersonsFee(): void
    {
        $fee = MembershipFee::forCompany("1-19");

        $this->assertSame(25.0, $fee->minimum());
        $this->assertGreaterThan(MembershipFee::PERSON_MINIMUM, $fee->minimum());
    }

    public function testTheTwoLargerBracketsAreUnchanged(): void
    {
        $this->assertSame(100.0, MembershipFee::forCompany("20-199")->minimum());
        $this->assertSame(200.0, MembershipFee::forCompany(">200")->minimum());
    }

    /**
     * Der eigentliche Fehler im Formular: die Validierung ließ nur `10.00`,
     * `15.00`, `20.00` und `custom` durch, der Mindestbeitrag einer Firma lag
     * darüber. Jede Kombination war damit ungültig, und der einzige Weg hinein
     * war das Wunschbetragsfeld. Deshalb muss jeder Vorschlag mindestens den
     * Mindestbeitrag erreichen — sonst schlägt das Formular etwas vor, das es
     * selbst zurückweist.
     */
    public function testEverySuggestionSatisfiesItsOwnMinimum(): void
    {
        foreach (["1-19", "20-199", ">200"] as $employees) {
            $fee = MembershipFee::forCompany($employees);

            foreach ($fee->presetValues() as $preset) {
                $this->assertGreaterThanOrEqual(
                    $fee->minimum(),
                    $preset,
                    "$employees suggests $preset below its own minimum"
                );
            }
        }

        foreach (MembershipFee::forPerson()->presetValues() as $preset) {
            $this->assertGreaterThanOrEqual(MembershipFee::PERSON_MINIMUM, $preset);
        }
    }

    /**
     * Der erste Vorschlag ist der Mindestbeitrag: das Formular hat den ersten
     * vorausgewählt, und eine Firma soll nicht mit einem Betrag starten, den
     * sie nicht gewählt hat.
     */
    public function testTheFirstSuggestionIsTheMinimum(): void
    {
        foreach (["1-19", "20-199", ">200"] as $employees) {
            $fee = MembershipFee::forCompany($employees);

            $this->assertSame($fee->minimum(), $fee->presetValues()[0], "for $employees");
        }
    }

    /**
     * Die Schreibweise ist die des Formulars, nicht die der Anzeige. Die
     * Validierung vergleicht den geposteten String mit dieser Liste, und
     * `25,00` oder `25` wären beide ein „ungültige Auswahl“.
     */
    public function testSuggestionsAreFormattedTheWayTheFormPostsThem(): void
    {
        $this->assertSame(["25.00", "50.00", "100.00"], MembershipFee::forCompany("1-19")->presets());
        $this->assertSame(["100.00", "200.00", "300.00"], MembershipFee::forCompany("20-199")->presets());
        $this->assertSame(["200.00", "400.00", "600.00"], MembershipFee::forCompany(">200")->presets());
    }

    // ── Bestandsmitglieder ───────────────────────────────────────────────────

    /**
     * Eine bestehende Mitgliedschaft behält ihren Boden.
     *
     * Die Erinnerungsmails verlinken den Beitragsschritt mit
     * `edit=membership-fee`; der nullt `amount` und validiert neu. Mit dem
     * angehobenen Mindestbeitrag käme ein Mitglied, das seit Jahren 5 € zahlt,
     * aus diesem Schritt nicht mehr heraus, ohne seinen Beitrag zu verfünffachen.
     */
    public function testAnExistingSmallCompanyKeepsTheOldFloor(): void
    {
        $this->assertSame(5.0, MembershipFee::forCompany("1-19", grandfathered: true)->minimum());
    }

    /** Für die beiden großen Klassen hat sich nichts geändert, auch nicht rückwirkend. */
    public function testGrandfatheringChangesNothingForTheLargerBrackets(): void
    {
        $this->assertSame(100.0, MembershipFee::forCompany("20-199", grandfathered: true)->minimum());
        $this->assertSame(200.0, MembershipFee::forCompany(">200", grandfathered: true)->minimum());
    }

    /** Die Vorschläge sind dieselben — nur der Boden darunter ist ein anderer. */
    public function testGrandfatheringDoesNotChangeTheSuggestions(): void
    {
        $this->assertSame(
            MembershipFee::forCompany("1-19")->presets(),
            MembershipFee::forCompany("1-19", grandfathered: true)->presets()
        );
    }

    // ── Der Rand ─────────────────────────────────────────────────────────────

    /**
     * `employees` ist ein ENUM, ein vierter Wert kann also nur aus einem
     * Datensatz kommen, der älter ist als die Spalte. Er darf nicht auf dem
     * Beitrag einer Einzelperson landen.
     */
    public function testAnUnknownBracketFallsBackToTheSmallestCompanyAndNotToAPerson(): void
    {
        $fee = MembershipFee::forCompany("42");

        $this->assertSame(25.0, $fee->minimum());
        $this->assertSame(MembershipFee::forCompany("1-19")->presets(), $fee->presets());
    }
}
