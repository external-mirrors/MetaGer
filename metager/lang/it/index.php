<?php
return [
    'plugin' => 'Installare MetaGer',
    'plugin-title' => 'Aggiungi MetaGer al tuo browser',
    'key' => [
        'placeholder' => 'Inserite la vostra MetaGer Key per avviare la ricerca.',
        'tooltip' => [
            'nokey' => 'Impostare la ricerca senza pubblicità',
            'empty' => 'Gettone esaurito. Ricarica ora.',
            'low' => 'Gettone presto esaurito. Ricarica ora.',
            'full' => 'Ricerca senza annunci attivata.',
        ],
    ],
    'placeholder' => 'MetaGer: Ricerca e ricerca protetta dalla privacy',
    'searchbutton' => 'Avviare la ricerca MetaGer',
    'foki' => [
        'web' => 'Web',
        'bilder' => 'Immagini',
        'nachrichten' => 'Notizie',
        'science' => 'Scienza',
        'produkte' => 'Prodotti',
        'maps' => 'Mappe',
    ],
    'adfree' => 'Utilizzare MetaGer senza pubblicità',
    'skip' => [
        'search' => 'Passa all\'inserimento della query di ricerca',
        'navigation' => 'Vai alla navigazione',
        'fokus' => 'Passa alla selezione del focus di ricerca',
    ],
    'lang' => 'linguaggio wwitch',
    'searchreset' => 'eliminare l\'input della query di ricerca',
    'searchbar-replacement' => [
        'tagline' => 'Open source. Senza pubblicità. Anonimo.',
        'message' => 'La tua chiave è il tuo accesso – nessun account, nessun indirizzo e-mail. Solo il credito dipende da essa.',
        'first_time' => 'Prima volta qui?',
        'start' => 'Configura una chiave',
        // Swapped in by resources/js/accountBreadcrumb.js when this browser has
        // rendered a signed-in page before. Three strings, replacing three
        // elements in place — nothing appears, nothing moves.
        'welcome_back' => 'Bentornato.',
        'welcome_back_message' => "Su questo dispositivo hai già effettuato l'accesso. Accedi con la stessa chiave: il tuo credito è ancora lì.",
        'welcome_back_button' => 'Accedi di nuovo',
        'have_key' => 'Accedi con la mia chiave',
        'login' => 'Accedi',
        'key_error' => "La chiave inserita non è valida. Controllare l'immissione.",
        'login_code_error' => "Il codice di accesso inserito non è valido. Suggerimento: i codici di accesso sono validi solo quando sono visibili su un altro dispositivo!",
        'payment_id_error' => "È stato inserito un ID di pagamento che non è una chiave corretta. La chiave è lunga 36 caratteri.",
        'new_key' => 'Non c\'è ancora la chiave?',
        'extension' => 'Rimanete connessi e anonimi con la nostra estensione web',
    ],
    // The landing page shown to a visitor without a key: hero, "how it works",
    // and the five benefit cards. It came from the keymanager's own root page
    // (pass/views/index.ejs, pass/lang/*/index.json), which /keys used to serve
    // and which now redirects here.
    //
    // Placeholders are Laravel's :name, not i18next's {{name}}, and the links
    // are passed in from parts/landing/* so the locale prefix and the /keys
    // paths stay in one place.
    'landing' => [
        'title' => 'Cercare e navigare nel web senza essere osservati',
        'description' => 'MetaGer rispetta la vostra privacy e vi permette di visitare qualsiasi sito web in modo anonimo.',
        'advantages' => [
            'ads' => 'Senza pubblicità',
            'tracking' => 'Senza tracciamento',
            'logging' => 'Senza registrazioni',
            'compromise' => 'Senza compromessi',
        ],
        'calltoaction' => 'Come funziona',
        'benefits' => [
            'browsing' => [
                'heading' => 'Non solo ricerca anonima: anche navigazione anonima',
                'description' => 'Con la vostra chiave MetaGer potete aprire qualsiasi sito web in un browser privato che funziona in sicurezza sui nostri server, non sul vostro dispositivo. I siti non possono sapere chi siete né da dove state navigando, e tutto viene cancellato automaticamente al termine della sessione. Nessuna installazione, nessuna configurazione: basta aprire e iniziare.',
                'fingerprinting' => 'Fingerprinting',
                'tracking' => 'Tracciamento',
            ],
            'ads' => [
                'heading' => 'Senza pubblicità',
                'description' => 'Pubblicità e privacy vanno raramente d\'accordo. Per questo su MetaGer non c\'è alcun tipo di pubblicità, così possiamo proteggere la vostra privacy senza compromessi.',
                'ads' => 'Pubblicità',
                'tracking' => 'Link di tracciamento',
            ],
            'logging' => [
                'heading' => 'Senza registrazioni',
                'description' => 'Cercare su Internet lascia di solito una scia di dati. Noi non abbiamo bisogno di conservarne nessuno: il nostro motore di ricerca è costruito in modo che combattere lo spam non richieda registri. Inoltre non incontrerete un solo captcha sul nostro sito, nemmeno usando una VPN.',
                'logging' => 'Registrazione',
            ],
            'compromise' => [
                'heading' => 'Senza compromessi',
                'description' => 'Invece di un account legato ai vostri dati personali, ricevete semplicemente una chiave generata in modo casuale, senza nome né indirizzo e-mail. Scegliete tra diversi <a href=":linkPaymentMethods">metodi di pagamento</a>, incluso il pagamento in contanti, completamente anonimo. Con la nostra <a href=":linkApp">app per Android</a> o l\'estensione per browser potete perfino dimostrare che le vostre ricerche restano anonime, grazie ai <a href=":linkToken">token anonimi</a>.',
                'compromise' => 'Dati personali',
            ],
            'efficiency' => [
                'heading' => 'Cercare in modo più efficiente',
                'description' => 'Trovate più in fretta ciò che cercate. Quando è utile, inseriamo deep link chiari, notizie rilevanti e video direttamente nei risultati di ricerca. Anche la nostra ricerca di immagini attinge a fonti aggiuntive.',
            ],
        ],
        'howitworks' => [
            'heading' => 'Come funziona',
            'steps' => [
                [
                    'heading' => 'La chiave viene generata automaticamente',
                    'description' => 'La vostra chiave MetaGer viene generata automaticamente. Nessuna registrazione, nessun dato personale necessario. È tutto ciò che serve per usare MetaGer.',
                ],
                [
                    'heading' => 'Attivate il vostro accesso',
                    'description' => 'Un <a href=":linkCost">pagamento</a> una tantum aggiunge credito alla vostra chiave, che chiamiamo token. Attiva la ricerca senza pubblicità e senza tracciamento e la navigazione anonima, comprese tutte le funzioni attuali e future di MetaGer. Circa 500 token (5 €) bastano di solito per circa 2 mesi.',
                    'membership' => 'Nota: i membri della nostra associazione senza scopo di lucro <a href="https://suma-ev.de" target="_blank">SUMA-EV</a> possono usare MetaGer senza costi aggiuntivi. <a href=":linkMembership" target="_blank">Diventate membri ora</a>',
                ],
                [
                    'heading' => 'Usate MetaGer ovunque',
                    'description' => 'Usate la stessa chiave su quanti dispositivi volete, oppure condividetela con amici e familiari. Basta aprire MetaGer su un qualsiasi dispositivo, inserire la chiave e potete cercare – o navigare in modo anonimo.',
                ],
            ],
            'start' => 'Inizia',
            'login' => 'Ho già una chiave',
        ],
    ],
];
