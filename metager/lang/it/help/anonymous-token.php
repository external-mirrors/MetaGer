<?php

/**
 * Anonyme Token — /hilfe/anonyme-token.
 *
 * Aus dem "anonymous-token"-Zweig von pass/lang/<locale>/help.json.
 * Der Pfad /keys/help/anonymous-token wird dauerhaft hierher weitergeleitet:
 * er steht in bereits versandten Mitglieds-Willkommensmails.
 */

return [
    "heading" => "Gettoni anonimi",
    "description" => [
        "heading" => "Cosa sono i token anonimi?",
        "text" => "Se si utilizza una chiave MetaGer, si riceve una password generata in modo casuale che il browser invia a noi con ogni query di ricerca in modo da poter abilitare la ricerca senza pubblicità. Se si utilizza la nostra <a href=\"/app\" target=\"_blank\">app per Android</a>, o la nostra estensione web per <a href=\"https://chrome.google.com/webstore/detail/gjfllojpkdnjaiaokblkmjlebiagbphd\" target=\"_blank\" rel=\"noopener\">Chrome</a> e <a href=\"https://addons.mozilla.org/firefox/addon/metager-suche/\" target=\"_blank\" rel=\"noopener\">Firefox</a>, invece della password, il browser ci invia una password generata casualmente (token anonimo) con ogni richiesta di ricerca per l'autenticazione, che viene generata localmente. In questo modo si garantisce che ogni password sia unica e non abbia alcun legame con la chiave MetaGer vera e propria, né tra le singole password.",
    ],
    "technical-function" => [
        "texts" => [
            "In una firma RSA classica, prenderemmo il token anonimo <code>m</code>, l'esponente segreto <code>d</code>, e il modulo pubblico <code>N</code> della nostra chiave privata e creeremmo la firma usando <code>m^d (mod N)</code>. Tuttavia, vogliamo che <code>m</code> rimanga segreto.",
            "Pertanto, il terminale crea un numero casuale <code>r</code> utilizzando un generatore di numeri casuali, che non è correlato al divisore <code>N</code>. Quindi il massimo comune divisore di <code>r</code> e <code>N</code> deve essere <code>1</code>.",
            "Poiché <code>r</code> è un numero casuale, ne consegue che <code>m'</code> non rivela alcuna informazione sul token anonimo memorizzato localmente <code>m</code>.",
            "Il nostro server riceve ora il token anonimo offuscato <code>m'</code> dal dispositivo finale insieme alla chiave MetaGer da utilizzare. Sottraiamo un token dalla chiave e inviamo la firma, anch'essa offuscata, <code>s'&Congruent; (m')^d (mod N)</code> al dispositivo finale.",
            "Il terminale può ora calcolare la firma RSA valida <code>s</code> per il token anonimo non criptato: <code>s&Congruent; s' r^-1 (mod N)</code>. Questo funziona perché per le chiavi RSA, <code>r^(e*d)&Congruent; r (mod N)</code>. E quindi anche: <code>s &Congruent; s' * r^-1 &Congruent; (m')^d*r^-1 &Congruent; m^d*r^(e*d)*r^-1 &Congruent; m^d*r*r^-1 &Congruent; m^d (mod N)</code>.",
            "Il vostro dispositivo finale ci invia ora il token anonimo non criptato insieme alla firma associata per l'autorizzazione durante una ricerca. La chiave stessa non ci viene più inviata durante la ricerca.",
        ],
        "heading" => "L'algoritmo alla base:",
    ],
    "problem" => [
        "heading" => "Quale problema dovrebbero risolvere i token anonimi?",
        "text" => "Se il vostro browser ci inviasse sempre la stessa password ad ogni ricerca, avremmo almeno teoricamente la possibilità di stabilire una correlazione tra tutte le ricerche effettuate con la stessa chiave. Anche se non lo facessimo, ovviamente, la fiducia sarebbe comunque necessaria per avere la certezza di una ricerca anonima. Per non dover solo promettere la ricerca anonima, ma poterla anche dimostrare, abbiamo introdotto i token anonimi.",
    ],
    "general-function" => [
        "heading" => "Come funziona?",
        "texts" => [
            "Vogliamo quindi che le password una tantum siano generate direttamente dal vostro dispositivo endpoint, che poi ci invierete per l'autenticazione durante le vostre ricerche. Tuttavia, per ogni token anonimo sul vostro dispositivo finale, dobbiamo assicurarci che un token regolare sia stato sottratto dalla vostra chiave MetaGer, senza (e questo è il punto cruciale) dirci quale chiave MetaGer è stata usata per generare il token anonimo.",
            "Tradizionalmente, a questo scopo si utilizza una forma di firma crittografica. In questo caso, firmeremmo il token anonimo generato. In questo modo, quando l'utente ci invia il token anonimo insieme alla firma in un secondo momento, possiamo essere sicuri che il token anonimo sia valido. Tuttavia, per ottenere la firma, avreste dovuto inviarci il token anonimo insieme alla vostra chiave reale, il che avrebbe annullato l'anonimato.",
            "Pertanto, utilizziamo una forma modificata di firma crittografica, la cosiddetta <a href=\"https://it.wikipedia.org/wiki/Firma_cieca\" target=\"_blank\">firma cieca</a>. Per creare un'analogia con la vita reale, è come inviarci il vostro token anonimo in una busta di carta carbone. In questo esempio, non saremmo in grado di aprire la busta, ma potremmo firmare dall'esterno, in modo da trasferire la nostra firma al token anonimo all'interno. Quando si riceve la busta, è possibile rimuoverla e inviarci la password e la firma in un secondo momento. Potremmo così confermare che si tratta effettivamente della nostra firma.",
            "In realtà, questa analogia è un po' fuorviante, perché nel processo reale, nel momento in cui ci inviate il token anonimo e la firma, non solo non abbiamo mai visto il token anonimo prima, ma nemmeno la firma stessa. Eppure possiamo verificare che la firma è stata generata da noi.",
        ],
    ],
    "meaning" => [
        "heading" => "Cosa significa questo per le ricerche autenticate?",
        "texts" => [
            "Utilizzando l'algoritmo descritto, sia noi che voi possiamo garantire che per le vostre ricerche autenticate venga utilizzata ogni volta una nuova password casuale non correlata alla vostra chiave MetaGer.",
            "La particolarità di questo algoritmo è che tutti i componenti che garantiscono l'anonimato vengono eseguiti localmente sul dispositivo. Il codice sorgente eseguito può essere visualizzato e verificato da chiunque in qualsiasi momento.",
            "La cosa migliore è che non è necessario configurare nulla per utilizzare i token anonimi. È sufficiente installare/utilizzare la nostra estensione per il browser/applicazione per Android per far sì che il vostro dispositivo utilizzi i token anonimi per tutte le ricerche.",
        ],
    ],
];
