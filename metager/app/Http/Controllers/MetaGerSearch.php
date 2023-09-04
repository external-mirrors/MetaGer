<?php

namespace App\Http\Controllers;

use App\Localization;
use App\MetaGer;
use App\Models\Authorization\Authorization;
use App\Models\Configuration\Searchengines;
use App\Models\DisabledReason;
use App\Models\Quicktips\Quicktips;
use App\PrometheusExporter;
use App\QueryTimer;
use App\SearchSettings;
use Blade;
use Carbon;
use Cookie;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Prometheus\CollectorRegistry;

class MetaGerSearch extends Controller
{
    public function search(Request $request, MetaGer $metager, $timing = false)
    {
        $query_timer = \app()->make(QueryTimer::class);
        $language = Localization::getLanguage();

        $preferredLanguage = array($request->getPreferredLanguage());
        if (!empty($preferredLanguage) && !empty($language)) {
            PrometheusExporter::PreferredLanguage($language, $preferredLanguage);
        }

        if ($request->filled("chrome-plugin")) {
            return redirect(LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/plugin"));
        }

        $settings = app(SearchSettings::class);

        if ($settings->fokus === "maps") {
            return redirect()->to('https://maps.metager.de/map/' . $settings->q . '/1240908.5493525574,6638783.2192695495,6');
        }

        # If there is no query parameter we redirect to the startpage
        if (empty(trim($settings->q))) {
            return redirect(LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), '/'));
        }

        # Nach Spezialsuchen überprüfen:
        $query_timer->observeStart("Search_CheckSpecialSearches");
        $metager->checkSpecialSearches($request);
        $query_timer->observeEnd("Search_CheckSpecialSearches");

        # Search query can be empty after parsing the formdata
        # we will cancel the search in that case and show an error to the user
        if (empty($settings->q)) {
            return $metager->createView();
        }

        if (empty(app(Searchengines::class)->getEnabledSearchengines())) {
            /**
             * Temporary migration to fix settings for users that already
             * had now invalid settings saved when we updated.
             */
            if (!app(Authorization::class)->canDoAuthenticatedSearch()) {
                $settings = app(SearchSettings::class);
                $setting_removed = false;
                foreach ($settings->parameterFilter as $filterName => $filter) {
                    // Check if the user has an option enabled that is only available with metager key
                    if (
                        Cookie::has($settings->fokus . "_setting_" . $filter->{"get-parameter"}) &&
                        in_array($filter->value, array_keys($filter->{"disabled-values"})) &&
                        in_array(DisabledReason::PAYMENT_REQUIRED, $filter->{"disabled-values"}[$filter->value])
                    ) {
                        Cookie::queue(Cookie::forget($settings->fokus . "_setting_" . $filter->{"get-parameter"}));
                        $setting_removed = true;
                    }
                }
                if ($setting_removed) {
                    return redirect(url()->full());
                }
            }
            return redirect(route("settings", ["focus" => $settings->fokus]) . "#engines");
        }

        app(Searchengines::class)->checkPagination();

        $query_timer->observeStart("Search_CreateQuicktips");

        /** @var Quicktips */
        $quicktips = $metager->createQuicktips();
        $query_timer->observeEnd("Search_CreateQuicktips");

        $query_timer->observeStart("Search_StartSearch");
        $metager->startSearch();
        $query_timer->observeEnd("Search_StartSearch");

        $query_timer->observeStart("Search_WaitForMainResults");
        $metager->waitForMainResults();
        $query_timer->observeEnd("Search_WaitForMainResults");

        $query_timer->observeStart("Search_RetrieveResults");
        $metager->retrieveResults();
        $query_timer->observeEnd("Search_RetrieveResults");

        // Versuchen die Ergebnisse der Quicktips zu laden
        if ($quicktips !== null) {
            $query_timer->observeStart("Search_LoadQuicktips");
            $quicktips->loadResults();
            $query_timer->observeEnd("Search_LoadQuicktips");
        }

