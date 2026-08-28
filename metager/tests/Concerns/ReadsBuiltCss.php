<?php

namespace Tests\Concerns;

/**
 * Read a built stylesheet back, the way the browser gets it.
 *
 * A handful of layout promises are arithmetic between numbers that live in
 * different LESS files and cannot see each other — a fixed element's gutter
 * against a centred column's width, a media query in parts/account.less against
 * its complement in pages/resultpage. Those are worth asserting, and they have
 * to be asserted on the compiled output: the source can look right while the
 * compiled variables say something else, and the browser only ever reads the
 * compiled file.
 *
 * Note the build minifies, and lightningcss rewrites conditions on the way —
 * `@media (max-width: 1150px)` comes out as `@media (width<=1150px)`. Anything
 * matching on media conditions has to read the number, not the spelling.
 */
trait ReadsBuiltCss
{
    protected function builtCss(string $entry): string
    {
        $manifest = json_decode(file_get_contents(public_path("build/manifest.json")), true);
        $file = $manifest[$entry]["file"] ?? null;
        $this->assertNotNull($file, "[$entry] is not in the Vite manifest; run `npm run build`.");

        return file_get_contents(public_path("build/" . $file));
    }
}
