<?php

/**
 * Gutscheinaktionen (/konto/gutscheinaktionen) —
 * App\Http\Controllers\CampaignController.
 *
 * Aus dem Keymanager (`/key/<uuid>/campaigns`) hierher gezogen; der Wortlaut
 * ist der von dessen `campaign.json` (`manage.*`), bis auf `unreachable` und
 * `create.error.*` — die sind neu, weil die Fehler jetzt einzeln als Code
 * ankommen statt als vorformulierter Fließtext (siehe CampaignIssuer).
 */

return [
    'heading' => 'Gutscheinaktionen',
    'description' => 'Verschenke Schlüssel aus deinem eigenen Token-Guthaben, zum Beispiel an Freunde oder Kollegen. Verschenkte Schlüssel ziehen ihre Token erst bei tatsächlicher Benutzung von deinem Schlüssel ab – nicht genutzte Geschenke kosten dich nichts.',
    'unreachable' => 'Deine Gutscheinaktionen konnten gerade nicht geladen werden. Bitte versuche es später erneut.',
    'copy_link' => 'Link kopieren',
    'public_link' => 'Öffentlicher Link',
    'delete_note' => 'Abgelaufene und deaktivierte Kampagnen werden automatisch gelöscht.',
    'print_cards' => 'Karten drucken (PDF)',
    'disable' => 'Deaktivieren',
    'delete' => 'Jetzt löschen',

    'status' => [
        'active' => 'aktiv',
        'disabled' => 'deaktiviert',
        'expired' => 'abgelaufen',
    ],

    'facts' => [
        'tokens_per_key' => ':tokens Token pro Schlüssel',
        'redeemed' => ':redeemed von :total eingelöst',
        'budget' => ':left von :total Token übrig',
        'expires' => 'endet am :date',
    ],

    'create' => [
        'heading' => 'Kampagne erstellen',
        'info' => 'Die Kampagne wird durch diesen Schlüssel gedeckt: Verschenkte Token werden bei Benutzung von deinem Guthaben abgezogen. Kampagnen laufen 3 Monate, verschenkte Schlüssel sind nach dem Einlösen 1 Monat gültig.',
        'name' => 'Name (nur für dich sichtbar)',
        'tokens_per_key' => 'Token pro verschenktem Schlüssel',
        'total_volume' => 'Maximale Token insgesamt',
        'total_volume_hint' => 'Dein Schlüssel enthält aktuell :charge Token. Du kannst nie mehr verschenken als dein Guthaben.',
        'voucher_count' => 'Anzahl Gutscheine (optional)',
        'voucher_count_hint' => 'Standard: Maximale Token geteilt durch Token pro Schlüssel.',
        'submit' => 'Kampagne erstellen',
        'error' => [
            'tokens_per_key_too_high' => 'Token pro Schlüssel darf die maximale Gesamtmenge nicht übersteigen.',
            'voucher_count_out_of_range' => 'Die Anzahl der Gutscheine passt nicht zu Token pro Schlüssel und maximaler Gesamtmenge.',
            'over_budget' => 'Die maximale Gesamtmenge übersteigt dein verfügbares Guthaben.',
            'too_many_active' => 'Du hast bereits die maximale Anzahl aktiver Kampagnen erreicht.',
            'invalid' => 'Die Kampagne konnte nicht angelegt werden. Bitte prüfe deine Angaben.',
            'unreachable' => 'Die Kampagne konnte gerade nicht angelegt werden. Bitte versuche es später erneut.',
        ],
    ],

    /**
     * /c — App\Http\Controllers\VoucherController. Wortlaut aus dem
     * Keymanager (`campaign.json`, `enter`/`teaser`/`redeemed`/`error`), bis
     * auf `redeemed.to_account` und `redeemed.qr_alt`, die dort nicht als
     * eigener Schlüssel standen.
     */
    'redeem' => [
        'enter' => [
            'heading' => 'Gutschein einlösen',
            'description' => 'Du hast einen Gutschein-Code für kostenlose MetaGer-Suchen erhalten? Gib ihn hier ein, um deinen persönlichen MetaGer-Schlüssel zu bekommen.',
            'label' => 'Dein Gutscheincode',
            'submit' => 'Code einlösen',
            'invalid_code' => 'Dieser Code ist ungültig. Bitte überprüfe deine Eingabe.',
            'rate_limited' => 'Zu viele Versuche. Bitte versuche es später erneut.',
        ],
        'teaser' => [
            'heading' => 'Dein MetaGer-Geschenk',
            'tokens' => 'Token',
            'description' => 'Mit diesem Code erhältst du einen eigenen MetaGer-Schlüssel mit :tokens Token - suche werbefrei und ohne Tracking im Web.',
            'validity' => 'Der Schlüssel ist nach dem Einlösen :days Tage gültig.',
            'submit' => 'Schlüssel abholen',
        ],
        'redeemed' => [
            'heading' => 'Hier ist dein MetaGer-Schlüssel!',
            'description' => 'Dein neuer Schlüssel ist mit :tokens Token aufgeladen.',
            'save' => [
                'heading' => '1. Schlüssel speichern',
                'description' => 'Dein Schlüssel ist dein Login - er wird nur hier angezeigt und kann nicht wiederhergestellt werden. Speichere ihn im Passwortmanager, lade den QR-Code herunter oder drucke diese Seite aus.',
            ],
            'copy_key' => 'Schlüssel kopieren',
            'validity' => 'Der Schlüssel ist gültig bis :date.',
            'use' => [
                'heading' => '2. Lossuchen',
                'description' => 'Öffne diesen Link, um den Schlüssel in deinem Browser zu aktivieren. Speichere ihn als Lesezeichen, um angemeldet zu bleiben.',
            ],
            'copy_url' => 'Link kopieren',
            'start_searching' => 'Jetzt lossuchen',
            'to_account' => 'Zu meinem Konto',
            'qr_alt' => 'QR-Code für den Schlüssel',
            'no_cookies' => 'Dieser Browser scheint keine Cookies zu speichern. Sichere dir stattdessen den Schlüssel oder den QR-Code oben.',
        ],
        'error' => [
            'heading' => 'Das hat nicht geklappt',
            'invalid_code' => 'Diesen Code gibt es nicht. Bitte überprüfe deine Eingabe.',
            'invalid_token' => 'Dieser Link ist ungültig oder abgelaufen.',
            'already_redeemed' => 'Dieser Code wurde bereits eingelöst.',
            'campaign_inactive' => 'Diese Aktion ist beendet. Der Code kann nicht mehr eingelöst werden.',
            'budget_exhausted' => 'Alle Geschenke dieser Aktion wurden bereits vergeben.',
            'rate_limited' => 'Zu viele Versuche. Bitte versuche es später erneut.',
            'unreachable' => 'Der Gutschein konnte gerade nicht eingelöst werden. Bitte versuche es später erneut.',
            'unknown' => 'Ein unerwarteter Fehler ist aufgetreten. Bitte versuche es später erneut.',
            'retry' => 'Code eingeben',
        ],
    ],
];