        $admitad = [];
        if (!app(Authorization::class)->canDoAuthenticatedSearch()) {
            $newAdmitad = new \App\Models\Admitad($metager);
            if (!empty($newAdmitad->hash)) {
                $admitad[] = $newAdmitad;
            }
        }
        $admitad = $metager->parseAffiliates($admitad);

        # Alle Ergebnisse vor der Zusammenführung ranken:
        $query_timer->observeStart("Search_RankAll");
        $metager->rankAll();
        $query_timer->observeEnd("Search_RankAll");

        # Ergebnisse der Suchmaschinen kombinieren:
        $query_timer->observeStart("Search_PrepareResults");
        $metager->prepareResults();
        $query_timer->observeEnd("Search_PrepareResults");


        $query_timer->observeStart("Search_Affiliates");

        // Add Advertisement for Donations
        $donation_advertisement_position = null;
        if (!app(Authorization::class)->canDoAuthenticatedSearch()) {
            $donation_advertisement_position = $metager->addDonationAdvertisement();
        }
        $query_timer->observeEnd("Search_Affiliates");

        foreach (app(Searchengines::class)->getEnabledSearchengines() as $engine) {
            if ($engine->loaded) {
                $engine->setNew(false);
                $engine->markNew();
            }
        }
        $query_timer->observeStart("Search_CacheFiller");
        try {
            $authorization = app(Authorization::class);
            $searchengines = app(Searchengines::class);
            $settings = app(SearchSettings::class);
            Cache::put("loader_" . $metager->getSearchUid(), [
                "metager" => [
                    "authorization" => $authorization,
                    "searchengines" => $searchengines,
                    "settings" => $settings,
                    "quicktips" => $quicktips
                ],
                "donation_advertisement_position" => $donation_advertisement_position,
                "admitad" => $admitad,
                "engines" => $metager->getEngines(),
            ], 60 * 60);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        $query_timer->observeEnd("Search_CacheFiller");

        $registry = CollectorRegistry::getDefault();
        $counter = $registry->getOrRegisterCounter('metager', 'result_counter', 'counts total number of returned results', []);
        $counter->incBy(sizeof($metager->getResults()));
        $counter = $registry->getOrRegisterCounter('metager', 'query_counter', 'counts total number of search queries', []);
        $counter->inc();

        $query_timer->observeTotal();
        if ($quicktips !== null) {
            $quicktip_results = $quicktips->quicktips;
        } else {
            $quicktip_results = null;
        }

        $script_src_elem = "'self'";
        $img_src = "'self' data:";
        $connect_src = "'self'";
        if (app(Searchengines::class)->getEnabledSearchengine("yahoo") !== null) {
            $script_src_elem .= " https://s.yimg.com https://msadsscale.azureedge.net https://www.clarity.ms";
            $img_src .= " https://search.yahoo.com https://xmlp.search.yahoo.com";
            $connect_src .= " https://search.yahoo.com https://*.clarity.ms https://browser.pipe.aria.microsoft.com";
        }

        return response($metager->createView($quicktip_results), 200, [
            "Cache-Control" => "max-age=3600, must-revalidate, public",
            "Content-Security-Policy" => "default-src 'self'; script-src 'self'; script-src-elem $script_src_elem; script-src-attr 'self'; style-src 'self'; style-src-elem 'self'; style-src-attr 'self'; img-src $img_src; font-src 'self'; connect-src $connect_src; frame-src 'self'; frame-ancestors 'self'; form-action 'self' metager.org metager.de",
            "Last-Modified" => gmdate("D, d M Y H:i:s T"),
        ]);
    }

    public function searchTimings(Request $request, MetaGer $metager)
    {
        $request->merge([
            'eingabe' => "Hannover",
        ]);
        return $this->search($request, $metager, true);
    }

