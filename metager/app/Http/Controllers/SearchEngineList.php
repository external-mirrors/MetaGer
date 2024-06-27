<?php

namespace App\Http\Controllers;

use App\Localization;
use App\Models\Configuration\Searchengines;
use App\Models\DisabledReason;
use LaravelLocalization;

class SearchEngineList extends Controller
{
    function index()
    {

        $suma_file = config_path() . "/sumas.json";

        if (empty($suma_file)) {
            abort(404);
        }
        $suma_file = json_decode(file_get_contents($suma_file));
        if ($suma_file === null) {
            abort(404);
        }

        $locale = LaravelLocalization::getCurrentLocaleRegional();
        $lang = Localization::getLanguage();
        $sumas = [];

        $search_engines = app(Searchengines::class);

        foreach ($suma_file->foki as $fokus_name => $fokus) {
            foreach ($fokus->sumas as $suma_name) {
                if (!array_key_exists($suma_name, $search_engines->sumas))
                    continue;
                if ($search_engines->sumas[$suma_name]->configuration->disabled && in_array(DisabledReason::SUMAS_CONFIGURATION, $search_engines->sumas[$suma_name]->configuration->disabledReasons))
                    continue;
                if (!array_key_exists($fokus_name, $sumas))
                    $sumas[$fokus_name] = [];
                $sumas[$fokus_name][$suma_name] = $search_engines->sumas[$suma_name]->configuration->infos;
            }
        }

        return view('search-engine')
            ->with('title', trans('titles.search-engine'))
            ->with('navbarFocus', 'info')
            ->with('suma_infos', $sumas);
    }
}
