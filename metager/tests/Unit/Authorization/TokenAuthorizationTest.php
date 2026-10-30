<?php

namespace Tests\Unit\Authorization;

use App\Models\Authorization\TokenAuthorization;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * The `tokens` and `decitokens` headers (and cookies of the same names) carry a
 * JSON list of anonymous tokens, each an object with token/signature/date, and
 * reach this constructor verbatim from AuthorizationServiceProvider.
 *
 * It checked that the decoded value was a list and then passed every element to
 * AnonymousTokenPayment::addJSONToken(object), so a list whose elements were not
 * objects — `["x"]`, `[1]`, `[[]]` — was a TypeError and a 500 on any page that
 * resolves the Authorization, the error page rendering that 500 included.
 * GlitchTip METAGER-19M/19N/19O/19P/19Q/19R/19S/19T/19U/1A1: ~65 events in one
 * hour, from /suggest, /suggest/cost and /meta/meta.ger3.
 *
 * Such an element is not a token, so it is ignored, the same as an object
 * missing one of the three fields already was.
 *
 * Constructed directly rather than through a request: the provider reads the
 * headers in register(), which in a feature test runs before the test request
 * exists, so withHeader() never reaches this path.
 */
class TokenAuthorizationTest extends TestCase
{
    public static function malformedLists(): array
    {
        return [
            "a string" => ['["garbage"]'],
            "an integer" => ["[1]"],
            "a nested list" => ["[[]]"],
            "null" => ["[null]"],
        ];
    }

    #[DataProvider("malformedLists")]
    public function testATokenListElementThatIsNotAnObjectIsIgnored(string $list): void
    {
        $authorization = new TokenAuthorization(tokenauthorization: null, tokenString: $list);

        $this->assertEquals(0, $authorization->availableTokens);
    }

    #[DataProvider("malformedLists")]
    public function testADecitokenListElementThatIsNotAnObjectIsIgnored(string $list): void
    {
        $authorization = new TokenAuthorization(tokenauthorization: null, decitokenString: $list);

        $this->assertEquals(0, $authorization->availableTokens);
    }
}
