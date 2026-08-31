<?php
return [
    /**
     * The account, wherever it appears: the pill in the corner, the block at the
     * top of the site menu, and the one alert that interrupts.
     *
     * Its own file rather than more keys under index/sidebar, because the same
     * strings are now rendered from three different views on two different
     * layouts, and none of them is "the index page".
     */
    'pill' => [
        'charge' => ':charge Token',
        // Shown instead of the key code when the key cannot be named — a legacy
        // non-UUID key whose canonical form we could not resolve.
        'signed_in' => 'Connesso',
        // The webextension: the key never reaches us, so there is nothing to name.
        'anonymous' => 'connesso in modo anonimo',
        // The mark is decorative and the code is styled shorthand, so the whole
        // answer has to be in the label for anyone not reading the pixels.
        //
        // "ending in", not a bare "key :fingerprint": these are the last six
        // characters of the key (KeyUser::getKeyFingerprint()), and calling them
        // the key invited people to type them into the sign-in form — where,
        // until the guard in the keymanager's POST /key/enter, they resolved to
        // an empty phantom account.
        'aria' => 'Il mio account – chiave che termina con :fingerprint, :charge Token',
        'aria_nocharge' => 'Il mio account – chiave che termina con :fingerprint',
        'aria_nofingerprint' => 'Il mio account – :charge Token',
        'aria_anonymous' => "Il mio account – connesso in modo anonimo tramite l'estensione web",
    ],
    'sidebar' => [
        'balance' => ':charge Token · senza pubblicità',
        // Not "0 Token · …": at zero the searches are not ad-free, they do
        // not happen at all.
        'balance_empty' => 'Token esauriti',
        'manage' => 'Gestisci',
        'topup' => 'Ricarica',
        'logout' => 'Esci',
        'login' => 'Accedi',
        'create' => 'Configura',
        'logged_out' => "Non hai effettuato l'accesso. Con una chiave cerchi senza pubblicità e in modo anonimo.",
        'anonymous_hint' => "Senza pubblicità · gestito dall'estensione web",
        // The webextension holds the key, so the account lives in its popup.
        // The button this labels is hidden until the extension reveals it.
        'extension_settings' => "Gestisci nell'estensione",
    ],
    /**
     * The account page itself — /konto, moved here from /keys/key/<uuid>.
     *
     * Taken from the keymanager's pass/lang/<locale>/key.json, but mostly new.
     * The old page was almost nothing but button labels; what it never said is
     * what any of them are *for* — which is exactly what support gets asked.
     *
     * Not carried over: `key.share.*`. The share button handed the settings URL,
     * key included, to `navigator.share` and therefore to the operating system's
     * share sheet. Passing an account on is not something a button should
     * advertise; whoever wants to can copy the URL. The copy button stayed.
     */
    'page' => [
        'heading' => 'Il mio account',

        // Not "your key: 123456". These are the last six characters, and calling
        // them the key led people to type them into the sign-in form.
        'fingerprint' => 'Chiave che termina con :fingerprint',
        'fingerprint_unknown' => 'Accesso effettuato',

        'balance' => [
            'unit' => 'token',
            'one_token' => 'Un token corrisponde a una ricerca.',
            'valid_until' => 'Credito valido fino al :date',
            'empty' => 'Credito esaurito. Senza token non può cercare: ricarichi per continuare.',
            'low' => 'Il credito sta per esaurirsi.',
            'unknown' => 'Al momento non riusciamo a leggere il suo credito. Dipende da noi e non da lei — riprovi tra qualche minuto.',
            'orders_summary' => 'Da :count ricariche, che scadono una dopo l\'altra',
            'orders_heading' => 'Date di scadenza',
            'order' => ':amount token fino al :date',
        ],

        'actions' => [
            'topup' => 'Ricaricare il credito',
            'search' => 'Vai alla ricerca',
        ],

        'charge' => [
            'heading' => 'Ricaricare il credito',
            'lede' => 'Un token è una ricerca e costa un centesimo. Tutti i prezzi sono comprensivi di IVA.',
            'tokens' => ':amount token',
            'price' => ':price €',
            'more' => 'Tutti i prezzi e i metodi di pagamento',

            /**
             * Why no package is on offer right now. Three sentences for three
             * states, all three of which the old page had too — except that it
             * said "your key is already fully charged", which is not true: what
             * is full is not the balance but the number of open top-ups.
             */
            'blocked' => [
                'proxy' => 'Sta navigando attraverso una delle nostre sessioni proxy. Nel frattempo la ricarica è disattivata per la sua sicurezza: un pagamento porta a un fornitore di servizi di pagamento, e questo non deve vedere questa sessione. Apra questa pagina senza sessione proxy per ricaricare.',
                'full' => 'Su questa chiave ci sono già tre ricariche. Non appena la più vecchia sarà esaurita o scaduta, potrà ricaricare di nuovo.',
                'member' => 'È socio di SUMA-EV e cerca senza costi aggiuntivi. Non le serve alcun pacchetto di token.',
            ],
        ],

        /**
         * The section the old page did not have: QR code, settings URL and the
         * transfer button sat there in one row, with not a sentence about what
         * they are for.
         */
        'save' => [
            'heading' => 'Metta al sicuro il suo accesso',
            'text' => 'Finché questo browser conserva il cookie, lei resta connesso. Se lo perde — un nuovo dispositivo, dati di navigazione cancellati —, la sua chiave è l\'unico modo per tornare al suo credito. Eccola, ed ecco tre modi per portarla con sé.',

            /**
             * The key itself.
             *
             * It has to be here — the sign-in form asks for it first of all —
             * and it is collapsed, because this page gets photographed for
             * support tickets. The old page showed it large and always.
             */
            'key' => [
                'summary' => 'Mostra e copia la chiave',
                'label' => 'La sua chiave',
                'action' => 'Copia la chiave',
                'hint' => '36 caratteri. Sono quelli con cui accede su qualsiasi altro dispositivo. Chiusa perché di questa pagina si scattano spesso fotografie: chi vede la sua chiave cerca a sue spese.',
            ],

            'qr' => [
                'label' => 'Codice QR',
                'alt' => 'Codice QR che porta alla sua chiave',
                'action' => 'Salva come immagine',
                'hint' => 'L\'immagine che il modulo di accesso richiede. Può caricarla lì o fotografarla con la fotocamera.',
            ],

            'url' => [
                'label' => 'Segnalibro',
                'action' => 'Copia l\'URL',
                'hint' => 'Aprire questo URL ripristina la chiave insieme alle impostazioni di ricerca di questo browser.',
            ],

            /**
             * The transfer dialog. The keymanager called it "generate login
             * code" — a label naming the means rather than the end, which is why
             * it never answered "how do I get MetaGer onto my phone?", even
             * though that is exactly what the button does.
             */
            'transfer' => [
                'label' => 'Un altro dispositivo',
                'action' => 'Accedi su un dispositivo',
                'hint' => 'Mostra un codice breve da digitare nel modulo di accesso dell\'altro dispositivo, invece di ricopiare l\'intera chiave.',

                'title' => 'Accedere su un altro dispositivo',
                'description' => 'Inserisca questo codice sull\'altro dispositivo nel modulo di accesso, dove di norma va la chiave.',
                'waiting' => 'Recupero del codice …',
                'note' => 'Il codice vale per un solo accesso e solo finché è visibile qui. Chiuda questa finestra non appena l\'ha inserito.',
                'failed' => 'Non è stato possibile recuperare il codice. Chiuda la finestra e riprovi tra un istante.',
                'close' => 'Chiudi',
            ],
        ],

        /**
         * What still lives in the keymanager. A list at the foot rather than the
         * three equal tabs of before: hardly anybody has campaigns, and a third
         * tab claimed otherwise.
         */
        'more' => [
            'heading' => 'Altro',
            'orders' => 'Ordini e fatture',
            'campaigns' => 'Campagne di buoni',
            'help' => 'Aiuto sulla chiave',
            'logout' => 'Esci',
            // Signing out only clears the cookie. Someone who does not know
            // that will not click it — and someone who reads it as "delete
            // account" certainly will not.
            'logout_hint' => 'Rimuove la chiave da questo browser. Il credito resta sulla chiave.',
        ],
    ],

    'empty' => [
        'message' => 'I tuoi Token sono esauriti.',
        'action' => 'Ricarica ora',
    ],
];
