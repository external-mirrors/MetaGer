<?php

return [
    "title" => 'MetaGer - Help',
    "backarrow" => 'Back',
    "easy-help" => 'By clicking on the symbol <a title="For easy help, click here" href="/hilfe/easy-language/privacy-protection" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>, you will access a simplified version of the help.',

    "privacy" => [
        "title" => "Anonymity and Data Security",
        "1" => 'Tracking cookies, session IDs, and IP addresses <a title="For easy help, click here" href="/hilfe/easy-language/privacy-protection#tracking" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        "2" => 'None of these are used, stored, retained, or otherwise processed here at MetaGer (exception: short-term storage for protection against hacking and bot attacks). Because we consider this topic extremely important, we have also created ways to help you achieve the highest level of security: the MetaGer TOR Hidden Service and our anonymizing proxy server.',
        "3" => "More information can be found below. The functions are accessible under 'Services' in the navigation bar.",
    ],

    "tor" => [
        "title" => 'TOR Hidden Service <a title="For easy help, click here" href="/hilfe/easy-language/privacy-protection#torhidden" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        "1" => "For many years, MetaGer has been hiding and not storing IP addresses. However, these addresses are temporarily visible on the MetaGer server while a search is running: if MetaGer were to be compromised, an attacker could read and store your addresses. To meet the highest security requirements, we operate a MetaGer instance on the Tor network: the MetaGer TOR Hidden Service - accessible via: <a href=\"/tor/\" target=\"_blank\" rel=\"noopener\">https://metager.de/tor/</a>. To use it, you need a special browser, which you can download from <a href=\"https://www.torproject.org/\" target=\"_blank\" rel=\"noopener\">https://www.torproject.org/</a>.",
        "2" => "You can access MetaGer in the Tor browser at: http://metagerv65pwclop2rsfzg4jwowpavpwd6grhhlvdgsswvo6ii4akgyd.onion .",
    ],

    "proxy" => [
        "title" => 'Anonymizing MetaGer Proxy Server <a title="For easy help, click here" href="/hilfe/easy-language/privacy-protection#proxy" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        "1" => "To use it, you only need to click on 'OPEN ANONYMOUSLY' at the bottom of the result on the MetaGer results page. Your request will then be routed to the target website through our anonymizing proxy server, and your personal data will remain fully protected. Important: if you follow links on the pages from this point on, you will remain protected by the proxy. However, you cannot enter a new address in the address field at the top. In this case, you will lose protection. You can see whether you are still protected in the address field, which will display: https://proxy.suma-ev.de/?url=here is the actual address.",
    ],

    "maps" => [
        "title" => "MetaGer Maps",
        "1" => 'Preserving privacy in the age of global data giants has also led us to develop <a href="https://maps.metager.de" target="_blank">https://maps.metager.de</a>: the (to our knowledge) only route planner that offers full functionality via browser and app without storing user locations. All of this is verifiable because our software is open source. For using maps.metager.de, we recommend our fast app version. You can download our apps from <a href="/app" target="_blank">here</a> (or of course also from the Play Store).',
        "2" => "This map function can also be accessed from the MetaGer search (and vice versa). Once you have searched for a term in MetaGer, you will see a new search focus 'Maps' in the upper right corner. Clicking on it will take you to a corresponding map.",
        "3" => "Upon loading, the map displays the points (POIs = Points of Interest) found by MetaGer, which are also listed in the right column. When zooming, this list adapts to the map section. Hovering your mouse over a marker on the map or in the list highlights the corresponding item. Click 'Details' to get more information about that point from the database below.",
    ],

    "content" => [
        'title' => 'Questionable Content / Youth Protection <a title="For easy help, click here" href="/help/easy-language/privacy-protection#content" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        "explanation" => [
            '1' => 'I have received "hits" that I find not only annoying but also contain, in my opinion, illegal content!',
            '2' => 'If you find something on the internet that you consider illegal or harmful to minors, you can contact <a href="mailto:hotline@jugendschutz.net" target="_blank" rel="noopener">hotline@jugendschutz.net</a> by email or visit <a href="http://www.jugendschutz.net/" target="_blank" rel="noopener">www.jugendschutz.net</a> and fill out the complaint form available there. It is helpful to provide a brief note on what you consider to be inadmissible and how you came across this content. You can also report questionable content directly to us. To do so, send an email to our youth protection officer (<a href="mailto:jugendschutz@metager.de" target="_blank" rel="noopener">jugendschutz@metager.de</a>).',
        ],
    ],
];
