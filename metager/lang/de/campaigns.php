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
];
