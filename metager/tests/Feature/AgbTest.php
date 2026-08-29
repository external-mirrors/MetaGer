<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Die AGB sind ein Vertragstext, und beim Umzug hat sich daran nichts geändert.
 *
 * Der Abzug in fixtures/agb-de.txt wurde von /keys/agb genommen, bevor der
 * Keymanager die Seite abgegeben hat. Das ist der einzige Beleg dafür, dass der
 * Umzug wirklich nur ein Umzug war — bei jedem anderen Text wäre das eine
 * Fleißaufgabe, hier ist es das, was jemand mit rechtlichem Blick sehen will.
 *
 * Genau eine Abweichung ist beabsichtigt und steht unten ausgeschrieben: der
 * Text nennt seine eigene Fundstelle, und die stand wörtlich als
 * "metager.de/keys/agb" im Vertrag. Ein Vertrag, der auf eine Weiterleitung
 * zeigt, wäre die schlechtere Wahl gewesen.
 */
class AgbTest extends TestCase
{
    /**
     * Die Anker, an denen der Bezahlvorgang im Keymanager hängt.
     *
     * Auf der alten Seite gab es sie nicht: templates/revocation.ejs verlinkt
     * seit jeher /keys/agb#refund, und views/agb.ejs hat nie eine passende id
     * ausgegeben — der Klick landete am Seitenanfang. Wer hier eine id
     * umbenennt, macht das im anderen Repository wieder kaputt.
     */
    public function testTheAnchorsTheCheckoutLinksToExist(): void
    {
        $response = $this->get("/de-DE/agb")->assertOk();

        $response->assertSee('id="rueckerstattung"', false);
        $response->assertSee('id="anbieter"', false);
        $response->assertSee('id="vertragsschluss"', false);
        $response->assertSee('id="haftung"', false);
    }

    /**
     * Die id-Liste steht im Blade und wird über die Position zugeordnet.
     * Kommt ein Abschnitt dazu, verschieben sich die Anker stillschweigend —
     * außer hier fällt es auf.
     */
    public function testTheSectionIdsStillCoverEverySection(): void
    {
        $sections = trans("agb.paragraphs");

        $this->assertCount(
            7,
            $sections,
            "Der Vertragstext hat nicht mehr sieben Abschnitte. Die id-Liste in "
            . "resources/views/agb.blade.php ordnet über die Position zu und muss "
            . "mitgezogen werden — insbesondere #rueckerstattung."
        );

        $response = $this->get("/de-DE/agb")->assertOk();
        foreach (["anbieter", "vertragsschluss", "gewaehrleistung", "schluessel", "token", "haftung", "schlussbestimmungen"] as $id) {
            $response->assertSee('id="' . $id . '"', false);
        }
    }

    /** #rueckerstattung muss auf die freiwillige 30-Tage-Rückgabe zeigen, nicht irgendwohin. */
    public function testTheRefundAnchorSitsOnTheRefundClause(): void
    {
        $clause = trans("agb.paragraphs.5.paragraphs.2");

        $this->assertStringContainsString("zurückzuerstatten", $clause);
        $this->assertStringContainsString("30 Tagen", $clause);
    }

    public function testTheGermanTextIsUnchangedSinceTheMove(): void
    {
        $expected = file(
            __DIR__ . "/fixtures/agb-de.txt",
            FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES
        );

        // Die eine beabsichtigte Änderung, hier statt im Abzug: so bleibt die
        // Datei ein unangetasteter Abzug der alten Seite.
        $expected = array_map(
            static fn(string $line): string => str_replace("metager.de/keys/agb", "metager.de/agb", $line),
            $expected
        );

        $actual = $this->renderedLines("/de-DE/agb");

        $this->assertSame($expected, $actual);
    }

    /**
     * Die Übersetzungen tragen den Hinweis, dass nur das deutsche Original
     * verbindlich ist — und der Verweis darauf muss auf die deutsche Seite
     * zeigen und nicht mehr auf /keys.
     */
    public function testATranslationSaysItIsNotTheBindingVersion(): void
    {
        $response = $this->get("/en-GB/agb")->assertOk();

        $response->assertSeeText("legally binding version");
        // Nicht url("/de-DE/agb"): unter einem en-GB-Request setzt
        // URL::formatPathUsing dessen Präfix noch davor. Der Pfad reicht.
        $response->assertSee('/de-DE/agb"', false);
        $response->assertDontSee("/keys/agb", false);
    }

    /** Die deutsche Fassung ist das Original und braucht den Hinweis nicht. */
    public function testTheGermanVersionCarriesNoTranslationNotice(): void
    {
        $response = $this->get("/de-DE/agb")->assertOk();

        $response->assertDontSee("alert-warning", false);
    }

    /**
     * Der Inhalt der Seite als Textzeilen, unabhängig davon, in welche Tags
     * die Vorlage ihn packt — genau die Normalisierung, mit der auch der Abzug
     * genommen wurde.
     *
     * @return list<string>
     */
    private function renderedLines(string $path): array
    {
        $html = $this->get($path)->assertOk()->getContent();

        $start = strpos($html, '<div id="agb">');
        $this->assertNotFalse($start, "Der AGB-Container fehlt in der Ausgabe");
        $end = strpos($html, "</main>", $start);

        $body = substr($html, $start, $end - $start);
        $body = preg_replace('#<(script|style).*?</\1>#s', " ", $body);
        $body = preg_replace("#<[^>]+>#", "\n", $body);
        $body = html_entity_decode($body, ENT_QUOTES | ENT_HTML5, "UTF-8");

        $lines = [];
        foreach (explode("\n", $body) as $line) {
            $line = trim(preg_replace("/\s+/u", " ", $line));
            if ($line !== "") {
                $lines[] = $line;
            }
        }

        return $lines;
    }
}
