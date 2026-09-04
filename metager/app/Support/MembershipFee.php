<?php

namespace App\Support;

use App\Models\Membership\MembershipApplication;

/**
 * Was eine Mitgliedschaft mindestens kostet, und was das Formular vorschlägt.
 *
 * Beides stand vorher an sechs Stellen, und die sechs waren sich nicht einig.
 * Der Mindestbeitrag als `$min_amount`-Leiter in
 * {@see \App\Http\Controllers\MembershipController::submitMembershipForm}, die
 * Vorschläge dreimal wörtlich als `10.00`/`15.00`/`20.00` — einmal als
 * Whitelist in derselben Validierung, einmal als drei Radiobuttons in
 * `membership/form.blade.php`, einmal in der Rückfrage, ob ein bestehender
 * Beitrag auf einen Vorschlag oder in das Wunschbetragsfeld gehört
 * ({@see \App\Http\Controllers\MembershipController::contactData}).
 *
 * Dass diese drei Zahlen dieselben sein müssen, war keine Absicht, sondern
 * Zufall — und für Firmen war der Zufall bereits ausgegangen: die Whitelist
 * ließ nur 10, 15 und 20 € durch, der Mindestbeitrag einer Firma ab 20
 * Mitarbeitenden lag bei 100 €. Beides zusammen heißt, dass eine Firma über
 * die Vorschläge gar nicht beitreten konnte, sondern zwingend „Wunschbetrag“
 * wählen und die Zahl selbst tippen musste. Genau deshalb kommen Mindestbetrag
 * und Vorschläge jetzt aus einer Hand: sie sind dieselbe Entscheidung.
 *
 * Die drei Größenklassen sind nicht frei wählbar. `employees` ist ein ENUM in
 * `membership_companies`, und {@see \App\Models\Membership\CiviCrm} baut daraus
 * den Namen des Mitgliedschaftstyps im CRM (`company.20-199.monthly`). Eine
 * vierte Klasse wäre eine Migration *und* neue Typen im CiviCRM — die
 * Beitragsordnung ändert deshalb die Beträge, nicht die Klassen.
 */
final class MembershipFee
{
    /** Der reguläre Mindestbeitrag einer natürlichen Person. */
    public const PERSON_MINIMUM = 5.0;

    /**
     * Der ermäßigte Mindestbeitrag.
     *
     * Die Validierung lässt ihn für jede Person zu und verlangt unterhalb von
     * {@see self::PERSON_MINIMUM} einen Nachweis; erst der entscheidet.
     * `CiviCrm::CREATE_MEMBERSHIP` trennt `person.reduced` von
     * `person.regular` an derselben Grenze.
     */
    public const PERSON_REDUCED_MINIMUM = 2.5;

    /** @var array<string, float> Mindestbeitrag je Größenklasse. */
    private const COMPANY_MINIMUM = [
        "1-19" => 25.0,
        "20-199" => 100.0,
        ">200" => 200.0,
    ];

    /**
     * Was vor der neuen Beitragsordnung galt.
     *
     * Für die beiden großen Klassen dasselbe — geändert hat sich nur die
     * kleine. Deren 5 € waren kein Programmierfehler: die Beitragsordnung auf
     * suma-ev.de nennt für 1-19 Mitarbeitende genau diesen Betrag, und deshalb
     * hatte die `$min_amount`-Leiter im Controller für diese Klasse auch keinen
     * eigenen Zweig — der Voreinstellungswert war schon der richtige. Was sich
     * ändert, ist also die Ordnung und nicht die Umsetzung: eine Firma mit
     * neunzehn Menschen, die alle suchen, zahlte bisher den Mindestbeitrag
     * eines einzelnen Menschen.
     *
     * Bestandsmitglieder behalten diesen Boden. Die Beitragsschritte des
     * Formulars werden aus den Erinnerungsmails heraus mit `edit=membership-fee`
     * neu durchlaufen ({@see \App\Mail\Membership\PaymentReminder}), und dabei
     * wird `amount` genullt und neu validiert — ein angehobener Mindestbeitrag
     * würde ein bestehendes Mitglied daran hindern, den Beitrag zu bestätigen,
     * den es seit Jahren zahlt. Die neue Ordnung gilt für neue Anträge.
     *
     * @var array<string, float>
     */
    private const COMPANY_MINIMUM_GRANDFATHERED = [
        "1-19" => self::PERSON_MINIMUM,
        "20-199" => 100.0,
        ">200" => 200.0,
    ];

