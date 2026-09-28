<?php

return [
    'title' => 'Privacy policy',
    'introduction' => 'For maximum transparency, we list what data we collect from you and how we use it. The protection of your data is important to us and it should be to you too. <strong>Please read this statement carefully; it is in your interest.</strong>',
    'responsible_party' => [
        'title' => 'Responsible persons and contact persons',
        'description' => 'MetaGer and related services are operated by <a href="https://suma-ev.de">SUMA-EV</a>, which is also the author of this statement. In this statement, “we” generally means SUMA-EV. You can find our contact details in our <a href=":link_impress">Imprint</a>. We can be reached by email using our <a href=":link_contact">contact form</a>.'
    ],
    'principles' => [
        'title' => 'Principles',
        'description' => 'As a non-profit association, we are committed to free access to knowledge. Since we know that free research is not compatible with mass surveillance, we also take data protection very seriously. We have always only processed the data that is absolutely necessary for the operation of our services. Data protection is always our standard. We do not operate profiling – i.e. the automatic creation of user profiles.'
    ],
    'contexts' => [
        'title' => 'Incoming data by context',
        'metager' => [
            'title' => 'Use of the web search engine MetaGer',
            'description' => 'When using our web search engine MetaGer via its web form or through its OpenSearch interface, the following data is generated:',
            'query' => 'As an integral part of metasearch, the search query is transmitted to our partners to obtain search results for display on the results page. The results received, including the search term, are kept for display for a few hours.',
            'preferences' => 'We use this data (e.g. language settings) to answer the respective search query. We store some of this data on a non-personal basis for statistical purposes.',
            'suggest' => 'If search suggestions are enabled for the address bar of your browser (optional) we need to store your suggestion settings on our server. The key will be a concatenated string of your IP Address, your useragent and your browsers accept-language header which is sha1 hashed. If the setting is disabled (default) nothing will be stored.'
        ],
        'contact' => [
            'title' => 'Use of the contact form',
            'description' => 'When using the MetaGer contact form, the following data is generated, which we store for reference purposes up to 2 months after the completion of your request:',
            'contact' => 'Will be stored for reference purposes up to 2 months after completion of your request.',
        ],
        'donate' => [
            'title' => 'Use of the donation form',
            'description' => 'The following data transmitted in the donation form will be stored for 2 months for processing:',
            'contact' => 'We use this data exclusively for possible queries and under no circumstances pass it on to third parties.',
            'payment' => 'The payment details will only be used to process the donation and will not be passed on to third parties under any circumstances. For tax reasons, we are obliged to keep and therefore save this data for 10 years. They will then be automatically deleted and will not otherwise be processed further.',
            'message' => 'The message you enter here will be transmitted to us and taken into account when processing your donation.',
        ],
        'key' => [
            'title' => 'Checkout MetaGer key',
            'contact' => 'We use this data exclusively for possible queries or for invoicing and under no circumstances pass it on to third parties.',
            'payment' => 'The payment details will only be used to process the donation and will not be passed on to third parties under any circumstances. For tax reasons, we are obliged to keep and therefore save this data for 10 years. They will then be automatically deleted and will not otherwise be processed further.',
        ],
        'membership' => [
            'title' => 'Applying for and managing a SUMA-EV membership',
            'description' => 'When applying for a SUMA-EV membership, and for the duration of your membership, the following data is generated:',
            'contact' => 'For individuals, name and email address; for organisations, the organisation\'s name, its approximate size, and the name and email address of a contact person. A postal address is optional and, if provided, is used in particular to issue a donation receipt. We do not pass this data on to third parties under any circumstances.',
            'payment' => 'We use your payment details exclusively for the recurring collection of your membership fee. How we process them, who receives them and how long we keep them is described under "Payment processing".',
            'reduction' => 'If you apply for a reduced membership fee, we process the proof you upload (e.g. a notice of transfer payments) exclusively to verify your entitlement to the reduction. Such proof may contain particularly sensitive data under Art. 9 GDPR; we therefore only store it for as long as this verification requires, and do not pass it on to third parties.',
            'crm' => 'To manage memberships, we use our own membership administration system, also operated by SUMA-EV. The data listed above is stored there for the duration of your membership and afterwards for the duration of the statutory retention periods. Your membership is also linked to a MetaGer key, through which your membership fee is credited as usage credit for MetaGer (see "Checkout MetaGer key").',
            'cookies' => 'The application form and the member portal set no cookies when you merely open them. Only once you submit a form or sign in to the member portal do we set a technically necessary session cookie, so that we can, for example, show you input errors or keep you signed in. It becomes invalid after two hours of inactivity and is deleted when you sign out. If you open the form through a link that states a language different from your browser\'s language setting, we remember that language in a cookie that is deleted when you close your browser. The legal basis is Section 25(2) no. 2 TDDDG.',
        ],
        'payments' => [
            'title' => 'Payment processing',
            'description' => 'Payments to SUMA-EV – currently membership fees, in future also donations and MetaGer keys – are processed by our own payment system, also operated by SUMA-EV. The following data is generated:',
            'ip' => 'Only when paying by SEPA direct debit: as part of the record of your direct debit mandate (see below). Otherwise it is not stored.',
            'useragent' => 'Only when paying by SEPA direct debit: as part of the record of your direct debit mandate (see below). Otherwise it is not stored.',
            'contact' => 'We receive your name and email address from the service you are paying for (e.g. the membership application). We use them for emails about your payment, such as bank transfer details, payment links or payment reminders.',
            'payment' => 'Depending on the payment method, account holder and IBAN, a reference to your PayPal account or your card at the respective provider, and the amount, time and status of each payment.',
            'methods' => 'We process SEPA direct debits and bank transfers ourselves; direct debits are submitted to our bank with account holder, IBAN, amount and mandate reference. When paying with PayPal, you enter your details directly with PayPal (Europe) S.à r.l. et Cie, S.C.A.; only once you choose PayPal as the payment method does the payment page load program code from PayPal, which gives PayPal your IP address and allows it to set its own cookies. When paying by card or Wero, you enter your details directly with VR Payment GmbH. We never receive card numbers or PayPal or Wero login details, only a reference that lets us collect recurring fees. The legal basis is Art. 6(1)(b) GDPR.',
            'mandate' => 'If you give us a SEPA direct debit mandate online, we keep a record of the mandate\'s content (account holder, IBAN, mandate reference, amount and interval, the mandate text shown to you) together with the time it was given, your IP address and your user agent. We need this record to prove to our bank that you gave the mandate if a direct debit is disputed; only in that case do we show it to the bank. The legal basis is Art. 6(1)(b) GDPR and our legitimate interest in being able to prove the mandate (Art. 6(1)(f) GDPR).',
            'cookies' => 'The payment pages themselves set no cookies when you open them. Only if your input is incomplete or invalid do we briefly set a technically necessary session cookie to show you the errors; it is deleted as soon as they have been shown (Section 25(2) no. 2 TDDDG).',
            'retention' => 'If a payment does not go through because you abandon it, we delete your name and email address one week after the payment process expired. If, for example, a membership application is declined before anything was collected, we delete your payment details and the mandate record immediately. All other payment details and mandate records are kept for as long as the payment relationship exists and afterwards for the duration of the statutory retention periods (up to 10 years).',
        ],
        'suma' => [
            'title' => 'Use of the website <a href="https://suma-ev.de">suma-ev.de</a>',
            'description' => 'When visiting websites of the domain "suma-ev.de", the following data is collected and stored for up to one week:',
            'function' => 'When visiting websites of the domain "suma-ev.de", the following data is collected and stored for up to one week:',
            'other' => 'On the other websites of our domains, we only process the data collected to answer inquiries and within the scope of the other points of this data protection declaration.',
            'startpage' => 'On the start page of our MetaGer service, we use the user agent you have transmitted to show you the appropriate plug-in installation instructions for your browser.',
        ],
        'newsletter' => [
            'title' => 'Register for the SUMA-EV newsletter',
            'description' => 'In order to keep you informed about our activities, we offer an e-mail newsletter. We store the following data until you unsubscribe:',
            'contact' => 'We use this data exclusively to send you our newsletter and under no circumstances pass it on to third parties.',
        ],
        'maps' => [
            'title' => 'Use of Maps.MetaGer.de',
            'description' => 'When using the MetaGer map service, the following data is generated:',
            'ip' => 'We are using your IP address to estimate a good starting location to focus the map on initially. To do so your IP address is processed locally. The results are not stored anywhere and will be immediately deleted after your request.'
        ],
        'proxy' => [
            'title' => 'Use of the anonymizing proxy',
            'description' => 'When using the anonymizing proxy, the following data is generated:',
        ],
        'quote' => [
            'title' => 'Use of the citation search',
            'description' => 'The search term entered is used to search for results in the citation database. In contrast to web searches with MetaGer, it is not necessary to pass on the search term to third parties because the citation database is located on our server. Other data will not be saved or passed on.',
        ],
        'asso' => [
            'title' => 'Use of the Associator',
            'description' => 'The associator uses the search term to determine and display the terms associated with it. Other data will not be saved or passed on.',
        ],
        'mapsapp' => [
            'title' => 'Use of the MetaGer app',
            'description' => 'Using the MetaGer app is the same as using MetaGer via a web browser.',
        ],
        'plugin' => [
            'title' => 'Use of the MetaGer plugin',
            'description' => 'When using the MetaGer plugin, the following data is generated:',
        ]
    ],
    'hosting' => [
        'title' => 'Hosting',
        'description' => 'Our services are administered by us, the SUMA-EV, and operated on hardware rented from Hetzner Online GmbH.',
    ],
    'monitoring' => [
        'title' => 'Error Tracking and Application Monitoring',
        'description' => 'To ensure the reliability of our services, we use the open-source error tracking tool GlitchTip, both in the backend and the frontend of our applications. GlitchTip runs exclusively on our own infrastructure; no data is shared with or transmitted to any third-party analytics or monitoring provider.',
        'collected' => [
            'title' => 'What data is generated',
            'description' => 'When an error occurs in our web application or on our servers, we automatically collect:',
            'error' => 'An error description and a stack trace identifying the affected location in the code.',
            'useragent' => 'Browser type, operating system, and approximate device type, determined from your user agent.',
            'url' => 'The address (URL) of the page or function where the error occurred.',
        ],
        'not_collected' => [
            'title' => 'What data is not generated',
            'description' => 'Your IP address is removed before it is ever transmitted to GlitchTip and never reaches the system. We do not associate error reports with any individual or user account – not even for logged-in users. We also do not collect passwords, payment details, or the content of any forms or messages you submit through our services.',
        ],
        'retention' => 'We store error reports for 30 days; they are then automatically deleted.',
        'base' => 'The legal basis for this processing is our legitimate interest in operating our services reliably and securely (Art. 6 (1) (f) GDPR).',
    ],
    'description' => [
        'title' => 'Description of resulting data',
        'ip' => [
            'title' => 'Internet protocol address',
            'description' => 'The Internet protocol address (hereinafter referred to as IP) is mandatory in order to use web services such as MetaGer. This IP, in combination with a date – similar to a telephone number – clearly identifies an Internet access and its owner. In general, the first three (of a total of four) blocks of an IP are not personal. If rear blocks of the IP are shortened, the shortened address identifies the approximate geographical area around the Internet connection.',
            'example_full' => 'Examples (full IP address)',
            'example_partial' => 'Examples (first two blocks only)'
        ],
        'useragent' => [
            'title' => 'User agent identifier',
            'description' => 'When you call up a website, your browser automatically sends an identifier, usually with data about the browser and operating system used. This browser identifier (the so-called user agent) can be used by websites, for example, to recognize mobile devices and present them with a customized output.',
            'example' => 'Example'
        ],
        'payment' => [
            'title' => 'Payment Details',
            'description' => 'When purchasing a MetaGer key, different payment data are required depending on the payment provider',
            'examples' => 'Examples',
            'name' => 'Max Mustermann, mail@example.com',
            'card' => 'Last digits of the credit card number',
        ],
        'query' => [
            'title' => 'Entered search query',
            'description' => 'Search terms entered are absolutely necessary for a web search. As a rule, no personal data can be obtained from them; among other things, because they do not have a fixed structure.',
            'examples' => 'Examples',
            'example_1' => 'water consumption shower',
            'example_2' => 'Lyrics On a tree a cuckoo',
        ],
        'preferences' => [
            'title' => 'User Preferences',
            'description' => 'In addition to form data and user agents, the browser often transfers other data. This includes language selection, search settings, accept headers, do not track headers and more.',
        ],
        'contact' => [
            'title' => 'Contact Details',
            'description' => 'Hierunter fällt der von Ihnen angegebene Name (Vor- und Nachname), sowie Ihre E-Mail Adresse. Diese Daten nutzen wir ausschließlich, um Ihnen zu antworten und geben Sie unter keinen Umständen weiter an Dritte.'
        ],
        'message' => [
            'title' => 'Message',
            'description' => 'The message entered here will be transmitted to us and used to process your request.',
        ],
        'reduction' => [
            'title' => 'Proof for a reduced membership fee',
            'description' => 'A document you upload to prove your entitlement to a reduced membership fee, for example a notice confirming receipt of transfer payments.',
        ],
        'error' => [
            'title' => 'Error Report (Stack Trace)',
            'description' => 'When a technical error occurs in our application, an error description together with a stack trace is automatically generated. This shows us where in the source code the error occurred so that we can fix it.',
        ]
    ],
    'base' => [
        'title' => 'Legal basis for processing',
        'description' => 'The legal basis for the processing of your personally identifiable data is either Art. 6 (1) (a) GDPR if you consent to the processing by using our services, or Art. 6 (1) (f) GDPR if the processing is necessary to protect our legitimate interests, or another legal basis if we notify you separately.',
    ],
    'rights' => [
        'title' => 'Your rights as a user (and our obligations)',
        'description' => 'So that you can also protect your personal data, we clarify (according to Art. 13 DSGVO) that you have the following rights:',
        'information' => [
            'title' => 'Right Of Providing Information',
            'description' => 'You have the right (Art. 15 GDPR) to request information from us at any time as to whether and if so which of your data we (metager.de and SUMA-EV) have about you. We will send you as soon as possible, i.e. within a few days, a complete copy of the data we have stored or otherwise stored about you in accordance with Article 15 Paragraph 3 Subsection 1 GDPR. We prefer the electronic method for this in accordance with Article 15 Paragraph 3 Subparagraph 3 GDPR; For this purpose, we will save your email address for the duration of the processing. Please inform us if you specifically want the information in paper form.',
        ],
        'correction' => [
            'title' => 'Right To Correction And Supplementation',
            'description' => 'According to Article 16 GDPR. If we have stored incorrect data about you, you can request that this be corrected. This also applies to missing components, here you have the right to supplement.',
        ],
        'deletion' => [
            'title' => 'Right to Erasure',
            'description' => 'According to Article 17 GDPR',
        ],
        'processing' => [
            'title' => 'Right To Restriction Of Processing',
            'description' => 'Pursuant to Article 18 GDPR; For example, if you have asked us to delete or change data about you, you can impose a processing ban on us for the time it takes us to do so. This is possible regardless of whether we ultimately change, delete, etc. the data in question.',
        ],
        'complaint' => [
            'title' => 'Right To Complain',
            'description' => 'According to Article 13 paragraph 2 letter d) GDPR you can complain about us to the data protection officer of the state of Lower Saxony. Online: <a href="https://www.lfd.niedersachsen.de/startseite/">Data Protection Officer</a>',
        ],
        'opposition' => [
            'title' => 'Right To Object To Processing',
            'description' => 'According to Article 21 GDPR; for example, if you are on a list and want to be there, you can still prohibit the processing or further processing of that data.',
        ],
        'portability' => [
            'title' => 'Right To Data Portability',
            'description' => 'According to Article 20 GDPR; This means that we are obliged to provide you with the requested data in a legible, possibly machine-readable or customary manner so that you would be able to make the data accessible to another person as it is (to to transfer).',
        ],
        'obligation_notify' => [
            'title' => 'Obligation to notify in connection with the correction or deletion of personal data or the restriction of processing:',
            'description' => 'According to Article 19 GDPR; if we should have made data that you have entrusted to us accessible to third parties (which we never do), we would be obliged to inform them that we would, at your request, delete, change, etc. have performed.',
        ],
        'perception' => 'To exercise these rights, it is sufficient to contact us using our <a href=":contact_link">contact form</a></b>. If you prefer the letter form, send us mail to our office address:',
    ],
    'changes' => [
        'title' => 'Changes to this Statement',
        'description' => 'Like our offers, this data protection declaration is also subject to constant change. You should therefore read them again regularly.',
        'date' => 'This version of our privacy policy is dated: :date',
    ],
    'data' => [
        'ip' => 'IP-Address',
        'useragent' => 'User-Agent',
        'query' => 'Search Query',
        'preferences' => 'User Preferences',
        'contact' => 'Contact Details',
        'message' => 'Message',
        'payment' => 'Payment Data',
        'reduction' => 'Reduction Proof',
        'error' => 'Error Report',
        'referrer' => 'the referrer you sent',
        'gps' => 'Location Data',
        'optional' => 'optional',
        'unused' => 'Will not be saved or shared.'
    ]
];