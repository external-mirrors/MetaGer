<?php

namespace App\Models;

abstract class XmlSearchengine extends Searchengine
{
    public function loadresults($results)
    {
        try {
            $resultsXml = simplexml_load_string($results);
            $this->loadXmlResults($resultsXml);
        } catch (\Exception $e) {
            abort(500, "\n~~~~~~~~\n$results\n~~~~~~~~\nis not a valid xml string");
        }
    }

    protected abstract function loadXmlResults($resultsXml);
}
