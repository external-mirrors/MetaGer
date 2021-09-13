<?php
return [
    "title"                 => "MetaGer - Help",

    "stopworte.title" => "Exclude single words",
    "stopworte.1" => "If you want to exclude words within the search result, you have to put a \"-\" in front of that word",
    "stopworte.2" => "Example: You are looking for a new car, but no BMW. Then your search should be <div class=\"well well-sm\">new car -bmw</div>",
    "stopworte.3" => "car new -bmw",

    "mehrwortsuche.title" => "Searching for more than one word",
    "mehrwortsuche.1" => "Without quotation you will get results containing one or some of the words of your search entry. Use quotes for the search for exact phrases, citations....",
    "mehrwortsuche.2" => "Example: search for Shakespears <div class=\"well well-sm\">to be or not to be</div> will deliver many results, but the exact phrase will only be found using <div class=\"well well-sm\">\"to be or nor to be\"</div>",
    "mehrwortsuche.3" => "Please use quotes to make sure to get your search words in the results list.",
    "mehrwortsuche.3.example" => '"round-table" "decision"',
    "mehrwortsuche.4" => "Put words or phrases in quotation marks to search for exact combinations.",
    "mehrwortsuche.4.example" => '"round-table decision"',

    "grossklein.title" => "Upper case vs. lower case",
    "grossklein.1" => "Upper case will not be distinguished from lower case",
    "grossklein.2" => "Example: Searching for",
    "grossklein.2.example" => "capitalization",
    "grossklein.3" => "should give the same results as",
    "grossklein.3.example" => "CAPITALIZATION",


    "urls.title" => "Exclude URLs",
    "urls.explanation" => "Use \"-url:\" to exclude search results containing specified words.",
    "urls.example.1" => "Example: You don' t want the word \"dog\" in the results:",
    "urls.example.2" => "Type <i>my search words</i> -url:dog",

    "bang.title" => "!bangs",
    "bang.1" => "MetaGer uses a little a special spelling called \"!bang syntax\". A !bang starts with the \"!\" and doesn't contain blanks (\"!twitter\", \"!facebook\" for example). If you use a !bang supported by MetaGer you will see a new entry in the \"Quicktips\". We direct then to the specified service (click the button). Read more about our method departing from others:  <a href=\"/en/hilfe/#bangs\" target=\"_blank\" rel=\"noopener\"> FAQ</a>",
    "faq.18.h" => "Why are the !bangs not opended directly?",
    "faq.18.b" => "The !bang -\\\"redirections\\\" are part of our quicktips and they need an additional click. We had to decide between easy-to-use and keep-control-of-data. We find it necessary to show that the links are third party property (DuckDuckGo). So there is a two way protection: first we do not transfer your searchwords but only the !bang to DuckDuckGo. On the other hand the user confirms the !bang-target explicit. We don't have the ressources to maintain all this !bangs, we are sorry.",

    "searchinsearch.title" => "Search in search",
    "searchinsearch.1" => "The result will be stored in a new TAB appearing at the right side of the screen. It is called \"Saved results\". You can store here single results from several searches. The TAB persists. Entering this TAB you get your personal result list with tools to filter and sort the results. Click another TAB to go back for further searches. You won´t have this if the screen is too small. More info (only german so far): <a href=\"http://blog.suma-ev.de/node/225\" target=\"_blank\" rel=\"noopener\"> SUMA blog</a>.",


];