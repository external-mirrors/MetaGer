<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The suggest endpoint answers in JSON and echoes the query back as its first
 * element, the way the OpenSearch suggestions format wants it. A query that is
 * not valid UTF-8 (`%FF`, from a scanner) could not be encoded and answered
 * 500 with "Malformed UTF-8 characters" (GlitchTip METAGER-1AD). No provider
 * could suggest anything for it either, so it now counts as an empty query.
 */
class SuggestInvalidUtf8Test extends TestCase
{
    #[Test]
    public function a_query_that_is_not_utf8_answers_an_empty_suggestion_list(): void
    {
        $this->get("/suggest?query=%FFab%C3")
            ->assertOk()
            ->assertExactJson(["", [], [], []]);
    }

    #[Test]
    public function a_utf8_query_is_still_echoed(): void
    {
        $this->get("/suggest?query=" . rawurlencode("grüße"))
            ->assertOk()
            ->assertExactJson(["grüße", [], [], []]);
    }
}
