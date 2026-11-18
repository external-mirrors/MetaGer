<?php
return [
    'title' => 'MetaGer - Aiuto',
    'backarrow' => 'Indietro',
    'tor' => [
        'title' => 'Servizio nascosto TOR <a title="For easy help, click here" href="/hilfe/easy-language/privacy-protection#eh-torhidden" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '2' => 'È possibile accedere a MetaGer nel browser Tor all\'indirizzo: http://metagerv65pwclop2rsfzg4jwowpavpwd6grhhlvdgsswvo6ii4akgyd.onion .',
        '1' => 'Da molti anni MetaGer nasconde e non memorizza gli indirizzi IP. Tuttavia, questi indirizzi sono temporaneamente visibili sul server MetaGer durante l\'esecuzione di una ricerca: se MetaGer venisse compromesso, un malintenzionato potrebbe leggere e memorizzare i vostri indirizzi. Per soddisfare i massimi requisiti di sicurezza, gestiamo un\'istanza di MetaGer sulla rete Tor: il MetaGer TOR Hidden Service, accessibile tramite: <a href="/tor/" target="_blank" rel="noopener">https://metager.de/tor/</a>. Per utilizzarlo, è necessario un browser speciale, che si può scaricare da <a href="https://www.torproject.org/" target="_blank" rel="noopener">https://www.torproject.org/</a>.',
    ],
    'proxy' => [
        'title' => 'Apri in modo anonimo (SafeBrowse) <a title="For easy help, click here" href="/hilfe/easy-language/privacy-protection#eh-proxy" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        'intro' => 'Con «Apri in modo anonimo» visiti un sito web senza che questo veda mai il tuo dispositivo. La pagina viene aperta in un browser che funziona sui nostri server: SafeBrowse. Il sito ha a che fare solo con questo browser e non viene a sapere né il tuo indirizzo IP né nulla sul tuo dispositivo, sul tuo browser o sui tuoi cookie.',
        'use' => [
            'title' => 'Come aprire una pagina in modo anonimo',
            'text' => [
                'Fai clic su «Apri in modo anonimo» (l’icona dello scudo) in un risultato. La pagina si apre in una nuova scheda.',
                'Se vuoi aprire ogni risultato in modo anonimo, attiva «Aprire i risultati in modo anonimo» nelle impostazioni. Ogni link di un risultato passa allora per SafeBrowse, e con «Apri direttamente» visiti una pagina senza SafeBrowse.',
            ],
        ],
        'session' => [
            'title' => 'La sessione',
            'text' => [
                'Il primo risultato aperto in modo anonimo avvia una sessione. Ognuno dei successivi si apre nella stessa sessione, in una scheda propria. Lì continui a navigare come al solito: segui link, compili moduli, scrivi un nuovo indirizzo o un termine di ricerca nella barra degli indirizzi. Nel frattempo resti protetto.',
                'Una sessione costa token della tua chiave MetaGer; quanti e per quanto tempo è indicato prima dell’avvio. Finché usi la sessione, viene prolungata automaticamente. Quando smetti di usarla, termina da sola poco dopo, oppure la chiudi con «Chiudi sessione».',
                'Quando la sessione termina, il browser sui nostri server viene cancellato con tutto ciò che conteneva: pagine visitate, cookie, accessi e file scaricati. La sessione successiva inizia con un browser nuovo e vuoto. Salva quindi prima i file scaricati sul tuo dispositivo da «Download».',
            ],
        ],
        'views' => [
            'title' => 'Due visualizzazioni',
            'text' => [
                '«Pagina» mostra una copia del sito web senza i suoi script. La leggi, scorri, cerchi e copi come qualsiasi pagina; i tuoi clic e ciò che scrivi vanno al browser sui nostri server. «Browser completo» mostra questo browser come immagine, con tutte le sue schede. Lì funziona tutto, anche i video e le pagine che «Pagina» non riesce a mostrare. Passi dall’una all’altra nella barra in alto.',
            ],
        ],
        'nojs' => [
            'title' => 'Senza JavaScript',
            'text' => [
                'Anche se JavaScript è disattivato nel tuo browser, «Apri in modo anonimo» apre la pagina in SafeBrowse. Vedi allora una copia della pagina in cui link, pulsanti e moduli funzionano. Viene ricaricata dopo ogni clic, ma non cambia da sola. I video, le finestre di dialogo della pagina e il caricamento di file non funzionano in questo modo. Se il tuo browser blocca i cookie, i risultati che apri da una pagina dei risultati finiscono comunque nella stessa sessione, anche se continui a cercare da lì.',
            ],
        ],
        'limits' => [
            'title' => 'Cosa «Apri in modo anonimo» non fa',
            'text' => [
                'Il sito web non ti riconosce dal tuo dispositivo, ma sì da ciò che gli dici tu stesso: se accedi o inserisci dati personali, sa chi sei. Se non vuoi mostrarti nemmeno a MetaGer, usa MetaGer tramite Tor (vedi sopra).',
            ],
        ],
    ],
    'content' => [
        'title' => 'Contenuti discutibili / Protezione dei giovani <a title="For easy help, click here" href="/help/easy-language/privacy-protection#eh-content" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        'explanation' => [
            '1' => 'Ho ricevuto "visite" che non solo sono fastidiose, ma che contengono anche, a mio avviso, contenuti illegali!',
            '2' => 'Se trovate su Internet qualcosa che ritenete illegale o dannoso per i minori, potete contattare <a href="mailto:hotline@jugendschutz.net" target="_blank" rel="noopener">hotline@jugendschutz.net</a> via e-mail o visitare <a href="http://www.jugendschutz.net/" target="_blank" rel="noopener">www.jugendschutz.net</a> e compilare il modulo di reclamo disponibile. È utile fornire una breve nota su ciò che si ritiene inammissibile e su come ci si è imbattuti in questo contenuto. Potete anche segnalarci direttamente i contenuti discutibili. Per farlo, inviate un\'e-mail al nostro responsabile della tutela dei giovani (<a href="mailto:jugendschutz@metager.de" target="_blank" rel="noopener">jugendschutz@metager.de</a>).',
        ],
    ],
    'maps' => [
        'title' => 'Mappe MetaGer',
        '1' => 'La tutela della privacy nell\'era dei giganti globali dei dati ci ha portato anche a sviluppare <a href="https://maps.metager.de" target="_blank">https://maps.metager.de</a>: l\'unico (a nostra conoscenza) pianificatore di itinerari che offre tutte le funzionalità tramite browser e app senza memorizzare la posizione dell\'utente. Tutto questo è verificabile perché il nostro software è open source. Per l\'utilizzo di maps.metager.de, consigliamo la nostra versione veloce dell\'app. È possibile scaricare le nostre app da <a href="/app" target="_blank">qui</a> (o naturalmente anche dal Play Store).',
        '2' => 'È possibile accedere alla funzione mappe anche dalla ricerca di MetaGer (e viceversa). Dopo aver cercato un termine in MetaGer, nell\'angolo in alto a destra apparirà un nuovo focus di ricerca "Mappe". Facendo clic su di esso si accede alla mappa corrispondente.',
        '3' => 'Al momento del caricamento, la mappa visualizza i punti (POI = Point of Interest) trovati da MetaGer, che sono elencati anche nella colonna di destra. Quando si esegue lo zoom, l\'elenco si adatta alla sezione della mappa. Passando il mouse su un indicatore della mappa o dell\'elenco si evidenzia l\'elemento corrispondente. Fare clic su "Dettagli" per ottenere ulteriori informazioni su quel punto dal database sottostante.',
    ],
    'easy-help' => 'Facendo clic sul simbolo <a title="For easy help, click here" href="/hilfe/easy-language/privacy-protection" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a> , si accede a una versione semplificata della guida.',
    'privacy' => [
        'title' => "Anonimato e sicurezza dei dati",
        '1' => 'Cookie di monitoraggio, ID di sessione e indirizzi IP <a title="For easy help, click here" href="/hilfe/easy-language/privacy-protection#eh-tracking" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '2' => 'Nessuno di questi dati viene utilizzato, memorizzato, conservato o altrimenti elaborato qui a MetaGer (eccezione: memorizzazione a breve termine per la protezione contro gli attacchi di hacking e bot). Poiché consideriamo questo argomento estremamente importante, abbiamo anche creato dei modi per aiutarvi a raggiungere il massimo livello di sicurezza: il servizio MetaGer TOR Hidden e il nostro browser anonimo.',
        '3' => "Ulteriori informazioni sono disponibili di seguito. Le funzioni sono accessibili sotto \"Servizi\" nella barra di navigazione.",
    ],
];
