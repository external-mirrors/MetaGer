<?php

namespace App\Models;

use App\Http\Controllers\Pictureproxy;
use App\Models\DeepResults\Button;
use App\Models\DeepResults\Imagesearchdata;
use App\Search\Blacklists;
use App\SearchSettings;

/* Die Klasse Result sammelt alle Informationen über ein einzelnes Suchergebnis.
 *  Die Results werden von den Suchmaschinenspezifischen Parser-Skripten erstellt.
 */

class Result
{
    public $titel; # Der Groß Angezeigte Name für das Suchergebnis
    public $originalLink;
    public $link; # Der Link auf die Ergebnisseite
    public $anzeigeLink; # Der tatsächlich angezeigte Link (rein optisch)
    public $descr; # Die eventuell gekürzte Beschreibung des Suchergebnisses
    public $longDescr; # Die ungekürzte Beschreibung des Suchergebnisses
    public $gefVon = []; # Die Suchmaschine von der dieses Ergebnis stammt
    public $gefVonLink = [];
    public $age;
    public $sourceRank; # Das Ranking für dieses Suchergebnis von der Seite, die es geliefert hat (implizit durch Ergebnisreihenfolge: 20 - Position in Ergebnisliste)
    public $partnershop; # Ist das Ergebnis von einem Partnershop? (bool)
    public $image; # Ein Vorschaubild für das Suchergebnis (als URL)
    public $logo;
    public $imageDimensions; # Ein Array in welchem wenn verfügbar Breite/Höhe des Bildes gespeichert sind ["width" => ..., "height" => ...]
    public $proxyLink; # Der Link für die Seite über unseren Proxy-Service
    public $engineBoost = 1; # Der Boost für den Provider des Suchergebnisses
    public $valid = true; # Ob das Ergebnis noch gültig ist (bool)
    public $host; # Der aus dem Link gelesene Host des Suchergebnisses
    public $strippedHost; # Der Host in Form "foo.bar.de"
    public $strippedDomain; # Die Domain in Form "bar.de"
    public $strippedLink; # Der Link in Form "foo.bar.de/test"
    public $rank; # Das Ranking für das Ergebnis
    public $additionalInformation = [];
    public $new = true;
    public $changed = false;
    /** @var Result[] */
    public $inheritedResults = [];

    public $deepResults = [
        "buttons" => [],
    ];
    const DESCRIPTION_LENGTH = 250;

    # Erstellt ein neues Ergebnis
    public function __construct($engineBoost, $titel, $link, $anzeigeLink, $descr, $gefVon, $gefVonLink, $sourceRank, $additionalInformation = [])
    {
        $this->titel = $this->sanitizeText(strip_tags(trim($titel)));
        $this->link = trim($link);
        $this->originalLink = trim($link);
        $this->anzeigeLink = trim($anzeigeLink);
        $this->anzeigeLink = preg_replace("/(http[s]{0,1}:\/\/){0,1}(www\.){0,1}/si", "", $this->anzeigeLink);
        $this->descr = $this->sanitizeText(strip_tags(trim($descr), '<p>'));
        $this->descr = preg_replace("/\n+/si", " ", $this->descr);
        $this->longDescr = $this->descr;
        if (strlen($this->descr) > self::DESCRIPTION_LENGTH) {
            $this->descr = wordwrap($this->descr, self::DESCRIPTION_LENGTH);
            $this->descr = substr($this->descr, 0, strpos($this->descr, "\n"));
            $this->descr .= "…"; // Ellipsis character
        }
        $this->gefVon[] = trim($gefVon);
        $this->gefVonLink[] = trim($gefVonLink);
        $this->proxyLink = $this->generateProxyLink($this->link);
        $this->sourceRank = $sourceRank;
        if ($this->sourceRank <= 0 || $this->sourceRank > 20) {
            $this->sourceRank = 20;
        }
        $this->sourceRank = 20 - $this->sourceRank;
        $this->engineBoost = empty($engineBoost) ? 1 : $engineBoost;
        $this->valid = true;
        $this->host = @parse_url($link, PHP_URL_HOST);
        $this->strippedHost = $this->getStrippedHost($this->link);
        $this->strippedDomain = $this->getStrippedDomain($this->link);
        $this->strippedLink = $this->getStrippedLink($this->link);
        $this->rank = 0;
        $this->partnershop = isset($additionalInformation["partnershop"]) ? $additionalInformation["partnershop"] : false;
        $this->image = isset($additionalInformation["image"]) ? $additionalInformation["image"] : "";
        $this->imageDimensions = isset($additionalInformation["imagedimensions"]) ? $additionalInformation["imagedimensions"] : [];
        $this->price = isset($additionalInformation["price"]) ? $additionalInformation["price"] : 0;
        $this->price_text = is_int($this->price) ? $this->price_to_text($this->price) : $this->price;
        $this->additionalInformation = $additionalInformation;
        $this->hash = md5(serialize($this));
    }

    private function price_to_text($price)
    {
        $euros = floor($price / 100);
        $cents = $price % 100;
        $price_text = $euros . ',';
        if ($cents < 10) {
            $price_text .= '0';
        }
        $price_text .= $cents . ' €';
        return $price_text;
    }

