<?php

namespace App\Http\Controllers;

use App;
use Cache;
use Carbon;
use Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use LaravelLocalization;
use Predis\PredisException;
use Response;
use Validator;

class Pictureproxy extends Controller
{
    /**
     * The largest upstream image the proxy reads. Everything it fetches is
     * held in memory several times over (the body, the Response, Imagick's
     * output, Cache::put's serialisation), so a ~100 MB image ran the
     * 256 MB memory_limit out (GlitchTip METAGER-18K). Anything larger
     * answers 404, as an image that cannot be fetched always has.
     */
    public const MAX_IMAGE_BYTES = 16 * 1024 * 1024;

    public function get(Request $request)
    {
        // Validate input arguments
        $validator = Validator::make($request->all(), [
            'data' => 'required',
        ]);

        $thumbnail_width = $request->input("thumbnail_width", null);

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
            'url'     => 'required|url',
        ]);
        if ($validator->fails()) {
            abort(404);
        }

        $image_hash = md5($input_data["url"] . $thumbnail_width);
        // A Redis/sentinel hiccup here is just a cache miss, not a reason to
        // fail the whole proxied image (GlitchTip METAGER-I): fetching the
        // image fresh is exactly what an actual cache miss already does.
        try {
            $cacheHit = Cache::has($image_hash);
        } catch (PredisException $e) {
            $cacheHit = false;
        }
        if ($cacheHit) {
            $response = Cache::get($image_hash);
        } else {
            try {
                $url = $input_data["url"];

                $context = stream_context_create([
                    "http" => [
                        "method" => "GET",
                        "header" => [
                            "User-Agent: " . $request->userAgent(),
                        ],
                    ],
                ]);

                $stream       = fopen($url, "rb", false, $context);
                $responseCode = explode(" ", $http_response_header[0])[1];
                $contentType  = "";
                foreach ($http_response_header as $header) {
                    if (strpos($header, "Content-Type:") === 0) {
                        $tmp         = explode(": ", $header);
                        $contentType = $tmp[1];
                    }
                    if (stripos($header, "Content-Length:") === 0 && (int) trim(substr($header, 15)) > self::MAX_IMAGE_BYTES) {
                        fclose($stream);
                        abort(404);
                    }
                }
                // Content-Length is optional; the read itself is the cap.
                $file = stream_get_contents($stream, self::MAX_IMAGE_BYTES + 1);
                fclose($stream);
                if ($file === false || strlen($file) > self::MAX_IMAGE_BYTES) {
                    abort(404);
                }
                if (stripos($contentType, "image/") === false) {
                    $finfo       = new \finfo(FILEINFO_MIME_TYPE);
                    $contentType = $finfo->buffer($file);
                }
                if (stripos($contentType, "image/") === false) {
                    abort(404);
                }

                if ($contentType === "image/jpeg") {
                    $file = $this->processJPEG($file, $thumbnail_width);
                }

                $response = Response::make($file, $responseCode, [
                    'Content-Type'  => $contentType,
                    "Cache-Control" => "max-age=3600, must-revalidate, public",
                    "Last-Modified" => gmdate("D, d M Y H:i:s T"),
                ]);
                Cache::put($image_hash, $response, now()->addMinutes(15));
            } catch (\ErrorException $e) {
                $response = Response::make("", 404);
            } catch (PredisException $e) {
                // The image was fetched fine; only caching it failed. Serve
                // it anyway rather than 500ing over a lost cache write.
            }
        }
        return $response;
    }

    private function processJPEG(string $jpeg, $thumbnail_width)
    {
        $src_image = new \Imagick();
        $src_image->readImageBlob($jpeg);

        $src_width = $src_image->getImageWidth();
        if ($thumbnail_width !== null && $src_width > $thumbnail_width) {
            $src_image->thumbnailImage($thumbnail_width, 0);
        }

        $src_image->setInterlaceScheme(\Imagick::INTERLACE_PLANE);
        return $src_image->getImageBlob();
    }

    public static function generateUrl($link)
    {
        $params = [
            "url"     => $link,
            "expires" => now()->addDay(),
        ];

        $params = Crypt::encrypt($params);
        return route("imageproxy", ["data" => $params]);
    }
}