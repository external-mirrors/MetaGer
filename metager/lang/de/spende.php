<?php

return [
    'headline.1' => 'Ihre Spende',
    'headline.2' => 'Mit Ihrer Spende unterstützen Sie den Erhalt und die Weiterentwicklung der unabhängigen Suchmaschine metager.de und die Arbeit des gemeinnützigen Trägervereins SUMA-EV. <a href=":aboutlink" rel="noopener" target=_blank>Mehr erfahren</a> und <a href=":beitrittlink" target="_blank" rel="noopener">Mitglied werden.</a>.',

    'headline.3' => 'Welchen Betrag möchten Sie spenden?',

    'breadcrumps' => [
        'amount' => 'Betrag wählen',
        'payment_method' => 'Zahlungsart wählen',
        'payment_interval' => 'Zahlungsinterval wählen'
    ],

    'amount' => [
        'description' => 'Wählen Sie nachfolgend den Betrag, welchen Sie spenden möchten. Möchten Sie die Spende mit einer SEPA/SWIFT Banküberweisung durchführen? Dann finden Sie alternativ unsere Kontoverbindung unten auf dieser Seite.',
        'custom' => 'Wunschbetrag',
        'taxes' => 'Spenden an den <a href="https://suma-ev.de">SUMA-EV</a> sind steuerlich absetzbar, da der Verein vom Finanzamt Hannover Nord als gemeinnützig anerkannt ist, eingetragen in das Vereinsregister beim Amtsgericht Hannover unter VR200033.',
        'banktransfer' => [
            'title' => "Unsere Kontoverbindung"
        ],
        'membershiphint' => [
            'title' => 'Oder vielleicht Mitglied werden?',
            'description' => 'Als Mitglied im <a href="https://suma-ev">SUMA-EV</a> können Sie MetaGer werbefrei verwenden und erhalten Zugriff auf alle kostenpflichtigen Suchmaschinen.'
        ]
    ],
    'interval' => [
        'heading' => 'Darf es eine regelmäßige Spende sein?',
        'frequency' => [
            'once' => 'Einmalig',
            'monthly' => 'Monatlich',
            'quarterly' => 'Vierteljährlich',
            'six-monthly' => 'Halbjährlich',
            'annual' => 'Jährlich'
        ]
    ],

    'payment-method' => [
        'heading' => 'Wie möchten Sie die Zahlung durchführen?',
        'methods' => [
            'banktransfer' => 'Banküberweisung',
            'directdebit' => 'Lastschrift',
            'paypal' => 'PayPal',
            'venmo' => 'Venmo',
            'itau' => 'Itau',
            'credit' => 'Kredit',
            'paylater' => 'Später zahlen',
            'applepay' => 'Applepay',
            'ideal' => 'IDEAL',
            'sepa' => 'Lastschrift',
            'bancontact' => 'Bancontact',
            'giropay' => 'Giropay',
            'eps' => 'EPS',
            'sofort' => 'SOFORT',
            'mybank' => 'MyBank',
            'blik' => 'BLIK',
            'p24' => 'P24',
            'wechatpay' => 'WeChatPay',
            'payu' => 'Payu',
            'trustly' => 'Trustly',
            'oxxo' => 'Oxxo',
            'boleto' => 'Boleto',
            'boletobacario' => 'Boletobancario',
            'mercadopago' => 'Mercadopago',
            'mulitbanco' => 'Multibanco',
            'satispay' => 'Satispay',
            'paidy' => 'Paidy',
            'card' => 'Kredit-/Debitkarte'
        ]
    ],

    'execute-payment' => [
        'heading' => 'Zahlung abschließen',
        'item-name' => 'Spende an den SUMA-EV',
        'card' => [
            'number' => 'Kartennummer',
            'expiration' => 'Gültig bis',
            'cvv' => 'CVV',
            'submit' => "Jetzt Spenden",
            'recurring-hint' => 'Hinweis: Eine direkte Kreditkartenzahlung ohne Address-/Namensvalidierung ist nur für einmalige Spenden möglich.',
            'error' => [
                '9500' => 'Kreditkarte als betrügerisch abgelehnt',
                '5100' => 'Die Kreditkarte wurde vom Kreditinstitut abgelehnt',
                '00N7' => 'Falsche CVV. Bitte Eingabe überprüfen',
                '5400' => 'Kreditkarte abgelaufen',
                '5180' => 'Luhn Überprüfung fehlgeschlagen',
                '5120' => 'Kreditkarte wurde wegen nicht ausreichender Deckung abgelehnt.',
                '9520' => 'Kreditkarte als verloren/gestohlen abgelehnt',
                '0500' => 'Kreditkarte wurde vom Kreditinstitut abgelehnt',
                '1330' => 'Kreditkarte ungültig. Bitte überprüfen Sie Ihre Eingabe',
                'generic' => 'Kreditkarte wurde vom Kreditinstitut abgelehnt',
            ]
        ],
        'banktransfer' => [
            'description' => [
                'once' => 'Bitte stoßen Sie bei Ihrer Hausbank eine Banküberweisung auf folgende Bankverbindung an (z.B. über Onlinebanking). Sie können auch mit Ihrer Onlinebanking App den QR Code scannen, um die Daten automatisch zu übernehmen.',
                'recurring' => 'Bitte erstellen Sie bei Ihrer Hausbank einen Dauerauftrag auf folgende Bankverbindung (z.B. über Onlinebanking). Sie können auch mit Ihrer Onlinebanking App den QR Code scannen, um die Daten automatisch zu übernehmen.',
            ],
            'qr-remittance' => 'Spende vom :date',
            'qrdownload' => 'Herunterladen'
        ],
        'processing' => 'Zahlung wird verarbeitet'
    ],

    'frequency.name' => 'Häufigkeit',

    'frequency.once' => 'Einmalig',
    'frequency.monthly' => 'Monatlich',
    'frequency.quarterly' => 'Vierteljährlich',
    'frequency.six-monthly' => 'Halbjährlich',
    'frequency.annual' => 'Jährlich',

    'head.lastschrift' => 'Lastschrift',
    'ueberweisung' => 'Überweisung',
    'paypal.0' => 'Paypal / Kreditkarte',


    'bankinfo.1' => 'Um für den SUMA-EV unseren Trägerverein zu spenden, brauchen Sie nur eine Überweisung auf folgendes Konto zu tätigen:',
    'bankinfo.2.0' => 'SUMA-EV',
    'bankinfo.2.1' => 'IBAN: DE64 4306 0967 4075 0332 01',
    'bankinfo.2.2' => 'BIC: GENODEM1GLS',
    'bankinfo.2.3' => 'Bank: GLS Gemeinschaftsbank, Bochum',
    'bankinfo.2.4' => '(Konto-Nr.: 4075 0332 01, BLZ: 43060967)',
    'bankinfo.3' => 'Falls Sie eine Spendenbescheinigung wünschen, teilen Sie uns bitte Ihre vollständige Adresse mit. Bei Spenden bis 300,-€ genügt der Kontoauszug für die Absetzbarkeit beim Finanzamt.',

    'lastschrift.info' => 'Wenn Sie per Lastschrift spenden möchten, tragen Sie in das nachfolgende Formular bitte die Informationen zur Spendenhöhe und Ihre Kontoinformationen ein. Wir buchen dann bequem innerhalb der nächsten 2 Wochen vom angegebenen Konto ab.',
    'lastschrift.info2' => 'Sofern unter Regelmäßigkeit nicht anders von Ihnen angegeben, findet eine Abbuchung stets nur einmalig statt.',
    'lastschrift.1' => 'Spenden mittels elektronischem Lastschriftverfahren:',
    'lastschrift.2' => 'Tragen Sie hier Ihre Kontodaten ein. Wir buchen dann entsprechend von Ihrem Konto ab. Notwendige Felder sind mit einem "*" gekennzeichnet.',
    'lastschrift.3f' => 'Bitte geben Sie den Vornamen des Kontoinhabers ein:',
    'lastschrift.3f.placeholder' => 'Vorname',
    'lastschrift.3l' => 'Bitte geben Sie den Nachnamen des Kontoinhabers ein:',
    'lastschrift.3l.placeholder' => 'Nachname',
    'lastschrift.3c' => 'Bitte geben Sie den Firmenkontonamen ein:',
    'lastschrift.3c.placeholder' => 'Firmenname',
    'lastschrift.4' => 'Ihre E-Mail Adresse:',
    'lastschrift.5' => 'Ihre Telefonnummer, um Ihre Spende ggf. durch einen Rückruf zu verifizieren:',
    'lastschrift.6' => 'Ihre IBAN:',
    'lastschrift.7' => 'Ihre BIC (Nur notwendig für Transaktionen aus dem EU Ausland):',
    'lastschrift.8.message.label' => 'Hier können Sie uns ggf. noch eine Mitteilung dazu senden:',
    'lastschrift.8.message.placeholder' => 'Weitere Angaben',
    'lastschrift.10' => 'Ihre Daten werden über eine verschlüsselte Verbindung zu uns übertragen und können von Dritten nicht mitgelesen werden. SUMA-EV verwendet Ihre Daten ausschlie&szlig;lich für die Spendenabrechnung; Ihre Daten werden nicht weitergegeben. Spenden an den SUMA-EV sind steuerlich absetzbar, da der Verein vom Finanzamt Hannover Nord als gemeinnützig anerkannt ist, eingetragen in das Vereinsregister beim Amtsgericht Hannover unter VR200033.',
    'lastschrift.private' => 'Privatperson:',
    'lastschrift.company' => 'Firma:',


    'paypal.1' => 'Mit einem Klick auf Spenden werden Sie zu Paypal weitergeleitet.',


    'submit' => 'Abschicken',

    'member.1' => 'Oder doch lieber Mitglied werden?',
    'member.2' => 'Es kostet nicht mehr und bietet viele Vorteile:',
    'member.3' => 'Werbefreie Nutzung von MetaGer',
    'member.4' => 'Förderung der Suchmaschine MetaGer',
    'member.5' => 'Mitgliedsbeitrag steuerlich absetzbar',
    'member.6' => 'Mitbestimmungsrechte im Verein',
    'member.7' => 'Antragsformular',


    'drucken' => 'Drucken',

    'danke.title' => 'Herzlichen Dank! Wir haben Ihre Spendenbenachrichtigung erhalten.',
    'danke.nachricht' => 'Falls Sie Kontaktdaten angegeben haben, erhalten Sie demnächst auch eine persönliche Nachricht.',
    'danke.kontrolle' => 'Folgende Nachricht hat uns erreicht:',

    'danke.schluessel' => 'Als kleines Dankeschön bieten wir unseren Spendern einen Schlüssel für werbefreie Suchen. <br> Dieser lässt sich eingeben indem man auf das Schlüsselsymbol neben der Suchleiste klickt. <br> Ihr Schlüssel lautet: ',

    'telefonnummer' => 'Telefonnummer',
    'iban' => 'IBAN',
    'bic' => 'BIC',
    'betrag' => 'Betrag',
    'danke.message' => 'Ihre Nachricht',

    'error.name' => 'Es scheint, als hätten sie keinen Namen angegeben. Bitte versuchen Sie es erneut.',
    'error.iban' => 'Die eingegebene IBAN scheint nicht Korrekt zu sein. Nachricht wurde nicht gesendet.',
    'error.bic' => 'Die eingegebene IBAN gehört nicht zu einem Land aus dem SEPA Raum. Für einen Bankeinzug benötigen wir eine BIC von Ihnen.',
    'error.amount' => 'Der eingegebene Spendenbetrag ist ungültig. Bitte korrigieren Sie Ihre Eingabe und versuchen es erneut.',
    'error.frequency' => 'Die eingegebene Häufigkeit für Ihre Spende ist ungültig.',
    'error.robot' => 'Die Eingabe war nicht korrekt',
];