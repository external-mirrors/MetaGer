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

    public function search(Request $request, MetaGer $metager, $timing = false)
    {
        if(empty($request->input('eingabe'))) {
            return redirect("admin/stress/search?eingabe=test");
        }
        $metager->setDummy(true);
        parent::search($request, $metager, $timing);
    }
}