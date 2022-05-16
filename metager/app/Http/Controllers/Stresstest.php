<?php

namespace App\Http\Controllers;

use App;
use App\MetaGer;
use Cache;
use Illuminate\Http\Request;
use LaravelLocalization;
use Log;
use View;

/* The controller uses the MetaGers dummy engine ( documentation: https:\/\/gitlab.metager.de\/open-source\/dummy-engine )
*  to generate a list of test results for stress testing purposes at "/admin/stress".
*  For local testing go to config/stress.json and change sumas->dummy->host to "dummy-nginx".
*  To activate browser and human verfication use the following route: "/admin/stress/verify".
*/ 
class Stresstest extends MetaGerSearch
{
    public function index(Request $request, MetaGer $metager, $timing = false)
    {
        # adds / replaces query input with a random string to avoid cached results
        $request->merge(["eingabe" => "test" . rand()]);

        # deactivates adgoal
        $metager->setDummy(true);

        parent::search($request, $metager, $timing);
    }
}