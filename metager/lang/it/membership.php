<?php
return [
    'submit' => 'Donare',
    'payment' => [
        'method' => [
            'directdebit' => [
                'accountholder' => [
                    'label' => 'Titolare del conto (se diverso)',
                    'placeholder' => 'John Smith',
                ],
                'label' => 'Addebito diretto SEPA',
            ],
            'title' => '4. Il vostro metodo di pagamento',
            'banktransfer' => 'Bonifico bancario',
        ],
        'interval' => [
            'title' => '3. Il vostro intervallo di pagamento',
            'annual' => 'annuale',
            'six-monthly' => 'semestrale',
            'quarterly' => 'trimestrale',
            'monthly' => 'mensile',
        ],
    ],
    'title' => 'La vostra adesione a SUMA-EV',
    'description' => 'Grazie per aver preso in considerazione l\'iscrizione a <a href=\'https://suma-ev.de/mitglieder/\' target=\'_blank\'></a> nella nostra associazione di sostegno senza scopo di lucro. Per poter elaborare la vostra domanda di adesione, abbiamo bisogno solo di alcune informazioni, che potete compilare qui.',
    'success' => 'Grazie mille per averci inviato la sua candidatura. La elaboreremo il prima possibile. In seguito riceverete una mail con ulteriori informazioni da parte nostra all\'indirizzo indicato.',
    'startpage' => 'Torna alla pagina iniziale',
    'contact' => [
        'title' => '1. I vostri dati di contatto',
        'name' => [
            'label' => 'Il tuo nome',
            'placeholder' => 'Max Mustermann / Muster GmbH',
        ],
        'email' => [
            'label' => 'Il vostro indirizzo e-mail',
            'placeholder' => 'max@mustermann.de',
        ],
    ],
    'fee' => [
        'title' => '2. La quota associativa',
        'description' => 'Selezionare di seguito la quota di adesione desiderata (mensile).',
        'amount' => [
            'custom' => [
                'label' => 'Importo personalizzato',
                'placeholder' => '5,00€',
            ],
        ],
    ],
];
