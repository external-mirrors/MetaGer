<?php

namespace Tests\Feature;

use App\Support\MembershipFee;
use Tests\TestCase;

/**
 * /firmen — die Seite für Firmen und Organisationen.
 *
 * Sie ist das Ziel der vier B2B-Hinweise ({@see MembershipAdvertisingTest}) und
 * der einzige Ort, an dem steht, was eine Firmenmitgliedschaft ist. Zwei Dinge
 * daran können still kaputtgehen, und beide stehen deshalb hier:
 *
 * Die Beträge. Sie kommen aus {@see MembershipFee}, aus derselben Quelle wie
 * die Validierung des Beitrittsformulars. Eine Seite, die 25 € verspricht, und
 * ein Formular, das 25 € zurückweist, wäre genau der Fehler, den es hier
 * jahrelang gab — nur andersherum, siehe {@see \Tests\Unit\MembershipFeeTest}.
 *
 * Der Schlüssel. Die Zusage der Seite ist, dass die Mitarbeitenden einer Firma
 * keinen Schlüssel eingeben und keinen zu sehen bekommen. Solange das nicht
 * technisch gelöst ist, heißt das: hier steht keine fertige Konfiguration zum
 * Herunterladen, weil in einer solchen Datei — OpenSearch-Deskriptor,
 * policies.json, Chrome-Policy — der Schlüssel im Klartext stünde. Wer diese
 * Seite später um einen bequemen Download erweitert, soll hier anhalten.
 */
class BusinessPageTest extends TestCase
{
    /** @return array<string, string> */
    private static function speaking(string $language): array
    {
        return ["Accept-Language" => "$language;q=0.9"];
    }

    // ── Die Seite ────────────────────────────────────────────────────────────

    public function testThePageRespondsInGermanWithItsTitleAndSections(): void
    {
        $response = $this->get("/firmen", self::speaking("de-DE,de"))->assertOk();

        $response->assertSee("<title>" . e(__("business.title", locale: "de")) . "</title>", false);
        $response->assertSeeText(__("business.benefits.heading", locale: "de"));
        $response->assertSeeText(__("business.setup.heading", locale: "de"));
        $response->assertSeeText(__("business.fee.heading", locale: "de"));
        $response->assertSeeText(__("business.education.heading", locale: "de"));
    }

    /**
     * Der Weg von hier führt in den Firmenzweig des Beitrittsformulars, nicht
     * in den allgemeinen: `?type=company` ist das, was `form.blade.php` liest,
     * und ohne den Parameter landet eine Firma im Formular für Personen.
     */
    public function testTheCallToActionLeadsIntoTheCompanyBranchOfTheForm(): void
    {
        $this->get("/firmen", self::speaking("de-DE,de"))
            ->assertOk()
            ->assertSee(route("membership_form", ["type" => "company"]), false)
            ->assertSeeText(__("business.actions.join", locale: "de"));
    }

    // ── Die Beträge ──────────────────────────────────────────────────────────

    /**
     * Jeder Mindestbeitrag steht auf der Seite, und zwar der, den das Formular
     * anschließend durchlässt.
     */
    public function testEveryBracketIsShownWithTheMinimumTheFormEnforces(): void
    {
        $response = $this->get("/firmen", self::speaking("de-DE,de"))->assertOk();

        foreach (MembershipFee::companyBrackets() as $employees => $minimum) {
            $response->assertSeeText(__("business.fee.brackets.$employees", locale: "de"));
            $response->assertSeeText(number_format($minimum, 0, ",", "."));

            $this->assertSame(
                $minimum,
                MembershipFee::forCompany($employees)->minimum(),
                "the page and the form disagree about $employees"
            );
        }
    }

    /** Die kleinste Klasse ist die, die sich geändert hat — 5 € wären der alte Fehler. */
    public function testTheSmallestBracketDoesNotShowThePersonsFee(): void
    {
        $this->assertSame(25.0, MembershipFee::companyBrackets()["1-19"]);

        $this->get("/firmen", self::speaking("de-DE,de"))
            ->assertOk()
            // Roh und nicht als Text: die Blade setzt ein geschütztes
            // Leerzeichen zwischen Zahl und Einheit, und strip_tags() löst
            // Entitäten nicht auf.
            ->assertSee("25&nbsp;" . __("business.fee.unit", locale: "de"), false);
    }

    // ── Der Schlüssel ────────────────────────────────────────────────────────

    /**
     * Die Einschränkung steht auf der Seite und nicht im Kleingedruckten: der
     * Schlüssel bleibt bei der Organisation, die Einrichtung läuft über die IT.
     */
    public function testThePageSaysWhoHoldsTheKey(): void
    {
        $this->get("/firmen", self::speaking("de-DE,de"))
            ->assertOk()
            ->assertSee('id="business-key"', false)
            ->assertSeeText(__("business.setup.hint.heading", locale: "de"));
    }

    /**
     * Und sie bietet keine fertige Konfiguration an.
     *
     * Ein OpenSearch-Deskriptor oder eine `policies.json` für den Rollout wäre
     * das Naheliegende — und trüge den Schlüssel als Klartext in eine Datei,
     * die auf jedem Arbeitsplatz lesbar ist. Das ist der ungelöste Teil; bis er
     * gelöst ist, darf die Seite ihn nicht versprechen.
     */
    public function testThePageOffersNoConfigurationFileCarryingTheKey(): void
    {
        $response = $this->get("/firmen", self::speaking("de-DE,de"))->assertOk();

        $response->assertDontSee("policies.json", false);
        $response->assertDontSee("opensearch.xml", false);
    }

    // ── Die Sprache ──────────────────────────────────────────────────────────

    /**
     * Nicht auf Deutsch heißt: derselbe Hinweis, den das Beitrittsformular
     * gibt, und kein Weg in ein Formular, das der Besucher nicht lesen kann.
     * Eine 404 wäre die schlechtere Antwort — den Link gibt jemand weiter.
     */
    public function testANonGermanVisitorGetsTheSameNoticeAsOnTheMembershipForm(): void
    {
        $response = $this->get("/firmen", self::speaking("en-US,en"))->assertOk();

        $response->assertSee("non-german", false);
        $response->assertDontSeeText(__("business.benefits.heading", locale: "de"));
        $response->assertDontSee(route("membership_form", ["type" => "company"]), false);
    }
}
