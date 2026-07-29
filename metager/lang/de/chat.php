<?php

return [
    "empty" => "Chatte sicher und privat mit MetaGer.",
    "empty_note" => "Stell eine Frage, um loszulegen. Dein Chat bleibt privat und eine Historie wird nur gespeichert, wenn du das möchtest.",

    "unavailable" => [
        "title" => "Der Chat ist derzeit nicht verfügbar",
        "body" => "Der Chat-Dienst ist momentan nicht erreichbar. Bitte versuchen Sie es in Kürze erneut.",
    ],

    "skiplinks" => [
        "conversation" => "Zum Gespräch springen",
    ],

    "model" => [
        "label" => "Modell",
        "cost_per_message" => "ca. :cost pro Nachricht",

        "speed" => [
            "fast" => "Schnell",
            "balanced" => "Ausgewogen",
            "thorough" => "Gründlich",
        ],

        // Klartext statt Marketing, ein Satz pro Modell. Schlüssel ist die Modell-ID aus
        // metager-chat/config/models.json; fehlt ein Eintrag, wird die Zeile einfach ohne
        // Beschreibung angezeigt.
        "descriptions" => [
            "openai/gpt-4o" => "Vielseitig und zuverlässig – eine gute Voreinstellung, wenn Sie unsicher sind.",
            "openai/gpt-4o-mini" => "Antwortet sehr schnell und kostet am wenigsten. Gut für kurze Fragen.",
            "anthropic/claude-3-5-sonnet" => "Nimmt sich mehr Zeit und liefert die sorgfältigsten Antworten bei langen Texten.",
            "anthropic/claude-3-5-haiku" => "Schnell und günstig, mit etwas mehr Ausdauer bei längeren Texten.",
        ],
    ],

    "attachment" => [
        "label" => "Datei anhängen",
        "choose" => "Datei anhängen…",
        "remove" => "Entfernen",
    ],

    "composer" => [
        "label" => "Ihre Nachricht",
        "placeholder" => "Nachricht eingeben…",
        "send" => "Senden",
        "stop" => "Stopp",
    ],

    // Nur mit JavaScript sichtbar (resources/js/chat/affordances.js).
    "action" => [
        "copy" => "Kopieren",
        "copy_done" => "Kopiert",
        "regenerate" => "Neu generieren",
        "download" => "Herunterladen",
    ],

    "error" => [
        "no_key" => "Für den Chat wird ein MetaGer-Schlüssel benötigt.",
        "unavailable" => "Der Chat ist derzeit nicht erreichbar. Bitte versuchen Sie es gleich noch einmal.",
        "generic" => "Die Nachricht konnte nicht abgeschlossen werden.",
        "file_too_large" => "Diese Datei ist zu groß. Erlaubt sind bis zu :size.",
        "file_not_text" => "Diese Datei konnte nicht gelesen werden. Bitte hängen Sie eine Textdatei an.",
        "file_failed" => "Die Datei konnte nicht hochgeladen werden. Bitte versuchen Sie es erneut.",
        "attachment_gone" => "Die angehängte Datei ist abgelaufen. Bitte hängen Sie sie erneut an.",
    ],
];
