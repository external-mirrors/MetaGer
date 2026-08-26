<?php

namespace Tests\Unit\Authorization;

use App\Models\Authorization\AnonymousTokenPayment;
use App\Models\Authorization\Token;
use Tests\TestCase;

/**
 * Extends Tests\TestCase rather than PHPUnit's: checkTokens() touches the
 * Cache/Cookie/Request facades, which need the container.
 *
 * checkTokens() posts unchecked tokens to the keymanager and reads back a 422
 * body listing which ones failed. It used to read `$error->param` and
 * `$error->msg` unconditionally on every entry of that body. GlitchTip
 * METAGER-D/G ("Undefined property: stdClass::$param", ~270 occurrences in
 * production) show the keymanager sometimes answers 422 with an error object
 * that has neither property, and PHP's "undefined property" notice becomes an
 * uncaught ErrorException that 500s the whole request. This pins the fix:
 * such an error is skipped rather than fatal.
 */
class AnonymousTokenPaymentTest extends TestCase
{
    private function paymentReturning(int $code, ?array $body): AnonymousTokenPayment
    {
        $token = new Token("token-value", "signature-value", "2024-01-01");

        return new class ($token, $code, $body) extends AnonymousTokenPayment {
            private int $stubCode;
            private ?array $stubBody;

            public function __construct(Token $token, int $stubCode, ?array $stubBody)
            {
                parent::__construct(0.0, [$token], []);
                $this->stubCode = $stubCode;
                $this->stubBody = $stubBody;
            }

            protected function sendTokenCheckRequest(array $payload): array
            {
                return [
                    "code" => $this->stubCode,
                    "body" => $this->stubBody === null ? "not json" : json_encode($this->stubBody),
                ];
            }
        };
    }

    /**
     * The 200 branch, moved behind sendTokenCheckRequest() by the same
     * refactor that made the 422 branch testable — otherwise only the error
     * path would gain coverage while the success path silently lost its own.
     */
    public function testA200ResponseAcceptsTheToken(): void
    {
        $payment = $this->paymentReturning(200, []);

        $result = $payment->checkTokens();

        $this->assertTrue($result);
        $this->assertCount(1, $payment->tokens, "A token the keymanager accepted must be spendable.");
    }

    public function testA422ErrorWithoutAParamPropertyIsSkippedRatherThanFatal(): void
    {
        $payment = $this->paymentReturning(422, [
            "errors" => [
                // No "param" at all: the shape GlitchTip caught.
                ["msg" => "Some other validation error"],
            ],
        ]);

        $result = $payment->checkTokens();

        $this->assertFalse($result, "An unrecognised error entry should leave the token unverified, not fatal.");
    }

    public function testA422ErrorWithoutAMsgPropertyIsSkippedRatherThanFatal(): void
    {
        $payment = $this->paymentReturning(422, [
            "errors" => [
                ["param" => "tokens"],
            ],
        ]);

        $result = $payment->checkTokens();

        $this->assertFalse($result);
    }

    public function testA422ErrorForAKnownParamAndMsgStillMarksTheTokenInvalid(): void
    {
        $payment = $this->paymentReturning(422, [
            "errors" => [
                [
                    "param" => "tokens",
                    "msg" => "Invalid Signatures",
                    "values" => [
                        ["token" => "token-value", "signature" => "signature-value", "date" => "2024-01-01", "status" => "invalid"],
                    ],
                ],
            ],
        ]);

        $result = $payment->checkTokens();

        $this->assertFalse($result);
        $this->assertCount(0, $payment->tokens, "A token the keymanager rejected must not be spent.");
    }
}