    /** @var array<string, list<float>> Die Vorschläge je Größenklasse. */
    private const COMPANY_PRESETS = [
        "1-19" => [25.0, 50.0, 100.0],
        "20-199" => [100.0, 200.0, 300.0],
        ">200" => [200.0, 400.0, 600.0],
    ];

    /** @var list<float> Die Vorschläge für eine natürliche Person. */
    private const PERSON_PRESETS = [10.0, 15.0, 20.0];

    /**
     * @param list<float> $presets
     */
    private function __construct(
        private readonly float $minimum,
        private readonly array $presets,
    ) {
    }

    /**
     * Der Beitrag, der für diesen Antrag gilt.
     *
     * Ohne Antrag — der erste Aufruf des Formulars, in dem noch keine
     * Kontaktdaten stehen — gilt der Beitrag einer Person: der Beitragsschritt
     * ist dann ohnehin noch nicht sichtbar, und was das Formular in diesem
     * Moment zeigt, ist die Frage nach Person oder Firma.
     */
    public static function forApplication(?MembershipApplication $application): self
    {
        if ($application === null) {
            return self::forPerson();
        }

        if ($application->company !== null) {
            return self::forCompany($application->company->employees, grandfathered: (bool) $application->is_update);
        }

        return self::forPerson();
    }

    public static function forPerson(): self
    {
        return new self(self::PERSON_REDUCED_MINIMUM, self::PERSON_PRESETS);
    }

    /**
     * @param string $employees eine der drei Größenklassen des ENUMs
     * @param bool $grandfathered ob der Antrag eine bestehende Mitgliedschaft ändert
     */
    public static function forCompany(string $employees, bool $grandfathered = false): self
    {
        $minima = $grandfathered ? self::COMPANY_MINIMUM_GRANDFATHERED : self::COMPANY_MINIMUM;

        return new self(
            // Eine unbekannte Größenklasse kann nur aus einem Datensatz
            // kommen, der älter ist als das ENUM. Der Mindestbeitrag der
            // kleinsten Klasse ist dann die verträglichste Antwort — und nicht
            // der Beitrag einer Einzelperson, auf den ein `?? 5.0` hier
            // hinausliefe.
            $minima[$employees] ?? $minima["1-19"],
            self::COMPANY_PRESETS[$employees] ?? self::COMPANY_PRESETS["1-19"],
        );
    }

    /**
     * Die Größenklassen mit ihrem Mindestbeitrag, in der Reihenfolge des ENUMs.
     *
     * Für /firmen, damit die Seite dieselben Beträge nennt, die das Formular
     * anschließend annimmt. Grandfathering spielt hier keine Rolle: eine Seite,
     * die für neue Anträge wirbt, nennt die neue Beitragsordnung.
     *
     * @return array<string, float>
     */
    public static function companyBrackets(): array
    {
        return self::COMPANY_MINIMUM;
    }

    /** Der Mindestbeitrag in Euro pro Monat. */
    public function minimum(): float
    {
        return $this->minimum;
    }

    /**
     * Die Vorschläge, so wie das Formular sie sendet und die Validierung sie
     * erwartet: `"25.00"`, nicht `25.0` und nicht `"25,00"`.
     *
     * @return list<string>
     */
    public function presets(): array
    {
        return array_map(self::format(...), $this->presets);
    }

    /**
     * Die Vorschläge als Zahl, für den Vergleich mit einem gespeicherten
     * Beitrag.
     *
     * @return list<float>
     */
    public function presetValues(): array
    {
        return $this->presets;
    }

    /** Ein Betrag in der Schreibweise, die das Formular sendet. */
    public static function format(float $amount): string
    {
        return number_format($amount, 2, ".", "");
    }
}
