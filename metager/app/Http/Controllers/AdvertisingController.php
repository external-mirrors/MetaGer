<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdvertisingController extends Controller
{
    public function overview(Request $request)
    {
        return view("advertising.overview", ["title" => __("titles.advertising.overview"), "css" => [mix("/css/advertising/light.css")], "darkcss" => [mix("/css/advertising/dark.css")]]);
    }
}
