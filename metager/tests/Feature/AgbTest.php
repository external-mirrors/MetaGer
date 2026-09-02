<?php

namespace Tests\Feature;

use App\Landing\KeyPrice;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Die AGB sind ein Vertragstext, und was sich daran ändert, steht hier.
 *
 * Der Abzug in fixtures/agb-de.txt wurde von /keys/agb genommen, bevor der
 * Keymanager die Seite abgegeben hat. Das ist der einzige Beleg dafür, was der
 * Umzug am Vertrag getan hat — bei jedem anderen Text wäre das eine
 * Fleißaufgabe, hier ist es das, was jemand mit rechtlichem Blick sehen will.
 *
 * Die Datei bleibt deshalb ein unangetasteter Abzug der alten Seite. Jede
 * beabsichtigte Abweichung wird in testTheGermanTextIsUnchangedSinceTheMove
 * als benannte Ersetzung darauf angewendet, statt in den Abzug
 * hineinzuwandern: so ist die Liste der Änderungen am Vertrag genau so lang
 * wie die Liste der Ersetzungen und kann nicht stillschweigend wachsen.
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
        Http::fake();

        $expected = file(
            __DIR__ . "/fixtures/agb-de.txt",
            FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES
        );

        // (1) Der Vertrag nennt seine eigene Fundstelle, und die stand wörtlich
        //     als "metager.de/keys/agb" darin. Ein Vertrag, der auf eine
        //     Weiterleitung zeigt, wäre die schlechtere Wahl gewesen.
        $expected = array_map(
            static fn(string $line): string => str_replace("metager.de/keys/agb", "metager.de/agb", $line),
            $expected
        );

        // (2) Die Paketliste in §4 stimmte nicht mit dem überein, was der
        //     Checkout verkauft: 12000 Token standen drin und waren nie
        //     kaufbar, 500 fehlten und waren es immer. Jetzt kommt sie aus
        //     derselben Quelle wie /preise.
        $expected = $this->replaceBlock(
            $expected,
            [
                "1000 Token : 10 Euro",
                "2000 Token : 20 Euro",
                "3000 Token : 30 Euro",
                "4000 Token : 40 Euro",
                "6000 Token : 60 Euro",
                "12000 Token: 120 Euro",
            ],
            array_map(
                static fn(int $tokens, int $euro): string => "$tokens Token: $euro Euro",
                array_keys(KeyPrice::tiers()),
                array_values(KeyPrice::tiers())
            )
        );

        // (3) Ein geänderter Vertragstext ist eine neue Fassung, also rückt das
        //     Datum mit. Es steht als letzte Zeile unter dem Text.
        $expected = $this->replaceBlock(
            $expected,
            ["Stand: November 2025"],
            [trans("agb.date")]
        );

        $actual = $this->renderedLines("/de-DE/agb");

        $this->assertSame($expected, $actual);
    }

    /**
     * Die Paketliste ist der eine Teil des Vertrags, der eine Tatsache über den
     * Shop behauptet — und der deshalb still falsch werden kann, wenn jemand
     * dort ein Paket hinzunimmt. In allen Sprachen, weil ein Verwender die
     * Übersetzung liest, die ihm angezeigt wird.
     */
    public function testTheTokenPackagesAreTheOnesThatCanBeBought(): void
    {
        // Ohne Antwort vom Keymanager fällt KeyPrice auf config/metager
        // zurück, und genau das soll hier verglichen werden: der Test gehört
        // in dieses Repository und kann nur prüfen, was dieses Repository
        // über den Preis weiß.
        Http::fake();

        $expected = KeyPrice::tiers();

        foreach (glob(dirname(__DIR__, 2) . "/lang/*/agb.php") as $file) {
            $locale = basename(dirname($file));
            $packages = (require $file)["paragraphs"][3]["paragraphs"][3];

            $this->assertIsArray(
                $packages,
                "$locale: die Paketliste in §4 ist keine Liste mehr — steht sie noch an "
                . "derselben Stelle? resources/views/agb.blade.php rendert sie über die Position."
            );

            $actual = [];
            foreach ($packages as $package) {
                preg_match_all("/\\d+/", $package, $numbers);
                $this->assertCount(
                    2,
                    $numbers[0],
                    "$locale: \"$package\" nennt nicht genau zwei Zahlen (Token und Euro)."
                );
                $actual[(int) $numbers[0][0]] = (int) $numbers[0][1];
            }

            $this->assertSame(
                $expected,
                $actual,
                "$locale: die AGB nennen andere Tokenpakete als der Checkout verkauft. "
                . "Kaufbar ist, was in der config des Keymanagers unter price.purchasable "
                . "steht; lang/*/agb.php muss das aufzählen und nichts sonst."
            );
        }
    }

    /**
     * Ein Ersatz für genau einen zusammenhängenden Block von Zeilen, der da
     * sein muss — eine Ersetzung, die ins Leere läuft, wäre eine Abweichung,
     * die niemand mehr sieht.
     *
     * @param list<string> $lines
     * @param list<string> $from
     * @param list<string> $to
     * @return list<string>
     */
    private function replaceBlock(array $lines, array $from, array $to): array
    {
        $at = null;
        foreach (array_keys($lines) as $index) {
            if (array_slice($lines, $index, count($from)) === $from) {
                $this->assertNull($at, "Der Block kommt im Abzug mehr als einmal vor: " . $from[0]);
                $at = $index;
            }
        }

        $this->assertNotNull(
            $at,
            "Der Block steht nicht mehr im Abzug: " . $from[0] . " — die Ersetzung ist "
            . "damit gegenstandslos und gehört gelöscht."
        );

        return array_merge(
            array_slice($lines, 0, $at),
            $to,
            array_slice($lines, $at + count($from))
        );
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
