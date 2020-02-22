<?php

namespace App\Models\parserSkripte;

use App\Models\XmlSearchengine;
use Log;

class Qualigo extends XmlSearchengine
{

    public function __construct($name, \StdClass $engine, \App\MetaGer $metager)
    {
        parent::__construct($name, $engine, $metager);
    }

    protected function loadXmlResults($resultsXml)
    {
        if(empty($resultsXml) || !$resultsXml){
            Log::error("A problem occurred parsing results from $this->name:");
            return;
        }
        try {
            $results = $resultsXml->xpath('//RL/RANK');
            foreach ($results as $result) {
                $title = $result->{"TITLE"}->__toString();
                $link = $result->{"URL"}->__toString();
                $anzeigeLink = $result->{"ORIGURL"}->__toString();
                $descr = $result->{"ABSTRACT"}->__toString();
                $this->counter++;
                $this->ads[] = new \App\Models\Result(
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
        } catch (\Exception $e) {
            Log::error("A problem occurred parsing results from $this->name:");
            Log::error($e->getMessage());
            return;
        }
    }

    protected function getDynamicParams()
    {
        $params = [];

        $params["ip"] = $this->ip;
        $params["agent"] = $this->useragent;

        return $params;
    }
}
