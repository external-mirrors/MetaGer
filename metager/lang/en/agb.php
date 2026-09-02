<?php

/**
 * Allgemeine Geschäftsbedingungen für die Token-Aufladung — /agb.
 *
 * Vertragstext, aus pass/lang/<locale>/agb.json des Keymanagers übernommen.
 * Tests\Feature\AgbTest vergleicht die gerenderte deutsche Fassung Zeile für
 * Zeile mit einem Abzug der alten Seite; jede Abweichung steht dort
 * ausgeschrieben, damit sie mit rechtlichem Blick nachlesbar bleibt. Es sind
 * drei:
 *
 *   - Der Text nennt seine eigene Fundstelle. Die stand wörtlich als
 *     "metager.de/keys/agb" im Vertrag und ist jetzt der Platzhalter :agburl.
 *   - Die Paketliste in §4 nannte 12000 Token, die es nicht zu kaufen gibt,
 *     und verschwieg die 500, die es gibt. Sie zählt jetzt genau das auf, was
 *     der Checkout verkauft — AgbTest::testTheTokenPackagesAreTheOnesThatCanBeBought
 *     vergleicht sie in allen Sprachen mit App\Landing\KeyPrice.
 *   - Weil sich der Vertragstext damit geändert hat, ist auch das "Stand:"-
 *     Datum weitergerückt.
 */

