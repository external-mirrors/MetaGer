<?php

namespace app\Models\parserSkripte;

use App\Models\Searchengine;
use Log;

class Scopia extends Searchengine
{
    public $results = [];

    public function __construct($name, \stdClass $engine, \App\MetaGer $metager)
    {
        parent::__construct($name, $engine, $metager);
    }

    public function loadResults($result)
    {
        $result = html_entity_decode($result);
        $result = preg_replace("/<description>(.*?)<\/description>/si", "<description><![CDATA[ $1 ]]></description>", $result);
        $result = preg_replace("/<title>(.*?)<\/title>/si", "<title><![CDATA[ $1 ]]></title>", $result);
        $result = preg_replace("/<url>(.*?)<\/url>/si", "<url><![CDATA[ $1 ]]></url>", $result);

        try {

            $content = \simplexml_load_string($result);
            if (!$content) {
                return;
            }

            $results = $content->xpath('//results/result');
            foreach ($results as $result) {

                $title = $result->title->__toString();
                $link = $result->url->__toString();
                $anzeigeLink = $link;
                $descr = $result->description->__toString();
                $this->counter++;
                if(! $this->containsPornContent($title.$descr)) { //see note at filtering method
                    $this->results[] = new \App\Models\Result(
                        $this->engine,
                        $title,
                        $link,
                        $anzeigeLink,
                        $descr,
                        $this->engine->{"display-name"},
                        $this->engine->homepage,
                        $this->counter
                    );
                }
                
            }
        } catch (\Exception $e) {
            Log::error("A problem occurred parsing results from $this->name:");
            Log::error($e->getMessage());
            return;
        }
    }

    private function containsPornContent($text) {
        // Returns true if pornographic content is detected
        // We noticed scopia often serving pornographic results for non-pornographic queries. After much deliberation we decided to filter pornographic from scopia. Those will have to be supplied by other search engines.

        $words = [
            "fisting" => 60,
            "live cam" => 60,
            "fick" => 60,
            "anal" => 60,
            "dildo" => 60,
            "masturbat" => 60,
            "gangbang" => 60,
            "fotze" => 60,
            "porn" => 50,
            "anus" => 50,
            "penetration" => 50,
            "cuckold" => 50,
            "orgasmus" => 50,
            "milf" => 50,
            "dilf" => 50,
            "voyeur" => 40,
            "fuck" => 40,
            "nude" => 40,
            "muschi" => 40,
            "sex" => 40,
            "nackt" => 40,
            "amateur" => 30,
            "schlampe" => 30,
            "eroti" => 30,
            "dick" => 30,
            "teen" => 30,
            "hardcore" => 30,
            "fetisch" => 30,
            "pussy" => 30,
            "pussies" => 30,
            "cheat" => 20,
            "gratis" => 20,
            "geil" => 20,
            "video" => 10,
            "girl" => 10,
            "boy" => 10,
            "weib" => 10,
            "titt" => 10,
            "bikini" => 10,
            "hot " => 10,
            "pics" => 10,
            "free" => 10,
        ];
        $acc = 0;
        foreach($words as $word => $score) {
            if (stristr($text,$word)) {
                $acc += $score;
            }
        }
        return $acc >= 100;
    }

    public function getNext(\App\MetaGer $metager, $result)
    {
        $result = html_entity_decode($result);
        $result = str_replace("&", "&amp;", $result);
        try {
            $content = \simplexml_load_string($result);

        } catch (\Exception $e) {
            Log::error("A problem occurred parsing results from $this->name:");
            Log::error($e->getMessage());
            return;
        }

        if (!$content) {
            return;
        }

        $more = $content->xpath('//results/more')[0]->__toString() === "1" ? true : false;

        if ($more) {
            $results = $content->xpath('//results/result');
            $number = $results[sizeof($results) - 1]->number->__toString();
            # Erstellen des neuen Suchmaschinenobjekts und anpassen des GetStrings:
            $newEngine = unserialize(serialize($this->engine));
            $newEngine->{"get-parameter"}->s = $number;
            $next = new Scopia($this->name, $newEngine, $metager);
            $this->next = $next;
        }

    }
}
