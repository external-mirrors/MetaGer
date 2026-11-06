<?php
return [
    'title' => 'Far rimuovere un risultato di ricerca – MetaGer',
    'heading' => 'Far rimuovere un risultato di ricerca',
    'lead' => 'MetaGer è un metamotore di ricerca: non abbiamo un indice proprio, ma mostriamo ciò che trovano altri motori di ricerca. Un risultato si può quindi rimuovere davvero solo da dove proviene: dal sito web e dai motori di ricerca di cui MetaGer mostra i risultati. Questa pagina Le indica dove.',
    'toc' => [
        'label' => 'In questa pagina',
    ],
    'website' => [
        'heading' => 'Si rivolga direttamente al sito web',
        'text' => 'È l’unica strada che funziona ovunque. Una volta cancellata o modificata la pagina, ogni motore di ricerca la rimuove o la aggiorna alla visita successiva, MetaGer compreso, senza che Lei debba chiederlo a ciascuno.',
        'tips' => [
            'La maggior parte dei siti web indica un indirizzo di contatto nelle note legali o nell’informativa sulla privacy. Chieda lì di cancellare il contenuto, di correggerlo o almeno di togliere il Suo nome.',
            'Per i dati personali, i siti web nell’UE devono rispondere a una richiesta ai sensi dell’art. 17 del GDPR (diritto alla cancellazione).',
            'Se il sito web è Suo: cancelli la pagina in modo che risponda con «404» o «410», oppure la contrassegni con «noindex». I motori di ricerca la rimuoveranno da soli.',
        ],
        'sources' => 'Per i risultati provenienti da :sources questa è l’unica strada.',
    ],
    'kind' => [
        'heading' => 'Di che cosa si tratta?',
        'intro' => 'Se il sito web non La aiuta, o se non può aspettare, i motori di ricerca hanno procedure proprie. Quella adatta dipende da ciò che vuole ottenere.',
        'personal_data' => [
            'summary' => 'Dati personali: il diritto all’oblio',
            'text' => 'Un risultato per il Suo nome porta a informazioni su di Lei che sono inesatte, superate, non pertinenti o eccessive. In base all’art. 17 del GDPR può chiedere a un motore di ricerca di non mostrare più quel risultato nelle ricerche sul Suo nome. Il motore di ricerca bilancia il Suo interesse con quello del pubblico all’informazione, quindi non tutte le richieste vengono accolte. Nelle schede qui sotto lo trova alla voce «Dati personali».',
        ],
        'outdated' => [
            'summary' => 'Contenuti superati',
            'text' => 'Il sito web ha già cancellato o modificato la pagina, ma il risultato mostra ancora il vecchio titolo o testo. Il problema si risolve da solo alla visita successiva del motore di ricerca; nella maggior parte dei casi può anche chiedere un aggiornamento anticipato. Nelle schede qui sotto lo trova alla voce «Contenuti superati».',
        ],
        'legal' => [
            'summary' => 'Diritto d’autore e altri motivi legali',
            'text' => 'Un risultato viola il Suo diritto d’autore o altri diritti. I motori di ricerca ricevono queste segnalazioni tramite appositi moduli legali; di solito può inviarle solo il titolare dei diritti o chi agisce per suo conto. Nelle schede qui sotto lo trova alla voce «Diritto d’autore e legale».',
        ],
        'illegal' => [
            'summary' => 'Contenuti illeciti',
            'text' => 'Rappresentazioni di abusi sessuali su minori, contenuti dannosi per i minori, istigazione all’odio e simili: li segnali prima a un centro di segnalazione. Questo interviene sul contenuto stesso, presso chi lo ospita, e collabora con la polizia. Se desidera inoltre che MetaGer non mostri più un tale risultato, ci invii solo il suo indirizzo (URL) come testo. Non ci invii mai il contenuto stesso, nemmeno screenshot o allegati.',
            'hotlines' => 'Centri di segnalazione:',
        ],
    ],
    'providers' => [
        'heading' => 'Si rivolga ai motori di ricerca mostrati da MetaGer',
        'text' => 'MetaGer non esplora il web da solo. Interroghiamo i motori di ricerca qui sotto nel momento in cui Lei cerca, mostriamo ciò che restituiscono e conserviamo la loro risposta nella cache per qualche ora. Se uno di essi rimuove un risultato, questo sparisce anche da ciò che MetaGer riceve da esso.',
        'which' => 'Il motore di ricerca da cui proviene un risultato è indicato sotto il risultato nella pagina dei risultati di MetaGer. Se proviene da più motori, si rivolga a ciascuno di essi.',
        'shows' => 'Su MetaGer in:',
        'kinds' => [
            'personal_data' => 'Dati personali',
            'outdated' => 'Contenuti superati',
            'legal' => 'Diritto d’autore e legale',
        ],
        'no_form' => 'Nessun modulo, contattare :name',
        'contact' => 'Contattare :name',
        'us' => 'Vai al modulo',
        'google' => 'MetaGer riceve i risultati di Google tramite il servizio Serper. Ciò che Google rimuove dal proprio indice sparisce poi anche da MetaGer.',
        'brave' => 'Brave Search gestisce un proprio indice. Le richieste sui dati personali vanno inviate via email a privacy@brave.com con oggetto «RTBF request»; la pagina collegata spiega che cosa deve contenere l’email.',
        'mojeek' => 'Mojeek gestisce un proprio indice e non modifica i risultati a mano. Per motivi legali rimuove pagine su richiesta; scriva tramite la sua pagina di contatto e indichi gli indirizzi esatti.',
        'staan' => 'Staan è l’interfaccia di ricerca di European Search Perspective. Riceve le richieste sui dati personali tramite un proprio modulo; compili ogni campo e alleghi i documenti giustificativi. La rimozione riguarda solo l’indice di Staan.',
        'polias' => 'Polias gestisce un proprio indice e non ha un modulo. Scriva a hallo@polias.eu indicando l’indirizzo della pagina e il motivo.',
        'pixabay' => 'Pixabay ospita direttamente le immagini. Usi il modulo di segnalazione o il pulsante di segnalazione nella pagina dell’immagine su Pixabay.',
        'suma' => 'TootNews è gestito da SUMA-EV, l’associazione che sta dietro a MetaGer. I post si trovano su server Mastodon; si rivolga prima a loro e poi usi il nostro modulo.',
        'website' => 'BASE mostra ciò che contengono gli archivi in cui cerca. Se l’archivio rimuove un record, questo sparisce anche da BASE.',
    ],
    'us' => [
        'heading' => 'Rimozione dalle nostre ricerche',
        'text' => 'SUMA-EV gestisce propri indici di ricerca: Nolm e TootNews / TrötNews. Accettiamo richieste solo per questi, e solo tramite il nostro modulo, non via email. MetaGer non è tra questi: non ha un indice proprio, ed è per questo che questa pagina indica dove una rimozione ha effetto. Una richiesta si svolge così:',
        'steps' => [
            'Sceglie la ricerca interessata e conferma il Suo indirizzo email tramite un link che Le inviamo.',
            'Carica la Sua prova e indica per ogni pagina l’indirizzo, i termini di ricerca e il motivo.',
            'Controlliamo prima la prova e poi ogni pagina. Se ha diritto alla rimozione, ad esempio in base all’art. 17 del GDPR per le ricerche sul Suo nome, nascondiamo il risultato in quella ricerca. Rimane sul sito web e in tutti gli altri motori di ricerca.',
            'Rispondiamo entro un mese. Se una richiesta è particolarmente complessa, possono servire fino a due mesi in più; in tal caso glielo comunichiamo entro il primo mese. Se non nascondiamo un risultato, Le indichiamo i motivi.',
        ],
        'include' => 'Ci servono gli indirizzi esatti (URL) delle singole pagine. Non possiamo rimuovere interi siti web o «tutto ciò che riguarda il mio nome».',
        'identity' => 'Come prova ci serve una copia del Suo documento d’identità e, per una richiesta per conto di un’altra persona, anche una procura e una copia del suo documento d’identità. Altrimenti chiunque potrebbe pretendere che nascondiamo indirizzi a piacere. Sulla copia può oscurare tutto ciò che non ci serve per il confronto. La conserviamo cifrata e la cancelliamo 6 mesi dopo la chiusura della pratica, insieme agli altri Suoi dati.',
        'contact' => 'Vai al modulo',
    ],
];
