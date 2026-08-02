<?php

namespace App\Models\Configuration;

use App\Localization;
use App\Suggestions;

/**
 * Canonical description of MetaGer's global (fokus-independent) settings.
 * Single source of truth for:
 *  - SearchSettings' global setting key validity/defaults
 *  - the settings page's generic global-settings form
 *  - the machine-readable GET /meta/settings/schema endpoint
 *
 * Per-focus settings ({fokus}_blpage / {fokus}_engine_{name} / {fokus}_setting_{name})
 * are pattern-validated by SearchSettings::isValidSetting() and described via
 * SearchEngineRegistry (engines) + filters.json (parameter-filters) instead of
 * being enumerated here, mirroring how they're already keyed by fokus + name.
 *
 * Each definition:
 *  - key: cookie name / GET-parameter name / header name / form field name (all the same)
 *  - type: "enum" (rendered as a <select>) or "token" (opaque value, e.g. the MetaGer key,
 *          not rendered by the generic settings loop)
 *  - values: ordered list of ["value" => ..., "label" => <trans key>, "translate" => bool]
 *  - default: the value considered "unset"/default
 *  - clients: which consumers this setting is relevant to ("web", "headless")
 */
class SettingsSchema
{
    /**
     * @return array<int, array>
     */
    public static function globalSettings(): array
    {
        $settings = [
            [
                "key" => "key",
                "type" => "token",
                "clients" => ["web", "headless"],
            ],
            [
                "key" => "suggestion_provider",
                "type" => "enum",
                "label" => "settings.suggestions.provider.label",
                "values" => self::suggestionProviderValues(),
                "default" => "off",
                "clients" => ["web", "headless"],
            ],
            [
                "key" => "suggestion_delay",
                "type" => "enum",
                "label" => "settings.suggestions.delay.label",
                "values" => [
                    ["value" => "short", "label" => "settings.suggestions.delay.short", "translate" => true],
                    ["value" => "medium", "label" => "settings.suggestions.delay.medium", "translate" => true],
                    ["value" => "long", "label" => "settings.suggestions.delay.long", "translate" => true],
                ],
                "default" => "medium",
                "clients" => ["web", "headless"],
            ],
            [
                "key" => "suggestion_addressbar",
                "type" => "enum",
                "label" => "settings.suggestions.addressbar.label",
                "values" => self::onOffValues(),
                "default" => "off",
                "clients" => ["web", "headless"],
            ],
            [
                "key" => "tips",
                "type" => "enum",
                "label" => "settings.tips.label",
                "values" => self::onOffValues(),
                "default" => "on",
                "clients" => ["web", "headless"],
            ],
            [
                "key" => "tiles_startpage",
                "type" => "enum",
                "label" => "settings.tiles_startpage.label",
                "values" => self::onOffValues(),
                "default" => "on",
                "clients" => ["web", "headless"],
            ],
            [
                "key" => "new_tab",
                "type" => "enum",
                "label" => "settings.newTab",
                "values" => self::onOffValues(),
                "default" => "off",
                "clients" => ["web", "headless"],
            ],
            [
                // Web-only: the app themes itself natively and never renders
                // MetaGer's HTML, so this setting has no meaning for it.
                "key" => "dark_mode",
                "type" => "enum",
                "label" => "settings.darkmode",
                "values" => [
                    ["value" => "system", "label" => "settings.system", "translate" => true],
                    ["value" => "light", "label" => "settings.light", "translate" => true],
                    ["value" => "dark", "label" => "settings.dark", "translate" => true],
                ],
                "default" => "system",
                "clients" => ["web"],
            ],
        ];

        if (Localization::getLanguage() === "de") {
            $settings[] = [
                "key" => "zitate",
                "type" => "enum",
                "label" => "Zitate",
                "values" => [
                    ["value" => "on", "label" => "settings.zitate.on", "translate" => true],
                    ["value" => "off", "label" => "settings.zitate.off", "translate" => true],
                ],
                "default" => "on",
                "clients" => ["web", "headless"],
            ];
        }

        return $settings;
    }

    /**
     * @return string[] every valid global setting key
     */
    public static function globalSettingKeys(): array
    {
        return array_column(self::globalSettings(), "key");
    }

    /**
     * @param string[] $clients only include settings relevant to these clients
     * @return array<int, array>
     */
    public static function forClients(array $clients): array
    {
        return array_values(array_filter(
            self::globalSettings(),
            fn($setting) => sizeof(array_intersect($setting["clients"], $clients)) > 0
        ));
    }

    private static function onOffValues(): array
    {
        return [
            ["value" => "on", "label" => "settings.suggestions.on", "translate" => true],
            ["value" => "off", "label" => "settings.suggestions.off", "translate" => true],
        ];
    }

    private static function suggestionProviderValues(): array
    {
        $values = [
            ["value" => "off", "label" => "settings.suggestions.off", "translate" => true],
        ];
        foreach (Suggestions::GET_AVAILABLE_PROVIDERS() as $name => $class) {
            $values[] = [
                "value" => $name,
                "label" => ucfirst($name) . " (" . $class::COST . " Token)",
                "translate" => false,
            ];
        }
        return $values;
    }
}
