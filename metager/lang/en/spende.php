<?php
return [
    'headline'      => [
        1   => 'Your Donation',
        2   => 'With your donation: you support maintenance and development of the independent search engine metager.org and its supporting association SUMA-EV. <a href=":aboutlink" rel="noopener" target=_blank>Read more</a> and <a href=":beitrittlink" target="_blank" rel="noopener">become a member.</a>',
        3   => 'How much would you like to donate?',
        4   => 'How frequent do you want to donate?',
        5   => 'Choose a payment method',
        6   => 'Bank Account Informations',
    ],
    'wunschbetrag'  => [
        'placeholder' => 'Amount in €',
        'label'       => 'Custom amount',
    ],
    'frequency'     => [
        'once'        => 'Once',
        'monthly'     => 'Monthly',
        'quarterly'   => 'Quarterly',
        'six-monthly' => 'Six-Monthly',
        'annual'      => 'Annual',
    ],
    'head'          => [
        'lastschrift' => 'Sepa direct debit',
    ],
    'ueberweisung'  => 'Bank transfer',
    'bankinfo'      => [
        1   => 'By bank transfer',
        2   => [
            1   => 'IBAN: DE64 4306 0967 4075 0332 01',
            2   => 'BIC: GENODEM1GLS',
            3   => 'Bank: GLS Gemeinschaftsbank, Bochum',
            4   => '(AN: 4075 0332 01, BC: 43060967)',
            0   => 'SUMA-EV - Association for Free Access to Knowledge.',
        ],
        3   => 'If you wish to receive a donation receipt,
please specify your full adress and (if available)
your E-Mail adress on the money transfer form.',
    ],
    'lastschrift'   => [
        'info'    => 'If you would like to donate by direct debit, please enter the information about the amount of the donation and your account information in the form below. We will then conveniently debit the specified account within the next 2 weeks.',
        'info2'   => 'Unless otherwise specified by you under Regularity, a charge will only be made once.',
        1         => 'Donate by Sepa direct debit',
        2         => 'Enter your account data. We will debit your bank account accordingly. This method is only available for SEPA area. Required fields are marked with "*"',
        '3c'      => [
            'placeholder' => 'Business Name',
        ],
        '3f'      => [
            'placeholder' => 'First Name',
        ],
        '3l'      => [
            'placeholder' => 'Last Name',
        ],
        4         => 'Your E-Mail Adress:',
        5         => 'Your phone number to verify your donation by callback:',
        6         => 'Your IBAN:',
        7         => 'Your BIC:',
        8         => [
            'message' => [
                'placeholder' => 'Message',
                'label'       => 'Here you can informally add a message to your donation:',
            ],
        ],
        10        => 'Your information is transmitted encrypted and is not read by a third party. SUMA-EV only uses your information for accounting; Your information is not passed on. Donations to the SUMA-EV are tax-deductible, because the association is recognized as charitable by the Finanzamt Hannover Nord (revenue board), listed in the register of associations, Amtsgericht Hannover under VR200033. A certificate for single donations above 300,-EUR is sent automatically (post address is required!). For donations below 300,-EUR an account current is enough for tax-deduction.',
        'private' => '*As a Person:',
        'company' => '*As a Business:',
    ],
    'paypal'        => [
        1   => 'You will be redirected where you can finalize your donation.',
        0   => 'Paypal / Credit Card',
    ],
    'submit'        => 'Donate',
    'member'        => [
        1   => 'Or rather become a member?',
        2   => 'It costs the same and gives many advantages.',
        3   => 'Ad-free usage of MetaGer',
        4   => 'Help funding MetaGer\'s development',
        5   => 'Tax deductable in Germany',
        6   => 'Voting rights in our NGO',
        7   => 'Membership form',
    ],
    'drucken'       => 'print',
    'danke'         => [
        'title'      => 'Thank you very much!! We received your donation message for MetaGer to SUMA-EV',
        'nachricht'  => 'If you submitted your contact data we will notify you personally soon.',
        'kontrolle'  => 'The following message has reached us:',
        'message'    => 'Your message',
        'schluessel' => 'As a small thank you, we offer our donors a key for advertising-free searches. <br> This can be entered by clicking on the key symbol next to the search bar. <br> Your key is:',
    ],
    'telefonnummer' => 'phone number',
    'iban'          => 'IBAN',
    'bic'           => 'BIC',
    'betrag'        => 'Amount',
    'error'         => [
        'iban'      => 'The IBAN entered does not seem to be correct. Message was not sent.',
        'bic'       => 'The IBAN entered does not belong to a country in the SEPA area. For a direct debit we need a BIC from you.',
        'amount'    => 'The donation amount entered is invalid. Please correct your entry and try again.',
        'name'      => 'It seems like they didn\'t give a name. Please try again.',
        'frequency' => 'The frequency you entered for your donation is invalid.',
        'robot'     => 'The input was not correct',
    ],
];
