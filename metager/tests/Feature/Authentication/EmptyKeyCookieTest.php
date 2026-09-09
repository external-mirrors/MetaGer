<?php

namespace Tests\Feature\Authentication;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * A `key` cookie that holds nothing usable, and the dead end it used to be.
 *
 * This suite exists because of a support mail, and it is worth writing down
 * what that mail described, because the shape of it is the whole bug: a
 * ten-year user opens Firefox on her homepage and gets the landing page's
 * "welcome back" copy; she presses "log in again"; she never reaches the form;
 * she ends up back where she started. "Es dreht sich im Kreis."
 *
 * Reproduced against production with nothing but an empty cookie:
 *
 *     /          200, landing hero        (the startpage says: you are logged out)
 *     /anmelden  302 -> /konto            (the login page says: you have a key)
 *     /konto     302 -> /anmelden?…       (the account page says: no you don't)
 *
 * Two readings of one cookie, three pages, no way out. {@see \App\Authentication\KeyAuthGuard}
 * read it as nobody (`$key === ""`), while LoginController::show() asked
 * `cookie("key") !== null`, which an empty string satisfies. Both were
 * defensible on their own; together they removed the only door.
 *
 * Nothing in this application writes such a cookie — Symfony renders an empty
 * value as a deletion, so `Cookie::forever("key", "")` deletes rather than
 * stores. It arrives from outside: any client that clears the cookie by
 * *setting* it (`document.cookie = "key="`) instead of expiring it. So the fix
 * is not "stop writing it", it is "survive it, and clean it up" — which is why
 * the last test here is the important one.
 */
class EmptyKeyCookieTest extends TestCase
{
    /** A valid-shaped UUID v4, the form AccountController::keyOf() accepts. */
    private const KEY = "aaaaaaaa-bbbb-4ccc-9ddd-eeeeee123456";

    private function keyserverKnows(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            "*/api/json/key/*" => Http::response(["key" => self::KEY, "charge" => 42.0]),
            "*" => Http::response(""),
        ]);
    }

    /**
     * The one that mattered: she could not get to the form.
     *
     * `withUnencryptedCookie`, not `withCookie` — `key` is in
     * EncryptCookies::$except, and a cookie the test encrypts is not the cookie
     * the browser sends.
     */
    public function testTheLoginFormIsReachableWithAnEmptyKeyCookie(): void
    {
        $this->keyserverKnows();

        $response = $this->withUnencryptedCookie("key", "")->get("/anmelden")->assertOk();

        $response->assertSee('name="key"', false);
        $response->assertSee(trans("login.submit"));
    }

    /** Whitespace is the same nothing, and used to sign a visitor in as a garbage key. */
    public function testTheLoginFormIsReachableWithAWhitespaceKeyCookie(): void
    {
        $this->keyserverKnows();

        $this->withUnencryptedCookie("key", "   ")->get("/anmelden")
            ->assertOk()
            ->assertSee('name="key"', false);
    }

    /**
     * The other half of the circle. One hop away is fine — that is the account
     * page saying "sign in first". Landing back on a page that sends you here
     * again is not.
     */
    public function testTheAccountPageDoesNotBounceBackIntoTheLoginPage(): void
    {
        $this->keyserverKnows();

        $bounce = $this->withUnencryptedCookie("key", "")->get("/konto")->assertRedirect();

        $target = $bounce->headers->get("Location");
        $this->assertStringContainsString("/anmelden", $target);

        // Following it must end at the form, not at another redirect.
        $this->withUnencryptedCookie("key", "")->get($target)->assertOk()->assertSee('name="key"', false);
    }

    /**
     * A real key still goes to the account page. The fix narrows what counts as
     * "has a key"; it must not stop counting the actual ones.
     */
    public function testARealKeyCookieStillSkipsTheLoginForm(): void
    {
        $this->keyserverKnows();

        $this->withUnencryptedCookie("key", self::KEY)->get("/anmelden")
            ->assertRedirect(route("account"));
    }

    /**
     * And the state heals itself.
     *
     * Without this the fix would only make the dead end survivable: the login
     * form comes back, but the startpage still greets her as a stranger on
     * every visit, because the cookie that causes it is still in the jar and
     * nothing ever removes it. The guard now expires it on sight, so the next
     * page view is a clean one whatever wrote it.
     */
    public function testAnEmptyKeyCookieIsExpiredOnSight(): void
    {
        $this->keyserverKnows();

        $this->withUnencryptedCookie("key", "")->get("/")
            ->assertOk()
            ->assertCookieExpired("key");
    }
}
