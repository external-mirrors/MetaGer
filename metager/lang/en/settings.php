<?php
return [
    'header' => [
        '1' => 'Search Preferences',
        '2' => 'Used Search Engines',
        '3' => 'Search Filters',
        '4' => 'Black list',
    ],
    'text' => [
        '1' => 'To save your search settings, we use non-personally identifiable cookies. These are stored in plain text in your browser.',
        '2' => 'Below you can see all search engines available for this focus. You can switch them on/off by clicking on the name.',
        '3' => 'At this point you can set search filters permanently. With the selection of a search filter, only search engines are available that support this filter. Conversely, only search filters are displayed which are supported by the current search engine selection.',
        '4' => 'Here you can add domains to exclude when searching. If you want to exclude all subdomains start with "*.". One domain per line.',
    ],
    'hint' => [
        'header' => 'Restore all current settings',
        'loadSettings' => 'Here you will find a link that you can set as a home page or bookmark to restore your current settings.',
        'hint' => 'These settings affect all foci and sub-pages!',
    ],
    'disabledByFilter' => 'Disabled by Search Filter:',
    'address' => 'Address',
    'save' => 'Save',
    'reset' => 'Delete all settings',
    'back' => 'Back to the last page',
    'add' => 'Add',
    'clear' => 'Clear black list',
    'copy' => 'Copy',
    'darkmode' => 'Toggle dark mode',
    'suggestions' => [
        "label" => 'Search suggestions',
        "off" => "Disabled",
        "on" => "Enabled"
    ],
    'self_advertisements' => [
        "label" => "Subtle advertisements for our own service"
    ],
    'system' => 'System Default',
    'dark' => 'Dark',
    'light' => 'Light',
    'newTab' => 'Open results in new tabs',
    'off' => 'off',
    'on' => 'on',
    'more' => 'More Settings',
    'noSettings' => 'Currently no settings are set!',
    'allSettings' => [
        'header' => 'Settings on :root',
        'text' => 'Here you will find an overview of all settings and cookies you have set. You can delete individual entries or remove them all. Keep in mind that the associated settings will no longer be used.',
    ],
    'meaning' => 'Meaning',
    'actions' => 'Actions',
    'engineDisabled' => 'The search engine :engine will not be queried in focus :focus.',
    'inFocus' => 'in focus',
    'key' => 'Your key to the ad-free search',
    'blentry' => 'Black list entry',
    'removeCookie' => 'Remove this cookie',
    'aria' => [
        'label' => [
            '1' => 'active',
            '2' => 'deactivated',
        ],
    ],
    'metager-key' => [
        'header' => 'Advertising free search',
        'charge' => 'Credit: :token Token',
        'manage' => 'Charge key',
        'logout' => 'Remove key',
        'no-key' => 'You have not set up a key for ad-free searches.',
        'actions' => [
            'info' => 'What is it?',
            'login' => 'Set up existing key',
            'create' => 'Create new key',
        ],
    ],
    'externalservice' => [
        'heading' => 'Use an external search service',
        'description' => 'You can configure to use any of the following external search engines instead of our integrated solution. We will redirect your searches to the configured provider.'
    ],
    'disabledBecausePaymentRequired' => 'You can use the following search engines with a <a href=":link" target="_blank">MetaGer key</a>.',
    'no-engines' => 'With the current search settings, no search engine is queried.',
    'cost' => 'We calculate <strong>:cost tokens</strong> per search query with the current settings.',
    'cost-free' => 'Your searches are <strong>free</strong> with the current settings.',
    'free' => 'free',
    'enable-engine' => 'Switch on search engine',
    'disable-engine' => 'Turn off search engine',
    'filtered-engine' => 'Search engine disabled by filter',
    'payment-engine' => 'Search engine requires MetaGer key set up',
];