<?php
return [
    'title' => 'Removing a search result – MetaGer',
    'meta_description' => 'How to get a search result removed: the right to be forgotten, outdated content, copyright. Where a request works at Google, Brave and others.',
    'heading' => 'Removing a search result',
    'lead' => 'MetaGer is a metasearch engine: it has no index of its own, but shows what other search engines find. So a result can only really be removed at the place it comes from: the website, and the search engines whose results MetaGer shows. This page tells you where.',
    'toc' => [
        'label' => 'On this page',
    ],
    'website' => [
        'heading' => 'Contact the website itself',
        'text' => 'This is the only step that works everywhere. Once the page is deleted or changed, every search engine drops or updates it the next time it visits, and MetaGer with them, without anyone having to ask each of them.',
        'tips' => [
            'Most websites name a contact in their legal notice (imprint) or privacy policy. Ask them to delete the content, correct it, or at least remove your name.',
            'For personal data, websites in the EU have to answer a request under Art. 17 GDPR (right to erasure).',
            'If you run the website yourself: delete the page so that it answers with “404” or “410”, or mark it “noindex”. Search engines then drop it on their own.',
        ],
        'sources' => 'For results from :sources this is the only way.',
    ],
    'kind' => [
        'heading' => 'What kind of request is it?',
        'intro' => 'If the website does not help, or you cannot wait for it, the search engines have their own processes. Which one applies depends on what you want.',
        'personal_data' => [
            'summary' => 'Personal data: the right to be forgotten',
            'text' => 'A result for your name links to information about you that is inaccurate, outdated, irrelevant or excessive. Under Art. 17 GDPR you can ask a search engine to stop showing that result for searches for your name. The search engine weighs your interest against the public’s interest in the information, so not every request succeeds. Look for “Personal data” in the cards below.',
        ],
        'outdated' => [
            'summary' => 'Outdated content',
            'text' => 'The website has already removed or changed the page, but the result still shows the old title or text. This fixes itself the next time the search engine visits the page; most of them also let you ask for an earlier refresh. Look for “Outdated content” in the cards below.',
        ],
        'legal' => [
            'summary' => 'Copyright and other legal reasons',
            'text' => 'A result infringes your copyright or other rights. Search engines take such notices through their own legal forms; usually only the rights holder or someone acting for them can send one. Look for “Copyright and legal” in the cards below.',
        ],
        'illegal' => [
            'summary' => 'Illegal content',
            'text' => 'Depictions of child sexual abuse, content harmful to minors, incitement to hatred and similar content: please report them to a hotline first. They act on the content itself, at its host, and work with the police. If you also want MetaGer to stop showing such a result, send us only its address (URL) as text, by email. Please never send us the content itself, including screenshots or attachments.',
            'hotlines' => 'Hotlines:',
        ],
    ],
    'providers' => [
        'heading' => 'Ask the search engines MetaGer shows',
        'text' => 'MetaGer does not search the web itself. It asks the search engines below at the moment you search, shows what they return, and caches their answer for a few hours. So once one of them removes a result, it is also gone from what MetaGer gets from that engine.',
        'which' => 'Which search engine a result comes from is written under the result on MetaGer’s result page. One result can come from several of them; then ask each one.',
        'shows' => 'On MetaGer in:',
        'kinds' => [
            'personal_data' => 'Personal data',
            'outdated' => 'Outdated content',
            'legal' => 'Copyright and legal',
        ],
        'no_form' => 'No form, contact :name',
        'contact' => 'Contact :name',
        'servers' => 'The Mastodon servers TootNews searches',
        'google' => 'MetaGer receives Google’s results through the service Serper. What Google removes from its index is then gone from MetaGer too.',
        'brave' => 'Brave Search runs its own index. Requests about personal data go by email to privacy@brave.com with the subject “RTBF request”; the linked page says what the email needs to contain.',
        'mojeek' => 'Mojeek runs its own index and does not edit its results by hand. It removes pages for legal reasons on request; write to it through its contact page and name the exact URLs.',
        'staan' => 'Staan is the search interface of European Search Perspective. It takes requests about personal data through its own form; fill in every field and attach the supporting documents. The removal only affects the Staan index.',
        'polias' => 'Polias runs its own index and has no form. Write to hallo@polias.eu with the page’s address and the reason.',
        'pixabay' => 'Pixabay hosts the pictures itself. Use the report form or the report button on the picture’s page on Pixabay.',
        'suma' => 'TootNews is run by SUMA-EV, the association behind MetaGer, and searches posts on a few Mastodon servers. Each result shows the server it comes from; ask the people who run that server to delete or hide the post.',
        'nolm' => 'Nolm is SUMA-EV’s own search engine, separate from MetaGer. Nolm searches the Open Web Index. Request the removal with the Open Web Index’s takedown form: what is removed there is also gone from Nolm.',
        'website' => 'BASE shows what the repositories it searches hold. Once the repository removes a record, it disappears from BASE.',
    ],
];
