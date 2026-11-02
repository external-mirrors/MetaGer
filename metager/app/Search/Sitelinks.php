<?php

namespace App\Search;

use App\Models\DeepResults\Button;
use App\Models\Result;

/**
 * A result's sitelinks as the page shows them.
 *
 * Engines send them as they are: Serper a handful of pages on the site, Brave
 * for a Wikipedia article its whole table of contents — eighteen links, every
 * one of them the article itself with a #fragment, printed as six ragged lines
 * of the accent colour under one result. So:
 *
 *  - the first VISIBLE are shown inline, the rest only behind a <details>,
 *    which opens without JavaScript;
 *  - a set that consists only of the result's own page with a fragment is
 *    named for what it is, "Abschnitte", not "Links";
 *  - of those sections, the ones every article ends with (Weblinks,
 *    Einzelnachweise, …) are dropped. Only sections: a site whose sitelinks
 *    include a page called "Literatur" means that page;
 *  - each one carries a proxy link of its own, so "Anonym öffnen" reaches a
 *    sitelink too. The page puts those in the expanded list, where there is
 *    room for a shield beside every link.
 *
 * Presentation only. `out=json` still exposes every button the engine sent.
 */
final class Sitelinks
{
    public const VISIBLE = 4;

    /**
     * The headings of the source lists an encyclopedia article ends with,
     * normalised as by normaliseHeading(). For the languages MetaGer is
     * offered in.
     */
    private const REFERENCE_SECTIONS = [
        // de
        "siehe auch", "literatur", "weblinks", "einzelnachweise", "anmerkungen", "fußnoten", "quellen", "belege",
        // en
        "see also", "references", "external links", "further reading", "notes", "bibliography", "sources", "citations", "footnotes", "notes and references",
        // fr
        "voir aussi", "notes et références", "références", "liens externes", "bibliographie", "articles connexes",
        // es
        "véase también", "referencias", "enlaces externos", "bibliografía", "notas",
        // it
        "voci correlate", "note", "bibliografia", "collegamenti esterni",
        // nl
        "zie ook", "externe links", "externe link", "referenties", "bronnen", "literatuur", "noten",
        // pt
        "ver também", "referências", "ligações externas", "bibliografia",
        // pl
        "zobacz też", "przypisy", "bibliografia", "linki zewnętrzne",
        // sv, da, fi, ca
        "se även", "referenser", "externa länkar", "se også", "eksterne henvisninger", "kilder", "katso myös", "lähteet", "aiheesta muualla", "vegeu també", "referències", "enllaços externs",
    ];

    /**
     * @param list<Sitelink> $links
     */
    private function __construct(
        public readonly array $links,
        public readonly bool $areSections,
    ) {
    }

    public static function of(Result $result): self
    {
        $page = self::withoutFragment($result->link);

        $links = [];
        $seen = [];
        foreach ($result->deepResults["buttons"] ?? [] as $button) {
            if (!$button instanceof Button || empty($button->link) || isset($seen[$button->link])) {
                continue;
            }
            $seen[$button->link] = true;
            $links[] = $button;
        }

        $isSection = fn(Button|Sitelink $button) => $page !== null
            && parse_url($button->link, PHP_URL_FRAGMENT)
            && self::withoutFragment($button->link) === $page;

        $links = array_values(array_filter(
            $links,
            fn(Button $button) => !($isSection($button) && in_array(self::normaliseHeading($button->title), self::REFERENCE_SECTIONS, true))
        ));

        $links = array_map(
            fn(Button $button) => new Sitelink((string) $button->title, $button->link, $result->generateProxyLink($button->link)),
            $links
        );

        $areSections = $links !== [] && count(array_filter($links, $isSection)) === count($links);

        return new self($links, $areSections);
    }

    public function isEmpty(): bool
    {
        return $this->links === [];
    }

    /** @return list<Sitelink> */
    public function inline(): array
    {
        return array_slice($this->links, 0, self::VISIBLE);
    }

    public function hasMore(): bool
    {
        return count($this->links) > self::VISIBLE;
    }

    /**
     * Host, path and query, without scheme or fragment, the path decoded and
     * without a trailing slash: the article and its sections are not always
     * spelled alike (München and M%C3%BCnchen).
     */
    private static function withoutFragment(string $url): ?string
    {
        $parts = parse_url($url);
        if ($parts === false || empty($parts["host"])) {
            return null;
        }

        return mb_strtolower($parts["host"])
            . rtrim(rawurldecode($parts["path"] ?? ""), "/")
            . (isset($parts["query"]) ? "?" . $parts["query"] : "");
    }

    /** "  Literatur (chronologisch) " → "literatur" */
    private static function normaliseHeading(string $title): string
    {
        $title = preg_replace('/\s*\([^)]*\)\s*$/u', "", $title);
        $title = preg_replace('/\s+/u', " ", $title);

        return mb_strtolower(trim($title));
    }
}
