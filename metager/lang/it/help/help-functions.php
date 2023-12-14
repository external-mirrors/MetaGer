<?php
return [
    'stopworte' => [
        'title' => 'Escludere le parole singole',
        '2' => 'Esempio: State cercando un\'auto nuova, ma non una BMW. Allora la ricerca dovrebbe essere <div class="well well-sm">auto nuova -bmw</div>',
    ],
    'bang' => [
        'title' => 'bangs',
        '1' => 'MetaGer utilizza un\'ortografia speciale chiamata "!bang syntax". Un !bang inizia con il "!" e non contiene spazi vuoti ("!twitter", "!facebook" per esempio). Se si utilizza un !bang supportato da MetaGer, si vedrà una nuova voce nei "Consigli rapidi". In questo modo si viene indirizzati al servizio specificato (cliccando sul pulsante).',
    ],
    'searchinsearch' => [
        'title' => 'Ricerca nella ricerca',
    ],
    'selist' => [
        'title' => 'Voglio aggiungere metager.de all\'elenco dei motori di ricerca del mio browser.',
        'explanation_b' => 'Alcuni browser richiedono un URL. Si prega di utilizzare "https://metager.org/meta/meta.ger3?eingabe=%s" senza segni di citazione. Se ci sono ancora problemi, si prega di scrivere un\'e-mail a <a href="/en/kontakt" target="_blank" rel="noopener">.</a>',
        'explanation_a' => 'Provare prima a installare il plugin più recente disponibile. Utilizza il link sotto il campo di ricerca, che ha un rilevamento automatico del browser.',
    ],
    'title' => 'MetaGer - Aiuto',
    'backarrow' => ' Indietro',
    'mehrwortsuche' => [
        'title' => 'Ricerca di più parole',
        '2' => 'Esempio: la ricerca di Shakespears <div class="well well-sm">to be or not to be</div> fornirà molti risultati, ma la frase esatta sarà trovata solo utilizzando <div class="well well-sm">"to be or nor to be".</div>',
        '3' => [
            'text' => 'Si prega di utilizzare le virgolette per essere certi di ottenere le parole di ricerca nell\'elenco dei risultati.',
        ],
        '4' => [
            'text' => 'Mettete le parole o le frasi tra virgolette per cercare le combinazioni esatte.',
        ],
    ],
    'urls' => [
        'title' => 'Escludere gli URL',
        'explanation' => 'Usare "-url:" per escludere i risultati della ricerca contenenti le parole specificate.',
        'example_b' => 'Digitare <i>le mie parole di ricerca</i> -url:dog',
        'example_a' => 'Esempio: Non si vuole che la parola "cane" compaia nei risultati:',
    ],
    'faq' => [
        '18' => [
            'b' => 'I !bang - "reindirizzamenti" fanno parte dei nostri quicktips e necessitano di un ulteriore clic. Abbiamo dovuto decidere tra la facilità d\'uso e il controllo dei dati. Riteniamo necessario mostrare che i link sono di proprietà di terzi (DuckDuckGo). La protezione è quindi duplice: da un lato non trasferiamo le parole di ricerca dell\'utente, ma solo i link a DuckDuckGo. Dall\'altro lato, l\'utente conferma esplicitamente il !bang-target. Non abbiamo le risorse per mantenere tutti questi !bang, ci dispiace.',
        ],
    ],
];
