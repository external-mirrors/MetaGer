<?php
return [
    'description' => [
        'query' => [
            'description' => 'I termini di ricerca inseriti sono assolutamente necessari per una ricerca sul Web. Di norma, non è possibile ricavare dati personali da essi; tra l\'altro, perché non hanno una struttura fissa.',
            'examples' => 'Esempi',
            'example_1' => 'consumo d\'acqua doccia',
            'example_2' => 'Testo Su un albero un cuculo',
            'title' => 'Domanda di ricerca inserita',
        ],
        'preferences' => [
            'title' => 'Preferenze dell\'utente',
            'description' => 'Oltre ai dati dei moduli e agli agenti utente, il browser spesso trasferisce altri dati. Tra questi, la selezione della lingua, le impostazioni di ricerca, le intestazioni di accettazione, le intestazioni "do not track" e altro ancora.',
        ],
        'contact' => [
            'title' => 'Dettagli di contatto',
            'description' => 'Qui si trova il nome (cognome e nome) indicato da Ihnen, nonché il suo indirizzo e-mail. Questi dati vengono utilizzati in modo mirato per rispondere a Ihnen e per fornire a Sie unter keinen Umständen weiter an Dritte.',
        ],
        'message' => [
            'title' => 'Messaggio',
            'description' => 'Il messaggio qui inserito sarà trasmesso a noi e utilizzato per elaborare la vostra richiesta.',
        ],
        'title' => 'Descrizione dei dati risultanti',
        'ip' => [
            'title' => 'Indirizzo di protocollo Internet',
            'description' => 'L\'indirizzo di protocollo Internet (di seguito denominato IP) è obbligatorio per poter utilizzare servizi web come MetaGer. Questo IP, insieme a una data - simile a un numero di telefono - identifica chiaramente un accesso a Internet e il suo proprietario. In generale, i primi tre (su un totale di quattro) blocchi di un IP non sono personali. Se i blocchi posteriori dell\'IP sono abbreviati, l\'indirizzo abbreviato identifica l\'area geografica approssimativa della connessione a Internet.',
            'example_full' => 'Esempi (indirizzo IP completo)',
            'example_partial' => 'Esempi (solo i primi due blocchi)',
        ],
        'useragent' => [
            'title' => 'Identificatore dell\'agente utente',
            'description' => 'Quando si richiama un sito web, il browser invia automaticamente un identificatore, solitamente contenente dati sul browser e sul sistema operativo utilizzato. Questo identificatore del browser (il cosiddetto user agent) può essere utilizzato dai siti web, ad esempio, per riconoscere i dispositivi mobili e presentare loro un output personalizzato.',
            'example' => 'Esempio',
        ],
        'payment' => [
            'title' => 'Dettagli sul pagamento',
            'description' => 'Quando si acquista una chiave MetaGer, sono richiesti diversi dati di pagamento a seconda del provider di pagamento',
            'examples' => 'Esempi',
            'name' => 'Max Mustermann, mail@example.com',
            'card' => 'Ultime cifre del numero della carta di credito',
        ],
    ],
    'base' => [
        'title' => 'Base giuridica del trattamento',
        'description' => 'La base giuridica per il trattamento dei vostri dati personali identificabili è l\'Art. 6 (1) (a) GDPR se l\'utente acconsente al trattamento utilizzando i nostri servizi, oppure l\'Art. 6 (1) (f) GDPR se il trattamento è necessario per tutelare i nostri legittimi interessi, o un\'altra base giuridica se ve lo comunichiamo separatamente.',
    ],
    'rights' => [
        'title' => 'I diritti dell\'utente (e i nostri obblighi)',
        'description' => 'Affinché possiate proteggere i vostri dati personali, vi informiamo (ai sensi dell\'art. 13 DSGVO) che avete i seguenti diritti:',
        'information' => [
            'title' => 'Diritto di fornire informazioni',
            'description' => 'Avete il diritto (art. 15 GDPR) di chiederci in qualsiasi momento se e quali dati vi riguardano (metager.de e SUMA-EV). Vi invieremo il più presto possibile, ossia entro pochi giorni, una copia completa dei dati che abbiamo memorizzato o altrimenti memorizzato su di voi ai sensi dell\'articolo 15, paragrafo 3, sottosezione 1 del GDPR. A tal fine, ai sensi dell\'articolo 15, paragrafo 3, comma 3, del GDPR, preferiamo il metodo elettronico; a tal fine, memorizzeremo il vostro indirizzo e-mail per tutta la durata del trattamento. Vi preghiamo di informarci se desiderate specificamente le informazioni in forma cartacea.',
        ],
        'correction' => [
            'title' => 'Diritto alla correzione e all\'integrazione',
            'description' => 'Ai sensi dell\'articolo 16 del GDPR. Se abbiamo memorizzato dati errati su di voi, potete richiederne la correzione. Ciò vale anche per i dati mancanti, per i quali avete il diritto di chiedere un\'integrazione.',
        ],
        'deletion' => [
            'title' => 'Diritto alla cancellazione',
            'description' => 'Ai sensi dell\'articolo 17 del GDPR',
        ],
        'processing' => [
            'title' => 'Diritto alla limitazione del trattamento',
            'description' => 'Ai sensi dell\'articolo 18 del GDPR; ad esempio, se ci avete chiesto di cancellare o modificare i vostri dati, potete imporci un divieto di trattamento per il tempo necessario a farlo. Ciò è possibile indipendentemente dal fatto che alla fine modifichiamo, cancelliamo, ecc. i dati in questione.',
        ],
        'complaint' => [
            'title' => 'Diritto di reclamo',
            'description' => 'Ai sensi dell\'articolo 13, paragrafo 2, lettera d) del GDPR, è possibile presentare un reclamo al responsabile della protezione dei dati del Land Bassa Sassonia. Online: <a href="https://www.lfd.niedersachsen.de/startseite/">Responsabile della protezione dei dati</a>',
        ],
        'opposition' => [
            'title' => 'Diritto di opporsi al trattamento',
            'description' => 'Ai sensi dell\'articolo 21 del GDPR, ad esempio, se si è inseriti in un elenco e si desidera esservi inseriti, si può comunque vietare il trattamento o l\'ulteriore elaborazione di tali dati.',
        ],
        'portability' => [
            'title' => 'Diritto alla portabilità dei dati',
            'description' => 'Ai sensi dell\'articolo 20 del GDPR, ciò significa che siamo obbligati a fornirvi i dati richiesti in un modo leggibile, possibilmente leggibile a macchina o consueto, in modo che possiate rendere i dati accessibili a un\'altra persona così come sono (da trasferire).',
        ],
        'obligation_notify' => [
            'title' => 'Obbligo di notifica in relazione alla correzione o cancellazione dei dati personali o alla limitazione del trattamento:',
            'description' => 'Ai sensi dell\'articolo 19 del GDPR, se avessimo reso accessibili a terzi i dati che ci avete affidato (cosa che non facciamo mai), saremmo obbligati a informarli che, su vostra richiesta, li avremmo cancellati, modificati, ecc.',
        ],
        'perception' => 'Per esercitare tali diritti, è sufficiente contattarci utilizzando il nostro <a href=":contact_link">formulario di contatto</a></b>. Se preferite la forma epistolare, inviateci una lettera all\'indirizzo del nostro ufficio:',
    ],
    'changes' => [
        'description' => 'Come le nostre offerte, anche la presente dichiarazione sulla protezione dei dati è soggetta a continue modifiche. Si consiglia pertanto di rileggerla regolarmente.',
        'date' => 'Questa versione della nostra politica sulla privacy è datata: :date',
        'title' => 'Modifiche alla presente Dichiarazione',
    ],
    'data' => [
        'ip' => 'Indirizzo IP',
        'useragent' => 'Agente utente',
        'query' => 'Query di ricerca',
        'preferences' => 'Preferenze dell\'utente',
        'contact' => 'Dettagli di contatto',
        'payment' => 'Dati di pagamento',
        'referrer' => 'il referente inviato',
        'gps' => 'Dati sulla posizione',
        'optional' => 'opzionale',
        'message' => 'Messaggio',
        'unused' => 'Non verrà salvato o condiviso.',
    ],
    'principles' => [
        'description' => 'Come associazione senza scopo di lucro, ci impegniamo per il libero accesso alla conoscenza. Poiché sappiamo che la libera ricerca non è compatibile con la sorveglianza di massa, prendiamo molto sul serio anche la protezione dei dati. Abbiamo sempre trattato solo i dati assolutamente necessari per il funzionamento dei nostri servizi. La protezione dei dati è sempre il nostro standard. Non effettuiamo profilazione, ossia la creazione automatica di profili di utenti.',
        'title' => 'Principi',
    ],
    'contexts' => [
        'metager' => [
            'description' => 'Quando si utilizza il nostro motore di ricerca MetaGer tramite il suo modulo web o la sua interfaccia OpenSearch, vengono generati i seguenti dati:',
            'query' => 'Come parte integrante del metasearch, la query di ricerca viene trasmessa ai nostri partner per ottenere risultati da visualizzare sulla pagina dei risultati. I risultati ricevuti, compreso il termine di ricerca, vengono conservati per la visualizzazione per alcune ore.',
            'preferences' => 'Utilizziamo questi dati (ad esempio, le impostazioni della lingua) per rispondere alla rispettiva domanda di ricerca. Alcuni di questi dati vengono memorizzati su base non personale a fini statistici.',
            'additionally' => 'I seguenti dati vengono raccolti anche se si utilizza la nostra versione ad-supported:',
            'botprotection' => 'Per proteggere il nostro servizio dal sovraccarico, dobbiamo limitare il numero di ricerche per connessione Internet. Solo a questo scopo, memorizziamo l\'indirizzo IP completo e un timestamp per un massimo di 96 ore. Se da un IP viene effettuato un numero considerevolmente elevato di ricerche, questo IP viene temporaneamente (al massimo 96 ore dopo l\'ultima ricerca) salvato in una lista nera. L\'IP viene poi cancellato.',
            'clarity' => 'Collaboriamo con Microsoft Clarity e Microsoft Advertising per offrirvi risultati di ricerca e pubblicità gratuiti su Yahoo. A tal fine, nella pagina dei risultati di MetaGer vengono registrati dati di utilizzo a fini statistici, compreso il vostro indirizzo IP.',
            'title' => 'Utilizzo del motore di ricerca web MetaGer',
        ],
        'contact' => [
            'title' => 'Utilizzo del modulo di contatto',
            'description' => 'Quando si utilizza il modulo di contatto di MetaGer, vengono generati i seguenti dati, che vengono conservati a scopo di riferimento fino a 2 mesi dopo il completamento della richiesta:',
            'contact' => 'Saranno conservati a scopo di riferimento fino a 2 mesi dopo il completamento della richiesta.',
        ],
        'donate' => [
            'title' => 'Utilizzo del modulo di donazione',
            'description' => 'I seguenti dati trasmessi nel modulo di donazione saranno conservati per 2 mesi per essere elaborati:',
            'contact' => 'Utilizziamo questi dati esclusivamente per eventuali richieste e non li trasmettiamo in nessun caso a terzi.',
            'payment' => 'I dati di pagamento saranno utilizzati solo per elaborare la donazione e non saranno in nessun caso trasmessi a terzi. Per motivi fiscali, siamo obbligati a conservare e quindi salvare questi dati per 10 anni. In seguito verranno automaticamente cancellati e non verranno più elaborati.',
            'message' => 'Il messaggio inserito qui sarà trasmesso a noi e preso in considerazione per l\'elaborazione della donazione.',
        ],
        'key' => [
            'title' => 'Chiave MetaGer',
            'contact' => 'Utilizziamo questi dati esclusivamente per eventuali richieste o per la fatturazione e non li trasmettiamo in nessun caso a terzi.',
            'payment' => 'I dati di pagamento saranno utilizzati solo per elaborare la donazione e non saranno in nessun caso trasmessi a terzi. Per motivi fiscali, siamo obbligati a conservare e quindi salvare questi dati per 10 anni. In seguito verranno automaticamente cancellati e non verranno più elaborati.',
        ],
        'suma' => [
            'title' => 'Utilizzo del sito web <a href="https://suma-ev.de">suma-ev.de</a>',
            'description' => 'Quando si visitano i siti web del dominio "suma-ev.de", i seguenti dati vengono raccolti e memorizzati per un massimo di una settimana:',
            'function' => 'Quando si visitano i siti web del dominio "suma-ev.de", i seguenti dati vengono raccolti e memorizzati per un massimo di una settimana:',
            'other' => 'Negli altri siti web dei nostri domini, trattiamo i dati raccolti solo per rispondere alle richieste e nell\'ambito degli altri punti della presente dichiarazione sulla protezione dei dati.',
            'startpage' => 'Nella pagina iniziale del nostro servizio MetaGer, utilizziamo l\'user agent trasmesso dall\'utente per mostrargli le istruzioni per l\'installazione del plug-in adatto al suo browser.',
        ],
        'newsletter' => [
            'title' => 'Iscriviti alla newsletter del SUMA-EV',
            'description' => 'Per tenervi informati sulle nostre attività, vi offriamo una newsletter via e-mail. Memorizziamo i seguenti dati fino alla cancellazione dell\'iscrizione:',
            'contact' => 'Utilizziamo questi dati esclusivamente per inviarvi la nostra newsletter e non li trasmettiamo in nessun caso a terzi.',
        ],
        'maps' => [
            'title' => 'Utilizzo di Maps.MetaGer.de',
            'description' => 'Quando si utilizza il servizio di mappe MetaGer, vengono generati i seguenti dati:',
        ],
        'proxy' => [
            'title' => 'Utilizzo del proxy di anonimizzazione',
            'description' => 'Quando si utilizza il proxy di anonimizzazione, vengono generati i seguenti dati:',
        ],
        'quote' => [
            'title' => 'Uso della ricerca per citazioni',
            'description' => 'Il termine di ricerca inserito viene utilizzato per cercare i risultati nel database delle citazioni. A differenza delle ricerche sul web con MetaGer, non è necessario trasmettere a terzi il termine di ricerca perché la banca dati delle citazioni si trova sul nostro server. Altri dati non vengono salvati o trasmessi.',
        ],
        'asso' => [
            'title' => 'Uso dell\'associatore',
            'description' => 'L\'associatore utilizza il termine di ricerca per determinare e visualizzare i termini ad esso associati. Gli altri dati non vengono salvati o trasmessi.',
        ],
        'mapsapp' => [
            'title' => 'Utilizzo dell\'applicazione MetaGer',
            'description' => 'L\'utilizzo dell\'app MetaGer è identico a quello di MetaGer tramite un browser web.',
        ],
        'plugin' => [
            'title' => 'Utilizzo del plugin MetaGer',
            'description' => 'Quando si utilizza il plugin MetaGer, vengono generati i seguenti dati:',
        ],
        'title' => 'Dati in arrivo per contesto',
    ],
    'hosting' => [
        'description' => 'I nostri servizi sono amministrati da noi, il SUMA-EV, e gestiti su hardware noleggiato da Hetzner Online GmbH.',
        'title' => 'Hosting',
    ],
    'title' => 'Informativa sulla privacy',
    'introduction' => 'Per la massima trasparenza, vi elenchiamo quali dati raccogliamo da voi e come li utilizziamo. La protezione dei vostri dati è importante per noi e dovrebbe esserlo anche per voi. <strong>Vi invitiamo a leggere attentamente la presente dichiarazione; è nel vostro interesse.</strong>',
    'responsible_party' => [
        'title' => 'Persone responsabili e di contatto',
        'description' => 'MetaGer e i servizi correlati sono gestiti da <a href="https://suma-ev.de">SUMA-EV</a>, che è anche l\'autore di questa dichiarazione. In questa dichiarazione, per "noi" si intende generalmente SUMA-EV. I nostri dati di contatto sono riportati nel nostro <a href=":link_impress">Imprint</a>. Possiamo essere contattati via e-mail utilizzando il nostro modulo di contatto <a href=":link_contact"></a> .',
    ],
];
