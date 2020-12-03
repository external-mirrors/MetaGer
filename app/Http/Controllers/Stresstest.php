<?php

namespace App\Http\Controllers;

use App;
use App\MetaGer;
use Cache;
use Illuminate\Http\Request;
use LaravelLocalization;
use Log;
use View;

class Stresstest extends MetaGerSearch
{
    public function index(Request $request, MetaGer $metager, $timing = false)
    {
        $request->merge(["eingabe" => "test" . rand()]);
        $metager->setDummy(true);
        $metager->setAdgoalHash(true);
        parent::search($request, $metager, $timing);
    }
}