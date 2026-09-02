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

    /**
     * /c — App\Http\Controllers\VoucherController. Wording ported verbatim
     * from the keymanager's `campaign.json` (`enter`/`teaser`/`redeemed`/
     * `error`), except `redeemed.to_account` and `redeemed.qr_alt`, which
     * were not their own keys there.
     */
    'redeem' => [
        'enter' => [
            'heading' => 'Redeem your voucher',
            'description' => 'You received a voucher code for free MetaGer searches? Enter it here to get your personal MetaGer key.',
            'label' => 'Your voucher code',
            'submit' => 'Redeem code',
            'invalid_code' => 'This code is not valid. Please check your input.',
            'rate_limited' => 'Too many attempts. Please try again later.',
        ],
        'teaser' => [
            'heading' => 'Your MetaGer gift',
            'tokens' => 'Tokens',
            'description' => 'This code gives you your own MetaGer key charged with :tokens tokens - search the web ad-free and without being tracked.',
            'validity' => 'The key is valid for :days days after redemption.',
            'submit' => 'Get my key',
        ],
        'redeemed' => [
            'heading' => 'Here is your MetaGer key!',
            'description' => 'Your new key is charged with :tokens tokens.',
            'save' => [
                'heading' => '1. Save your key',
                'description' => 'Your key is your login - it is only shown here and cannot be recovered. Save it in your password manager, download the QR code or print this page.',
            ],
            'copy_key' => 'Copy key',
            'validity' => 'The key is valid until :date.',
            'use' => [
                'heading' => '2. Start searching',
                'description' => 'Open this link to activate the key in your browser. Bookmark it to stay logged in.',
            ],
            'copy_url' => 'Copy link',
            'start_searching' => 'Start searching now',
            'to_account' => 'Go to my account',
            'qr_alt' => 'QR code for the key',
            'no_cookies' => 'This browser does not seem to keep cookies. Save the key or the QR code above instead.',
        ],
        'error' => [
            'heading' => 'This did not work',
            'invalid_code' => 'This code does not exist. Please check your input.',
            'invalid_token' => 'This link is invalid or has expired.',
            'already_redeemed' => 'This code has already been redeemed.',
            'campaign_inactive' => 'This campaign has ended. The code can no longer be redeemed.',
            'budget_exhausted' => 'All gifts of this campaign have been given out already.',
            'rate_limited' => 'Too many attempts. Please try again later.',
            'unreachable' => 'The voucher could not be redeemed right now. Please try again later.',
            'unknown' => 'An unexpected error occurred. Please try again later.',
            'retry' => 'Enter a code',
        ],
    ],
];
