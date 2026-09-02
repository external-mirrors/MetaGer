<?php

/**
 * Was ein MetaGer-Schlüssel kostet — /preise.
 *
 * Aus pass/lang/<locale>/cost.json des Keymanagers übernommen, wo diese Seite
 * bis zum Umzug lag. Die Preiszahlen selbst stehen bewusst nicht hier: sie
 * kommen über App\Landing\KeyPrice vom Keymanager, weil der Checkout sie
 * ausgibt.
 */

return [
    "headings" => [
        "Ecco quanto costa la chiave MetaGer",
        "Il più importante riassunto",
    ],
    "texts" => [
        "Per ogni ricerca web senza pubblicità su MetaGer con le impostazioni predefinite vi verrà addebitato <b>1 token</b>. Potete ricaricare la vostra chiave con uno di questi pacchetti di token in qualsiasi momento.",
    ],
    "short-info" => [
        [
            "heading" => "I gettoni sono validi per 2 anni",
            "text" => "I gettoni acquistati sono destinati a rimanere validi fino al loro esaurimento. Non esiste un ordine permanente.",
        ],
        [
            "heading" => "30 giorni di garanzia di rimborso",
            "text" => "Se non siete soddisfatti della vostra chiave, avete 30 giorni di tempo dall'acquisto per restituire il credito non utilizzato.",
        ],
        [
            "heading" => "La chiave viene impostata e utilizzata automaticamente nel browser.",
            "text" => "Non è necessario fare altro per utilizzare la chiave MetaGer nella ricerca. Dopo averla caricata, viene impostata automaticamente nel browser e riceverete informazioni su come impostarla facilmente su altri dispositivi.",
        ],
        [
            "heading" => "Nessuna tracciabilità",
            "text" => "Utilizzate la nostra <a href=\":linkapp\">app per Android</a>, o la nostra estensione per il browser, e mantenete l'anonimato grazie ai <a href=\":linktokens\">token anonimi</a>.",
        ],
    ],
    "pricing" => [
        "heading" => "Ecco come sono composti i nostri prezzi",
        "texts" => [
            "La maggior parte delle nostre entrate va direttamente ai servizi di ricerca interrogati. Vogliamo offrire un concetto sostenibile, il che implica che i motori di ricerca interrogati non subiscano alcun danno finanziario fornendo risultati di ricerca anonimi e privi di pubblicità per MetaGer. Inoltre, una quota copre i costi del personale e dei server e, naturalmente, le commissioni per i fornitori di servizi di pagamento e le tasse sono incluse nei prezzi.",
            "In questo modo, selezionando i servizi di ricerca da interrogare, è possibile non solo stabilire i propri costi, ma anche decidere allo stesso tempo quali progetti si vogliono sostenere. Da qui anche la fatturazione basata sui token.",
        ],
    ],
    "payment-methods" => [
        "heading" => "Metodi di pagamento",
        "texts" => [
            "Le chiavi MetaGer sono state progettate da noi in modo tale da non richiedere alcun dato personale. Tuttavia, al più tardi durante l'esecuzione di un pagamento, vengono solitamente richiesti alcuni dati. Ad esempio, l'IBAN del conto di pagamento o l'indirizzo e-mail del conto PayPal utilizzato. Il SUMA-EV non tratta direttamente questi dati e non li memorizza. Tuttavia, a seconda del metodo di pagamento, lo fa il fornitore di servizi di pagamento.",
            "Pertanto, i nostri metodi di pagamento sono configurati in modo tale da dover raccogliere il minor numero possibile di dati dell'utente, e in alcuni casi addirittura nessuno.",
        ],
        "anonymous" => "Metodi di pagamento anonimi",
        "more" => "Altri metodi di pagamento",
    ],
    /**
     * Die Namen der Zahlungsarten. Standen im "checkout"-Namensraum des
     * Keymanagers, der dort bleibt — hierher kopiert, weil diese Seite die
     * einzige war, die sie außerhalb des Bezahlvorgangs gebraucht hat.
     */
    "methods" => [
        "cash" => "Contanti",
        "prepay" => "Bonifico bancario",
        "card" => "Carta di credito/debito",
    ],
];
