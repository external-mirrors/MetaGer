<?php

/**
 * Fragen zum MetaGer-Schlüssel — /hilfe/schluessel.
 *
 * Aus dem "faq"-Zweig von pass/lang/<locale>/help.json des Keymanagers.
 */

return [
    "heading" => "Domande sulla chiave MetaGer",
    "faqs" => [
        [
            "summary" => "Come funziona la chiave MetaGer?",
            "description" => "Con una chiave MetaGer si effettuano ricerche senza pubblicità. Si ricevono dei gettoni da cui viene detratta una ricerca per ogni ricerca. Quando si utilizza una chiave MetaGer, tutte le funzioni che proteggono MetaGer dalle chiamate automatiche sono disattivate. Ciò significa che non vedrete richieste captcha e che il vostro indirizzo IP non verrà conservato per un periodo di tempo limitato. In poche parole, MetaGer sarà più veloce, più affidabile e più sicuro.",
        ],
        [
            "summary" => "Come funziona il token anonimo?",
            "description" => "È possibile utilizzare il token anonimo con l'estensione del browser o l'app. Questo vi permetterà di effettuare ricerche ancora più sicure con MetaGer. Utilizzando il token anonimo, una parte del vostro credito, sotto forma di password casuale, verrà memorizzata sul vostro dispositivo. Grazie a un <a href=\":tokenlink\">complesso processo crittografico</a>, diventa impossibile anche per noi associare le ricerche effettuate tra loro o con la vostra chiave.",
        ],
        [
            "summary" => "Come si usa la chiave MetaGer?",
            "description" => "La chiave MetaGer viene impostata e utilizzata automaticamente nel browser. Non è quindi necessario fare altro. Se si desidera utilizzare la chiave MetaGer su altri dispositivi, esistono diversi modi per configurarla:",
            "steps" => [
                [
                    "heading" => "Copiare l'URL",
                    "description" => "Nella pagina di gestione delle chiavi MetaGer è disponibile un'opzione per copiare un URL. Con questo URL tutte le impostazioni di MetaGer e la chiave MetaGer possono essere salvate su un altro dispositivo.",
                ],
                [
                    "heading" => "Salvare il file",
                    "description" => "Nella pagina di gestione delle chiavi MetaGer è disponibile un'opzione per salvare un file. In questo modo si salva la chiave MetaGer in un file. È possibile utilizzare questo file su un altro dispositivo per accedere con la propria chiave.",
                ],
                [
                    "heading" => "Scansione del codice QR",
                    "description" => "In alternativa, è possibile scansionare il codice QR visualizzato nella pagina di amministrazione per accedere a un altro dispositivo.",
                ],
                [
                    "heading" => "Inserire manualmente la chiave MetaGer",
                    "description" => "Naturalmente, è possibile inserire la chiave manualmente su un altro dispositivo.",
                ],
            ],
        ],
        [
            "summary" => "Devo inserire regolarmente la mia chiave. Cosa posso fare?",
            "description" => "Il browser viene istruito a memorizzare in modo permanente la chiave una volta generata o effettuato l'accesso. A seconda della configurazione del vostro browser, potreste aver impostato la cancellazione regolare dei cookie e dei dati del sito web, il che ovviamente vi farà uscire anche da MetaGer. Avete le seguenti opzioni:",
            "steps" => [
                [
                    "heading" => "Aggiungere un'eccezione",
                    "description" => "Nelle impostazioni di Firefox è possibile inserire MetaGer in una whitelist per l'eliminazione dei cookie e dei dati del sito web, in modo da mantenere l'accesso.",
                ],
                [
                    "heading" => "Installare l'estensione del browser",
                    "description" => "La nostra estensione del browser per <a href=\"https://addons.mozilla.org/firefox/addon/metager-suche/\" target=\"_blank\" rel=\"noopener\">Firefox</a> e <a href=\"https://chrome.google.com/webstore/detail/gjfllojpkdnjaiaokblkmjlebiagbphd\" target=\"_blank\" rel=\"noopener\">Chrome</a> è in grado di memorizzare le impostazioni di ricerca, compresa la chiave, senza utilizzare i cookie, in modo da poter cancellare tutti i dati del browser senza dover uscire da MetaGer.",
                ],
                [
                    "description" => "Se si utilizza un gestore di password, è possibile memorizzare la chiave in modo da poter accedere automaticamente. In alternativa, offriamo un <a href=\":keylink\">URL delle impostazioni</a> da memorizzare, ad esempio come segnalibro. Una volta aperto, l'URL delle impostazioni consente di effettuare il login senza immettere manualmente la chiave.",
                    "heading" => "Accesso senza inserire la chiave di 36 caratteri",
                ],
            ],
        ],
        [
            "summary" => "Non sono soddisfatto della chiave MetaGer. Cosa posso fare?",
            "description" => "In questo caso, è possibile richiedere il rimborso dei gettoni non utilizzati entro 30 giorni dall'acquisto. Per farlo, è necessario il proprio ID di pagamento. Per richiedere il rimborso, aprite la pagina di gestione delle chiavi di MetaGer. Cliccate sulla voce di menu \"Ordini\" e inserite il vostro ID di pagamento. Dopodiché potete cliccare sul pulsante \"Richiedi rimborso\" e inviare la richiesta di rimborso.",
        ],
        [
            "summary" => "Come posso effettuare una ricerca completamente anonima?",
            "description" => "La vostra privacy e il vostro anonimato sono molto importanti per noi. Per questo motivo offriamo metodi di pagamento anonimi (in contanti). Offriamo anche l'uso dei <a href=\":tokenlink\">token anonimi</a>, che possono essere utilizzati anche per effettuare ricerche anonime verificabili.",
        ],
        [
            "summary" => "Ho bisogno di una fattura. Come posso ottenerla?",
            "description" => "A tal fine, è sufficiente il proprio ID di pagamento. Per richiedere la fattura, aprire la pagina di amministrazione della chiave MetaGer. Cliccate sulla voce di menu \"Ordini\" e inserite il vostro ID di pagamento. A questo punto è possibile fare clic sul pulsante \"Richiedi fattura\" e avviare la richiesta di fattura. Per la fattura abbiamo bisogno del vostro nome e cognome, del vostro indirizzo e-mail e del vostro indirizzo.",
        ],
        [
            "summary" => "Vorrei caricare automaticamente la mia chiave MetaGer. Come fare?",
            "description" => "Per i nostri membri, la chiave inclusa nell'iscrizione viene automaticamente ricaricata su base mensile. L'importo del gettone dipende dalla quota associativa versata.",
        ],
        [
            "summary" => "Ho ricevuto una tessera o un link con un codice buono. Che cosa devo farne?",
            "description" => "Alcune organizzazioni regalano chiavi MetaGer con un credito fisso tramite tessere promozionali o un link. Aprite <a href=\":voucherlink\">la nostra pagina di riscatto</a>, inserite il codice stampato oppure scansionate il codice QR sulla tessera. Riceverete subito una nuova chiave MetaGer con il credito regalato, valido per un periodo limitato. Ogni codice può essere riscattato una sola volta.",
        ],
    ],
    "more-questions" => "Avete altre domande? Non esitate a utilizzare il nostro <a href=\":contactlink\" target=\"_blank\">modulo di contatto</a>.",
];