    /* Ranked das Ergebnis nach folgenden Aspekten:
     *  Startwert 0
     *  + 0.02 * Sourcerank (20 - Position in Ergebnisliste des Suchanbieters)
     *  * Engine-Boost
     */
    public function rank()
    {
        $eingabe = app(SearchSettings::class)->q;
        $rank = 0;

        # Boost für Source Ranking
        $rank += ($this->sourceRank * 0.02);

        # Boost für passende ??? URL
        $rank += $this->calcURLBoost($eingabe);

        # Boost für Vorkommen der Suchwörter:
        $rank += $this->calcSuchwortBoost($eingabe);

        # Boost für Suchmaschine
        if ($this->engineBoost > 0) {
            $rank *= floatval($this->engineBoost);
        }

        $this->rank = $rank;
    }

    # Berechnet den Ranking-Boost durch ??? URL
    public function calcURLBoost($tmpEingabe)
    {
        $link = $this->anzeigeLink;
        if (strpos($link, "http") !== 0) {
            $link = "http://" . $link;
        }
        $link = @parse_url($link, PHP_URL_HOST) . @parse_url($link, PHP_URL_PATH);
        $tmpLi = $link;
        $count = 0;
        $tmpLink = "";
        # Löscht verschiedene unerwünschte Teile aus $link und $tmpEingabe
        $regex = [
            "/\s+/si",
            # Leerzeichen
            "/http:/si",
            # "http:"
            "/https:/si",
            # "https:"
            "/www\./si",
            # "www."
            "/\//si",
            # "/"
            "/\./si",
            # "."
            "/-/si",
            # "-"
        ];
        foreach ($regex as $reg) {
            $link = preg_replace($regex, "", $link);
            $tmpEingabe = preg_replace($regex, "", $tmpEingabe);
        }
        foreach (str_split($tmpEingabe) as $char) {
            if (
                !$char
                || !$tmpEingabe
                || strlen($tmpEingabe) === 0
                || strlen($char) === 0
            ) {
                continue;
            }
            if (strpos(strtolower($tmpLink), strtolower($char)) >= 0) {
                $count++;
                $tmpLink = str_replace(urlencode($char), "", $tmpLink);
            }
        }
        if (strlen($this->descr) > 40 && strlen($link) > 0) {
            return $count / ((strlen($link)) * 60); # ???
        } else {
            return 0;
        }
    }

    # Berechnet den Ranking-Boost durch das Vorkommen von Suchwörtern
    private function calcSuchwortBoost($tmpEingabe)
    {
        $maxRank = 0.1;
        $tmpTitle = $this->titel;
        $tmpDescription = $this->descr;
        $isWithin = false;
        $tmpRank = 0;
        $tmpEingabe = preg_replace("/\b\w{1,3}\b/si", "", $tmpEingabe);
        $tmpEingabe = preg_replace("/\s+/si", " ", $tmpEingabe);

        foreach (explode(" ", trim($tmpEingabe)) as $el) {
            if (strlen($tmpTitle) === 0 || strlen($el) === 0 || strlen($tmpDescription) === 0) {
                continue;
            }

            $el = preg_quote($el, "/");
            if (strlen($tmpTitle) > 0) {
                if (preg_match("/\b$el\b/si", $tmpTitle)) {
                    $tmpRank += .7 * .6 * $maxRank;
                } elseif (strpos($tmpTitle, $el) !== false) {
                    $tmpRank += .3 * .6 * $maxRank;
                }
            }
            if (strlen($tmpDescription) > 0) {
                if (preg_match("/\b$el\b/si", $tmpDescription)) {
                    $tmpRank += .7 * .4 * $maxRank;
                } elseif (strpos($tmpDescription, $el) !== false) {
                    $tmpRank += .3 * .4 * $maxRank;
                }
            }
        }

        $tmpRank /= sizeof(explode(" ", trim($tmpEingabe))) * 10;
        return $tmpRank;
    }

    # Überprüft ob das Ergebnis aus irgendwelchen Gründen unerwünscht ist.
    public function isValid(\App\MetaGer $metager)
    {
        $bl = $metager->getUserDomainBlacklist();
        # Perönliche Host und Domain Blacklist
        if (
            in_array(strtolower($this->strippedHost), $metager->getUserHostBlacklist())
            || in_array(strtolower($this->strippedDomain), $metager->getUserDomainBlacklist())
        ) {
            return false;
        }

        # Persönliche URL Blacklist
        foreach ($metager->getUserUrlBlacklist() as $word) {
            if (strpos(strtolower($this->link), $word)) {
                return false;
            }
        }

        # Allgemeine URL und Domain Blacklist
        if ($this->isBlackListed($metager)) {
            return false;
        }

        # Stopworte
        foreach ($metager->getStopWords() as $stopWord) {
            $text = $this->titel . " " . $this->descr;
            if (stripos($text, $stopWord) !== false) {
                return false;
            }
        }

        // Possibly remove description
        if ($this->isDescriptionBlackListed($metager)) {
            $this->descr = "";
        }

        /*
        # Phrasensuche:
        $text = strtolower($this->titel) . " " . strtolower($this->descr);
        foreach ($metager->getPhrases() as $phrase) {
        if (strpos($text, $phrase) === false) {
        return false;
        }
        }
        */

        /* Der Dublettenfilter, der sicher stellt,
        *  dass wir nach Möglichkeit keinen Link doppelt in der Ergebnisliste haben.
        
        $dublettenLink = $this->strippedLink;
        if (!empty($this->provider->{"dubletten-include-parameter"}) && sizeof($this->provider->{"dubletten-include-parameter"}) > 0) {
        $dublettenLink .= "?";
        $query = parse_url($this->link);
        if (!empty($query["query"])) {
        $queryTmp = explode("&", $query["query"]);
        $query = [];
        foreach ($queryTmp as $getParameter) {
        $keyVal = explode("=", $getParameter);
        $query[$keyVal[0]] = $keyVal[1];
        }
        foreach ($this->provider->{"dubletten-include-parameter"} as $param) {
        if (!empty($query[$param])) {
        $dublettenLink .= $param . "=" . $query[$param] . "&";
        }
        }
        $dublettenLink = rtrim($dublettenLink, "&");
        }
        }
        if ($metager->addLink($this)) {
        $metager->addHostCount($this->strippedHost);
        return true;
        } else {
        return false;
        }*/
        return true;
    }

