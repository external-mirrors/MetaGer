<?php

namespace Tests\Feature;

use Tests\Concerns\ReadsBuiltCss;
use Tests\TestCase;

/**
 * Was auf /konto/gutscheinaktionen in einer Reihe steht, steht auf einer Höhe.
 *
 * Zwei Reihen auf dieser Seite, und beide standen schief. Die eine sind die
 * zwei Zahlenfelder des Anlegeformulars, die andere die Knopfzeile einer
 * Kampagne.
 *
 * ── Die Zahlenfelder ────────────────────────────────────────────────────────
 *
 * „Token pro verschenktem Schlüssel“ und „Maximale Token insgesamt“ teilen sich
 * eine Reihe, und ihre Eingabefelder saßen sichtbar versetzt zueinander. Zwei
 * Ursachen, die im Quelltext beide wie Nichts aussehen:
 *
 *   * Die Beschriftungen sind unterschiedlich lang und brechen deshalb bei
 *     unterschiedlichen Spaltenbreiten um. Was darunter steht, rutscht mit.
 *   * `align-content` ist im Raster von Haus aus `stretch`. Nur das rechte Feld
 *     trägt einen Hinweis, wurde damit höher, und das linke wurde von seiner
 *     Reihe auf dieselbe Höhe gedehnt — indem seine zwei Zeilen den Überschuss
 *     unter sich aufteilten. Beschriftung tiefer, Eingabefeld höher als sein
 *     Gegenüber, ohne dass eine einzige Angabe im Feld selbst das gesagt hätte.
 *
 * Beides hängt an zwei Angaben in pages/checkout.less, die für sich genommen
 * nach Kosmetik aussehen und beim nächsten Aufräumen genau deshalb wegfallen.
 * Also stehen sie hier.
 *
 * Auf dem *gebauten* Stylesheet, nicht auf dem LESS — dasselbe Argument wie in
 * {@see NavigationBandAlignmentTest}: der Quelltext kann richtig aussehen,
 * während die kompilierte Datei etwas anderes sagt, und der Browser liest nur
 * die kompilierte. Und nicht in Dusk, obwohl erst eine Rendering-Engine ein
 * Raster wirklich ausrechnet: diese Seite gibt es nur mit angemeldetem
 * Schlüssel und antwortendem Keyserver, und ein Browser lässt sich nicht wie
 * ein `Http::fake()` verkabeln.
 */
class CampaignsLayoutTest extends TestCase
{
    use ReadsBuiltCss;

    private const ENTRY = "resources/less/metager/pages/checkout.less";

    /**
     * Die Ursache, die ohne `subgrid` bleibt — und die einzige, die jeder
     * Browser versteht.
     */
    public function testAFieldDoesNotStretchItsOwnRows(): void
    {
        $this->assertStringContainsString(
            "align-content:start",
            $this->rule(".campaigns-create .campaigns-create__field"),
            "Ohne align-content:start dehnt die Reihe das hinweislose Feld auf die Höhe seines " .
                "Nachbarn, und beide Eingabefelder sitzen versetzt."
        );
    }

    /**
     * Die Ursache, die nur `subgrid` löst: die Felder rechnen ihre Zeilen nicht
     * mehr selbst, sondern übernehmen die drei der Reihe — dieselben für beide
     * Spalten, egal wie lang die Beschriftung darin umbricht.
     */
    public function testTheFieldsShareTheRowsOfTheirRow(): void
    {
        $css = $this->builtCss(self::ENTRY);

        $this->assertStringContainsString(
            "@supports (grid-template-rows:subgrid)",
            $css,
            "Der subgrid-Block ist weg. Er muss in @supports stehen: ohne Unterstützung wäre " .
                "`grid-row: span 3` eine Zeilenangabe ohne Zeilen."
        );
        $this->assertStringContainsString(
            "grid-template-rows:subgrid",
            $this->rule(".campaigns-create__row>.campaigns-create__field"),
        );
    }

    /**
     * Die Reihe muss die drei Zeilen auch anbieten — Beschriftung, Eingabefeld,
     * Hinweis. `subgrid` erbt nur, was da ist.
     */
    public function testTheRowDeclaresTheThreeRowsToShare(): void
    {
        $this->assertStringContainsString(
            "grid-template-rows:auto auto auto",
            $this->rule(".campaigns-create .campaigns-create__row"),
        );
    }

    /**
     * Die Knopfzeile einer Kampagne: „Karten drucken (PDF)" ist ein <a>,
     * „Deaktivieren" und „Jetzt löschen" sind <button> in einem <form> — POST,
     * damit sie ohne Skript funktionieren. Alle drei tragen .account-btn, und
     * sie standen 44px neben 39px hoch, weil der UA-Stylesheet einem <button>
     * `line-height: normal` gibt, wo ein <a> die 1.428 des Bodys erbt.
     *
     * Die Klasse ist global (parts/account.less) und die Angabe hilft überall,
     * wo beide Formen nebeneinander vorkommen — die Gutscheinaktionen sind die
     * Stelle, an der es aufgefallen ist, und die einzige, an der drei davon in
     * einer Reihe stehen.
     */
    public function testAnAnchorAndAButtonOfTheSameClassAreTheSameHeight(): void
    {
        $this->assertStringContainsString(
            "line-height:1.4",
            $this->rule(".account-btn", "resources/less/metager/metager.less"),
            "Ohne ausgeschriebene line-height erbt ein <a class=account-btn> die des Bodys und " .
                "ein <button class=account-btn> die des UA-Stylesheets — dieselbe Klasse, zwei Höhen."
        );
    }

    /**
     * Wie in {@see NavigationBandAlignmentTest}: erst die Regel eingrenzen,
     * dann darin suchen — sonst druckt ein Fehlschlag das ganze minifizierte
     * Stylesheet als Diff.
     */
    private function rule(string $selector, ?string $entry = null): string
    {
        $css = $this->builtCss($entry ?? self::ENTRY);

        $this->assertSame(
            1,
            preg_match("/" . preg_quote($selector, "/") . "\{([^}]*)\}/", $css, $match),
            "Keine Regel für [$selector] im gebauten Stylesheet. Wenn sie umbenannt wurde, " .
                "muss dieser Test mitziehen."
        );

        return $match[1];
    }
}
