<?php

namespace Tests\Unit;

use App\Models\Quicktips\Quicktips;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Quicktips::parseResults(), fed the quicktip server's Atom feed directly.
 *
 * The instance is built without its constructor: that one queues a fetch on
 * Redis, and none of it is what is under test here.
 */
class QuicktipsParsingTest extends TestCase
{
    public function testQuicktipsAreSortedIntoDisplayOrder(): void
    {
        $quicktips = $this->parse([
            $this->entry("tip", "Wussten Sie schon?"),
            $this->entry("wikipedia", "Fahrradkette"),
            $this->entry("spruch", "Ein Spruch"),
        ]);

        $this->assertSame(
            ["spruch", "wikipedia", "tip"],
            array_map(fn($quicktip) => $quicktip->type, $quicktips)
        );
    }

    private function parse(array $entries): array
    {
        $quicktips = (new ReflectionClass(Quicktips::class))->newInstanceWithoutConstructor();
        $quicktips->parseResults(
            '<feed xmlns="http://www.w3.org/2005/Atom" xmlns:mg="http://metager.de/opensearch/quicktips/">'
            . implode("", $entries)
            . '</feed>'
        );

        return $quicktips->quicktips;
    }

    private function entry(string $type, string $title): string
    {
        return "<entry><mg:type>{$type}</mg:type><title>{$title}</title>"
            . '<link href="https://example.org/"/><content>Beschreibung</content></entry>';
    }
}