    public function isBlackListed(\App\MetaGer $metager)
    {
        return app(Blacklists::class)->blocksResult(
            $this->strippedHost,
            $this->strippedLink,
            app(SearchSettings::class)->q
        );
    }

    public function isDescriptionBlackListed(\App\MetaGer $metager)
    {
        return app(Blacklists::class)->blocksDescription($this->strippedLink);
    }

    /* Liest aus einem Link den Host.
     *  Dieser wird dabei in die Form:
     *  "http://www.foo.bar.de/test?ja=1" -> "foo.bar.de"
     *  gebracht.
     */
    public function getStrippedHost($link)
    {
        $match = $this->getUrlElements($link);
        return $match['host'];
    }

    /* Entfernt "http://", "www" und Parameter von einem Link
     *  Dieser wird dabei in die Form:
     *  "http://www.foo.bar.de/test?ja=1" -> "foo.bar.de/test"
     *  gebracht.
     */
    public function getStrippedLink($link)
    {
        $match = $this->getUrlElements($link);
        return $match['host'] . $match['path'];
    }

    /* Liest aus einem Link die Domain.
     *  Dieser wird dabei in die Form:
     *  "http://www.foo.bar.de/test?ja=1" -> "bar.de"
     *  gebracht.
     */
    public function getStrippedDomain($link)
    {
        $match = $this->getUrlElements($link);
        return $match['domain'];
    }

    # Erstellt aus einem Link einen Proxy-Link für unseren Proxy-Service
    public function generateProxyLink($link)
    {
        if (!$link || empty($link)) {
            return "";
        }

        $parts = parse_url($link);

        $proxyUrl = "https://proxy.metager.de/";

        if (!empty($parts["host"])) {
            $proxyUrl .= $parts["host"];
            if (!empty($parts["path"])) {
                $proxyUrl .= "/" . rawurlencode(trim($parts["path"], "/"));
            }
        }

        // We need to generate the correct password for the Proxy URLs
        // It's an hmac sha256 hash of the url having the proxy password as secret
        $password = hash_hmac("sha256", rtrim($link, "/"), config("metager.metager.proxy.password"));

        $urlParameters = [
            "url" => $link,
            "password" => $password,
        ];

        $params = http_build_query($urlParameters, "", "&", PHP_QUERY_RFC3986);

        $proxyUrl .= "?" . $params;

        return $proxyUrl;
    }

    /* Liest aus einer URL alle Informationen aus
     * https://max:muster@www.example.site.page.com:8080/index/indexer/list.html?p1=A&p2=B#ressource
     * (?:((?:http)|(?:https))(?::\/\/))?
     * https://                  => [1] = http / https
     * (?:([a-z0-9.\-_~]+):([a-z0-9.\-_~]+)@)?
     * username:password@        => [2] = username, [3] = password
     * (?:(www)(?:\.))?
     * www.                      => [4] = www
     * ((?:(?:[a-z0-9.\-_~]+\.)+)?([a-z0-9.\-_~]+\.[a-z0-9.\-_~]+))
     * example.site.page.com     => [5] = example.site.page.com, [6] = page.com
     * (?:(?::)(\d+))?
     * :8080                     => [7] = 8080
     * ((?:(?:\/[a-z0-9.\-_~]+)+)(?:\.[a-z0-9.\-_~]+)?)?
     * /index/indexer/list.html  => [8] = /index/indexer/list.html
     * (\?[a-z0-9.\-_~]+=[a-z0-9.\-_~]+(?:&[a-z0-9.\-_~]+=[a-z0-9.\-_~]+)*)?
     * ?p1=A&p2=B                => [9] = ?p1=A&p2=B
     * (?:(?:#)([a-z0-9.\-_~]+))?
     * #ressource                => [10] = ressource
     */
    public function getUrlElements($url)
    {
        if (stripos($url, "http") !== 0) {
            $url = "http://" . $url;
        }

        $parts = parse_url($url);
        $re = [];

        $re["schema"] = empty($parts["scheme"]) ? "" : $parts["scheme"];
        $re["username"] = empty($parts["user"]) ? "" : $parts["user"];
        $re["password"] = empty($parts["pass"]) ? "" : $parts["pass"];
        $re["web"] = "";
        $re["host"] = $parts["host"];
        if (stripos($re["host"], "www.") === 0) {
            $re["web"] = "www.";
            $re["host"] = substr($re["host"], strpos($re["host"], ".") + 1);
        }
        $re["domain"] = $this->get_domain($re["host"]);

        $re["port"] = empty($parts["port"]) ? ($re["schema"] === "http" ? 80 : ($re["schema"] === "https" ? 443 : 80)) : $parts["port"];
        $re["path"] = empty($parts["path"]) ? "" : $parts["path"];
        $re["query"] = empty($parts["query"]) ? "" : $parts["query"];
        $re["fragment"] = empty($parts["fragment"]) ? "" : $parts["fragment"];

        return $re;
    }

