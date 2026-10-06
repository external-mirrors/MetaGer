<?php

namespace Tests\Unit\Authentication;

use App\Authentication\KeyIssuer;
use PHPUnit\Framework\TestCase;

/**
 * KeyIssuer::findInText() — the search half of the sign-in form's file
 * upload, alongside KeyResolver::resolveImage() itself
 * (tests/Feature/LoginSubmitTest.php exercises the full upload-to-cookie
 * path; this pins the extraction rules in isolation).
 */
class KeyIssuerFindInTextTest extends TestCase
{
    private const A_KEY = "5e9c1a2b-4f6d-4c3e-9a71-2b8d0f4e6c15";

    public function testAKeySurroundedByPlainTextIsFound(): void
    {
        $this->assertSame(
            self::A_KEY,
            KeyIssuer::findInText("Ihr SUMA-EV-Schlüssel\n\n".self::A_KEY."\n\nBewahren Sie diese Datei sicher auf.\n")
        );
    }

    public function testAnUppercaseKeyIsFoundAndLowercased(): void
    {
        $this->assertSame(self::A_KEY, KeyIssuer::findInText(strtoupper(self::A_KEY)));
    }

    public function testTheBareKeyAloneIsFound(): void
    {
        $this->assertSame(self::A_KEY, KeyIssuer::findInText(self::A_KEY));
    }

    public function testPlainTextWithNoKeyFindsNothing(): void
    {
        $this->assertNull(KeyIssuer::findInText("Das ist nur ein Text ohne Schlüssel."));
        $this->assertNull(KeyIssuer::findInText(""));
    }

    /**
     * The guard `resolveImage()` actually relies on: a real image's bytes are
     * all but certain to contain an invalid UTF-8 sequence somewhere across
     * the whole file, so this returns null before the regex search ever
     * runs against binary data at all — even if, by construction here, the
     * bytes happen to also contain a key-shaped string.
     */
    public function testInvalidUtf8BytesAreNeverSearched(): void
    {
        $binary = "\xFF\xFE".self::A_KEY;

        $this->assertNull(KeyIssuer::findInText($binary));
    }

    public function testANonUuidVersionIsNotMistakenForAKey(): void
    {
        // Version nibble changed from 4 to 1 — same shape, not a key.
        $notAKey = "5e9c1a2b-4f6d-1c3e-9a71-2b8d0f4e6c15";

        $this->assertNull(KeyIssuer::findInText($notAKey));
    }
}
