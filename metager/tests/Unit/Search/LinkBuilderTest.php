<?php

namespace Tests\Unit\Search;

use App\Search\LinkBuilder;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * LinkBuilder in isolation.
 *
 * tests/Feature/Search/SearchLinksTest covers these links as a whole search
 * produces them, which is what proves they are still wired up. This file is for
 * the parameter combinations that would need a search each: a request that is
 * already a load-more, a query that is empty, a fokus link built from a request
 * carrying several filters at once.
 *
 * Extends Tests\TestCase because action() needs the routes and the fokus link
 * reads the filter list off the engine registry.
 */
class LinkBuilderTest extends TestCase
{
    /**
     * @param array<string, string> $parameters
     */
    private function builderFor(array $parameters): LinkBuilder
    {
        return new LinkBuilder(Request::create("/meta/meta.ger3", "GET", $parameters));
    }

    /**
     * The set that never travels, whichever link is built.
     */
    public function testPagingAndPlumbingNeverTravel(): void
    {
        $link = $this->builderFor([
            "eingabe" => "kaffee",
            "page" => "3",
            "next" => "abc123",
            "out" => "json",
            "submit-query" => "Suche",
            "mgv" => "deadbeef",
            "ua" => "something",
        ])->forQuery("tee");

        foreach (["page=", "next=", "out=", "submit-query=", "mgv=", "ua="] as $parameter) {
            $this->assertStringNotContainsString($parameter, $link, "$parameter was inherited by a link that must not carry it.");
        }
    }

    /**
     * Settings the user chose are inherited, so a link is the same search with
     * one thing changed rather than a fresh one.
     */
    public function testTheUsersSettingsAreInherited(): void
    {
        $link = $this->builderFor(["eingabe" => "kaffee", "lang" => "de", "s" => "s"])->forQuery("tee");

        $this->assertStringContainsString("lang=de", $link);
        $this->assertStringContainsString("s=s", $link);
    }

    /**
     * Changing fokus drops every parameter filter, not only the one the test
     * happens to name — they are read from the engine registry rather than
     * listed by hand.
     */
    public function testEveryParameterFilterIsDroppedWhenTheFokusChanges(): void
    {
        $link = $this->builderFor([
            "eingabe" => "kaffee",
            "f" => "d",
            "s" => "s",
            "fc" => "on",
            "lang" => "de",
        ])->forFokus("bilder");

        $this->assertStringContainsString("focus=bilder", $link);
        $this->assertStringNotContainsString("f=d", $link);
        $this->assertStringNotContainsString("s=s", $link);
        $this->assertStringNotContainsString("fc=on", $link);
        $this->assertStringContainsString(
            "lang=de",
            $link,
            "The language is not a parameter filter and should have survived the change of fokus."
        );
    }

    /**
     * A fokus link replaces the fokus rather than adding a second one.
     */
    public function testTheFokusIsReplacedRatherThanAdded(): void
    {
        $link = $this->builderFor(["eingabe" => "kaffee", "focus" => "web"])->forFokus("nachrichten");

        $this->assertStringContainsString("focus=nachrichten", $link);
        $this->assertStringNotContainsString("focus=web", $link);
    }

    /**
     * An exclusion is appended to what the user typed, so several of them
     * accumulate the way they would if the user had typed each one.
     */
    public function testExclusionsAccumulateInTheQuery(): void
    {
        $builder = $this->builderFor(["eingabe" => "kaffee -site:a.de"]);

        $this->assertStringContainsString(
            rawurlencode("kaffee -site:a.de -site:b.de"),
            $builder->withoutHost("b.de")
        );
    }

    /**
     * Load-more, from a request that is already one: the uid is replaced, not
     * added, so following the link twice does not build a query string with two
     * of them.
     */
    public function testTheNextPageLinkReplacesAnExistingUid(): void
    {
        $link = $this->builderFor(["eingabe" => "kaffee", "next" => "older-uid"])->nextPage("newer-uid");

        $this->assertStringContainsString("next=newer-uid", $link);
        $this->assertStringNotContainsString("older-uid", $link);
    }

    /**
     * `out=results` is the format the load-more path answers in by default, so
     * it is not repeated in the link; anything else is, because the caller is
     * reading that format and wants more of it.
     */
    public function testTheNextPageLinkCarriesTheFormatUnlessItIsTheDefault(): void
    {
        $this->assertStringNotContainsString(
            "out=",
            $this->builderFor(["eingabe" => "kaffee", "out" => "results"])->nextPage("uid")
        );
        $this->assertStringContainsString(
            "out=json",
            $this->builderFor(["eingabe" => "kaffee", "out" => "json"])->nextPage("uid")
        );
    }

    /**
     * The language link says "all" rather than saying nothing, so the page can
     * tell a deliberate choice from never having made one.
     */
    public function testAskingForEveryLanguageSaysSoExplicitly(): void
    {
        $link = $this->builderFor(["eingabe" => "kaffee", "lang" => "de"])->everyLanguage();

        $this->assertStringContainsString("lang=all", $link);
    }

    /**
     * The language link keeps `page` and `out`, unlike every other link here:
     * it is the same search asked a second way, not a new one.
     */
    public function testAskingForEveryLanguageKeepsThePageAndFormat(): void
    {
        $link = $this->builderFor([
            "eingabe" => "kaffee",
            "lang" => "de",
            "page" => "2",
            "out" => "json",
        ])->everyLanguage();

        $this->assertStringContainsString("page=2", $link);
        $this->assertStringContainsString("out=json", $link);
    }

    /**
     * A request without a query still produces a usable link rather than a
     * warning about an undefined index — reachable from the startpage, where
     * the fokus tabs exist before anything has been typed.
     */
    public function testALinkCanBeBuiltFromARequestWithoutAQuery(): void
    {
        $this->assertStringContainsString(
            rawurlencode(" -site:beispiel.de"),
            $this->builderFor([])->withoutHost("beispiel.de")
        );
    }
}
