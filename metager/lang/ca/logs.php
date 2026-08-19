<?php

return [
    'login' => [
        'hint' => 'Inicieu la sessió per accedir al vostre compte.',
        'email' => 'Adreça electrònica',
        'code' => 'Codi d\'inici de sessió',
        'email_sent' => 'Si aquest compte ja està registrat, us hem enviat un codi d\'inici de sessió per correu electrònic. Introduïu-lo per iniciar la sessió.',
        'submit' => 'Envia',
        'restart' => 'Inici de sessió nou'
    ],
    'overview' => [
        "hint" => 'Aquí trobareu un resum de les vostres comandes i informació sobre l\'ús de l\'API. Assegureu-vos que les dades de facturació següents estiguin actualitzades i siguin correctes',
        'invoice-data' => [
            "heading" => "Dades de facturació",
            "email" => "Adreça electrònica",
            "company" => "Empresa",
            "full_name" => "Nom",
            "first_name" => "Nom",
            "last_name" => "Cognoms",
            "street" => "Carrer i número",
            "postal_code" => "Codi postal",
            "city" => "Població",
            "save" => "Desa",
            "update" => "Actualitza les dades de facturació"
        ],
        "abo" => [
            "heading" => "Accés a les dades actuals",
            "hint" => "Aquí podeu configurar l'accés als registres de consultes de cerca de MetaGer per als propers mesos. L'accés es renovarà automàticament segons l'interval de pagament seleccionat.",
            "interval" => [
                "label" => "Interval de pagament",
                "setting_values" => [
                    "never" => "Mai",
                    "monthly" => "mensual",
                    "annual" => "anual",
                    "quarterly" => "trimestral",
                    "six-monthly" => "semestral"
                ]
            ],
            "last_invoice" => "Última factura",
            "next_invoice" => "Propera factura",
            "never" => "Mai",
            "create" => "Configura",
            "update" => "Actualitza"
        ]
    ],
    "create_abo" => [
        "heading" => "Configura la subscripció",
        "interval" => "Interval de pagament",
        "conditions" => "Termes i condicions",
        "amount" => "Import de cada pagament",
        "conditions_hint" => "Emetem automàticament una factura per a cada interval de pagament. El vostre accés inclou l'accés als registres de MetaGer de tots els mesos compresos en el període de facturació (inclòs l'actual). La factura del període següent s'emetrà, sempre que sigui possible, un mes abans que comenci, per tal que l'ús sigui ininterromput.",
        "nda" => "Acord de confidencialitat (NDA)",
        "conditions_nda" => "Les dades proporcionades poden contenir dades personals, encara que no estiguin ordenades. Per aquest motiu, no podeu fer-les públiques de cap manera. Això inclou especialment les dades en brut, però també els models apresos a partir seu en l'àmbit de l'aprenentatge automàtic. Ara bé, sí que és possible donar accés públic a les respostes d'un model. Llegiu atentament l'acord de confidencialitat (NDA) següent i deseu-lo per als vostres arxius abans d'acceptar-lo continuant.",
        "accept" => "Accepto l'acord de confidencialitat (NDA) i les condicions de pagament",
        "cancel" => "Cancel·la la subscripció actual"
    ],
    "orders" => [
        "heading" => "Comandes",
        "status" => [
            "4" => "Completada",
            "5" => "Cancel·lada",
            "6" => "Reemborsada",
            "3" => "Parcialment pagada",
            "2" => "Lliurada",
            "1" => "Esborrany",
            "-1" => "Vençuda",
            "-2" => "Pagament pendent",
            "-3" => "Vista"
        ],
        "thead" => [
            "from" => "Accés des de",
            "to" => "Accés fins a",
            "price" => "Import de la factura",
            "status" => "Estat de la factura",
            "invoice" => "Factura"
        ]
    ],
    "api_keys" => [
        "heading" => "Clau d'API",
        "hint" => "Per poder consultar l'API us heu d'autenticar. Aquí podeu crear claus d'API per als vostres dispositius. <b>Nota</b>: les claus acabades de crear només es poden llegir un cop. Deseu-les just després de crear-les.",
        "thead" => [
            "name" => "Dispositiu",
            "key" => "clau",
            "created_at" => "Creada",
            "accessed_at" => "Últim accés",
            "actions" => "Accions"
        ],
        "new" => [
            "heading" => "Crea una clau nova",
            "name" => "Nom del dispositiu",
            "placeholder_name" => "Portàtil",
            "submit" => "Crea"
        ],
        "copy" => "Copia",
        "delete" => "Elimina"
    ],
    "api-docs" => [
        "hint" => "A continuació trobareu la documentació de la nostra API, que podeu fer servir per obtenir registres del nostre servidor.",
        "link" => "Documentació de l'API",
    ]
];
