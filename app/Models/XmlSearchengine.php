<?php

namespace App\Models;

use Log;

abstract class XmlSearchengine extends Searchengine
{
    public function loadresults($results)
    {
        try {
            $resultsXml = \simplexml_load_string($results);
            $this->loadXmlResults($resultsXml);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }

    abstract protected function loadXmlResults($resultsXml);
}