    public function loadMore(Request $request)
    {
        /**
         * There are three forms of requests to the resultpage
         * 1. Initial Request: Loads the fastest searchengines and sends them to the user
         * 2. Load more results (with JS): Loads new search engines that answered after the initial request was send
         * 3. Load more results (without JS): Loads new search engines that answered within 1s timeout
         */
        if ($request->filled('loadMore') && $request->filled('script') && $request->input('script') === "yes") {
            return $this->loadMoreJS($request);
        }
    }

    private function loadMoreJS(Request $request)
    {
        \app(SearchSettings::class)->javascript_enabled = true;
        # Create a MetaGer Instance with the supplied hash
        $hash = $request->input('loadMore', '');
        unset($request["loadMore"]);
        unset($request["script"]);

        # Parser Skripte einhängen
        $dir = app_path() . "/Models/parserSkripte/";
        foreach (scandir($dir) as $filename) {
            $path = $dir . $filename;
            if (is_file($path)) {
                require_once $path;
            }
        }

        $cached = Cache::get($hash);
        if ($cached === null) {
            if ($request->header("If-Modified-Since") !== null) {
                return response("", 304, [
                    "Cache-Control" => "max-age=3600, must-revalidate, public",
                    "Last-Modified" => gmdate("D, d M Y H:i:s T"),
                ]);
            } else {
                return response()->json(['finished' => true]);
            }
        }

        $engines = $cached["engines"];
        $admitad = $cached["admitad"];
        $mg = $cached["metager"];

        $metager = new MetaGer(substr($hash, strpos($hash, "loader_") + 7));
        $authorization = $mg["authorization"];
        app()->singleton(Authorization::class, function ($app) use ($authorization) {
            return $authorization;
        });
        $settings = $mg["settings"];
        app()->singleton(SearchSettings::class, function ($app) use ($settings) {
            return $settings;
        });
        $searchengines = $mg["searchengines"];
        app()->singleton(Searchengines::class, function ($app) use ($searchengines) {
            return $searchengines;
        });
        /** @var Quicktips */
        $quicktips = $mg["quicktips"];
        $quicktips->loadResults();


        # Nach Spezialsuchen überprüfen:
        $metager->checkSpecialSearches($request);

        $admitadCountBefore = sizeof($admitad);
        $engineCountBefore = 0;
        foreach (app(Searchengines::class)->getEnabledSearchengines() as $engine) {
            if ($engine->loaded) {
                $engineCountBefore++;
            }
        }

        # Checks Cache for engine Results
        $metager->checkCache();
        $metager->retrieveResults();

        if (!app(Authorization::class)->canDoAuthenticatedSearch()) {
            $newAdmitad = new \App\Models\Admitad($metager);
            if (!empty($newAdmitad->hash)) {
                $admitadCountBefore = -1; // Always Mark admitad as changed when adding a new request
                $admitad[] = $newAdmitad;
            }
        }
        $admitad = $metager->parseAffiliates($admitad);

        $metager->rankAll();
        $metager->prepareResults();

        // Add Advertisement for Donations
        if (!app(Authorization::class)->canDoAuthenticatedSearch() && array_key_exists("donation_advertisement_position", $cached) && $cached["donation_advertisement_position"] !== null) {
            $metager->addDonationAdvertisement($cached["donation_advertisement_position"]);
        }

        $result = [
            'finished' => true,
            'results' => "",
            'nextSearchLink' => $metager->nextSearchLink(),
            'imagesearch' => false,
        ];

        if ($quicktips->new) {
            $result["quicktips"] = Blade::render("parts.quicktips", ["quicktips" => $quicktips->quicktips]);
        }

        $newResults = 0;
        $viewResults = [];
        foreach ($metager->getResults() as $index => $resultTmp) {
            $viewResults[] = get_object_vars($resultTmp);
            if ($resultTmp->new) {
                $newResults++;
            }
            if ($metager->getFokus() === "bilder") {
                $result["imagesearch"] = true;
            }
        }

        $finished = true;
        $enginesLoaded = [];
        $enginesLoadedAfter = 0;
        foreach (app(Searchengines::class)->getEnabledSearchengines() as $engine) {
            if (!$engine->loaded) {
                $enginesLoaded[$engine->name] = false;
                $finished = false;
            } else {
                $enginesLoaded[$engine->name] = true;
                $engine->setNew(false);
                $engine->markNew();
                $enginesLoadedAfter++;
            }
        }

        if (sizeof($admitad) > 0) {
            $finished = false;
        }

        if ($request->header("If-Modified-Since") !== null && $engineCountBefore === $enginesLoadedAfter && $admitadCountBefore === sizeof($admitad) && !array_key_exists("quicktips", $result)) {
            // Nothing changed but we are not finished yet either
            return response("", 304);
        }

        $result["finished"] = $finished;
        $result["engines"] = $enginesLoaded;

        if ($newResults > 0) {
            $registry = CollectorRegistry::getDefault();
            $counter = $registry->getOrRegisterCounter('metager', 'result_counter', 'counts total number of returned results', []);
            $counter->incBy($newResults);
        }
        // Update new Engines
        $authorization = app(Authorization::class);
        $searchengines = app(Searchengines::class);
        $cacheControl = "no-cache, must-revalidate, public";
        if ($finished) {
            Cache::forget("loader_" . $metager->getSearchUid());
            $cacheControl = "max-age=3600, must-revalidate, public";
        } else {
            Cache::put("loader_" . $metager->getSearchUid(), [
                "metager" => [
                    "authorization" => $authorization,
                    "searchengines" => $searchengines,
                    "settings" => $settings,
                    "quicktips" => $quicktips
                ],
                "admitad" => $admitad,
                "engines" => $metager->getEngines(),
            ], 60 * 60);
        }

        $result["results"] = view('resultpages.results')
            ->with('results', $viewResults)
            ->with('eingabe', $metager->getEingabe())
            ->with('mobile', $metager->isMobile())
            ->with('warnings', $metager->warnings)
            ->with('htmlwarnings', $metager->htmlwarnings)
            ->with('errors', $metager->errors)
            ->with('apiAuthorized', $metager->isApiAuthorized())
            ->with('metager', $metager)
            ->with('fokus', $metager->getFokus())->render();

        # JSON encoding will fail if invalid UTF-8 Characters are in this string
        # mb_convert_encoding will remove thise invalid characters for us
        $result = mb_convert_encoding($result, "UTF-8", "UTF-8");
        return response()->json($result, 200, [
            "Cache-Control" => $cacheControl,
            "Last-Modified" => gmdate("D, d M Y H:i:s T"),
        ]);
    }