    /**
     * @param string $domain Pass $_SERVER['SERVER_NAME'] here
     *
     * @return string
     */
    private function get_domain($domain)
    {
        $domain = strtolower($domain);
        if (filter_var($domain, FILTER_VALIDATE_IP)) {
            return $domain;
        }

        $arr = array_slice(array_filter(explode('.', $domain, 4), function ($value) {
            return $value !== 'www';
        }), 0); //rebuild array indexes

        if (count($arr) > 2) {
            $count = count($arr);
            $_sub = explode('.', $count === 4 ? $arr[3] : $arr[2]);


            if (count($_sub) === 2) { // two level TLD
                $removed = array_shift($arr);
                if ($count === 4) // got a subdomain acting as a domain
                    $removed = array_shift($arr);
            } elseif (count($_sub) === 1) { // one level TLD
                $removed = array_shift($arr); //remove the subdomain             
                if (strlen($_sub[0]) === 2 && $count === 3) // TLD domain must be 2 letters
                    array_unshift($arr, $removed);
                else {
                    // non country TLD according to IANA
                    $tlds = array('aero', 'arpa', 'asia', 'biz', 'cat', 'com', 'coop', 'edu', 'gov', 'info', 'jobs', 'mil', 'mobi', 'museum', 'name', 'net', 'org', 'post', 'pro', 'tel', 'travel', 'xxx', );
                    if (count($arr) > 2 && in_array($_sub[0], $tlds) !== false) { //special TLD don't have a country
                        array_shift($arr);
                    }
                }
            } else { // more than 3 levels, something is wrong
                for ($i = count($_sub); $i > 1; $i--)
                    $removed = array_shift($arr);
            }
        } elseif (count($arr) === 2) {
            $arr0 = array_shift($arr);
            if (
                strpos(join('.', $arr), '.') === false
                && in_array($arr[0], array('localhost', 'test', 'invalid')) === false
            ) // not a reserved domain
            {
                // seems invalid domain, restore it
                array_unshift($arr, $arr0);
            }
        }

        return join('.', $arr);
    }

    # Getter

    public function getRank()
    {
        return $this->rank;
    }

    public function getDate(): \Carbon\Carbon|null
    {
        if (isset($this->additionalInformation["date"])) {
            return $this->additionalInformation["date"];
        } else {
            return null;
        }
    }

    /**
     * Das Ergebnis so, wie `out=json` es veröffentlicht.
     *
     * Bewusst eine kuratierte Auswahl statt `get_object_vars($this)`: die
     * Eigenschaften dieser Klasse sind Interna (`rank`, `engineBoost`, `new`,
     * `changed`, `hash`, `inheritedResults`, …), die sich mit dem Ranking-Code
     * ändern. Ein Dump davon würde jede dieser Änderungen zu einer
     * Breaking Change der API machen. Hier steht nur, was ein Client auch
     * anzeigen kann.
     *
     * Die Schema-Version steht in der Antwort-Hülle, nicht hier — siehe
     * `MetaGer::createView()`, `case 'json'`.
     */
    public function toApiArray(): array
    {
        return [
            "title" => $this->titel,
            "link" => $this->link,
            "displayLink" => $this->anzeigeLink,
            # Auf DESCRIPTION_LENGTH gekürzt; longDescription ist ungekürzt.
            "description" => $this->descr,
            "longDescription" => $this->longDescr,
            # Einstiegspunkt für "anonym öffnen" (SafeBrowse/Proxy). Ohne dieses
            # Feld kann ein Client die Funktion pro Ergebnis nicht anbieten.
            "proxyLink" => $this->proxyLink !== "" ? $this->proxyLink : null,
            "engines" => $this->enginesToApiArray(),
            "image" => $this->imageToApiArray(),
            "date" => $this->dateToApiArray(),
            "host" => $this->strippedHost,
            "domain" => $this->strippedDomain,
            "partnershop" => (bool) $this->partnershop,
            # price/price_text sind dynamische Eigenschaften (nicht oben
            # deklariert), deshalb hier defensiv gelesen.
            "price" => empty($this->price) ? null : ($this->price_text ?? null),
            "sitelinks" => $this->sitelinksToApiArray(),
        ];
    }

    /**
     * gefVon/gefVonLink sind zwei parallele Arrays, die beim Zusammenführen
     * gleicher Ergebnisse mitwachsen. Nach außen ist ein Array von Objekten
     * die ehrlichere Form: es kann nicht auseinanderlaufen.
     */
    private function enginesToApiArray(): array
    {
        $engines = [];
        foreach ($this->gefVon as $index => $name) {
            if (empty($name)) {
                continue;
            }
            $engines[] = [
                "name" => $name,
                "link" => $this->gefVonLink[$index] ?? null,
            ];
        }
        return $engines;
    }

