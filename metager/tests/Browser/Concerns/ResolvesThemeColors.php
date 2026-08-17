<?php

namespace Tests\Browser\Concerns;

use Laravel\Dusk\Browser;

/**
 * Reads back what a theme actually paints, and compares it to a snapshot.
 *
 * Shared because the same palette has to be checked from two differently
 * configured browsers: the settings the user picks explicitly, and the one the
 * operating system picks for them. Firefox preferences are fixed per test class,
 * so prefers-color-scheme cannot be varied within one.
 */
trait ResolvesThemeColors
{
    /**
     * Walks every rule of every loaded stylesheet and resolves what it paints.
     *
     * Each rule's declarations are applied to a probe element rather than
     * matched against the page, so a rule is covered whether or not the page
     * happens to contain an element it applies to — including the result page's
     * and the settings page's rules, which no page reachable without a key has.
     *
     * @return array<string, string> "selector { property }" => resolved value
     */
    protected function resolvePalette(Browser $browser): array
    {
        $resolved = $browser->script(self::PALETTE_COLLECTOR)[0];

        $this->assertNotEmpty(
            $resolved,
            "No colour declarations were collected — the walk found no stylesheets, which means " .
                "the page loaded without them rather than that nothing on it is themed."
        );

        return $resolved;
    }

