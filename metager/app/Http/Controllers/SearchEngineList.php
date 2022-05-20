<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SearchEngineList extends Controller
{
    function index()
    {
        $suma_file = "";
        if (App::isLocale("en")) {
            $suma_file = config_path() . "/sumasEn.json";
        } else {
            $suma_file = config_path() . "/sumas.json";
        }
        if (empty($suma_file)) {
            abort(404);
        }
        $suma_file = json_decode(file_get_contents($suma_file));
        if ($suma_file === null) {
            abort(404);
        }
        $sumas = [];
        foreach ($suma_file->foki as $fokus_name => $fokus) {
            foreach ($fokus->sumas as $suma_name) {
                $sumas[$fokus_name][] = $suma_name;
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