    /**
     * `$this->image` hat zwei Formen: die Bildersuche legt ein
     * Imagesearchdata-Objekt ab (Thumbnail, Vollbild, Maße, Proxy-URLs), Web-
     * und News-Ergebnisse eine blanke URL. Nach außen ist beides dieselbe
     * Struktur — ein Client soll nicht auf den Typ prüfen müssen.
     *
     * `proxyUrl` läuft über den MetaGer-Bildproxy: das Gerät kontaktiert den
     * fremden Host nicht. Für einen Privacy-Client ist das die richtige Wahl,
     * aber die URL ist über Pictureproxy::generateUrl() **nur einen Tag
     * gültig** und darf nicht länger zwischengespeichert werden.
     *
     * `imageDimensions` wird von keinem Parser befüllt; für Web-Ergebnisse sind
     * width/height deshalb immer null. Die Bildersuche liefert echte Maße,
     * sofern die Engine sie kennt — Brave meldet 0, Serper echte Werte.
     */
    private function imageToApiArray(): array | null
    {
        if (empty($this->image)) {
            return null;
        }

        if ($this->image instanceof Imagesearchdata) {
            return [
                "url" => $this->image->image,
                "width" => $this->dimensionOrNull($this->image->image_width),
                "height" => $this->dimensionOrNull($this->image->image_height),
                "proxyUrl" => $this->image->image_proxy ?? null,
                "thumbnail" => [
                    "url" => $this->image->thumbnail,
                    "width" => $this->dimensionOrNull($this->image->thumbnail_width),
                    "height" => $this->dimensionOrNull($this->image->thumbnail_height),
                    "proxyUrl" => $this->image->thumbnail_proxy ?? null,
                ],
            ];
        }

        $dimensions = is_array($this->imageDimensions) ? $this->imageDimensions : [];
        return [
            "url" => $this->image,
            "width" => $this->dimensionOrNull($dimensions["width"] ?? null),
            "height" => $this->dimensionOrNull($dimensions["height"] ?? null),
            "proxyUrl" => Pictureproxy::generateUrl($this->image),
            "thumbnail" => null,
        ];
    }

    /**
     * Das Ergebnisdatum als ISO-8601, oder null.
     *
     * Liest `additionalInformation["date"]` absichtlich selbst, statt getDate()
     * zu benutzen: die Parser sind sich über den Typ nicht einig. Die meisten
     * legen ein Carbon ab, Onenewspagevideo aber einen Unix-Timestamp als int.
     * getDate() ist auf `Carbon|null` typisiert und stirbt daran mit einem
     * TypeError. Für eine API, die einen kompletten Suchlauf ausliefert, ist
     * ein einzelnes unlesbares Datum kein Grund, die ganze Antwort zu verlieren.
     */
    private function dateToApiArray(): string | null
    {
        $date = $this->additionalInformation["date"] ?? null;

        if ($date instanceof \Carbon\Carbon) {
            return $date->toIso8601String();
        }
        if (is_int($date) && $date > 0) {
            return \Carbon\Carbon::createFromTimestamp($date)->toIso8601String();
        }
        return null;
    }

    /** 0 ist keine Bildgröße, sondern "unbekannt". */
    private function dimensionOrNull($value): int | null
    {
        return empty($value) ? null : (int) $value;
    }

    private function sitelinksToApiArray(): array
    {
        $sitelinks = [];
        foreach ($this->deepResults["buttons"] ?? [] as $button) {
            # deepResults ist ein ungetyptes Array, das die Parser befüllen.
            # Was kein Button ist, hat in der Antwort nichts zu suchen.
            if (!($button instanceof Button)) {
                continue;
            }
            $sitelinks[] = [
                "title" => $button->title,
                "link" => $button->link,
            ];
        }
        return $sitelinks;
    }

    public function getLangString()
    {
        $string = "";

        $string .= $this->titel;
        $string .= $this->descr;

        return $string;
    }

