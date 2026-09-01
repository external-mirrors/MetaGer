<?php

/**
 * Voucher campaigns (/konto/gutscheinaktionen) —
 * App\Http\Controllers\CampaignController.
 *
 * Taken from the keymanager's `/key/<uuid>/campaigns` area: the wording is
 * that of its `campaign.json` (`manage.*`), except `unreachable` and
 * `create.error.*` — those are new, since errors now arrive as individual
 * codes rather than pre-formatted prose (see CampaignIssuer).
 */

return [
    'heading' => 'Voucher campaigns',
    'description' => 'Give out keys from your own token balance, for example to friends or colleagues. Given-out keys deduct their tokens from your key only when they are actually used - unused gifts cost you nothing.',
    'unreachable' => 'Your voucher campaigns could not be loaded right now. Please try again later.',
    'copy_link' => 'Copy link',
    'public_link' => 'Public link',
    'delete_note' => 'Expired and disabled campaigns are deleted automatically.',
    'print_cards' => 'Print cards (PDF)',
    'disable' => 'Disable',
    'delete' => 'Delete now',

    'status' => [
        'active' => 'active',
        'disabled' => 'disabled',
        'expired' => 'expired',
    ],

    'facts' => [
        'tokens_per_key' => ':tokens tokens per key',
        'redeemed' => ':redeemed of :total redeemed',
        'budget' => ':left of :total tokens left',
        'expires' => 'ends :date',
    ],

    'create' => [
        'heading' => 'Create a campaign',
        'info' => 'The campaign is backed by this key: given-out tokens are deducted from your balance when they are used. Campaigns run for 3 months, given-out keys are valid for 1 month after redemption.',
        'name' => 'Name (only visible to you)',
        'tokens_per_key' => 'Tokens per given-out key',
        'total_volume' => 'Maximum total tokens',
        'total_volume_hint' => 'Your key currently holds :charge tokens. You can never give out more than your balance.',
        'voucher_count' => 'Number of vouchers (optional)',
        'voucher_count_hint' => 'Defaults to maximum total divided by tokens per key.',
        'submit' => 'Create campaign',
        'error' => [
            'tokens_per_key_too_high' => 'Tokens per key cannot exceed the maximum total.',
            'voucher_count_out_of_range' => 'The number of vouchers does not fit tokens per key and the maximum total.',
            'over_budget' => 'The maximum total exceeds your available balance.',
            'too_many_active' => 'You already have the maximum number of active campaigns.',
            'invalid' => 'The campaign could not be created. Please check your input.',
            'unreachable' => 'The campaign could not be created right now. Please try again later.',
        ],
    ],
];
