<?php
return [
    'bang' => [
        'title' => 'Mappe MetaGer <a title="For easy help, click here" href="/hilfe/easy-language/services#eh-maps" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '1' => 'MetaGer supporta in misura limitata uno stile di scrittura spesso definito sintassi "bang".<br>Un "bang" inizia sempre con un punto esclamativo e non contiene spazi. Quando nella query di ricerca viene utilizzato un \'\'bang\'\' supportato, nei nostri suggerimenti rapidi compare una voce che consente di continuare la ricerca con il rispettivo servizio (Twitter o Facebook) premendo un pulsante.',
        '2' => 'Perché i bang non vengono aperti direttamente?',
        '3' => 'I "reindirizzamenti" di !bang fanno parte dei nostri suggerimenti rapidi e richiedono un "clic" aggiuntivo. È stata una decisione difficile per noi, perché rende i !bang meno utili. Tuttavia, è purtroppo necessaria perché i link verso i quali avviene il reindirizzamento non provengono da noi ma da una terza parte, DuckDuckGo.<p>Ci assicuriamo sempre che i nostri utenti mantengano il controllo in ogni momento. Pertanto, proteggiamo in due modi: In primo luogo, il termine di ricerca inserito non viene mai trasmesso a DuckDuckGo, ma solo il !bang. In secondo luogo, l\'utente conferma esplicitamente la visita al target !bang. Purtroppo, per motivi di personale, al momento non possiamo controllare o mantenere tutti questi !bang da soli.',
    ],
    'selist' => [
        'title' => 'Aggiungere MetaGer all\'elenco dei motori di ricerca del browser <a title="For easy help, click here" href="/hilfe/easy-language/functions#eh-selist"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        'explanation_b' => 'Alcuni browser richiedono l\'inserimento di un URL; dovrebbe essere "https://metager.de/meta/meta.ger3?input=%s" senza virgolette. Potete generare voi stessi l\'URL cercando qualcosa su metager.de, quindi sostituendo ciò che si trova dietro "input=" nella barra degli indirizzi con %s. Se avete ancora problemi, contattateci: <a href="/kontalt" target="_blank" rel="noopener">Modulo di contatto</a>',
        'explanation_a' => 'Provare prima a installare il plugin attuale. Per installarlo, è sufficiente fare clic sul link direttamente sotto la casella di ricerca. Il browser dovrebbe essere già stato rilevato.',
    ],
    'title' => 'MetaGer - Aiuto',
    'backarrow' => 'Indietro',
    'mehrwortsuche' => [
        '4' => [
            'text' => 'Mettete le parole o le frasi tra virgolette per cercare le combinazioni esatte.',
        ],
    ],
    'urls' => [
        'title' => 'Escludere gli URL',
        'explanation' => 'È possibile escludere i risultati della ricerca che contengono parole specifiche nei link dei risultati utilizzando "-url:" nella ricerca.',
        'example_b' => '<i>la mia ricerca</i> -url:dog',
        'example_a' => 'Esempio: Si desidera escludere i risultati in cui la parola "cane" compare nel link del risultato:',
    ],
    'exactsearch' => [
        'example' => [
            '1' => "+parola di esempio",
            '2' => '+"frase di esempio"',
        ],
        'title' => 'Ricerca esatta <a title="For easy help, click here" href="/hilfe/easy-language/functions#exactsearch"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '1' => "Se si desidera trovare una parola specifica nei risultati della ricerca di MetaGer, è possibile anteporre a tale parola un segno più. Quando si usa il segno più e le virgolette, una frase viene cercata esattamente come è stata inserita.",
        '2' => "Esempio: S",
        '3' => 'Esempio: ',
    ],
    'key' => [
        '4' => 'Salva file <br>Quando ci si trova nella pagina <a href = "/chiavi/chiave/enter">di gestione</a> della chiave MetaGer, c\'è un\'opzione per salvare un file. In questo modo la chiave MetaGer viene salvata in un file. È possibile utilizzare questo file su un altro dispositivo per accedere alla chiave.',
        '5' => 'Scansione del codice QR <br>In alternativa, è possibile scansionare il codice QR visualizzato nella pagina di <a href = "/keys/key/enter">gestione</a> per accedere con un altro dispositivo.',
        '2' => 'Codice di accesso <br>Sulla <a href = "/keys/key/enter">pagina di gestione</a> della chiave MetaGer, è possibile utilizzare il codice di accesso per aggiungere la chiave a un altro dispositivo. È sufficiente inserire il codice numerico di sei cifre al momento dell\'accesso. Il codice di accesso può essere utilizzato una sola volta ed è valido solo finché la finestra è aperta.',
        '6' => 'Inserire manualmente la chiave MetaGer <br>È possibile inserire manualmente la chiave anche su un altro dispositivo.',
        'colors' => [
            'title' => 'Chiave MetaGer colorata',
            '1' => 'Per riconoscere facilmente se si sta effettuando una ricerca priva di pubblicità, abbiamo assegnato i colori ai nostri simboli chiave. Di seguito sono riportate le spiegazioni dei colori corrispondenti:',
            'grey' => 'Grigio: non è stata impostata una chiave. Si sta utilizzando la ricerca libera.',
            'red' => 'Rosso: se il simbolo della chiave è rosso, significa che la chiave è vuota. Sono state esaurite tutte le ricerche senza pubblicità. È possibile ricaricare la chiave nella pagina di gestione delle chiavi.',
            'green' => 'Verde: Se il simbolo della chiave è verde, significa che si sta utilizzando una chiave carica.',
            'yellow' => 'Giallo: Se vedete un tasto giallo, avete ancora un saldo di 30 gettoni. Le ricerche si stanno esaurendo. Si consiglia di ricaricare presto la chiave.',
        ],
        'title' => 'Aggiungi la chiave MetaGer <a title="For easy help, click here" href="/hilfe/easy-language/functions#eh-keyexplain"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '1' => 'La chiave MetaGer viene impostata automaticamente nel browser e utilizzata. Non è necessario fare altro. Se si desidera utilizzare la chiave MetaGer su altri dispositivi, esistono diversi modi per configurarla:',
        '3' => 'Copia URL <br>Quando ci si trova nella pagina <a href = "/chiavi/chiave/enter">di gestione</a> della chiave MetaGer, è disponibile un\'opzione per copiare un URL. Questo URL può essere utilizzato per salvare tutte le impostazioni di MetaGer, compresa la chiave MetaGer, su un altro dispositivo.',
    ],
    'multiwordsearch' => [
        'title' => 'Ricerca di più parole <a title="For easy help, click here" href="/hilfe/easy-language/functions#eh-severalwords"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '4' => [
            'example' => '"la tavola rotonda"',
            'text' => "Con la ricerca per frasi è possibile cercare combinazioni di parole anziché singole parole. È sufficiente racchiudere tra virgolette le parole che devono comparire insieme.",
        ],
        '3' => [
            'example' => '"il" "tavolo" "rotondo"',
            'text' => "Se volete assicurarvi che le parole della vostra ricerca appaiano anche nei risultati, dovete racchiuderle tra virgolette.",
        ],
        '2' => "Se questo non vi basta, avete due opzioni per rendere più precisa la vostra ricerca:",
        '1' => "Quando si cerca più di una parola in MetaGer, cerchiamo automaticamente di fornire risultati in cui tutte le parole compaiono o si avvicinano il più possibile.",
    ],
    'searchfunction' => [
        'title' => "Funzioni di ricerca",
    ],
    'stopwords' => [
        'title' => 'Parole d\'ordine <a title="For easy help, click here" href="/hilfe/easy-language/functions#eh-stopwordsearch"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '3' => "auto nuova -bmw",
        '2' => "Esempio: State cercando un'auto nuova, ma sicuramente non una BMW. Il vostro input sarebbe:",
        '1' => "Se si desidera escludere i risultati della ricerca in MetaGer che contengono parole specifiche (parole di esclusione / stopwords), è possibile farlo anteponendo a queste parole il segno meno.",
    ],
    'easy-help' => 'Facendo clic sul simbolo <a title="For easy help, click here" href="/hilfe/easy-language/services" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a> , si accede a una versione semplificata della guida.',
];