return [
    "heading" => "General Terms and Conditions for Token Top-Up (on Key)",
    "date" => "Status: August 2026",
    "translationNotice" => "Note: This is a translation of the valid German terms and conditions. The legally binding version can be found <a href=\":linkGerman\">here</a>",
    "paragraphs" => [
        [
            "heading" => "Provider, Scope and Amendments",
            "paragraphs" => [
                "The following General Terms and Conditions apply to the business relationships between users of the services of the websites metager.de and metager.org, in particular the top-up of tokens on the key, and the operator SUMA-EV. In the following, the 'users' of the token top-up / the key are also referred to as 'users', and SUMA-EV is hereinafter referred to as 'MetaGer'.",
                "These GTC are available at any time at :agburl, can be accessed, saved, and printed at any time. Past orders can be viewed in the customer area under 'Manage key – Orders' by entering the payment ID. This is only possible within 30 days from the purchase date.",
                "These conditions apply exclusively to users who are consumers within the meaning of § 13 of the German Civil Code. A consumer is any natural person who enters into a legal transaction for purposes that are predominantly neither commercial nor self-employed.",
                "MetaGer reserves the right to expand or restrict the user group and the group of eligible participants and further reserves the right to change or supplement these general terms and conditions for 'users' at any time if this is necessary in the interest of simple or secure processing or to prevent misuse. Changes to the general terms and conditions will be announced by publication on the MetaGer website. If the user does not agree with such changes or additions to the GTC, he must object to the change in writing to MetaGer within 4 weeks. Otherwise, the amended GTC are deemed approved and thus become an effective part of the contract.",
                "The online search engine metager.de, its partner sites, and associated software are operated by SUMA-EV. The registered office of SUMA-EV is Henniesruh 28D, 30655 Hannover. SUMA-EV is represented by the board, which in turn is represented by the managing director Dominik Hebeler. Registration number: VR200033, Register court: Amtsgericht Hannover.",
                "The following contact details apply:\nPhone: +49 511 34000070\nFax: +49 511 34001023\nContact form: metager.de/kontakt\n*domestic landline number.\n",
                "According to the regulation on online dispute resolution in consumer matters, we refer to the following link: http://ec.europa.eu/consumers/odr/",
            ],
        ],
        [
            "heading" => "Conclusion of Contract and Payment Terms",
            "paragraphs" => [
                "The provision of the various token packages by MetaGer does not constitute a legally binding contractual offer, but only a non-binding invitation to the user to make a top-up or purchase. By clicking the 'Make payment' button or a comparable text, the user submits a legally binding offer to conclude a purchase contract with MetaGer.",
                "Before submitting the order bindingly, the user can return to the website where the information is recorded and correct input errors or cancel the process by closing the internet browser by pressing the 'Back' button in the internet browser used after checking his details.",
                "The stated prices include statutory VAT and other price components. As this is a service, no shipping is required and the tokens are made available immediately after the payment process has been completed. Payment in advance is possible. If the user has chosen payment in advance, he undertakes to pay the purchase price immediately after conclusion of the contract.",
            ],
        ],
        [
            "heading" => "Warranty, Contract Language and Customer Service",
            "paragraphs" => [
                "The statutory warranty regulations apply.",
                "The contract language is German.",
                "A customer service for questions, complaints and objections is available on weekdays from 9:00 a.m. to 4:00 p.m. at the contact details of SUMA-EV.",
            ],
        ],
        [
            "heading" => "Key, Payment Options and Top-Up",
            "paragraphs" => [
                "The user can set up a credit account, hereinafter referred to as a key, top up credit on it and thus purchase tokens. Payment options include credit card and PayPal, among others. Cash payment by mail to the address of MetaGer given above is also possible.",
                "To use a MetaGer key and to top up tokens on it, the respective individual key must first be created on the MetaGer website.",
                "Depending on the selected package, the user receives exactly the purchased tokens for free (unlimited) use. The following purchase options are available:",
                [
                    "500 tokens: 5 euros",
                    "1000 tokens: 10 euros",
                    "2000 tokens: 20 euros",
                    "3000 tokens: 30 euros",
                    "4000 tokens: 40 euros",
                    "6000 tokens: 60 euros",
                ],
                "Through marketing campaigns with third parties as part of partner campaigns and customer loyalty programs, the user can also receive keys. In this case, these GTC and, if applicable, the respective campaign conditions always apply.",
            ],
        ],
        [
            "heading" => "Validity and Redemption of Tokens",
            "paragraphs" => [
                "Tokens can be redeemed by each user within the specified validity interval without limitation. The availability of the purchased tokens and how often they can be redeemed within a certain period is indicated on the overview page in the key.",
                "From the purchase of the tokens, they are valid for two calendar years. The validity date is always noted on the overview. After the validity expires, the offer also expires.",
                "After purchasing a token package, it is loaded directly onto the key.",
                "All top-ups as well as the entire process from key creation to token redemption are completely anonymous. The only exception is the data necessary for processing the payment.",
                "As proof of the top-up, MetaGer is entitled to check the payment process.",
                "The user is at no time obliged to provide his personal data when topping up the key. All information provided by him in this regard is voluntary. However, certain personal data may be required for invoicing and payment processing. Accordingly, the user must provide all information truthfully.",
                "The purchased token packages and the resulting tokens on a MetaGer key are not transferable. However, the transfer of the respective key by the user is expressly permitted by MetaGer.",
            ],
        ],
        [
            "heading" => "Liability",
            "paragraphs" => [
                "MetaGer is not liable for damages resulting from the use of the service. MetaGer does not guarantee or assume any responsibility for the correctness, completeness, reliability, quality and timeliness of other sites resulting from the use of the services.",
                "MetaGer provides an online service.",
                "MetaGer voluntarily offers the possibility to refund the purchase price of unused tokens, provided that the payment method used by the user supports this. Cash payment transactions are excluded. The refund must be requested by the user within 30 days of completion of the purchase process. For this purpose, the corresponding payment ID must be entered on the overview page.",
                "Tokens that have expired due to the passage of time are not refundable.",
                "MetaGer always endeavors to keep the functions as available as possible. MetaGer assumes no guarantee or liability for the availability of the internet or mobile network.",
                "MetaGer is only liable for intent and gross negligence. These and the above limitations of liability do not apply to liability for personal injury, liability under the Product Liability Act or liability for the breach of essential contractual obligations. Essential contractual obligations are those that are absolutely necessary for the proper execution of a contract so that the achievement of the purpose of the contract is not jeopardized and on whose compliance the customer may regularly rely. If such an essential contractual obligation is culpably breached, liability is limited to the typical contractual and foreseeable damage at the time of conclusion of the contract.",
                "All limitations and exclusions of liability also apply accordingly to representatives, executive employees, bodies and other vicarious agents and assistants of MetaGer.",
                "The user undertakes not to use the services offered for abusive purposes. In particular, it is abusive to provide third-party personal data for the purpose of deception or to obtain advantages.",
                "If the user intends to use the service beyond the usual household scope, this must be reported to MetaGer informally, preferably via the contact form, at the beginning of such use.",
            ],
        ],
        [
            "heading" => "Final Provisions",
            "paragraphs" => [
                "German law applies. The application of the UN Convention on Contracts for the International Sale of Goods is excluded.",
                "Should individual or several provisions of these General Terms and Conditions be or become invalid, this shall not affect the validity of the remaining provisions of these GTC. The parties undertake to replace invalid or void provisions with new provisions that legally comply with the economic content of the invalid or void provisions. The same applies if a gap should become apparent in the contract. To fill the gap, the parties undertake to work towards the establishment of appropriate provisions in this contract that come as close as possible to what the parties would have determined according to the meaning and purpose of this contract if they had considered the point. If no agreement is reached, the law shall apply additionally.",
            ],
        ],
    ],
];
