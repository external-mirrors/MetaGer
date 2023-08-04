<?php
return [
    'title' => 'MetaGer - Help',
    "backarrow" => 'Back',
    'glossary' => 'By clicking on the symbol<a title="This symbol leads to the glossary" href="/help/easy-language/glossary" ><img class="glossary-icon lm-only" src="/img/glossary-icon-lm.svg"/><img class="glossary-icon dm-only" src="/img/glossary-icon-dm.svg"/></a>, you can access explanations for difficult words.',
    "suchfunktion" => [
        "title" => "Search Functions",
    ],
    "stopworte" => [
        "title" => 'Stop Words',
        "1" => "Stop words are words you don't want to see. <br> If you don't want to see a word, do this:",
        "2" => "Example: <br> You want to search for a new car. <br> You don't want to see the word <strong>BMW</strong>. <br> So write:",
        "3" => "car new -bmw",
        "4" => "Put a minus sign in front of the word. <br> It will no longer appear in the search results.",
    ],
    "mehrwortsuche" => [
        "title" => "Multi-Word Search",
        "1" => "The multi-word search has 2 types.",
        "2" => "One word should be present in the results. <br> Then write it in quotation marks. <br> It looks like this:",
        "3" => [
            "0" => "Example: <br> You are searching for <strong>the round table</strong>. <br> You want to find the word <strong>round</strong> in the results. <br> So write the word like this:",
            "example" => 'the "round" table',
        ],
        "4" => 'There is another type of multi-word search. <br> You can also search for complete sentences. <br> You want to see a sentence exactly as written in the results. <br> Then do it like this:',
        "5" => [
            "0" => "Example: <br> You are searching for <strong>the round table</strong>.<br> You want to see it in exactly that order. <br> Write it like this:",
            "example" => '"the round table"',
        ],
    ],
    "exactsearch" => [
        "title" => "Exact Search",
        "1" => "With the exact search, what you write is exactly what is searched. <br> A word should appear exactly as written in the results. <br> Then write it with a plus sign before the word. <br> It looks like this: ",
        "2" => "Example: You are searching for the word 'example'. <br> You want the word to appear exactly as written in the results. <br> So write the word like this: ",
        "3" => "You can also search for complete sentences exactly as written in the results.",
        "4" => "Example: You are searching for an example sentence. <br> Write it like this:",
        "example" => [
            "1" => "+exampleword",
            "2" => '+"example phrase"',
        ],
    ],
    "bang" => [
        "title" => "!Bangs",
        "1" => "MetaGer supports a writing style called '!bang syntax'. <br> If you want to use it, it looks like this: <br> <strong>!twitter</strong> or <strong>!facebook</strong><br> Example:<br> You want to search for cats on Twitter. <br> So enter it like this:",
        "example" => "!twitter cat",
        "2" => "This will display a field on the right side while searching. <br> It looks like this:",
        "3" => "You can click the blue button. <br> Then the web page of Twitter with the search for cats will open. <br> This feature does not work on small screens like mobile phones.",
    ],
    "key" => [
        "maintitle" => 'MetaGer Key',

        "title" => [
            "1" => "Adding MetaGer Key",
            "2" => "Colored MetaGer Key",
        ],
        "alt" => [
            "empty" => 'Image of a red/orange key',
            "low" => 'Image of a yellow key',
            "full" => 'Image of a green key',
            "none" => 'Image of a gray key',
        ],

        "1" => 'You can search with us without seeing ads. <br> For this, you need a MetaGer Key. <br> You can buy it from us. <br> After payment, you will receive a password. <br> We call this password a key. <br> You can use the key on multiple devices simultaneously. <br> To do this, you need to set up the key. <br> First, open the administration page of the MetaGer Key. <br> There you have these options:',
        "2" => 'Login Code <br> To register another device with the login code, do the following: <br> Click the button <strong>Generate Login Code</strong>. <br> The button looks like this: <br> <img class="help-easy-language-image " src="/img/help-key-login-button-en.png"/> <br> Then, 6 numbers will be displayed. <br> They look like this, for example: <br><img class="help-easy-language-image " src="/img/help-key-login-code-en.png"/> <br> Enter these numbers on the device you want to add. <br> The 6 numbers are only valid once. <br> So, if you want to set up multiple devices, you need to do it each time anew.', 
        "3" => 'Copy URL <br> You can also copy the internet address. <br> To do this, click the button <strong>Copy URL</strong>. <br> The button looks like this: <br> <img class="help-easy-language-image " src="/img/help-key-url-button-en.png"/> <br> Now you have copied the link. <br> You can use the link to search with the MetaGer Key. ',   
        "4" => 'Save File <br> You can also save your MetaGer Key as a file. <br> To do this, click the button <strong>Save to File</strong>. <br> Now you have saved your key as a file. <br> Next, open the page for setting up the key on the new device. <br> It looks like this: <br> <img class="help-easy-language-image help-easy-language-key-image" src="/img/help-key-add-en.png"/> <br> Then click the button <strong>Upload Backup File</strong>. <br> The button looks like this: <br><img class="help-easy-language-image help-easy-language-key-image" src="/img/help-key-add-file-en.png"/> <br> Now, select the file with the MetaGer Key. <br> Then you can use the MetaGer Key.', 
        "5" => 'Scan QR Code <br> To register another device with the QR code, do the following: <br> Open the page for setting up the key on the new device. <br> It looks like this: <br> <img class="help-easy-language-image help-easy-language-key-image" src="/img/help-key-add-en.png"/> <br> Then click the button <strong>Scan QR Code</strong>. <br> The button looks like this: <br> <img class="help-easy-language-image help-easy-language-key-image" src="/img/help-key-qr-code-en.png"/> <br> Now scan the QR code. <br> After that, you can search without ads on your device. ',   
        "6" => 'Enter Manually <br> Of course, you can also enter the key manually. <br> To do this, type the long sequence of numbers and letters. ',   
        "7" => 'Sometimes you will see a colored key. <br> There is a reason for this. <br> The colors indicate how many searches without ads you have left. <br> There are the following colors: ',   
        "8" => 'Gray Key: <br> You see a gray key. <br> Then you have not set up a key yet.',   
        "9" => 'Red Key: <br> You see a red/orange key. <br> Then your balance is empty. <br> You can no longer search without ads. <br> We recommend recharging the key. <br> Then you can search without ads again.', 
        "10" => 'Green Key: <br> You see a green key. <br> Then you are currently searching without ads. <br> So, you can continue to search without ads.',   
        "11" => 'Yellow Key: <br> You see a yellow key. <br> Then you have almost used up your balance. <br> You currently have less than 30 tokens for use. <br> We recommend recharging the key soon. ',   
    ],
    "selist" => [
        "title" => [
            '0' => 'Adding MetaGer to the Browser\'s Search Engine List',
            '1' => 'Installing MetaGer',
        ],
        "explanation" => [
            '1' => 'On the homepage, there is a field <strong>Install MetaGer</strong>. <br> The field is below the search field. <br> It looks like this: <br>',
            '2' => 'Sometimes you may need to enter a URL. <br> It looks like this: <br> https://metager.de/meta/meta.ger3?eingabe=%s <br> If you encounter any problems, contact us using the <a href="/contact" target="_blank" rel="noopener">contact form</a>.',
        ],
    ],
];
