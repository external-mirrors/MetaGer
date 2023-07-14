<?php

namespace App\Http\Controllers;

use App;
use Carbon;
use Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use LaravelLocalization;
use Response;
use Validator;

class Pictureproxy extends Controller
{
    public function get(Request $request)
    {
        // Validate input arguments
        $validator = Validator::make($request->all(), [
            'data' => 'required',
        ]);

        if ($validator->fails()) {
            abort(404);
        }

        try {
            $input_data = Crypt::decrypt($request->input('data'));
        } catch (DecryptException $e) {
            abort(404);
        }

        $validator = Validator::make($input_data, [
            'expires' => 'required|after_or_equal:now',
            'url' => 'required|url',
        ]);
        if ($validator->fails()) {
            abort(404);
        }

        try {
            $url = $input_data["url"];

            $file = file_get_contents($url, false);
            $responseCode = explode(" ", $http_response_header[0])[1];
            $contentType = "";
            foreach ($http_response_header as $header) {
                if (strpos($header, "Content-Type:") === 0) {
                    $tmp = explode(": ", $header);
                    $contentType = $tmp[1];
                }
            }
            if (stripos($contentType, "image/") === false) {
                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $contentType = $finfo->buffer($file);
            }
            if (stripos($contentType, "image/") === false) {
                abort(404);
            }
            $response = Response::make($file, $responseCode, [
                'Content-Type' => $contentType,
                "Cache-Control" => "max-age=3600, must-revalidate, public",
                "Last-Modified" => gmdate("D, d M Y H:i:s T"),
            ]);
        } catch (\ErrorException $e) {
            $response = Response::make("", 404);
        }
        return $response;
    }

    public static function generateUrl($link)
    {
        $params = [
            "url" => $link,
            "expires" => now()->addDay()
        ];

        $params = Crypt::encrypt($params);
        return route("imageproxy", ["data" => $params]);
    }
}