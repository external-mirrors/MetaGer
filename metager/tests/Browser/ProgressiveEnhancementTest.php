<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * MetaGer must work without client-side JavaScript; JS is an enhancement only.
 *
 * That requirement is not assertable from a feature test — it is a property of a
 * real rendering engine with scripting switched off. This is the reason the Dusk
 * suite still exists after the static-page tests moved to tests/Feature.
 */
class ProgressiveEnhancementTest extends DuskTestCase
{
    /**
     * Firefox with scripting off. The sidebar is a <label for>/<summary>
     * disclosure precisely so it keeps working here.
     *
     * @var array<string, bool>
     */
    protected array $driverPreferences = [
        "javascript.enabled" => false,
    ];

    public function testStartpageRendersWithoutJavascript(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/de-DE")
                ->assertTitle(trans("titles.index", [], "de"))
                ->assertSee(trans("index.landing.title", [], "de"));
        });
    }

    /**
     * An anonymous visitor does not get a search box at all — the startpage
     * offers the key flow instead (see index.blade.php: the searchbar is only
     * included once a key is present). So the entry path that has to survive
     * without JavaScript is that CTA, and it has to be real links.
     *
     * Both paths, and the asymmetry between them is deliberate: logging in is a
     * button because it is what most people on this page need, and creating a
     * key is a link in the "first time here?" row because a second key splits
     * their balance. Both still have to work with scripting off — that is what
     * this pins. The returning-user copy swap is enhancement on top and is
     * covered in resources/js/accountBreadcrumb.test.js.
     *
     * The authorized search form and the result page itself need a key to reach,
     * so their no-JS coverage belongs with D0, where the search fixtures live.
     */
    public function testAnonymousStartpageOffersTheKeyFlowWithoutJavascript(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/de-DE")
                ->assertPresent("#searchbar-replacement")
                ->assertPresent("#searchbar-replacement a.startpage-login-btn")
                ->assertPresent("#searchbar-replacement .first-time-line a.startpage-create-link");

            // Real navigable links, not script hooks: an href a browser with no
            // JavaScript can follow on its own, carrying the locale prefix that
            // LaravelLocalization put on the current request.
            //
            // Asserted rather than clicked. /keys is not a MetaGer route — nginx
            // proxies "^(/.*)?/keys" to the keymanager service — so following the
            // link leaves the application under test, and its answer is that
            // service's business, not this suite's. Clicking through without
            // JavaScript is covered below, on a page MetaGer actually serves.
            //
            // Es zeigt seit dem vierten Umzugsschritt auf MetaGers eigene
            // Seite. /keys/key/create antwortet weiterhin, aber nur noch mit
            // einer Weiterleitung hierher — und /keys, worauf es davor zeigte,
            // ist inzwischen genau diese Seite.
            $this->assertStringContainsString(
                "/de-DE/schluessel-erstellen",
                $browser->attribute("#searchbar-replacement a.startpage-create-link", "href")
            );

            // Anmelden zeigt seit dem dritten Umzugsschritt auf MetaGers eigene
            // /anmelden. /keys/key/enter antwortet weiterhin — mit Schlüssel ist
            // es der Weg zum Konto —, aber für einen abgemeldeten Besucher wäre
            // der alte Pfad nur eine Weiterleitung hierher.
            $this->assertStringContainsString(
                "/de-DE/anmelden",
                $browser->attribute("#searchbar-replacement a.startpage-login-btn", "href")
            );
        });
    }

    /**
     * Die Anmeldeseite ohne Javascript.
     *
     * Sie ist der einzige Ort auf der Seite, an dem ein Besucher etwas eingibt,
     * das über einen Klick hinausgeht, und sie hat drei Wege hinein — getippter
     * Schlüssel, Sicherungsdatei, Kamera. Zwei davon müssen ohne Javascript
     * funktionieren, und der dritte darf nicht angeboten werden.
     *
     * Ein Feature-Test sieht das `hidden`-Attribut im Quelltext; was er nicht
     * sieht, ist, ob resources/js/login.js es entfernt hat — und genau das ist
     * hier die Aussage. Ohne Javascript bleibt es stehen.
     */
    public function testTheLoginPageWorksWithoutJavascript(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/de-DE/anmelden")
                ->assertTitle(trans("titles.login", [], "de"))
                // Das Feld ist type="text" und nicht type="password": die alte
                // Seite deckte es per Javascript beim Fokus auf, hier tippte
                // man seinen Schlüssel also blind.
                ->assertAttribute("#login-key", "type", "text")
                ->assertVisible(".login-submit")
                // Das Dateifeld bleibt sichtbar, statt hinter einem Label zu
                // stecken: nur so nennt der Browser die gewählte Datei von
                // selbst, und das ist genau das, was hier kein Skript tun kann.
                ->assertVisible("#login-file")
                // Und der Kamera-Scanner wird nicht angeboten. Ein Knopf, der
                // ohne Javascript nichts tut, ist schlechter als keiner.
                ->assertMissing("#login-qr");

            // Ein echtes Formular an eine echte Adresse, nicht ein Skripthaken:
            // ohne beides käme die Eingabe nirgendwo an. Die Adresse ist die
            // Seite selbst, seit auch der Vorgang hier liegt.
            $this->assertStringContainsString(
                "/de-DE/anmelden",
                $browser->attribute("#login-form", "action")
            );
            $this->assertSame("post", strtolower($browser->attribute("#login-form", "method")));
        });
    }

    /**
     * Die Seite zum Erstellen ohne Javascript.
     *
     * Sie ist die einzige Seite, deren Markup *nicht* den Zustand zeigt, den
     * ein Besucher mit Javascript zuerst sieht: der Schlüssel steht schon da,
     * und resources/js/key-create.js blendet ihn wieder weg und stellt den
     * Knopf davor. Das ist die richtige Richtung — ohne Skript fehlt die
     * Nachfrage, nicht der Schlüssel —, aber sie ist auch die, die man beim
     * Umbauen versehentlich umdreht, und dann liefert die Seite ohne Javascript
     * nichts als einen Knopf, der nichts tut.
     *
     * Ein Feature-Test sieht `data-state="ready"` im Quelltext. Was er nicht
     * sieht, ist, ob das Feld darunter tatsächlich sichtbar ist: die Zustände
     * hängen an CSS-Regeln zu diesem Attribut, und eine falsche Regel versteckt
     * genau das, was hier stehen bleiben muss.
     */
    public function testTheKeyCreationPageWorksWithoutJavascript(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/de-DE/schluessel-erstellen")
                ->assertTitle(trans("titles.key-create", [], "de"))
                // Der Schlüssel ist da und lesbar.
                ->assertVisible("#new-key")
                // Und der Weg weiter ist da. Ohne ihn wäre der Schlüssel
                // sichtbar und trotzdem unbenutzbar.
                ->assertVisible(".create-continue__button")
                // Der Knopf, der ohne Javascript nur zeigen würde, was schon
                // dasteht, wird nicht angeboten.
                ->assertMissing("#key-create-start")
                // Ebenso wenig die Kopierknöpfe: ohne Zwischenablage täten sie
                // nichts, und die Felder daneben lassen sich von Hand
                // markieren.
                ->assertMissing(".create-key__copy");

            $key = $browser->value("#new-key");
            $this->assertMatchesRegularExpression(
                "/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/",
                $key,
                "Ohne Javascript steht im Feld kein Schlüssel — dann hat die Seite nichts hergegeben."
            );

            // Was im Feld steht, ist auch das, was abgeschickt wird. Zwei
            // verschiedene Werte hier wären ein Besucher mit einem Schlüssel
            // auf dem Zettel und einem anderen im Konto.
            $this->assertSame(
                $key,
                $browser->attribute(".create-continue input[name=key]", "value")
            );

            $this->assertStringContainsString(
                "/de-DE/schluessel-erstellen",
                $browser->attribute(".create-continue", "action")
            );
            $this->assertSame("post", strtolower($browser->attribute(".create-continue", "method")));
        });
    }

    public function testSidebarNavigationWorksWithoutJavascript(): void
    {
        $this->browse(function (Browser $browser) {
            // `:not(.close)` and not just `[for=sidebarToggle]`: both labels
            // point at the same checkbox, and the hidden ✕ comes first in the
            // document, so the bare selector resolves to the one that cannot be
            // clicked. See testTheOpenSidebarCanBeClosedAgain… below.
            $browser->visit("/de-DE")
                ->click("label.sidebar-opener:not(.close)")
                ->clickLink("Datenschutz")
                ->waitForLocation("/de-DE/datenschutz")
                ->assertTitle(trans("titles.datenschutz", [], "de"));
        });
    }

    /**
     * Opening the sidebar is half a disclosure; closing it again is the other
     * half, and it is the half that broke.
     *
     * The ≡ and the ✕ are two labels for one checkbox, swapped by
     * `#sidebarToggle:checked ~ .sidebar-opener`. When the ≡ moved into
     * .navigation-cluster the ✕ went with it, one level below where a sibling
     * combinator can reach — so it was never revealed, while the cluster around
     * it hid itself because the open sidebar covers that corner. The menu opened
     * and nothing on the page could close it again. On the startpage there was
     * no second label to fall back on.
     *
     * A rendering engine is what proves this: the whole mechanism is one
     * selector against a checkbox's state, and nothing about it is visible in
     * the rendered HTML, which contains both labels either way.
     */
    public function testTheOpenSidebarCanBeClosedAgainWithoutJavascript(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/de-DE")
                // In the DOM from the start, and hidden until it is needed —
                // this is a CSS state swap, not markup that appears.
                ->assertPresent("label.sidebar-opener.close")
                ->assertMissing("label.sidebar-opener.close");

            // Named before it is used: the ✕ has to be a sibling of the
            // checkbox, not a descendant of the cluster, or the rule below
            // cannot reach it. Asserted on the DOM rather than on visibility,
            // because a ✕ in the wrong place is invisible for the wrong reason
            // and would pass the assertion above.
            $this->assertCount(
                0,
                $browser->elements(".navigation-cluster label.sidebar-opener.close"),
                "The close button is inside .navigation-cluster. `#sidebarToggle:checked ~ .sidebar-opener.close` "
                . "cannot reach it there, and the cluster hides itself while the sidebar is open — so the sidebar "
                . "opens with no way to close it. It belongs beside the checkbox in parts/sidebar.blade.php."
            );

            $browser->click("label.sidebar-opener:not(.close)")
                ->waitFor(".sidebar")
                ->assertVisible("label.sidebar-opener.close")
                ->click("label.sidebar-opener.close")
                ->waitUntilMissing(".sidebar");
        });
    }

    public function testNestedSidebarSectionOpensWithoutJavascript(): void
    {
        // The services group is a <summary>/<details> disclosure nested inside
        // the sidebar — two layers of CSS-only interaction, no script involved.
        $this->browse(function (Browser $browser) {
            $browser->visit("/de-DE")
                ->click("label.sidebar-opener:not(.close)")
                ->click("summary#navigationServices")
                ->clickLink("Widget")
                ->waitForLocation("/de-DE/widget")
                ->assertTitle(trans("titles.widget", [], "de"));
        });
    }
}
