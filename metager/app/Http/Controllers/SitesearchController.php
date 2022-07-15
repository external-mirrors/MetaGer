<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SitesearchController extends Controller
{
    public function loadPage(Request $request)
    {
        $css = file_get_contents(public_path("css/widget/widget-template.css"));
        return view('widget.sitesearch')
            ->with('title', trans('titles.sitesearch'))
            ->with('site', $request->input('site', ''))
            ->with('css', [mix('css/widget/widget.css'), mix('css/widget/widget-template.css')])
            ->with('template_preview', view('widget.websearch-template', ["site" => $request->input('site', '')])->render())
            ->with('template_webpage', view('widget.websearch-template', ["site" => $request->input('site', ''), "css" => $css])->render())
            ->with('navbarFocus', 'dienste');
    }
}
