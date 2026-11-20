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
        'signed_in' => 'Connecté',
        // The webextension: the key never reaches us, so there is nothing to name.
        'anonymous' => 'connecté anonymement',
        // The mark is decorative and the code is styled shorthand, so the whole
        // answer has to be in the label for anyone not reading the pixels.
        //
        // "ending in", not a bare "key :fingerprint": these are the last six
        // characters of the key (KeyUser::getKeyFingerprint()), and calling them
        // the key invited people to type them into the sign-in form — where,
        // until the guard in the keymanager's POST /key/enter, they resolved to
        // an empty phantom account.
        'aria' => 'Mon compte – clé se terminant par :fingerprint, :charge Token',
        'aria_nocharge' => 'Mon compte – clé se terminant par :fingerprint',
        'aria_nofingerprint' => 'Mon compte – :charge Token',
        'aria_anonymous' => "Mon compte – connecté anonymement via l'extension web",
    ],
    'sidebar' => [
        'balance' => ':charge Token',
        // At zero there is nothing left to search with, so not "0 tokens".
        'balance_empty' => 'Plus de Token',
        'manage' => 'Gérer',
        'topup' => 'Recharger',
        'logout' => 'Se déconnecter',
        'login' => 'Se connecter',
        'create' => 'Configurer',
        'logged_out' => 'Non connecté. Avec une clé, vous cherchez anonymement.',
        'anonymous_hint' => 'Géré par l\'extension web',
        // The webextension holds the key, so the account lives in its popup.
        // The button this labels is hidden until the extension reveals it.
        'extension_settings' => "Gérer dans l'extension",
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
        'heading' => 'Mon compte',

        // Not "your key: 123456". These are the last six characters, and calling
        // them the key led people to type them into the sign-in form.
        'fingerprint' => 'Clé se terminant par :fingerprint',
        'fingerprint_unknown' => 'Connecté',

        'balance' => [
            'unit' => 'jetons',
            'one_token' => 'Un jeton correspond à une recherche.',
            'valid_until' => 'Solde valable jusqu\'au :date',
            'empty' => 'Plus de solde. Sans jetons, vous ne pouvez pas effectuer de recherche : rechargez pour continuer.',
            'low' => 'Votre solde touche à sa fin.',
            'unknown' => 'Nous ne parvenons pas à consulter votre solde pour le moment. Cela vient de nous et non de vous — réessayez dans quelques minutes.',
            'orders_summary' => 'Issu de :count recharges, qui expirent l\'une après l\'autre',
            'orders_heading' => 'Dates d\'expiration',
            'order' => ':amount jetons jusqu\'au :date',
        ],

        'actions' => [
            'topup' => 'Recharger le solde',
            'search' => 'Aller à la recherche',
        ],

        'charge' => [
            'heading' => 'Recharger le solde',
            // The card's opening line sets up the choice: membership, recommended, or
            // a one-off package. The token price moved down to once.lede, next to the
            // packages it explains.
            'lede' => 'Deux façons de recharger votre clé. Nous vous recommandons la première.',
            'tokens' => ':amount jetons',
            'price' => ':price €',
            'or' => 'ou',
            'once' => [
                'heading' => 'Recharger une seule fois',
                'lede' => 'Sans adhésion et sans engagement. Un jeton correspond à une recherche et coûte un centime. Tous les prix s\'entendent TVA comprise.',
            ],

            /**
             * Membership, offered first and in every language ({@see \App\Support\MembershipOffer}).
             * A non-profit may give nothing in return for a membership fee, so the
             * text never says the fee pays for the key: members take part in the
             * research the statutes name by searching, and that is why their key is
             * topped up. `language` is shown everywhere but on the German interface.
             */
            'membership' => [
                'badge' => 'Notre suggestion',
                'heading' => 'Faites partie de MetaGer',
                'text' => 'Nos membres portent MetaGer. <a href="https://suma-ev.de" target="_blank" rel="noopener">SUMA-EV</a>, l\'association à but non lucratif qui le gère, mène des recherches sur la technologie des moteurs de recherche, comme le prévoient <a href="https://suma-ev.de/satzung" target="_blank" rel="noopener">ses statuts</a>. Ces recherches ont besoin d\'un moteur de recherche utilisé au quotidien, en conditions réelles, et nos membres le rendent possible en cherchant avec MetaGer. C\'est pourquoi leur clé est rechargée chaque mois, sans frais supplémentaires.',
                'points' => [
                    'refill' => 'Votre clé est rechargée chaque mois, sans frais supplémentaires',
                    'anonymous' => 'Grâce aux <a href=":url">jetons anonymes</a>, vous cherchez de manière anonyme, même en tant que membre',
                    'amount' => 'Vous fixez vous-même le montant de votre cotisation, à partir de 5 € par mois',
                    'vote' => 'Avec votre voix à l\'assemblée générale, vous participez aux décisions de l\'association',
                ],
                'language' => [
                    'heading' => 'Bon à savoir : nos assemblées se tiennent en allemand',
                    'text' => 'Le formulaire d\'adhésion et l\'e-mail de bienvenue vous parviennent dans votre langue. Les convocations à l\'assemblée générale et leurs procès-verbaux sont en allemand, la langue de travail de l\'association, et les membres les reçoivent actuellement par e-mail. Dans les mois à venir, nous proposerons une adhésion de soutien sans ces e-mails.',
                ],
                'action' => 'Devenir membre',
            ],

            /**
             * Why no package is on offer right now. Three sentences for three
             * states, all three of which the old page had too — except that it
             * said "your key is already fully charged", which is not true: what
             * is full is not the balance but the number of open top-ups.
             */
            'blocked' => [
                'proxy' => 'Vous naviguez actuellement via l\'une de nos sessions proxy. Pendant ce temps, la recharge est désactivée pour votre sécurité : un paiement mène à un prestataire de paiement, et celui-ci ne doit pas voir cette session. Ouvrez cette page sans session proxy pour recharger.',
                'full' => 'Cette clé porte déjà trois recharges. Dès que la plus ancienne sera épuisée ou expirée, vous pourrez recharger de nouveau.',
                'member' => 'Vous êtes membre de SUMA-EV et effectuez vos recherches sans frais supplémentaires. Vous n\'avez pas besoin de pack de jetons.',
            ],
        ],

        /**
         * The section the old page did not have: QR code, settings URL and the
         * transfer button sat there in one row, with not a sentence about what
         * they are for.
         */
        'save' => [
            'heading' => 'Sécurisez votre accès',
            'text' => 'Tant que ce navigateur conserve le cookie, vous restez connecté. S\'il le perd — nouvel appareil, données de navigation effacées —, votre clé est le seul chemin de retour vers votre solde. La voici, et voici les façons de l\'emporter.',

            /**
             * The key itself.
             *
             * It has to be here — the sign-in form asks for it first of all.
             * No longer collapsed: the QR code below it carries the same key
             * and is never collapsed, so hiding it here bought nothing.
             */
            'key' => [
                'label' => 'Votre clé',
                'action' => 'Copier la clé',
                'hint' => '36 caractères. C\'est avec eux que vous vous connectez sur tout autre appareil.',
            ],

            'qr' => [
                'label' => 'Code QR',
                'alt' => 'Code QR menant à votre clé',
                'action' => 'Enregistrer comme image',
                'hint' => 'L\'image que demande le formulaire de connexion. Vous pouvez l\'y téléverser ou la photographier avec l\'appareil photo.',
            ],

            'url' => [
                'label' => 'Signet',
                'action' => 'Copier l\'URL',
                'hint' => 'Ouvrir cette URL rétablit la clé ainsi que les paramètres de recherche de ce navigateur.',
            ],

            /**
             * The transfer dialog. The keymanager called it "generate login
             * code" — a label naming the means rather than the end, which is why
             * it never answered "how do I get MetaGer onto my phone?", even
             * though that is exactly what the button does.
             */
            'transfer' => [
                'label' => 'Autre appareil',
                'action' => 'Connecter un appareil',
                'hint' => 'Affiche un code court que vous saisissez dans le formulaire de connexion de l\'autre appareil, au lieu de recopier la clé entière.',

                'title' => 'Connecter un autre appareil',
                'description' => 'Saisissez ce code sur l\'autre appareil, dans le formulaire de connexion, à l\'endroit où figure normalement la clé.',
                'waiting' => 'Récupération du code …',
                'note' => 'Le code n\'est valable que pour une seule connexion et seulement tant qu\'il est affiché ici. Fermez cette fenêtre dès que vous l\'avez saisi.',
                'failed' => 'Le code n\'a pas pu être récupéré. Fermez la fenêtre et réessayez dans un instant.',
                'close' => 'Fermer',
            ],
        ],

        /**
         * What still lives in the keymanager. A list at the foot rather than the
         * three equal tabs of before: hardly anybody has campaigns, and a third
         * tab claimed otherwise.
         */
        'more' => [
            'heading' => 'Divers',
            'orders' => 'Commandes et factures',
            'campaigns' => 'Campagnes de bons',
            'help' => 'Aide sur la clé',
            'logout' => 'Se déconnecter',
            // Signing out only clears the cookie. Someone who does not know
            // that will not click it — and someone who reads it as "delete
            // account" certainly will not.
            'logout_hint' => 'Retire la clé de ce navigateur. Le solde reste sur la clé.',
        ],
    ],

    'empty' => [
        'message' => 'Vos Token sont épuisés.',
        'action' => 'Recharger maintenant',
        'message_anonymous' => 'Vos Token anonymes sont épuisés.',
    ],
];
