<?php

namespace App\Authentication;

use Illuminate\Support\HtmlString;

/**
 * The visual mark for a key.
 *
 * A key is a UUID and has no name, so the only thing a person can be shown is
 * some of the key itself. Six hex characters answer "which key is this?" only
 * if you *read and compare* them — and support calls are full of people who did
 * not notice they were signed in at all, let alone read a hex string. A mark is
 * recognised rather than read: you know it is yours before you have parsed
 * anything.
 *
 * Derived, never stored. The fingerprint already exists
 * ({@see KeyUser::getKeyFingerprint()}), so this adds no state anywhere and the
 * same key produces the same mark on every surface that can compute it —
 * including the keymanager dashboard and the webextension, which have to port
 * these four lines rather than share them.
 *
 * What it does *not* do is leak anything the fingerprint does not: the mark is
 * a function of the same six characters, so seeing one tells you exactly as
 * much as seeing the other, and neither recovers the remaining thirty.
 *
 * ┌ hue      (n % 12) * 30      twelve hues, evenly spaced — the part that
 * │                             still carries at 15px, where the pattern does not
 * └ pattern  bit i of n         4x4, left two columns mirrored to the right;
 *                               eight cells, so 256 patterns x 12 hues
 *
 * Saturation and lightness are fixed in the stylesheet, not here: they are a
 * theme decision (the dark palette needs a lighter mark), and keeping them there
 * means one number crosses into the markup instead of four colours.
 */
class KeyIdenticon
{
    /** Cells across and down. Half of it is drawn; the other half is a mirror. */
    private const GRID = 4;

    /**
     * The hue for a fingerprint, or null when there is nothing to derive from.
     *
     * Null is a real answer, not a failure: a webextension visitor has no
     * fingerprint by design, and a legacy non-UUID key has none we can trust.
     */
    public static function hue(?string $fingerprint): ?int
    {
        if (!self::isDrawable($fingerprint)) {
            return null;
        }

        return (hexdec($fingerprint) % 12) * 30;
    }

    /**
     * The filled cells, as [x, y] pairs, for a fingerprint.
     *
     * Exposed for the test: asserting on coordinates says something about the
     * derivation, where asserting on an SVG string only says the markup has not
     * been reformatted.
     *
     * @return array<int, array{int, int}>
     */
    public static function cells(?string $fingerprint): array
    {
        if (!self::isDrawable($fingerprint)) {
            return [];
        }

        $n = hexdec($fingerprint);
        $cells = [];

        for ($y = 0; $y < self::GRID; $y++) {
            for ($x = 0; $x < self::GRID / 2; $x++) {
                if (($n >> ($y * (self::GRID / 2) + $x)) & 1) {
                    $cells[] = [$x, $y];
                    $cells[] = [self::GRID - 1 - $x, $y];
                }
            }
        }

        return $cells;
    }

    /**
     * The mark, ready to drop into a blade.
     *
     * Always returns an element, because every caller needs something in that
     * slot: with no fingerprint it is the hatched "identity is not ours to
     * know" placeholder, which is a statement rather than a missing asset.
     *
     * The hue rides in as a class, not an inline `style`. It is one of twelve
     * fixed values ((n % 12) * 30), so a bounded set of `account-mark--hue-*`
     * rules in the stylesheet can carry it while the stylesheet still owns every
     * colour decision and the same markup serves both themes. An inline
     * `style="--account-mark-hue:…"` is what this used to emit, and metager.de's
     * CSP (`style-src-attr 'self'`, set in build/nginx/configuration/nginx.conf)
     * silently drops it — so every mark on the site rendered with the fallback
     * hue and one sandy colour regardless of the key.
     */
    public static function render(?string $fingerprint, string $class = ''): HtmlString
    {
        $classes = trim('account-mark ' . $class);

        $hue = self::hue($fingerprint);
        if ($hue === null) {
            return new HtmlString(
                '<span class="' . e($classes) . ' account-mark--anonymous" aria-hidden="true"></span>'
            );
        }

        $rects = '';
        foreach (self::cells($fingerprint) as [$x, $y]) {
            $rects .= '<rect x="' . $x . '" y="' . $y . '" width="1" height="1"/>';
        }

        return new HtmlString(
            '<span class="' . e($classes) . ' account-mark--hue-' . $hue . '" aria-hidden="true">'
            . '<svg viewBox="0 0 ' . self::GRID . ' ' . self::GRID . '" xmlns="http://www.w3.org/2000/svg" focusable="false">'
            . '<rect class="account-mark__ground" width="' . self::GRID . '" height="' . self::GRID . '"/>'
            . '<g class="account-mark__cells">' . $rects . '</g>'
            . '</svg></span>'
        );
    }

    /**
     * Exactly the six lowercase hex characters getKeyFingerprint() hands back.
     *
     * Strict on purpose. Anything else reaching this point means the fingerprint
     * contract changed, and drawing a mark from it would produce something that
     * looks deliberate and is not.
     */
    private static function isDrawable(?string $fingerprint): bool
    {
        return $fingerprint !== null && preg_match('/^[0-9a-f]{6}$/', $fingerprint) === 1;
    }
}
