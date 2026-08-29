<?php

/**
 * Fragen zum MetaGer-Schlüssel — /hilfe/schluessel.
 *
 * Aus dem "faq"-Zweig von pass/lang/<locale>/help.json des Keymanagers.
 */

return [
    "heading" => "Questions about the MetaGer key",
    "faqs" => [
        [
            "summary" => "How does the MetaGer key work?",
            "description" => "With a MetaGer key you search ad-free. You receive tokens from which one search is deducted per search. When you use a MetaGer key, all features that protect MetaGer from automated calls are disabled. This means that you won't see captcha requests and your IP address won't be kept for a limited time. Simply put, this will make MetaGer faster, more reliable and more secure.",
        ],
        [
            "summary" => "How does the anonymous token work?",
            "description" => "You can use the anonymous token with our browser extension or app. This will allow you to search even more securely with MetaGer. When using anonymous token, a part of your credit, in the form of random passwords, will be stored on your device. Through a <a href=\":tokenlink\">complex cryptographic process</a>, it becomes impossible even for us to associate your performed searches with each other, or with your key.",
        ],
        [
            "summary" => "How do I use the MetaGer key ?",
            "description" => "The MetaGer key is automatically set up and used in the browser. So you don't need to do anything else. If you want to use the MetaGer key on additional devices, there are several ways to set up the MetaGer key:",
            "steps" => [
                [
                    "heading" => "Copy URL",
                    "description" => "When you are on the MetaGer key management page, there is an option to copy a URL. With this URL all settings of MetaGer, as well as the MetaGer key can be saved on another device.",
                ],
                [
                    "heading" => "Save file",
                    "description" => "When you are on the MetaGer key management page, there is an option to save a file. This saves your MetaGer key as a file. You can then use this file on another device to log in there with your key.",
                ],
                [
                    "heading" => "Scan QR Code",
                    "description" => "Alternatively, you can also scan the QR code displayed on the administration page to log in to another device.",
                ],
                [
                    "heading" => "Enter MetaGer key manually",
                    "description" => "Of course, you can also enter the key manually on another device.",
                ],
            ],
        ],
        [
            "summary" => "I have to enter my key regulary. What can I do?",
            "description" => "We instruct your browser to permanently store the key once generated or logged in. Depending on your browser configuration you might have set it up to regularily delete cookies & website data which will of course log you out from MetaGer aswell. You have the following options:",
            "steps" => [
                [
                    "heading" => "Add an exception",
                    "description" => "In Firefox settings you can put MetaGer on a whitelist for an excemption of deleting cookies & website data which will keep you logged in.",
                ],
                [
                    "heading" => "Install our browser extension",
                    "description" => "Our browser extension for <a href=\"https://addons.mozilla.org/firefox/addon/metager-suche/\" target=\"_blank\" rel=\"noopener\">Firefox</a> and <a href=\"https://chrome.google.com/webstore/detail/gjfllojpkdnjaiaokblkmjlebiagbphd\" target=\"_blank\" rel=\"noopener\">Chrome</a> can store your search settings which include your key without using cookies so you can delete all browserdata without being logged out of MetaGer.",
                ],
                [
                    "heading" => "Login without entering the 36 character key",
                    "description" => "If you are using a password manager you can store the key in it so you can be logged in automatically. Alternatively we offer a <a href=\":keylink\">settings URL</a> to be stored i.e. as a bookmark. When opened the settings URL will log you in without manually entering the key.",
                ],
            ],
        ],
        [
            "summary" => "I am dissatisfied with the MetaGer key. What can I do?",
            "description" => "In this case, you can request a refund for unused tokens within 30 days of purchase. To do this, you will need your payment ID. To request a refund, open the MetaGer key management page. There, click on the \"Orders\" menu item and enter your payment ID. After that you can click on the button \"Request refund\" and send the refund request.",
        ],
        [
            "summary" => "How do I search completely anonymously?",
            "description" => "Your privacy and anonymity are very important to us. That is why we offer anonymous payment methods (cash). We also offer the use of <a href=\":tokenlink\">anonymous tokens</a>, which they can even use to search verifiably anonymously.",
        ],
        [
            "summary" => "I need an invoice. How do I get it?",
            "description" => "For this, you only need your payment ID. To request the invoice, open the MetaGer key administration page. Here you click on the \"Orders\" menu item and enter your payment ID. Now you can click on the button \"Request invoice\" and start the invoice request. For the invoice we need your full name, your e-mail address and your address.",
        ],
        [
            "summary" => "I would like to charge my MetaGer key automatically. How to do it?",
            "description" => "For our members, the key included in the membership is automatically topped up on a monthly basis. The amount of token here depends on the membership fee paid.",
        ],
        [
            "summary" => "I received a card or a link with a voucher code. What do I do with it?",
            "description" => "Some organizations give away MetaGer keys with a fixed amount of tokens via promotional cards or a link. Open <a href=\":voucherlink\">our redemption page</a>, enter the printed code, or scan the QR code on the card. You will immediately receive a new MetaGer key with the gifted tokens, valid for a limited time. Each code can only be redeemed once.",
        ],
    ],
    "more-questions" => "Do you have further questions? Then please feel free to use our <a href=\":contactlink\" target=\"_blank\">contact form</a>.",
];
