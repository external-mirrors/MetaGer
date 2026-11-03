<?php
namespace Tests;

use App\Search\Blacklists;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // Blacklists keeps its compiled snapshot in a static and in a file per
        // `blocklist:version`. The version lives in the Redis the whole suite
        // shares, while each test that writes entries has an in-memory
        // database of its own — so a snapshot left by one test would be
        // "current" for the next test that writes nothing. Cleared here rather
        // than in the tests that write entries, because the test that breaks
        // is never the one that forgot.
        Blacklists::flush();
    }
}
