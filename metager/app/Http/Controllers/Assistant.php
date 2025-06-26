<?php

namespace App\Http\Controllers;

use Crypt;
use Exception;
use Illuminate\Http\Request;
use App\Models\Assistant\Openai;

class Assistant extends Controller
{
    public function assist(Request $request)
    {
        // For now always Openai
        if ($request->filled("history")) {
            try {
                $assistant = unserialize(Crypt::decrypt($request->input("history")));
            } catch (Exception $e) {
                $assistant = new Openai();
            }
        } else {
            $assistant = new Openai();
        }

        if ($request->filled("prompt")) {
            $assistant->process($request->input("prompt"));
        }

        return response(view("assistant/base", [
            "assistant" => $assistant,
            "history" => Crypt::encrypt(serialize($assistant)),
            "css" => [mix("/css/assistant.css")],
            "js" => [mix("/js/assistant.js")]
        ]));
    }
}
