<?php

namespace App\Http\Controllers;

use App\Localization;
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
        foreach ($suma_file->foki as $fokus_name => $fokus) {
            foreach ($fokus->sumas as $suma_name) {
                if (
                    ## Lang support is not defined
                    (\property_exists($suma_file->sumas->{$suma_name}, "lang") && \property_exists($suma_file->sumas->{$suma_name}->lang, "languages") && \property_exists($suma_file->sumas->{$suma_name}->lang, "regions")) &&
                    ## Current Locale/Lang is not supported by this engine
                    (\property_exists($suma_file->sumas->{$suma_name}->lang->languages, $lang) || \property_exists($suma_file->sumas->{$suma_name}->lang->regions, $locale))
                ) {
                    $sumas[$fokus_name][] = $suma_name;
                }
            }
        }
        $suma_infos = [];
        foreach ($sumas as $fokus_name => $suma_list) {
            foreach ($suma_list as $index => $suma_name) {
                if (!$suma_file->sumas->{$suma_name}->disabled) {
                    $infos = $suma_file->sumas->{$suma_name}->infos;
                    $suma_infos[$fokus_name][$suma_name] = clone $infos;
                }
            }
        }
        return view('search-engine')
            ->with('title', trans('titles.search-engine'))
            ->with('navbarFocus', 'info')
            ->with('suma_infos', $suma_infos);
    }
}
