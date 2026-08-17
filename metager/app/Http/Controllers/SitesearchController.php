<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Http\Request;

class SitesearchController extends Controller
{
    public function loadPage(Request $request)
    {
        // Inlined into the snippet users paste onto their own site, so it has to be the
        // stylesheet's *content*, not a URL. Vite::content() resolves it through the manifest.
        $css = Vite::content("resources/less/metager/pages/widget/widget-template.less");
        return view('widget.sitesearch')
            ->with('title', trans('titles.sitesearch'))
            ->with('site', $request->input('site', ''))
            ->with('css', [Vite::asset('resources/less/metager/pages/widget/widget.less'), Vite::asset('resources/less/metager/pages/widget/widget-template.less')])
            ->with('template_preview', view('widget.websearch-template', ["site" => $request->input('site', '')])->render())
            ->with('template_webpage', view('widget.websearch-template', ["site" => $request->input('site', ''), "css" => $css])->render())
            ->with('navbarFocus', 'dienste');
    }
}
