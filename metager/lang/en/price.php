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
        "This is what your MetaGer key costs",
        "The most important summarized",
    ],
    "texts" => [
        "For each ad-free web search on MetaGer with default settings you will be charged <b>1 token</b>. You can top up your key with one of these token packages at any time.",
    ],
    "short-info" => [
        [
            "heading" => "Tokens remain valid for 2 years",
            "text" => "Your purchased token are designed to remain valid until they are used up. There is no standing order.",
        ],
        [
            "heading" => "30 days money back guarantee",
            "text" => "If you are dissatisfied with your key, you have 30 days after purchase to return the unused credit.",
        ],
        [
            "heading" => "Key is automatically set up and used in the browser",
            "text" => "You don't need to do anything else to use your MetaGer key in the search. After charging it, it is automatically set up in your browser and you will receive information on how to easily set it up on additional devices.",
        ],
        [
            "heading" => "No Tracking",
            "text" => "Use our <a href=\":linkapp\">Android app</a>, or our browser extension and be provably anonymous using <a href=\":linktokens\">anonymous tokens</a>.",
        ],
    ],
    "pricing" => [
        "heading" => "This is how our prices are composed",
        "texts" => [
            "The majority of our revenue flows directly on to the search services you query. We want to offer a sustainable concept, which implies that the queried search engines do not suffer any financial damage by providing anonymous and ad-free search results for MetaGer. In addition, there is a share to cover our personnel and server costs, and of course the fees for payment service providers and taxes are included in the prices.",
            "Thus, by selecting the search services to be queried, you can not only set your own costs, but also decide at the same time which projects you want to support. Hence also the token-based billing.",
        ],
    ],
    "payment-methods" => [
        "heading" => "Payment methods",
        "texts" => [
            "MetaGer keys have been designed by us in such a way that they do not require any personal data. Nevertheless, at the latest during the execution of a payment, some data is usually required. Be it the IBAN of the paying account, or the email address of the PayPal account used. The SUMA-EV does not process this data itself and does not store it. However, depending on the payment method, the payment service provider does.",
            "Therefore, our payment methods are configured in such a way that as little as possible, and in some cases even no user data at all, needs to be collected.",
        ],
        "anonymous" => "Anonymous payment methods",
        "more" => "Other payment methods",
    ],
    /**
     * Die Namen der Zahlungsarten. Standen im "checkout"-Namensraum des
     * Keymanagers, der dort bleibt — hierher kopiert, weil diese Seite die
     * einzige war, die sie außerhalb des Bezahlvorgangs gebraucht hat.
     */
    "methods" => [
        "cash" => "Cash",
        "prepay" => "Bank transfer",
        "card" => "Credit / debit card",
    ],
];
