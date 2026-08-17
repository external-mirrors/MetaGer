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

        const collected = {};

        const record = (prefix, rule) => {
            probe.style.cssText = rule.style.cssText;
            const computed = getComputedStyle(probe);

            for (const property of PROPERTIES) {
                // Only what this rule actually declares. The inline style has
                // shorthands already expanded, so `border: 1px solid @border-color`
                // shows up here as its four longhands — while a rule that merely
                // sets `color` does not drag in the border colours that default
                // to currentColor.
                if (probe.style.getPropertyValue(property) === "") {
                    continue;
                }

                // Read it computed rather than as authored: that is what turns
                // var(--text-color) back into a colour, which is the whole point.
                collected[prefix + rule.selectorText + " { " + property + " }"] =
                    computed.getPropertyValue(property);
            }

            probe.style.cssText = "";
        };

        const walk = (rules, prefix) => {
            for (const rule of rules) {
                if (rule.constructor.name === "CSSStyleRule") {
                    record(prefix, rule);
                } else if (rule.cssRules) {
                    // @media / @supports / @layer: the condition goes into the key,
                    // so a rule that only applies at one breakpoint cannot collide
                    // with the same selector outside it.
                    const condition = rule.conditionText || rule.media?.mediaText || "";
                    walk(rule.cssRules, prefix + "@" + condition + " ");
                }
            }
        };

        for (const sheet of document.styleSheets) {
            // A media attribute on the <link> itself, which is how the dark
            // theme is attached today. Unlike an @media rule inside a sheet
            // there is no condition left in the CSSOM to key the result by, so
            // a sheet the browser is not applying has to be skipped outright —
            // reading it would report the dark palette to a light page.
            const media = sheet.media?.mediaText;
            if (media && !window.matchMedia(media).matches) {
                continue;
            }

            let rules;
            try {
                rules = sheet.cssRules;
            } catch (e) {
                // A stylesheet the document may not read into. None of ours are
                // cross-origin, so this would be a surprise worth seeing.
                continue;
            }
            walk(rules, "");
        }

        holder.remove();

        // Sorted, so the snapshot diffs by colour rather than by load order.
        return Object.fromEntries(Object.entries(collected).sort(([a], [b]) => a < b ? -1 : a > b ? 1 : 0));
    JS;
}
