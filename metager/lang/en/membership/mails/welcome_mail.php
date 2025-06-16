<?php

return [
    'subject' => "Welcome to SUMA-EV",
    'general' => "Welcome to SUMA-EV! May I first ask how you found out about us? I assume through our search engine MetaGer? Together with you, the association now has :member_count members from a wide variety of areas. Below are some further explanations and tips that are more than useful for SUMA-EV.",
    'membership' => [
        'title' => 'Membership',
        'description' => 'This e-mail is also the confirmation of your membership. Please briefly confirm that you have received this e-mail. Your membership fee in the amount of **:amount€** is due for the first time at **:due**.',
        'banktransfer' => 'Please transfer this **:interval** to the following account, stating the purpose of use (**:mandate**):',
        'directdebit' => 'We will debit this **:interval** with the mandate reference **:mandate** from your account **:iban**.',
        'paypal' => 'We will debit this **:interval** from the specified PayPal account.',
        'card' => 'We will debit this **:interval** from the specified credit card.',
        'mandate' => 'Purpose of use',
        'next_payments' => 'The next contributions',
        'due' => 'Due date',
        'amount' => 'Contribution',
        'now' => 'Now',
        'interval' => [
            'monatlich' => 'monthly',
            'vierteljährlich' => 'quarterly',
            'halbjährlich' => 'six-monthly',
            'jährlich' => 'annually'
        ]
    ],
    'websites' => [
        'title' => 'Membership',
        'description' => 'At [suma-ev.de/beitraege/](https://suma-ev.de/beitraege/) you will find many of the previous circulars and newsletters. You can find previous SUMA-EV press releases at [suma-ev.de/presse/](https://suma-ev.de/presse/). You are also very welcome to receive an entry on our [public member list](https://suma-ev.de/mitglieder/liste-unserer-mitglieder/). In this case, just let us know briefly.'
    ],
    'key' => [
        'title' => 'Searching with MetaGer',
        'extension' => 'Our web extensions for',
        'description_first' => 'To set up the search with MetaGer, you need the following key, which you can use to log in on any number of devices. It can be used immediately. You can find everything about it on our [info pages](:infos). Your new member key will be automatically topped up by us every month to the value of **€10.00** at no extra cost. The key is',
        'description_second' => 'To start, simply go to our [startpage](:startpage_link). Enter the above key there and send it by clicking on “Register with key”. You can do the same by calling up the following settings URL in your web browser:',
        'description_third' => 'If you have deactivated cookies in your browser or regularly delete all website data in your browser, we recommend installing our browser extension',
        'description_fourth' => 'In such a case, this ensures that you can continue to use MetaGer without logging in again and enables verifiable anonymity in web searches by means of [anonymous token](:anonymous_token_link).',
        'description_fifth' => 'Alternatively, you can also set up the above URL as a bookmark for quick access or have the key saved in a password manager. However, with the default settings of most browsers, you will remain permanently logged in even without a browser extension.',
        'description_sixth' => 'If everything went well, you will see the MetaGer search field and a green key symbol. Click on this key symbol once. You will then see all the properties of your key. You will also see several options for transferring your key to other devices. The number of devices is not limited. And also check the settings that you can access via the MetaGer menu; other search engines, blacklists, night mode and more.'
    ],
    'mastodon' => [
        'title' => 'Mastodon - part of a big family',
        'description_first' => 'SUMA-EV is represented in the alternative and distributed social network Mastodon with its own [account](https://suma-ev.social/@MetaGer). For this purpose, we operate an [instance](https://suma-ev.social/) on our own servers. As a member, you also have the exclusive opportunity to join the [Fediverse](https://de.wikipedia.org/wiki/Mastodon_(Software)) via this instance. You will then receive a user account that ends at @suma-ev.social.',
        'description_second' => 'The aim is to facilitate a lively exchange: for example, on all topics relating to freedom of knowledge, surveillance and privacy, but of course also on general topics that have nothing to do with SUMA-EV, MetaGer and privacy protection.',
        'description_third' => 'To get started, you are welcome to create an account at suma-ev.social with the email address **:email**.'
    ],
    'greeting' => 'Best regards'
];