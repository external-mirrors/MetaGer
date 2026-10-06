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
        'error' => [
            'title' => 'Segnalazione di errore (traccia dello stack)',
            'description' => 'Quando si verifica un errore tecnico nella nostra applicazione, viene generata automaticamente una descrizione dell\'errore accompagnata da una traccia dello stack. Questo ci permette di individuare il punto del codice sorgente in cui si è verificato l\'errore, in modo da poterlo correggere.',
        ],
        'reduction' => [
            'title' => 'Giustificativo per una quota associativa ridotta',
            'description' => 'Un documento da caricare per dimostrare il diritto a una quota associativa ridotta, ad esempio una comunicazione che confermi la ricezione dei pagamenti di trasferimento.',
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
        'error' => 'Segnalazione di errore',
        'reduction' => 'Dimostrazione della riduzione',
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
            'title' => 'Utilizzo del motore di ricerca web MetaGer',
            'suggest' => 'Se i suggerimenti di ricerca sono abilitati per la barra degli indirizzi del browser (opzionale), dobbiamo memorizzare le impostazioni dei suggerimenti sul nostro server. La chiave sarà una stringa concatenata del vostro indirizzo IP, del vostro useragent e dell\'intestazione accept-language del vostro browser, sottoposta a hashhed sha1. Se l\'impostazione è disattivata (impostazione predefinita) non verrà memorizzato nulla.',
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
            'ip' => 'Utilizziamo il vostro indirizzo IP per stimare una buona posizione di partenza su cui concentrare inizialmente la mappa. A tal fine il vostro indirizzo IP viene elaborato localmente. I risultati non vengono memorizzati da nessuna parte e vengono immediatamente cancellati dopo la richiesta dell\'utente.',
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
        'membership' => [
            'title' => 'Richiesta e gestione dell\'iscrizione a SUMA-EV',
            'description' => 'Al momento della richiesta di adesione a SUMA-EV e per tutta la durata dell\'adesione, vengono generati i seguenti dati:',
            'contact' => 'Per le persone fisiche: nome e indirizzo e-mail; per le organizzazioni: nome dell’organizzazione, dimensioni approssimative e nome e indirizzo e-mail di una persona di contatto. L’indirizzo postale è facoltativo e, se fornito, viene utilizzato in particolare per l’emissione di una ricevuta di donazione. In nessun caso trasmettiamo questi dati a terzi.',
            'payment' => 'Utilizziamo i tuoi dati di pagamento esclusivamente per l\'addebito ricorrente della quota associativa. Le modalità di trattamento di tali dati, i destinatari e il periodo di conservazione sono descritti nella sezione "Elaborazione dei pagamenti".',
            'reduction' => 'Se richiedi una quota associativa ridotta, trattiamo la documentazione che carichi (ad esempio, un avviso di pagamento) esclusivamente per verificare il tuo diritto alla riduzione. Tale documentazione può contenere dati particolarmente sensibili ai sensi dell’art. 9 del GDPR; pertanto, la conserviamo solo per il tempo necessario a tale verifica e non la trasmettiamo a terzi.',
            'crm' => 'Per gestire le iscrizioni, utilizziamo il nostro sistema di gestione delle iscrizioni, gestito anch’esso da SUMA-EV. I dati sopra elencati vengono conservati in tale sistema per tutta la durata della vostra iscrizione e, successivamente, per la durata dei periodi di conservazione previsti dalla legge. La tua iscrizione è inoltre collegata a una chiave MetaGer, tramite la quale la quota associativa viene accreditata come credito di utilizzo per MetaGer (vedi «Checkout chiave MetaGer»).',
            'cookies' => 'Il modulo di richiesta e il portale dei membri non impostano cookie quando li si apre semplicemente. Solo una volta inviato un modulo o effettuato l’accesso al portale dei membri, impostiamo un cookie di sessione tecnicamente necessario, in modo da poter, ad esempio, segnalare eventuali errori di inserimento dati o mantenere attivo l’accesso. Il cookie decade dopo due ore di inattività e viene cancellato al momento della disconnessione. Se si apre il modulo tramite un link che indica una lingua diversa dall’impostazione linguistica del browser, memorizziamo tale lingua in un cookie che viene cancellato alla chiusura del browser. La base giuridica è l’articolo 25, paragrafo 2, n. 2 della TDDDG.',
        ],
        'payments' => [
            'title' => 'Elaborazione dei pagamenti',
            'description' => 'I pagamenti a favore di SUMA-EV – attualmente le quote associative, in futuro anche le donazioni e le chiavi MetaGer – vengono elaborati dal nostro sistema di pagamento, gestito anch’esso da SUMA-EV. Vengono generati i seguenti dati:',
            'ip' => 'Solo in caso di pagamento tramite addebito diretto SEPA: nell’ambito della registrazione del mandato di addebito diretto (vedi sotto). In caso contrario, il dato non viene conservato.',
            'useragent' => 'Solo in caso di pagamento tramite addebito diretto SEPA: nell’ambito della registrazione del mandato di addebito diretto (vedi sotto). In caso contrario, il dato non viene conservato.',
            'contact' => 'Riceviamo il tuo nome e il tuo indirizzo e-mail dal servizio per cui stai effettuando il pagamento (ad esempio, la richiesta di iscrizione). Li utilizziamo per inviarti e-mail relative al pagamento, quali i dettagli per il bonifico bancario, i link per il pagamento o i promemoria di pagamento.',
            'payment' => 'A seconda del metodo di pagamento, del titolare del conto e dell’IBAN, un riferimento al tuo conto PayPal o alla tua carta presso il rispettivo fornitore, nonché l’importo, l’ora e lo stato di ciascun pagamento.',
            'methods' => 'Gestiamo direttamente gli addebiti diretti SEPA e i bonifici bancari; gli addebiti diretti vengono inviati alla nostra banca indicando il titolare del conto, l’IBAN, l’importo e il riferimento del mandato. Quando paghi con PayPal, inserisci i tuoi dati direttamente presso PayPal (Europe) S.à r.l. et Cie, S.C.A.; solo dopo aver scelto PayPal come metodo di pagamento, la pagina di pagamento carica il codice di programma di PayPal, che fornisce a PayPal il tuo indirizzo IP e gli consente di impostare i propri cookie. Quando si paga con carta o tramite Wero, si inseriscono i propri dati direttamente presso VR Payment GmbH. Non riceviamo mai numeri di carta né dati di accesso a PayPal o Wero, ma solo un riferimento che ci consente di riscuotere gli addebiti ricorrenti. La base giuridica è l’art. 6, comma 1, lett. b) del GDPR.',
            'mandate' => 'Se ci conferisci online un mandato di addebito diretto SEPA, conserviamo una registrazione del contenuto del mandato (titolare del conto, IBAN, riferimento del mandato, importo e periodicità, testo del mandato che ti è stato mostrato) insieme all’ora in cui è stato conferito, al tuo indirizzo IP e al tuo user agent. Abbiamo bisogno di questa registrazione per dimostrare alla nostra banca che hai conferito il mandato nel caso in cui un addebito diretto venga contestato; solo in tal caso la mostriamo alla banca. La base giuridica è l’art. 6, comma 1, lett. b) del GDPR e il nostro legittimo interesse a poter dimostrare l’esistenza del mandato (art. 6, comma 1, lett. f) del GDPR).',
            'cookies' => 'Le pagine di pagamento non impostano alcun cookie quando vengono aperte. Solo nel caso in cui i dati inseriti siano incompleti o non validi, impostiamo temporaneamente un cookie di sessione tecnicamente necessario per segnalare gli errori; tale cookie viene cancellato non appena gli errori sono stati segnalati (art. 25, comma 2, n. 2 della TDDDG).',
            'retention' => 'Se un pagamento non va a buon fine perché lo interrompi, cancelliamo il tuo nome e il tuo indirizzo e-mail una settimana dopo la scadenza della procedura di pagamento. Se, ad esempio, una richiesta di iscrizione viene rifiutata prima che sia stato addebitato alcun importo, cancelliamo immediatamente i tuoi dati di pagamento e la registrazione dell’autorizzazione. Tutti gli altri dati di pagamento e le registrazioni relative alle autorizzazioni vengono conservati per tutta la durata del rapporto di pagamento e, successivamente, per la durata dei periodi di conservazione previsti dalla legge (fino a 10 anni).',
        ],
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
    'monitoring' => [
        'title' => 'Monitoraggio degli errori e delle applicazioni',
        'description' => 'Per garantire l\'affidabilità dei nostri servizi, utilizziamo lo strumento open source di tracciamento degli errori GlitchTip, sia nel backend che nel frontend delle nostre applicazioni. GlitchTip opera esclusivamente sulla nostra infrastruttura; nessun dato viene condiviso con o trasmesso a fornitori terzi di servizi di analisi o monitoraggio.',
        'collected' => [
            'title' => 'Quali dati vengono generati',
            'description' => 'Quando si verifica un errore nella nostra applicazione web o sui nostri server, raccogliamo automaticamente:',
            'error' => 'Una descrizione dell\'errore e una traccia dello stack che identifichi la posizione interessata nel codice.',
            'useragent' => 'Tipo di browser, sistema operativo e tipo approssimativo di dispositivo, determinati in base all\'user agent dell\'utente.',
            'url' => 'L\'indirizzo (URL) della pagina o della funzione in cui si è verificato l\'errore.',
        ],
        'not_collected' => [
            'title' => 'Quali dati non vengono generati',
            'description' => 'Il tuo indirizzo IP viene rimosso prima ancora di essere trasmesso a GlitchTip e non raggiunge mai il sistema. Non associamo le segnalazioni di errore a nessuna persona fisica né a nessun account utente, nemmeno nel caso di utenti che hanno effettuato l\'accesso. Inoltre, non raccogliamo password, dati di pagamento né il contenuto di eventuali moduli o messaggi inviati tramite i nostri servizi.',
        ],
        'retention' => 'Conserviamo i rapporti sugli errori per 30 giorni; successivamente vengono eliminati automaticamente.',
        'base' => 'La base giuridica di tale trattamento è il nostro legittimo interesse a garantire un funzionamento affidabile e sicuro dei nostri servizi (art. 6, comma 1, lettera f) del GDPR).',
    ],
];
