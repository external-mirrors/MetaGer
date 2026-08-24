<?php

namespace app\Models\parserSkripte;

use App\Localization;
use App\Models\Searchengine;
use App\Models\SearchengineConfiguration;
use Carbon;
use Log;

class Tootnews extends Searchengine
{
    /** troetnews.suma-ev.de instead of toot.suma-lab.de for a German search; see __construct(). */
    const GERMAN_HOST = 'troetnews.suma-ev.de';
    const GERMAN_DISPLAY_NAME = 'TroetNews';

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
                    'de' => '',
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

        // Same feed, same API, just a separate host (and brand) for the
        // German index. SearchengineConfiguration only ever reads a single
        // 'host'/'infos' from CONFIG_OVERLOAD, so the language-specific
        // values are swapped in here rather than expressed in the config
        // itself.
        if (Localization::getLanguage() === 'de') {
            $this->configuration->host = self::GERMAN_HOST;
            $this->configuration->infos->displayName = self::GERMAN_DISPLAY_NAME;
            $this->configuration->infos->homepage = 'https://' . self::GERMAN_HOST;
        }
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
                $additionalInformation = [];
                $published = (string) $result->{"published"};
                if ($published !== '') {
                    // RFC3339 (e.g. "2026-08-23T10:15:00+02:00"), which
                    // Carbon::parse() reads natively. A malformed date is
                    // dropped rather than left to Result::getDate() -- its
                    // return type is Carbon|null, so anything else stored
                    // under 'date' would TypeError there instead of here.
                    try {
                        $additionalInformation['date'] = Carbon::parse($published);
                    } catch (\Exception $e) {
                        Log::error("Could not parse the published date '$published' from $this->name:\n" . $e->getMessage());
                    }
                }
                $this->counter++;
                $this->results[] = new \App\Models\Result(
                    $this->configuration->engineBoost,
                    $title,
                    $link,
                    $anzeigeLink,
                    $descr,
                    $this->configuration->infos->displayName,
                    $this->configuration->infos->homepage,
                    $this->counter,
                    $additionalInformation
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