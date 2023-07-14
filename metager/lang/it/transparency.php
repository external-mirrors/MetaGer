<?php
return [
    'head' => [
        '1' => 'Dichiarazione di trasparenza',
        '2' => 'MetaGer è trasparente',
        '3' => 'Che cos\'è un motore di metasearch?',
        '4' => 'Qual è il vantaggio di un motore di metasearch?',
        '5' => 'Come si compone la nostra classifica?',
        'compliance' => 'Come risponde MetaGer alle richieste delle autorità?',
    ],
    'text' => [
        '2' => [
            '2' => 'Un motore di metaricerca combina i risultati di diversi motori di ricerca e li valuta nuovamente secondo i propri criteri. Ciò significa che il motore di metasearch non ha un proprio indice. Pertanto, i motori di metasearch non utilizzano crawler. Utilizzano l\'indice di altri motori di ricerca.',
            '1' => 'Per spiegare cosa sono i motori di metaricerca, è opportuno prima spiegare brevemente come funziona l\'indicizzazione dei normali motori di ricerca. I normali motori di ricerca ottengono i risultati delle loro ricerche da un database di pagine web, chiamato anche indice. I motori di ricerca utilizzano i cosiddetti "crawler", che raccolgono le pagine web e le aggiungono all\'indice (database). Il crawler parte da un insieme di pagine web e apre tutte le pagine web ad esse collegate. Queste vengono indicizzate, cioè aggiunte all\'indice. Poi il crawler apre le pagine web collegate a queste pagine web e continua in questo modo.',
        ],
        '1' => 'MetaGer è trasparente. Il nostro codice sorgente <a href=":sourcecode"></a> è concesso in licenza gratuita e disponibile a tutti. Non memorizziamo i dati degli utenti e teniamo alla protezione dei dati e alla privacy. Per questo motivo garantiamo l\'accesso anonimo ai risultati della ricerca. Questo è possibile grazie a un proxy anonimo e all\'accesso nascosto TOR. Inoltre, MetaGer ha una struttura organizzativa trasparente, poiché è sostenuto dall\'associazione no-profit <a href=":sumalink">SUMA-EV</a> di cui chiunque può diventare membro.',
        '3' => 'Un chiaro vantaggio dei motori di metaricerca è che l\'utente ha bisogno di un\'unica query di ricerca per accedere ai risultati di diversi motori di ricerca. Il motore di metaricerca fornisce i risultati rilevanti in un elenco ordinato di risultati. MetaGer non è un motore di metaricerca puro, poiché utilizza anche piccoli indici propri.',
        '4' => 'Prendiamo le classifiche dei motori di ricerca di origine e le soppesiamo. Queste classifiche vengono poi convertite in punteggi. Vengono assegnati o sottratti punti aggiuntivi per la presenza dei termini di ricerca nell\'URL e nello snippet, nonché per l\'eccessiva presenza di caratteri speciali (ad esempio, altri set di caratteri come il cirillico). Utilizziamo anche un elenco di blocco per rimuovere singole pagine dall\'elenco dei risultati. Blocchiamo le pagine web nella visualizzazione se siamo obbligati per legge a farlo. Ci riserviamo inoltre il diritto di bloccare pagine web con informazioni palesemente errate, pagine web di qualità estremamente scadente e altre pagine web particolarmente dubbie.',
        '5' => 'In caso di ulteriori domande o dubbi, non esitate a utilizzare il nostro modulo di contatto <a href=":contact"></a> e a porci le vostre domande!',
        'compliance' => 'Ci conformiamo alle richieste delle autorità se siamo legalmente obbligati a farlo e giungiamo alla conclusione che la nostra conformità non viola le libertà fondamentali. Prendiamo molto seriamente questa verifica. Inoltre, conserviamo il minor numero possibile di dati personali per ridurre il rischio di doverli rilasciare. Nella tabella seguente sono riportati i dati relativi alle richieste delle autorità che abbiamo elaborato negli ultimi 5 anni. Ulteriori informazioni seguiranno a breve.',
    ],
    'table' => [
        'compliance' => [
            'th' => [
                'authinfocomp' => 'Richieste di informazioni soddisfatte',
                'authblockcomp' => 'Richieste di blocco soddisfatte',
            ],
        ],
    ],
    'alt' => [
        'text' => [
            '1' => 'Rappresentazione visiva di due indici che si completano a vicenda per formare un meta-indice',
        ],
    ],
];
