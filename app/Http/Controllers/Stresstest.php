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
    public function index(Metager $metager)
    {
        return redirect("admin/stress/search?eingabe=test");
    }

    public function search(Request $request, MetaGer $metager, $timing = false, $nocache = false)
    {
        if(empty($request->input('eingabe'))) {
            return redirect("admin/stress/search?eingabe=test");
        }
        $metager->setDummy(true);
        $metager->setAdgoalHash(true);
        if(!empty($request->input('cache')) && $request->input('cache') === 'off') {
            parent::search($request, $metager, $timing, true);
        } else {
            parent::search($request, $metager, $timing);
        }
    }
}