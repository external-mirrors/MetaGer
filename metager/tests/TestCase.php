<?php
namespace Tests;

use App\Search\Blacklists;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use Tests\Support\BootCache;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication {
        createApplication as bootApplication;
    }

    public function createApplication(): Application
    {
        BootCache::prime(fn () => $this->bootApplication());

        return $this->bootApplication();
    }

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

        // A test that forgets to fake a service must fail fast rather than
        // wait out a real connection: /preise alone spent two seconds per
        // render on the keymanager.
        Http::preventStrayRequests();

        // App\Support\RedisFailover's backoff, which otherwise really sleeps
        // through its retry budget in every failover test.
        Sleep::fake();
    }
}
