<?php

/*
|--------------------------------------------------------------------------
| Where a search result can actually be removed
|--------------------------------------------------------------------------
|
| MetaGer shows other engines' results. Taking a result off MetaGer alone
| removes it from one small engine; removing it at the provider whose index
| MetaGer shows also removes it here. This file says, for every engine in
| config/foki.json, which provider that is and where its removal forms live.
|
| Read by the guide page (/entfernen, App\Http\Controllers\RemovalController)
| and, through the engine keys, by suma-crm: POST /api/blocklist/check
| answers with `engines[].name`, which is a key of `engines` below, and the
| reply to a removal request links to /entfernen#<anchor> of its provider
| (docs/blocklist-api.md). So an anchor is a public URL: renaming one breaks
| links already sent out in replies.
|
| Tests\Feature\RemovalPageTest fails when an engine in config/foki.json has
| no entry here, and when an anchor is missing from the page.
|
| The page shows a provider only while one of its engines is not switched off
| in its parser (CONFIG_OVERLOAD `disabled`). Switched-off engines (Yandex,
| One News Page) stay mapped here, so their provider comes back by itself
| when the engine does, and suma-crm can still name them.
|
| An engine mapped to null is one SUMA-EV no longer uses (eBay, TUBdok, the
| Minisucher), even though its parser and foki.json entry are still there.
| It gets no card and sends nobody anywhere.
|
| `type`:
|   - `provider`: a search index or platform with its own removal process.
|     One card per provider on the page.
|   - `website`: the result comes straight from the website or repository
|     that holds the content; there is nobody in between to ask. Its anchor
|     is the page's first step, "contact the website".
|   - `metager`: a source SUMA-EV runs itself; the right address is us.
|
| `links`: one URL per kind of request, or null where the provider has no
| process for it. A null falls back to `contact` on the page.
|   - `personal_data`: personal data / right to be forgotten (GDPR Art. 17)
|   - `outdated`:      the page changed or is gone, the result still shows it
|   - `legal`:         copyright and other legal reasons
|
| `note` is a translation key, so the page can say it in the visitor's
| language. URLs were checked by hand in November 2026; providers move these
| pages around, so a dead one is worth a look before assuming the form is gone.
|
*/

