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

        // Blacklists keeps its compiled list in a static and in a file per
        // `blocklist:version`, and the version lives in the Valkey the whole
        // suite shares — so a list compiled in one test would be "current"
        // for the next test that puts nothing in. Cleared here rather
        // than in the tests that write entries, because the test that breaks
        // is never the one that forgot.
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
