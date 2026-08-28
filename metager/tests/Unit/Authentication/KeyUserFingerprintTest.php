<?php

namespace Tests\Unit\Authentication;

use App\Authentication\KeyUser;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * The key fingerprint is the same six characters every time it is asked for.
 *
 * It is shown so a user can tell two of their own keys apart, which only works
 * if it does not change. The trap: the keyserver folds a non-UUID key (a legacy
 * string, a short code) into a UUID and KeyUser writes that canonical form back
 * onto $this->key — so a naive `substr($key, -6)` returns the cookie's tail
 * before the keyserver has been asked and the canonical tail afterwards, i.e. a
 * different value on a page that has loaded key data and one that has not.
 *
 * getKeyFingerprint() forces canonicalisation first and returns null when it
 * cannot be reached, so a caller never renders a fingerprint that will move.
 */
class KeyUserFingerprintTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        Http::fake(["*" => Http::response("")]);
    }

    public function testAUuidKeyIsItsOwnFingerprintWithNoKeyserverRoundTrip(): void
    {
        $key = "aaaaaaaa-bbbb-cccc-dddd-eeeeee123456";
        Cache::put("keyserver:key:" . $key, ["key" => $key, "charge" => 10.0], now()->addMinutes(10));

        $user = new KeyUser($key);

        $this->assertSame("123456", $user->getKeyFingerprint());
        $this->assertSame("123456", $user->getKeyFingerprint(), "The fingerprint changed on the second call.");
    }

    public function testANonUuidKeyReportsTheCanonicalFingerprintTheKeyserverReturns(): void
    {
        $canonical = "aaaaaaaa-bbbb-cccc-dddd-eeeeeeffcafe";
        // The cache stands in for the keyserver: it answers a non-UUID lookup
        // with the canonical UUID, exactly as the real keyserver does.
        Cache::put("keyserver:key:legacy-string", ["key" => $canonical, "charge" => 10.0], now()->addMinutes(10));

        $user = new KeyUser("legacy-string");

        $this->assertSame("ffcafe", $user->getKeyFingerprint());
        $this->assertSame("ffcafe", $user->getKeyFingerprint());
    }

    public function testANonUuidKeyThatCannotBeResolvedHasNoFingerprint(): void
    {
        // Nothing primed, keyserver faked to fail: the canonical form is unknown.
        $user = new KeyUser("legacy-string");

        $this->assertNull(
            $user->getKeyFingerprint(),
            "An unresolved legacy key must not show a fingerprint — it would differ once the keyserver answers."
        );
    }

    /**
     * The webextension case, and the sharpest one.
     *
     * KeyAuthGuard builds a KeyUser from the `anonymous-token-key` header and
     * marks it temporary. That key is an *anonymous token* the extension minted
     * and rotates — on expiry, or when its charge is spent — precisely so that
     * the user's real key never reaches us.
     *
     * A perfectly valid UUID, in other words, that identifies nobody and does
     * not survive the hour. Six characters of it would read as an account and
     * behave as a session id: the user would watch their identity change several
     * times a day, and the mark drawn from it (KeyIdenticon) would change colour
     * and shape with it.
     */
    public function testATemporaryUserHasNoFingerprintEvenWithAPerfectlyValidUuid(): void
    {
        $token = "aaaaaaaa-bbbb-cccc-dddd-eeeeee999999";
        Cache::put("keyserver:key:" . $token, ["key" => $token, "charge" => 10.0], now()->addMinutes(10));

        $user = new KeyUser($token);
        $user->temporary = true;

        $this->assertNull(
            $user->getKeyFingerprint(),
            "A rotating anonymous token was rendered as if it were the user's account."
        );
        $this->assertNull($user->getCharge(), "A temporary user has no balance we know of either.");
    }
}