return [
    // Every engine key in config/foki.json => a provider below.
    'engines' => [
        // web
        'brave' => 'brave',
        'serper_web' => 'google',
        'mojeek' => 'mojeek',
        'yandex' => 'yandex',
        'staan' => 'staan',
        // bilder
        'brave_images' => 'brave',
        'serper_images' => 'google',
        'pixabay' => 'pixabay',
        // nachrichten
        'brave_news' => 'brave',
        'serper_news' => 'google',
        'onenewspage' => 'onenewspage',
        'onenewspagegermany' => 'onenewspage',
        'tootnews' => 'suma',
        // produkte
        'serper_shopping' => 'google',
        'ebay' => null,
        // science
        'minism_science' => null,
        'tuhh' => null,
        'BASE' => 'website',
    ],

    'providers' => [
        // Serper is an API onto Google's results, so a removal at Google is
        // a removal from every serper_* engine.
        'google' => [
            'type' => 'provider',
            'name' => 'Google',
            'anchor' => 'google',
            'links' => [
                // The EU privacy (right to be forgotten) form.
                'personal_data' => 'https://reportcontent.google.com/forms/rtbf',
                // Explains and links the "Refresh outdated content" tool.
                'outdated' => 'https://support.google.com/websearch/answer/6349986',
                // "Report Content for Legal Reasons"; copyright starts here too.
                'legal' => 'https://support.google.com/legal/answer/3110420',
            ],
            'contact' => null,
            'note' => 'removal.providers.google',
        ],
        'brave' => [
            'type' => 'provider',
            'name' => 'Brave Search',
            'anchor' => 'brave',
            'links' => [
                // RTBF requests go by email (privacy@brave.com); this page
                // says what the email has to contain.
                'personal_data' => 'https://search.brave.com/help/brave-search-index-right-to-be-forgotten',
                'outdated' => null,
                'legal' => 'https://search.brave.com/help/copyright-dmca',
            ],
            'contact' => 'https://search.brave.com/help/contact',
            'note' => 'removal.providers.brave',
        ],
        'yandex' => [
            'type' => 'provider',
            'name' => 'Yandex',
            'anchor' => 'yandex',
            'links' => [
                'personal_data' => 'https://yandex.com/support/abuse/en/troubleshooting/oblivion',
                // "Information was removed from the source website but still
                // appears in Yandex search results".
                'outdated' => 'https://yandex.com/support/abuse/en/troubleshooting/search/default',
                'legal' => null,
            ],
            'contact' => 'https://yandex.com/support/abuse/en/',
            'note' => 'removal.providers.yandex',
        ],
        // Mojeek does not curate its results and has no form; it removes for
        // legal reasons on request through its contact page.
        'mojeek' => [
            'type' => 'provider',
            'name' => 'Mojeek',
            'anchor' => 'mojeek',
            'links' => [
                'personal_data' => null,
                'outdated' => null,
                'legal' => null,
            ],
            'contact' => 'https://www.mojeek.com/about/contact',
            'note' => 'removal.providers.mojeek',
        ],
        // Staan is European Search Perspective's API. Its own form takes
        // personal data (GDPR Art. 17) and removes from the Staan index
        // only; for illegal content it points to Qwant's page.
        'staan' => [
            'type' => 'provider',
            'name' => 'Staan',
            'anchor' => 'staan',
            'links' => [
                'personal_data' => 'https://staan.ai/removal',
                'outdated' => null,
                'legal' => 'https://help.qwant.com/en/docs/overview/how-to-report-unlawful-content/',
            ],
            'contact' => null,
            'note' => 'removal.providers.staan',
        ],
        'pixabay' => [
            'type' => 'provider',
            'name' => 'Pixabay',
            'anchor' => 'pixabay',
            'links' => [
                // Pixabay's terms (section 10) send every report here, or to
                // the report button on the image's own page.
                'personal_data' => 'https://pixabay.com/content-reports/',
                'outdated' => null,
                'legal' => 'https://pixabay.com/content-reports/',
            ],
            'contact' => 'https://pixabay.com/service/about/',
            'note' => 'removal.providers.pixabay',
        ],
        // A news aggregator: one form for removal, correction and DMCA.
        'onenewspage' => [
            'type' => 'provider',
            'name' => 'One News Page',
            'anchor' => 'onenewspage',
            'links' => [
                'personal_data' => 'https://www.onenewspage.com/contact/?type=removal',
                'outdated' => 'https://www.onenewspage.com/contact/?type=removal',
                'legal' => 'https://www.onenewspage.com/contact/?type=removal',
            ],
            'contact' => null,
            'note' => 'removal.providers.onenewspage',
        ],
        // TootNews/TroetNews is run by SUMA-EV. The content still lives on
        // a Mastodon instance, so the website comes first here too.
        'suma' => [
            'type' => 'metager',
            'name' => 'SUMA-EV (TootNews)',
            'anchor' => 'suma-ev',
            'links' => [
                'personal_data' => null,
                'outdated' => null,
                'legal' => null,
            ],
            'contact' => null,
            'note' => 'removal.providers.suma',
        ],
        // BASE shows what the repositories it harvests hold. Once the
        // repository removes a record, it drops out of BASE.
        'website' => [
            'type' => 'website',
            'name' => 'BASE',
            'anchor' => 'website',
            'links' => [
                'personal_data' => null,
                'outdated' => null,
                'legal' => null,
            ],
            'contact' => null,
            'note' => 'removal.providers.website',
        ],
    ],

    // Illegal content is not a removal request but a report. These take it.
    'hotlines' => [
        'jugendschutz' => [
            'name' => 'jugendschutz.net',
            'url' => 'https://www.jugendschutz.net/verstoss-melden',
        ],
        'internet-beschwerdestelle' => [
            'name' => 'Internet-Beschwerdestelle (eco & FSM)',
            'url' => 'https://www.internet-beschwerdestelle.de/de/index.html',
        ],
        'inhope' => [
            'name' => 'INHOPE',
            'url' => 'https://www.inhope.org/EN',
        ],
    ],
];