    public function botProtection($redirect)
    {
        $hash = md5(date('YmdHi'));
        return view('botProtection')
            ->with('hash', $hash)
            ->with('r', $redirect);
    }

    public function get($url)
    {
        $ctx = stream_context_create(array('http' => array('timeout' => 2)));
        return file_get_contents($url, false, $ctx);
    }

    private function startsWith($haystack, $needle)
    {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

    public function tips(Request $request)
    {
        $tipserver = '';
        if (App::environment() === "development") {
            $tipserver = "https://dev.quicktips.metager.de/1.1/tips.xml";
        } else {
            $tipserver = "https://quicktips.metager.de/1.1/tips.xml";
        }
        if (Localization::getLanguage() == "en") {
            $tipserver .= "?locale=en";
        }
        $tips_text = file_get_contents($tipserver);
        $tips = [];
        try {
            $tips_xml = \simplexml_load_string($tips_text);

            $tips_xml->registerXPathNamespace('mg', 'http://metager.de/tips/');
            $tips_xml = $tips_xml->xpath('mg:tip');
            foreach ($tips_xml as $tip_xml) {
                $tips[] = $tip_xml->__toString();
            }
        } catch (\Exception $e) {
            Log::error("A problem occurred loading tips from the tip server.");
            Log::error($e->getMessage());
            abort(500);
        }
        return view('tips')
            ->with('title', trans('tips.title'))
            ->with('tips', $tips);
    }
}