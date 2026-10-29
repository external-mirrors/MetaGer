<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * The web extension's block on /tor.
 *
 * The page links to the hidden service. Tor Browser users with the MetaGer web
 * extension also need the extension to be allowed on the onion address, which
 * is an optional permission it only asks for when told to — so this page
 * carries a block the extension fills in.
 *
 * The element ids and the `data-extension-onion-setup` attribute are a contract
 * with the extension's content script (`contentScripts/metagerPage.js` in the
 * metager-webextension repository). Nothing on this side reads them, so
 * renaming one breaks nothing here and silently removes the feature there;
 * these tests are what notice.
 */
class TorPageTest extends TestCase
{
    public function testTheExtensionBlockIsRenderedHidden(): void
    {
        $response = $this->get('/tor');

        $response->assertOk();
        // Hidden, all three: without the extension there is nothing to offer,
        // and the extension decides which of the two states applies.
        $response->assertSee('<div id="tor-extension" hidden>', false);
        $response->assertSee('<div id="tor-extension-disabled" hidden>', false);
        $response->assertSee('<div id="tor-extension-enabled" hidden>', false);
    }

    public function testBothStatesCarryAControlTheExtensionBinds(): void
    {
        $html = $this->get('/tor')->getContent();

        $this->assertSame(2, substr_count($html, 'data-extension-onion-setup'));
    }

    public function testTheBlockIsTranslated(): void
    {
        $this->get('/tor')->assertSee(trans('tor.extension.enable', [], 'en'));
        $this->get('/de-DE/tor')->assertSee(trans('tor.extension.enable', [], 'de'), false);
    }

    /**
     * Every locale with a tor.php carries the block's strings. A missing one
     * does not fail anywhere — it falls through to the fallback locale and a
     * Swedish visitor reads German.
     */
    public function testEveryLocaleTranslatesTheBlock(): void
    {
        $expected = array_keys((require lang_path('en/tor.php'))['extension']);

        $problems = [];
        foreach (glob(lang_path('*/tor.php')) as $path) {
            if (is_link(dirname($path))) {
                continue;
            }
            $strings = (require $path)['extension'] ?? [];
            foreach (array_diff($expected, array_keys($strings)) as $key) {
                $problems[] = basename(dirname($path)) . " is missing tor.extension.$key";
            }
        }

        $this->assertSame([], $problems);
    }
}