    /**
     * Sanitizes bold or special looking UTF-8 characters
     * and replaces them with normal looking ones.
     * Thanks to:
     * https://stackoverflow.com/questions/42254276/how-to-convert-strange-strong-bold-unicode-to-non-bold-utf-8-chars-in-php/63068771#63068771
     * $text => The text to sanitize
     * 
     * @return Sanitized version of the text
     */
    private function sanitizeText($text)
    {
        $target = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '!', '?', '.', ',', '"', "'"];
        $specialList = [
            'serifBold' => ['𝐚', '𝐛', '𝐜', '𝐝', '𝐞', '𝐟', '𝐠', '𝐡', '𝐢', '𝐣', '𝐤', '𝐥', '𝐦', '𝐧', '𝐨', '𝐩', '𝐪', '𝐫', '𝐬', '𝐭', '𝐮', '𝐯', '𝐰', '𝐱', '𝐲', '𝐳', '𝐀', '𝐁', '𝐂', '𝐃', '𝐄', '𝐅', '𝐆', '𝐇', '𝐈', '𝐉', '𝐊', '𝐋', '𝐌', '𝐍', '𝐎', '𝐏', '𝐐', '𝐑', '𝐒', '𝐓', '𝐔', '𝐕', '𝐖', '𝐗', '𝐘', '𝐙', '𝟎', '𝟏', '𝟐', '𝟑', '𝟒', '𝟓', '𝟔', '𝟕', '𝟖', '𝟗', '❗', '❓', '.', ',', '"', "'"],
            'serifItalic' => ['𝑎', '𝑏', '𝑐', '𝑑', '𝑒', '𝑓', '𝑔', 'ℎ', '𝑖', '𝑗', '𝑘', '𝑙', '𝑚', '𝑛', '𝑜', '𝑝', '𝑞', '𝑟', '𝑠', '𝑡', '𝑢', '𝑣', '𝑤', '𝑥', '𝑦', '𝑧', '𝐴', '𝐵', '𝐶', '𝐷', '𝐸', '𝐹', '𝐺', '𝐻', '𝐼', '𝐽', '𝐾', '𝐿', '𝑀', '𝑁', '𝑂', '𝑃', '𝑄', '𝑅', '𝑆', '𝑇', '𝑈', '𝑉', '𝑊', '𝑋', '𝑌', '𝑍', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '!', '?', '.', ',', '"', "'"],
            'serifBoldItalic' => ['𝒂', '𝒃', '𝒄', '𝒅', '𝒆', '𝒇', '𝒈', '𝒉', '𝒊', '𝒋', '𝒌', '𝒍', '𝒎', '𝒏', '𝒐', '𝒑', '𝒒', '𝒓', '𝒔', '𝒕', '𝒖', '𝒗', '𝒘', '𝒙', '𝒚', '𝒛', '𝑨', '𝑩', '𝑪', '𝑫', '𝑬', '𝑭', '𝑮', '𝑯', '𝑰', '𝑱', '𝑲', '𝑳', '𝑴', '𝑵', '𝑶', '𝑷', '𝑸', '𝑹', '𝑺', '𝑻', '𝑼', '𝑽', '𝑾', '𝑿', '𝒀', '𝒁', '𝟎', '𝟏', '𝟐', '𝟑', '𝟒', '𝟓', '𝟔', '𝟕', '𝟖', '𝟗', '❗', '❓', '.', ',', '"', "'"],
            'sans' => ['𝖺', '𝖻', '𝖼', '𝖽', '𝖾', '𝖿', '𝗀', '𝗁', '𝗂', '𝗃', '𝗄', '𝗅', '𝗆', '𝗇', '𝗈', '𝗉', '𝗊', '𝗋', '𝗌', '𝗍', '𝗎', '𝗏', '𝗐', '𝗑', '𝗒', '𝗓', '𝖠', '𝖡', '𝖢', '𝖣', '𝖤', '𝖥', '𝖦', '𝖧', '𝖨', '𝖩', '𝖪', '𝖫', '𝖬', '𝖭', '𝖮', '𝖯', '𝖰', '𝖱', '𝖲', '𝖳', '𝖴', '𝖵', '𝖶', '𝖷', '𝖸', '𝖹', '𝟢', '𝟣', '𝟤', '𝟥', '𝟦', '𝟧', '𝟨', '𝟩', '𝟪', '𝟫', '!', '?', '.', ',', '"', "'"],
            'sansBold' => ['𝗮', '𝗯', '𝗰', '𝗱', '𝗲', '𝗳', '𝗴', '𝗵', '𝗶', '𝗷', '𝗸', '𝗹', '𝗺', '𝗻', '𝗼', '𝗽', '𝗾', '𝗿', '𝘀', '𝘁', '𝘂', '𝘃', '𝘄', '𝘅', '𝘆', '𝘇', '𝗔', '𝗕', '𝗖', '𝗗', '𝗘', '𝗙', '𝗚', '𝗛', '𝗜', '𝗝', '𝗞', '𝗟', '𝗠', '𝗡', '𝗢', '𝗣', '𝗤', '𝗥', '𝗦', '𝗧', '𝗨', '𝗩', '𝗪', '𝗫', '𝗬', '𝗭', '𝟬', '𝟭', '𝟮', '𝟯', '𝟰', '𝟱', '𝟲', '𝟳', '𝟴', '𝟵', '❗', '❓', '.', ',', '"', "'"],
            'sansItalic' => ['𝘢', '𝘣', '𝘤', '𝘥', '𝘦', '𝘧', '𝘨', '𝘩', '𝘪', '𝘫', '𝘬', '𝘭', '𝘮', '𝘯', '𝘰', '𝘱', '𝘲', '𝘳', '𝘴', '𝘵', '𝘶', '𝘷', '𝘸', '𝘹', '𝘺', '𝘻', '𝘈', '𝘉', '𝘊', '𝘋', '𝘌', '𝘍', '𝘎', '𝘏', '𝘐', '𝘑', '𝘒', '𝘓', '𝘔', '𝘕', '𝘖', '𝘗', '𝘘', '𝘙', '𝘚', '𝘛', '𝘜', '𝘝', '𝘞', '𝘟', '𝘠', '𝘡', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '!', '?', '.', ',', '"', "'"],
            'sansBoldItalic' => ['𝙖', '𝙗', '𝙘', '𝙙', '𝙚', '𝙛', '𝙜', '𝙝', '𝙞', '𝙟', '𝙠', '𝙡', '𝙢', '𝙣', '𝙤', '𝙥', '𝙦', '𝙧', '𝙨', '𝙩', '𝙪', '𝙫', '𝙬', '𝙭', '𝙮', '𝙯', '𝘼', '𝘽', '𝘾', '𝘿', '𝙀', '𝙁', '𝙂', '𝙃', '𝙄', '𝙅', '𝙆', '𝙇', '𝙈', '𝙉', '𝙊', '𝙋', '𝙌', '𝙍', '𝙎', '𝙏', '𝙐', '𝙑', '𝙒', '𝙓', '𝙔', '𝙕', '𝟎', '𝟏', '𝟐', '𝟑', '𝟒', '𝟓', '𝟔', '𝟕', '𝟖', '𝟗', '❗', '❓', '.', ',', '"', "'"],
            'script' => ['𝒶', '𝒷', '𝒸', '𝒹', 'ℯ', '𝒻', 'ℊ', '𝒽', '𝒾', '𝒿', '𝓀', '𝓁', '𝓂', '𝓃', 'ℴ', '𝓅', '𝓆', '𝓇', '𝓈', '𝓉', '𝓊', '𝓋', '𝓌', '𝓍', '𝓎', '𝓏', '𝒜', 'ℬ', '𝒞', '𝒟', 'ℰ', 'ℱ', '𝒢', 'ℋ', 'ℐ', '𝒥', '𝒦', 'ℒ', 'ℳ', '𝒩', '𝒪', '𝒫', '𝒬', 'ℛ', '𝒮', '𝒯', '𝒰', '𝒱', '𝒲', '𝒳', '𝒴', '𝒵', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '!', '?', '.', ',', '"', "'"],
            'scriptBold' => ['𝓪', '𝓫', '𝓬', '𝓭', '𝓮', '𝓯', '𝓰', '𝓱', '𝓲', '𝓳', '𝓴', '𝓵', '𝓶', '𝓷', '𝓸', '𝓹', '𝓺', '𝓻', '𝓼', '𝓽', '𝓾', '𝓿', '𝔀', '𝔁', '𝔂', '𝔃', '𝓐', '𝓑', '𝓒', '𝓓', '𝓔', '𝓕', '𝓖', '𝓗', '𝓘', '𝓙', '𝓚', '𝓛', '𝓜', '𝓝', '𝓞', '𝓟', '𝓠', '𝓡', '𝓢', '𝓣', '𝓤', '𝓥', '𝓦', '𝓧', '𝓨', '𝓩', '𝟎', '𝟏', '𝟐', '𝟑', '𝟒', '𝟓', '𝟔', '𝟕', '𝟖', '𝟗', '❗', '❓', '.', ',', '"', "'"],
            'fraktur' => ['𝔞', '𝔟', '𝔠', '𝔡', '𝔢', '𝔣', '𝔤', '𝔥', '𝔦', '𝔧', '𝔨', '𝔩', '𝔪', '𝔫', '𝔬', '𝔭', '𝔮', '𝔯', '𝔰', '𝔱', '𝔲', '𝔳', '𝔴', '𝔵', '𝔶', '𝔷', '𝔄', '𝔅', 'ℭ', '𝔇', '𝔈', '𝔉', '𝔊', 'ℌ', 'ℑ', '𝔍', '𝔎', '𝔏', '𝔐', '𝔑', '𝔒', '𝔓', '𝔔', 'ℜ', '𝔖', '𝔗', '𝔘', '𝔙', '𝔚', '𝔛', '𝔜', 'ℨ', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '!', '?', '.', ',', '"', "'"],
            'frakturBold' => ['𝖆', '𝖇', '𝖈', '𝖉', '𝖊', '𝖋', '𝖌', '𝖍', '𝖎', '𝖏', '𝖐', '𝖑', '𝖒', '𝖓', '𝖔', '𝖕', '𝖖', '𝖗', '𝖘', '𝖙', '𝖚', '𝖛', '𝖜', '𝖝', '𝖞', '𝖟', '𝕬', '𝕭', '𝕮', '𝕯', '𝕰', '𝕱', '𝕲', '𝕳', '𝕴', '𝕵', '𝕶', '𝕷', '𝕸', '𝕹', '𝕺', '𝕻', '𝕼', '𝕽', '𝕾', '𝕿', '𝖀', '𝖁', '𝖂', '𝖃', '𝖄', '𝖅', '𝟎', '𝟏', '𝟐', '𝟑', '𝟒', '𝟓', '𝟔', '𝟕', '𝟖', '𝟗', '❗', '❓', '.', ',', '"', "'"],
            'monospace' => ['𝚊', '𝚋', '𝚌', '𝚍', '𝚎', '𝚏', '𝚐', '𝚑', '𝚒', '𝚓', '𝚔', '𝚕', '𝚖', '𝚗', '𝚘', '𝚙', '𝚚', '𝚛', '𝚜', '𝚝', '𝚞', '𝚟', '𝚠', '𝚡', '𝚢', '𝚣', '𝙰', '𝙱', '𝙲', '𝙳', '𝙴', '𝙵', '𝙶', '𝙷', '𝙸', '𝙹', '𝙺', '𝙻', '𝙼', '𝙽', '𝙾', '𝙿', '𝚀', '𝚁', '𝚂', '𝚃', '𝚄', '𝚅', '𝚆', '𝚇', '𝚈', '𝚉', '𝟶', '𝟷', '𝟸', '𝟹', '𝟺', '𝟻', '𝟼', '𝟽', '𝟾', '𝟿', '！', '？', '．', '，', '"', '＇'],
            'fullwidth' => ['ａ', 'ｂ', 'ｃ', 'ｄ', 'ｅ', 'ｆ', 'ｇ', 'ｈ', 'ｉ', 'ｊ', 'ｋ', 'ｌ', 'ｍ', 'ｎ', 'ｏ', 'ｐ', 'ｑ', 'ｒ', 'ｓ', 'ｔ', 'ｕ', 'ｖ', 'ｗ', 'ｘ', 'ｙ', 'ｚ', 'Ａ', 'Ｂ', 'Ｃ', 'Ｄ', 'Ｅ', 'Ｆ', 'Ｇ', 'Ｈ', 'Ｉ', 'Ｊ', 'Ｋ', 'Ｌ', 'Ｍ', 'Ｎ', 'Ｏ', 'Ｐ', 'Ｑ', 'Ｒ', 'Ｓ', 'Ｔ', 'Ｕ', 'Ｖ', 'Ｗ', 'Ｘ', 'Ｙ', 'Ｚ', '０', '１', '２', '３', '４', '５', '６', '７', '８', '９', '！', '？', '．', '，', '"', '＇'],
            'doublestruck' => ['𝕒', '𝕓', '𝕔', '𝕕', '𝕖', '𝕗', '𝕘', '𝕙', '𝕚', '𝕛', '𝕜', '𝕝', '𝕞', '𝕟', '𝕠', '𝕡', '𝕢', '𝕣', '𝕤', '𝕥', '𝕦', '𝕧', '𝕨', '𝕩', '𝕪', '𝕫', '𝔸', '𝔹', 'ℂ', '𝔻', '𝔼', '𝔽', '𝔾', 'ℍ', '𝕀', '𝕁', '𝕂', '𝕃', '𝕄', 'ℕ', '𝕆', 'ℙ', 'ℚ', 'ℝ', '𝕊', '𝕋', '𝕌', '𝕍', '𝕎', '𝕏', '𝕐', 'ℤ', '𝟘', '𝟙', '𝟚', '𝟛', '𝟜', '𝟝', '𝟞', '𝟟', '𝟠', '𝟡', '❕', '❔', '.', ',', '"', "'"],
            'capitalized' => ['ᴀ', 'ʙ', 'ᴄ', 'ᴅ', 'ᴇ', 'ꜰ', 'ɢ', 'ʜ', 'ɪ', 'ᴊ', 'ᴋ', 'ʟ', 'ᴍ', 'ɴ', 'ᴏ', 'ᴘ', 'q', 'ʀ', 'ꜱ', 'ᴛ', 'ᴜ', 'ᴠ', 'ᴡ', 'x', 'ʏ', 'ᴢ', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '﹗', '﹖', '﹒', '﹐', '"', "'"],
            //'circled' => ['ⓐ', 'ⓑ', 'ⓒ', 'ⓓ', 'ⓔ', 'ⓕ', 'ⓖ', 'ⓗ', 'ⓘ', 'ⓙ', 'ⓚ', 'ⓛ', 'ⓜ', 'ⓝ', 'ⓞ', 'ⓟ', 'ⓠ', 'ⓡ', 'ⓢ', 'ⓣ', 'ⓤ', 'ⓥ', 'ⓦ', 'ⓧ', 'ⓨ', 'ⓩ', 'Ⓐ', 'Ⓑ', 'Ⓒ', 'Ⓓ', 'Ⓔ', 'Ⓕ', 'Ⓖ', 'Ⓗ', 'Ⓘ', 'Ⓙ', 'Ⓚ', 'Ⓛ', 'Ⓜ', 'Ⓝ', 'Ⓞ', 'Ⓟ', 'Ⓠ', 'Ⓡ', 'Ⓢ', 'Ⓣ', 'Ⓤ', 'Ⓥ', 'Ⓦ', 'Ⓧ', 'Ⓨ', 'Ⓩ', '⓪', '①', '②', '③', '④', '⑤', '⑥', '⑦', '⑧', '⑨', '!', '?', '.', ',', '"', "'"],
            //'parenthesized' => ['⒜', '⒝', '⒞', '⒟', '⒠', '⒡', '⒢', '⒣', '⒤', '⒥', '⒦', '⒧', '⒨', '⒩', '⒪', '⒫', '⒬', '⒭', '⒮', '⒯', '⒰', '⒱', '⒲', '⒳', '⒴', '⒵', '🄐', '🄑', '🄒', '🄓', '🄔', '🄕', '🄖', '🄗', '🄘', '🄙', '🄚', '🄛', '🄜', '🄝', '🄞', '🄟', '🄠', '🄡', '🄢', '🄣', '🄤', '🄥', '🄦', '🄧', '🄨', '🄩', '⓿', '⑴', '⑵', '⑶', '⑷', '⑸', '⑹', '⑺', '⑻', '⑼', '!', '?', '.', ',', '"', "'"],
            'underlinedSingle' => ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '!', '?', '.', ',', '"', "'"],
            'underlinedDouble' => ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '!', '?', '.', ',', '"', "'"],
            'strikethroughSingle' => ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '!', '?', '.', ',', '"', "'"],
            'crosshatch' => ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '!', '?', '.', ',', '"', "'"],
        ];

        foreach ($specialList as $list) {
            $text = str_replace($list, $target, $text);
        }

        return $text;
    }
}