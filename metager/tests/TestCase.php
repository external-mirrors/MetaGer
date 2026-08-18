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

        // Blacklists parses each file once per *process*, not per request, and
        // remembers it under the file's mtime and size. That is what makes it
        // cheap in production, where the files are read-only mounts that never
        // change while the pod lives — but two tests writing different content
        // of the same length within one second are indistinguishable to a
        // stat(), so the whole suite would run against whichever one got there
        // first. Cleared here rather than in the tests that write the files,
        // because the test that breaks is never the one that forgot.
        Blacklists::flush();
    }
}
