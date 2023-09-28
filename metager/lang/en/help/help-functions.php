<?php

return [
    "urls" => [
        'title' => 'Exclude URLs',
        'explanation' => 'You can exclude search results that contain specific words in their result links by using "-url:" in your search.',
        'example_b' => '<i>my search</i> -url:dog',
        'example_a' => 'Example: You want to exclude results where the word "dog" appears in the result link:',
    ],
    'title' => 'MetaGer - Help',
    "selist" => [
        'title' => 'Add MetaGer to Your Browser\'s Search Engine List <a title="For easy help, click here" href="/hilfe/easy-language/functions#eh-selist"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        'explanation_b' => 'Some browsers require you to enter a URL; it should be "https://metager.de/meta/meta.ger3?input=%s" without quotation marks. You can generate the URL yourself by searching with metager.de for something, then replacing what is behind "input=" in the address bar with %s. If you still have any problems, please contact us: <a href="/kontalt" target="_blank" rel="noopener">Contact Form</a>',
        'explanation_a' => 'Please try first to install the current plugin. To install, simply click on the link directly below the search box. Your browser should have already been detected there.',
    ],
    
    "searchfunction" => [
        "title" => "Search Functions"
    ],
    "stopwords" => [
        "title" => 'Stopwords <a title="For easy help, click here" href="/hilfe/easy-language/functions#eh-stopwordsearch"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        "3" => "car new -bmw",
        "2" => "Example: You are looking for a new car, but definitely not a BMW. Your input would be:",
        "1" => "If you want to exclude search results in MetaGer that contain specific words (exclusion words / stopwords), you can do so by prefixing these words with a minus sign.",
    ],
    "key"    => [
        "title" => 'Add MetaGer Key <a title="For easy help, click here" href="/hilfe/easy-language/functions#eh-keyexplain"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        "1" => 'The MetaGer key is automatically set up in your browser and used. You don\'t need to do anything else. If you want to use the MetaGer key on other devices, there are several ways to set up the MetaGer key:',
        "2"=>'Login Code <br>On the management page of the MetaGer key, you can use the login code to add your key to another device. Simply enter the six-digit number code when logging in. The login code can only be used once and is only valid as long as the window is open.',
        "3"=>'Copy URL <br>When you are on the management page of the MetaGer key, there is an option to copy a URL. This URL can be used to save all MetaGer settings, including the MetaGer key, on another device.',
        '4'=>'Save File <br>When you are on the management page of the MetaGer key, there is an option to save a file. This saves your MetaGer key as a file. You can then use this file on another device to log in with your key.',
        '5'=>'Scan QR Code <br>Alternatively, you can also scan the QR code displayed on the management page to log in with another device.',
        '6'=>'Manually Enter MetaGer Key <br>You can also manually enter the key on another device.',
        'colors'=> [
            'title'=>'Colored MetaGer Key',
            '1'=>'To easily recognize whether you are searching ad-free, we have given our key symbol colors. Below are explanations for the corresponding colors:',
            'grey'=>'Grey: You have not set up a key. You are using the free search.',
            'red'=>'Red: If your key symbol is red, it means this key is empty. You have used up all ad-free searches. You can recharge the key on the key management page.',
            'green'=>'Green: If your key symbol is green, then you are using a charged key.',
            'yellow'=>'Yellow: If you see a yellow key, you still have a balance of 30 tokens. Your searches are running out. It is recommended to recharge the key soon.',
        ],
    ],
    "multiwordsearch" => [
        "title" => 'Multi-Word Search <a title="For easy help, click here" href="/hilfe/easy-language/functions#eh-severalwords"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        "4" => [
            "example" => '"the round table"',
            "text" => "With a phrase search, you can search for word combinations instead of individual words. Simply enclose the words that should appear together in quotation marks.",
        ],
        "3" => [
            "example" => '"the" "round" "table"',
            "text" => "If you want to make sure that words from your search also appear in the results, you need to enclose them in quotation marks.",
        ],
        "2" => "If this is not enough for you, you have 2 options to make your search more precise:",
        "1" => "When searching for more than one word in MetaGer, we automatically try to provide results where all the words appear or come as close as possible.",
    ],
    "exactsearch" =>[
        "title" => 'Exact Search <a title="For easy help, click here" href="/hilfe/easy-language/functions#exactsearch"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        "1" =>"If you want to find a specific word in the MetaGer search results, you can prefix that word with a plus sign. When using a plus sign and quotation marks, a phrase is searched exactly as you entered it.",
        "2" =>"Example: S",
        "3" =>'Example: ',
        "example" => [
            "1" => "+exampleword",
            "2" => '+"example phrase"',
        ],
    ],
    "bang"  => [
        "title" => '!bangs <a title="For easy help, click here" href="/hilfe/easy-language/functions#eh-bangs"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        "1" => "MetaGer supports to a limited extent a writing style often referred to as '!bang' syntax.<br>A '!bang' always starts with an exclamation mark and does not contain spaces. Examples include '!twitter' or '!facebook'.<br>When a supported !bang is used in the search query, a entry appears in our quick tips, allowing you to continue the search with the respective service (Twitter or Facebook) at the push of a button.",
        "2" => 'Why are !bangs not opened directly?',
        "3" => 'The !bang "redirects" are part of our quick tips and require an additional "click." This was a difficult decision for us, as it makes !bangs less useful. However, it is unfortunately necessary because the links to which the redirection occurs do not originate from us but from a third-party, DuckDuckGo.<p>We always ensure that our users retain control at all times. Therefore, we protect in two ways: First, the entered search term is never transmitted to DuckDuckGo, only the !bang. Second, the user explicitly confirms the visit to the !bang target. Unfortunately, due to staffing reasons, we cannot currently check or maintain all these !bangs ourselves.',
    ],
    "backarrow" => 'Back',
    "easy-help"=> 'By clicking on the symbol <a title="For easy help, click here" href="/hilfe/easy-language/functions"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>, you will access a simplified version of the help.',
];
