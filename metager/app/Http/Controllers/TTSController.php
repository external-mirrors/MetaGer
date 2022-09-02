<?php

namespace App\Http\Controllers;

use Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;

class TTSController extends Controller
{

    public function tts(Request $request)
    {
        $text_params = $request->input("text", "");

        try {
            $text_params = Crypt::decrypt($text_params);
        } catch (DecryptException $exception) {
            abort(404);
        }
        $text = $text_params["text"];
        $locale = $text_params["locale"];

        $voices = [
            "de" => "bits1-hsmm",
            "en_GB" => "dfki-prudence-hsmm",
        ];

        $mary_tts_params = [
            "INPUT_TEXT"    => $text,
            "INPUT_TYPE"    => "TEXT",
            "OUTPUT_TYPE"   => "AUDIO",
            "AUDIO"         => "WAVE_FILE",
            "LOCALE"        => $locale,
            "VOICE"         => $voices[$locale]
        ];

        $mary_tts_url = config("metager.metager.tts.base_url");

        if (empty($mary_tts_url)) {
            abort(404);
        }

        $get_params = \http_build_query($mary_tts_params, "", "&", PHP_QUERY_RFC1738);
        $mary_tts_url .= "/process?" . $get_params;

        $content = \file_get_contents($mary_tts_url);

        return response($content, 200, [
            "Content-Type"      => "audio/x-wav",
            "Pragma"            => "no-cache",
            "Cache-Control"     => "no-cache, no-store, must-revalidate",
            "Content-Length"    => strlen($content),
        ]);
    }

    /**
     * Generates a URL for transforming given text into
     * speech in given locale
     * 
     * @param $text The Text to convert to speech
     * @param $locale The Locale of the speech (only "de" and "en" supported currently; fallback is en)
     */
    public static function CreateTTSUrl(string $text, string $locale = "en_GB")
    {
        $supported_locales = [
            "de" => "de",
            "en" => "en_GB"
        ];
        $text_param = [
            "text" => $text,
            "locale" => in_array($locale, array_keys($supported_locales)) ? $supported_locales[$locale] : "en_GB",
        ];
        $text_param = Crypt::encrypt($text_param);

        $url = route("tts", ["text" => $text_param]);
        return $url;
    }
}