    /**
     * @param array<string, string> $resolved
     */
    protected function assertPaletteMatchesSnapshot(array $resolved, string $theme): void
    {
        $snapshot = __DIR__ . "/../snapshots/theme-colors-{$theme}.json";
        $current = json_encode($resolved, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";

        if (getenv("UPDATE_THEME_SNAPSHOTS")) {
            file_put_contents($snapshot, $current);
            $this->markTestSkipped("Rewrote " . basename($snapshot) . " — read the diff before committing it.");
        }

        $this->assertFileExists(
            $snapshot,
            "Missing baseline. Create it with UPDATE_THEME_SNAPSHOTS=1 php artisan dusk --filter ThemeColors"
        );

        $this->assertSame(
            file_get_contents($snapshot),
            $current,
            "The {$theme} palette changed. Every differing line is a colour that now renders " .
                "differently than it did; if that is intended, regenerate the snapshot."
        );
    }

    /**
     * The display state of the two theme-only visibility classes.
     *
     * @return array{dark: string, light: string}
     */
    protected function resolveThemeOnlyVisibility(Browser $browser): array
    {
        return $browser->script(
            <<<'JS'
                const probe = (className) => {
                    const el = document.createElement("div");
                    el.className = className;
                    document.body.appendChild(el);
                    const display = getComputedStyle(el).display;
                    el.remove();
                    return display;
                };
                return { dark: probe("dm-only"), light: probe("lm-only") };
            JS
        )[0];
    }

    private const PALETTE_COLLECTOR = <<<'JS'
        const PROPERTIES = [
            "color", "background-color", "background-image",
            "border-top-color", "border-right-color", "border-bottom-color", "border-left-color",
            "outline-color", "text-decoration-color", "caret-color", "column-rule-color",
            "fill", "stroke", "filter", "box-shadow", "opacity",
        ];
        const SENTINEL = "rgb(1, 2, 3)";

        // The rules come from the stylesheet's own text, fetched over HTTP,
        // rather than from the CSSOM.
        //
        // The CSSOM cannot be used for this: Firefox serialises a declaration
        // that is still waiting on a custom property as an empty value, so
        // `border: 1px solid var(--border-color)` reads back out of cssText as
        // `border-top-color: ;` and the colour is simply gone. That is a
        // property of the readout, not of the page — the same rule written by
        // hand computes rgb(204, 204, 204) — but it would quietly hide every
        // rule this refactor touches. The text is never lossy.
        const fetchText = (url) => {
            const request = new XMLHttpRequest();
            request.open("GET", url, false);
            request.send();
            return request.status === 200 ? request.responseText : "";
        };

        // A rule splitter, not a CSS parser: it tracks brace depth to cut the
        // text into (condition, selector, declarations) and knows just enough
        // about at-rules to descend into the conditional ones and skip the rest.
        const splitRules = (css, condition, out) => {
            let index = 0;

            while (index < css.length) {
                const open = css.indexOf("{", index);
                if (open === -1) {
                    break;
                }

                // Statements ending in a semicolon (@charset, @import) are not
                // blocks; drop them and keep what follows as the prelude.
                let prelude = css.slice(index, open);
                prelude = prelude.slice(prelude.lastIndexOf(";") + 1).trim();

                let depth = 1;
                let cursor = open + 1;
                let quote = null;

                while (cursor < css.length && depth > 0) {
                    const character = css[cursor];

                    if (quote !== null) {
                        if (character === quote && css[cursor - 1] !== "\\") {
                            quote = null;
                        }
                    } else if (character === '"' || character === "'") {
                        quote = character;
                    } else if (character === "{") {
                        depth++;
                    } else if (character === "}") {
                        depth--;
                    }

                    cursor++;
                }

                const body = css.slice(open + 1, cursor - 1);

                if (prelude.startsWith("@")) {
                    const atRule = prelude.split(/[\s(]/)[0];

                    if (atRule === "@media" || atRule === "@supports" || atRule === "@layer") {
                        // The condition goes into the key, so a rule that only
                        // applies at one breakpoint cannot collide with the same
                        // selector outside it.
                        splitRules(body, condition + prelude + " ", out);
                    }
                    // @font-face, @keyframes and @page paint nothing on their own.
                } else if (prelude !== "") {
                    out.push({ condition, selector: prelude, body });
                }

                index = cursor;
            }
        };

        const rules = [];

        for (const sheet of document.styleSheets) {
            // A media attribute on the <link> itself, which is how the dark
            // theme is attached today. A sheet the browser is not applying has
            // to be skipped, or the light page would be read as the dark one.
            const media = sheet.media?.mediaText;
            if (media && !window.matchMedia(media).matches) {
                continue;
            }

            const css = sheet.href
                ? fetchText(sheet.href)
                : (sheet.ownerNode?.textContent ?? "");

            splitRules(css, "", rules);
        }

        const holder = document.createElement("div");
        // Inherited properties get a value no stylesheet uses, so a rule that
        // asks for `inherit` or `currentColor` records something stable instead
        // of whatever the surrounding theme happens to supply.
        holder.style.color = SENTINEL;
        holder.style.fill = SENTINEL;
        holder.style.stroke = SENTINEL;
        holder.style.caretColor = SENTINEL;
        holder.style.position = "absolute";
        holder.style.visibility = "hidden";

        const probe = document.createElement("div");
        holder.appendChild(probe);
        document.body.appendChild(holder);

        // Declarations go onto the probe's own style, not into a <style> element
        // we insert. The deployment sends style-src 'self', which blocks an
        // injected <style> outright — its .sheet comes back null — while writing
        // through the CSSOM is script, not markup, and is not governed by it.
        //
        // Assigning the fetched text is also lossless where copying a rule's
        // declarations one by one is not: the text still says
        // `border: 1px solid var(--border-color)` and the parser sees it whole.
        const apply = (declarations) => {
            probe.style.cssText = declarations ?? "";
        };

        // What the probe reads with no rule applied. Anything a rule leaves at
        // these values, it did not paint.
        //
        // Relevance is decided by the computed value rather than by asking which
        // properties a rule declares, because the CSSOM cannot answer that once
        // a var() is in play. The cost is that a rule setting only `color` also
        // records the border colours that follow currentColor; they are stable
        // and correct, so the snapshot carries them.
        const untouched = {};
        for (const property of PROPERTIES) {
            untouched[property] = getComputedStyle(probe).getPropertyValue(property);
        }

        const collected = {};

        for (const rule of rules) {
            try {
                apply(rule.body);
            } catch (e) {
                // Declarations the parser will not take in this position.
                continue;
            }

            const computed = getComputedStyle(probe);

            for (const property of PROPERTIES) {
                // Computed, not authored: that is what turns var(--text-color)
                // back into a colour, which is the whole point.
                const value = computed.getPropertyValue(property);

                if (value === untouched[property] || value === SENTINEL || value === "") {
                    continue;
                }

                collected[rule.condition + rule.selector + " { " + property + " }"] = value;
            }
        }

        apply(null);
        holder.remove();

        // Sorted, so the snapshot diffs by colour rather than by load order.
        return Object.fromEntries(Object.entries(collected).sort(([a], [b]) => a < b ? -1 : a > b ? 1 : 0));
    JS;
}
