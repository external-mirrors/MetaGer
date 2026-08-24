<?php

namespace app\Models\parserSkripte;

use App\Models\Searchengine;
use App\Models\SearchengineConfiguration;
use Log;

class Tootnews extends Searchengine
{
    const CONFIG_OVERLOAD = [
        'tootnews' => [
            'host' => 'toot.suma-lab.de',
            'path' => '/search',
            'port' => 443,
            'query-parameter' => 'q',
            'input-encoding' => 'utf8',
            'output-encoding' => 'utf8',
            'lang' => [
                'parameter' => '',
                'languages' => [
                    'en' => '',
                ],
                'regions' => [],
            ],
            'engine-boost' => 1,
            'cost' => 1,
            'get-parameter' => [
                'out' => 'api',
            ],
            'infos' => [
                'homepage' => 'https://toot.suma-lab.de',
                'index_name' => null,
                'display_name' => 'TootNews',
                'founded' => '2026',
                'headquarter' => 'Hannover',
                'operator' => 'SUMA-EV',
                'index_size' => null,
            ],
        ],
    ];
    public $results = [];

    public function __construct($name, SearchengineConfiguration $configuration)
    {

        parent::__construct($name, $configuration);
    }

    public function loadResults($result)
    {
        try {
            $content = \simplexml_load_string($result);
            if (!$content) {
                return;
            }

            // The feed declares a default Atom namespace (xmlns="..." with no
            // prefix), so an unprefixed xpath query never matches: XPath 1.0
            // only matches unprefixed names against elements that have no
            // namespace at all. local-name() sidesteps registering the
            // namespace and keeps working if the feed's namespace URI changes.
            $results = $content->xpath("//*[local-name()='feed'][1]/*[local-name()='entry']") ?: [];
            foreach ($results as $result) {
                $title = $this->text($result->{"title"});
                $link = (string) $result->{"link"}['href'];
                if ($title === '' || $link === '') {
                    continue;
                }
                $anzeigeLink = (string) $result->children('http://metager.de/opensearch/')->anzeigeLink;
                if ($anzeigeLink === '') {
                    $anzeigeLink = $link;
                }
                $descr = $this->text($result->{"content"});
                $this->counter++;
                $this->results[] = new \App\Models\Result(
                    $this->configuration->engineBoost,
                    $title,
                    $link,
                    $anzeigeLink,
                    $descr,
                    $this->configuration->infos->displayName,
                    $this->configuration->infos->homepage,
                    $this->counter
                );
            }
        } catch (\Exception $e) {
            Log::error("A problem occurred parsing results from $this->name:\n" . $e->getMessage() . "\n" . $result);
            return;
        }
    }


    public function getNext(\App\MetaGer $metager, $result)
    {
        return;
    }

    /**
     * Tags stripped, then entities decoded -- in that order.
     *
     * asXML() re-serialises the node, which re-escapes any "&", "<", ">" that
     * were entities in the source (`&amp;` back to `&amp;`) rather than
     * leaving them decoded, so strip_tags() alone still leaves "&amp;"
     * sitting in the text users see. html_entity_decode() alone has the
     * opposite problem: cast to string, a node with real nested markup
     * (`<b>text</b>`) loses that markup's text outright instead of just its
     * tags, because (string) only reads the node's own direct text.
     */
    private function text(\SimpleXMLElement $node): string
    {
        return html_entity_decode(strip_tags($node->asXML() ?: ''), ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}