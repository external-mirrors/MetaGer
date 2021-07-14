<?php

namespace app\Models\parserSkripte;

use App\Models\Searchengine;
use Log;

class Yacy extends Searchengine
{
    public $results = [];

    public function __construct($name, \stdClass $engine, \App\MetaGer $metager)
    {
        parent::__construct($name, $engine, $metager);
    }

    public function loadResults($result)
    {

        try {
            $content = json_decode($result, true);
            $content = $content["channels"];

            foreach ($content as $channel) {
                $items = $channel["items"];
                foreach ($items as $item) {
                    $title = $item["title"];
                    $link = $item["link"];
                    $anzeigeLink = $link;
                    $descr = $item["description"];

                    $this->counter++;
                    $this->results[] = new \App\Models\Result(
                        $this->engine,
                        $title,
                        $link,
                        $anzeigeLink,
                        $descr,
                        $this->engine->{"display-name"}, $this->engine->homepage,
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

    public function getNext(\App\MetaGer $metager, $result)
    {
        try{
            $resultCount = 0;
            $content = json_decode($result, true);
            $content = $content["channels"];

            foreach ($content as $channel) {
                $items = $channel["items"];
                $resultCount += sizeof($items);
            }

            if($resultCount > 0){
                $next = clone $this;
                $next->engine->{"get-parameter"}["startRecord"] = $this->engine->{"get-parameter"}["startRecord"] + 10;
                $next->getString = $this->generateGetString($metager->getQ());
                $next->updateHash();
                $this->next = $next;
            }
        } catch (\Exception $e) {
            Log::error("A problem occurred parsing results from $this->name:");
            Log::error($e->getMessage());
            return;
        }
    }
}
