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

        // Blacklists keeps its compiled list in a static and in a file per
        // `blocklist:version`, and the version lives in the Valkey the whole
        // suite shares — so a list compiled in one test would be "current"
        // for the next test that puts nothing in. Cleared here rather
        // than in the tests that write entries, because the test that breaks
        // is never the one that forgot.
        Blacklists::flush();
    }
}
