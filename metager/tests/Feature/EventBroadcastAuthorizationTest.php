<?php

namespace Tests\Feature;

use App\Events\KeyChanged;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * The external event endpoints, and the token that guards them.
 *
 * Regression test. `EventAuthorization` compared the incoming bearer token
 * against `config('metager.event_authorization')`, but the file is
 * config/metager/metager.php and the nested directory doubles the segment, so
 * the real key is `metager.metager.event_authorization`. The wrong path
 * resolved to null and `bearerToken()` returns a string whenever the header is
 * present, so nothing could ever match: every push was answered 401.
 *
 * It failed silently in both directions. The keymanager sends these with
 * fetch(), which resolves rather than rejects on a 4xx, and its only error
 * handling is a .catch() — so a 401 was indistinguishable from success. On the
 * MetaGer side nothing was logged because the request was rejected exactly as
 * an unauthorized request is supposed to be. The visible symptom was a browser
 * extension whose websocket connected fine and then never received a
 * KeyChanged, so the displayed key balance stopped tracking discharges.
 *
 * The `metager.metager.*` path is asserted explicitly below rather than left
 * implicit, because moving that config file is precisely what breaks this and
 * a test that only set the value through the middleware's own path would move
 * with the bug.
 */
class EventBroadcastAuthorizationTest extends TestCase
{
    /**
     * The config path the middleware must read. Spelled out as a literal: the
     * doubled segment is the whole point of the test.
     */
    private const CONFIG_PATH = 'metager.metager.event_authorization';

    private const TOKEN = 'event-token-for-testing-4f2b9c';

    private const KEY = 'f5d1278e-8109-4dd9-be1e-4197e04873b9';

    protected function setUp(): void
    {
        parent::setUp();
        config([self::CONFIG_PATH => self::TOKEN]);
    }

    public function testTheConfiguredTokenAuthorizesAKeyUpdate(): void
    {
        Event::fake([KeyChanged::class]);

        $response = $this->withHeader('Authorization', 'Bearer ' . self::TOKEN)
            ->postJson('/api/event/key/update', [
                'key' => self::KEY,
                'change' => -1.5,
                'new_charge' => 12.5,
            ]);

        $response->assertOk()->assertJsonPath('status', 'success');

        Event::assertDispatched(
            KeyChanged::class,
            fn(KeyChanged $event) => $event->key === self::KEY
            && $event->change === -1.5
            && $event->new_charge === 12.5
        );
    }

    /**
     * What the extension actually subscribes to. The channel carries the key,
     * so renaming it silently orphans every connected client.
     */
    public function testTheEventBroadcastsOnTheKeysOwnChannel(): void
    {
        $channels = (new KeyChanged(self::KEY, -1.5, 12.5))->broadcastOn();

        $this->assertSame(
            ['App.Models.Authorization.Key.' . self::KEY],
            array_map(fn($channel) => $channel->name, $channels)
        );
    }

    public function testAWrongTokenIsRejectedAndDispatchesNothing(): void
    {
        Event::fake([KeyChanged::class]);

        $this->withHeader('Authorization', 'Bearer not-the-configured-token')
            ->postJson('/api/event/key/update', [
                'key' => self::KEY,
                'change' => -1.5,
                'new_charge' => 12.5,
            ])
            ->assertUnauthorized();

        Event::assertNotDispatched(KeyChanged::class);
    }

    public function testAMissingTokenIsRejected(): void
    {
        Event::fake([KeyChanged::class]);

        $this->postJson('/api/event/key/update', [
            'key' => self::KEY,
            'change' => -1.5,
            'new_charge' => 12.5,
        ])->assertUnauthorized();

        Event::assertNotDispatched(KeyChanged::class);
    }

    /**
     * The failure mode that hid the bug: when the middleware reads a path that
     * does not exist, the expected value is null. Nothing may authorize against
     * that — least of all a request that sends no token at all, which is the one
     * case where a naive `$token !== $expected` comparison could accidentally
     * succeed.
     */
    public function testAnUnconfiguredTokenAuthorizesNobody(): void
    {
        Event::fake([KeyChanged::class]);
        config([self::CONFIG_PATH => null]);

        $this->postJson('/api/event/key/update', [
            'key' => self::KEY,
            'change' => -1.5,
            'new_charge' => 12.5,
        ])->assertUnauthorized();

        $this->withHeader('Authorization', 'Bearer anything')
            ->postJson('/api/event/key/update', [
                'key' => self::KEY,
                'change' => -1.5,
                'new_charge' => 12.5,
            ])->assertUnauthorized();

        Event::assertNotDispatched(KeyChanged::class);
    }

    /**
     * The login endpoint shares the middleware, so it shares the regression.
     */
    public function testTheLoginEventEndpointIsGuardedByTheSameToken(): void
    {
        $this->withHeader('Authorization', 'Bearer not-the-configured-token')
            ->postJson('/api/event/key/login', ['key' => self::KEY])
            ->assertUnauthorized();

        $this->withHeader('Authorization', 'Bearer ' . self::TOKEN)
            ->withHeader('X-Login-Token', 'a-login-token')
            ->postJson('/api/event/key/login', ['key' => self::KEY])
            ->assertOk();
    }

    /**
     * A key update without a charge is a no-op, not a broadcast of nothing.
     */
    public function testAnUpdateWithoutANewChargeIsRejected(): void
    {
        Event::fake([KeyChanged::class]);

        $this->withHeader('Authorization', 'Bearer ' . self::TOKEN)
            ->postJson('/api/event/key/update', ['key' => self::KEY, 'change' => -1.5])
            ->assertStatus(400);

        Event::assertNotDispatched(KeyChanged::class);
    }
}
