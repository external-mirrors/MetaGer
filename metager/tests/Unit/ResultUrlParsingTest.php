<?php

namespace Tests\Unit;

use App\Models\Result;
use Tests\TestCase;

/**
 * `Result::getUrlElements()` runs unconditionally from the constructor for
 * every result of every engine (`getStrippedHost`/`Domain`/`Link`). It called
 * `parse_url($url)` and then read `$parts["host"]` straight off the return
 * value — but parse_url() returns `false` outright for a malformed URL rather
 * than an array missing a "host" key (e.g. a non-numeric port, as below).
 * Reading an offset off `false` is a warning Laravel's error handler turns
 * into an exception, so one parser handing a garbage string to $link (it
 * mistook some other line of upstream markup for the link field) turned into
 * an HTTP 500 for the whole search rather than one dropped result. Seen in
 * production from Onenewspage, but the bug is in the shared constructor, not
 * that parser.
 */
class ResultUrlParsingTest extends TestCase
{
    private function resultWithLink(string $link): Result
    {
        return new Result(1, "Titel", $link, $link, "Beschreibung", "Engine", "https://example.org", 1);
    }

    public function testAMalformedLinkDoesNotThrow(): void
    {
        $result = $this->resultWithLink("http://host:abc");

        $this->assertSame("", $result->strippedHost);
        $this->assertSame("", $result->strippedDomain);
        $this->assertSame("", $result->strippedLink);
    }

    public function testAWellFormedLinkStillExtractsItsHost(): void
    {
        $result = $this->resultWithLink("https://www.example.org/pfad");

        $this->assertSame("example.org", $result->strippedHost);
        $this->assertSame("example.org", $result->strippedDomain);
    }

    /**
     * The crash is gone once the link stops throwing, but a Result with no
     * host is still garbage -- it renders as a card with an empty host and a
     * dead link. isValid() is what MetaGer::validOnly() filters the result
     * list through before rendering, so that is where "no host" has to mean
     * "drop it" for the fix to be complete rather than just non-fatal.
     */
    public function testAResultWithNoHostIsNotValid(): void
    {
        $result = $this->resultWithLink("http://host:abc");

        // createStub() does not run App\MetaGer's real constructor, which is
        // fine here: isValid() returns before it ever reads $metager.
        $metager = $this->createStub(\App\MetaGer::class);

        $this->assertFalse($result->isValid($metager));
    }
}
