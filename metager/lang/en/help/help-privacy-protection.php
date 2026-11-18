<?php

return [
    "title" => 'MetaGer - Help',
    "backarrow" => 'Back',
    "easy-help" => 'By clicking on the symbol <a title="For easy help, click here" href="/hilfe/easy-language/privacy-protection" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>, you will access a simplified version of the help.',

    "privacy" => [
        "title" => "Anonymity and Data Security",
        "1" => 'Tracking cookies, session IDs, and IP addresses <a title="For easy help, click here" href="/hilfe/easy-language/privacy-protection#eh-tracking" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        "2" => 'None of these are used, stored, retained, or otherwise processed here at MetaGer (exception: short-term storage for protection against hacking and bot attacks). Because we consider this topic extremely important, we have also created ways to help you achieve the highest level of security: the MetaGer TOR Hidden Service and our Anonymous Browser.',
        "3" => "More information can be found below. The functions are accessible under 'Services' in the navigation bar.",
    ],

    "tor" => [
        "title" => 'TOR Hidden Service <a title="For easy help, click here" href="/hilfe/easy-language/privacy-protection#eh-torhidden" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        "1" => "For many years, MetaGer has been hiding and not storing IP addresses. However, these addresses are temporarily visible on the MetaGer server while a search is running: if MetaGer were to be compromised, an attacker could read and store your addresses. To meet the highest security requirements, we operate a MetaGer instance on the Tor network: the MetaGer TOR Hidden Service - accessible via: <a href=\"/tor/\" target=\"_blank\" rel=\"noopener\">https://metager.de/tor/</a>. To use it, you need a special browser, which you can download from <a href=\"https://www.torproject.org/\" target=\"_blank\" rel=\"noopener\">https://www.torproject.org/</a>.",
        "2" => "You can access MetaGer in the Tor browser at: http://metagerv65pwclop2rsfzg4jwowpavpwd6grhhlvdgsswvo6ii4akgyd.onion .",
    ],

    'proxy' => [
        'title' => 'Open anonymously (SafeBrowse) <a title="For easy help, click here" href="/hilfe/easy-language/privacy-protection#eh-proxy" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        'intro' => 'With “Open anonymously” you visit a website without it ever seeing your device. The page is opened in a browser that runs on our servers: SafeBrowse. The website only deals with that browser and learns neither your IP address nor anything about your device, your browser or your cookies.',
        'use' => [
            'title' => 'How to open a page anonymously',
            'text' => [
                'Click “Open anonymously” (the shield icon) on a result. The page opens in a new tab.',
                'If you want to open every result anonymously, turn on “Open results anonymously” in the settings. Every link of a result then goes through SafeBrowse, and “Open directly” visits a page without SafeBrowse.',
            ],
        ],
        'session' => [
            'title' => 'The session',
            'text' => [
                'The first result you open anonymously starts a session. Every further one opens in the same session, in a tab of its own. There you browse on as usual: follow links, fill in forms, type a new address or a search term into the address bar. You stay protected while doing so.',
                'A session costs tokens from your MetaGer key; how many for how long is shown before it starts. As long as you use the session, it is extended automatically. When you stop using it, it ends by itself shortly afterwards, or you end it with “Close Session”.',
                'When the session ends, the browser on our servers is deleted with everything that was in it: visited pages, cookies, logins and downloaded files. The next session starts with a new, empty browser. So save downloaded files to your device under “Downloads” before that.',
            ],
        ],
        'views' => [
            'title' => 'Two views',
            'text' => [
                '“Page” shows a copy of the website without its scripts. You read, scroll, search and copy in it as on any page; your clicks and input go to the browser on our servers. “Full browser” shows that browser as a picture, with all its tabs. Everything works there, including videos and pages that “Page” cannot show. You switch between the two in the bar at the top.',
            ],
        ],
        'nojs' => [
            'title' => 'Without JavaScript',
            'text' => [
                'Even with JavaScript turned off in your browser, “Open anonymously” opens the page in SafeBrowse. You then see a copy of the page in which links, buttons and forms work. It is reloaded after every click but does not change by itself. Videos, the page’s dialogs and uploading files do not work this way. If your browser blocks cookies, the results you open from one results page still land in the same session, also when you search on from there.',
            ],
        ],
        'limits' => [
            'title' => 'What “Open anonymously” does not do',
            'text' => [
                'The website does not recognise you by your device, but it does by what you tell it yourself: if you log in or enter personal data, it knows who you are. If you do not want to show yourself to MetaGer either, use MetaGer through Tor (see above).',
            ],
        ],
    ],

    "maps" => [
        "title" => "MetaGer Maps",
        "1" => 'Preserving privacy in the age of global data giants has also led us to develop <a href="https://maps.metager.de" target="_blank">https://maps.metager.de</a>: the (to our knowledge) only route planner that offers full functionality via browser and app without storing user locations. All of this is verifiable because our software is open source. For using maps.metager.de, we recommend our fast app version. You can download our apps from <a href="/app" target="_blank">here</a> (or of course also from the Play Store).',
        "2" => "This map function can also be accessed from the MetaGer search (and vice versa). Once you have searched for a term in MetaGer, you will see a new search focus 'Maps' in the upper right corner. Clicking on it will take you to a corresponding map.",
        "3" => "Upon loading, the map displays the points (POIs = Points of Interest) found by MetaGer, which are also listed in the right column. When zooming, this list adapts to the map section. Hovering your mouse over a marker on the map or in the list highlights the corresponding item. Click 'Details' to get more information about that point from the database below.",
    ],

    "content" => [
        'title' => 'Questionable Content / Youth Protection <a title="For easy help, click here" href="/help/easy-language/privacy-protection#eh-content" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        "explanation" => [
            '1' => 'I have received "hits" that I find not only annoying but also contain, in my opinion, illegal content!',
            '2' => 'If you find something on the internet that you consider illegal or harmful to minors, you can contact <a href="mailto:hotline@jugendschutz.net" target="_blank" rel="noopener">hotline@jugendschutz.net</a> by email or visit <a href="http://www.jugendschutz.net/" target="_blank" rel="noopener">www.jugendschutz.net</a> and fill out the complaint form available there. It is helpful to provide a brief note on what you consider to be inadmissible and how you came across this content. You can also report questionable content directly to us. To do so, send an email to our youth protection officer (<a href="mailto:jugendschutz@metager.de" target="_blank" rel="noopener">jugendschutz@metager.de</a>).',
        ],
    ],
];
