<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Assistant extends Controller
{
    public function assist(Request $request)
    {
        return response(view("assistant/base", [
            "css" => [mix("/css/assistant.css")],
            "js" => [mix("/js/assistant.js")]
        ]));
    }
}
