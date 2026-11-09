<?php

namespace Tests\Feature;

use App\Http\Controllers\Pictureproxy;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Process\Process;
use Tests\TestCase;

/**
 * The image proxy fetches the upstream image through PHP's http stream
 * wrapper, so these tests point it at a real local HTTP server rather than
 * Http::fake(), which only intercepts Laravel's HTTP client.
 */
class PictureproxyTest extends TestCase
{
    private static ?Process $server = null;
    private static string $origin;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        $socket = stream_socket_server('tcp://127.0.0.1:0');
        $port   = (int) substr(strrchr(stream_socket_get_name($socket, false), ':'), 1);
        fclose($socket);

        self::$origin = "http://127.0.0.1:$port";
        self::$server = new Process([PHP_BINARY, '-S', "127.0.0.1:$port", __DIR__ . '/../Fixtures/pictureproxy/router.php']);
        self::$server->start();
        for ($i = 0; $i < 100; $i++) {
            if (@fsockopen('127.0.0.1', $port)) {
                return;
            }
            usleep(50_000);
        }
        self::fail('the upstream test server did not start: ' . self::$server->getErrorOutput());
    }

    public static function tearDownAfterClass(): void
    {
        self::$server?->stop();
        parent::tearDownAfterClass();
    }

    private function proxied(string $upstreamPath): string
    {
        return Pictureproxy::generateUrl(self::$origin . $upstreamPath);
    }

    public function testAnImageWithinTheLimitIsProxied(): void
    {
        $response = $this->get($this->proxied('/png?bytes=2048'));

        $response->assertOk();
        $this->assertSame('image/png', $response->headers->get('Content-Type'));
        $this->assertSame(2048, strlen($response->getContent()));
    }

    /**
     * GlitchTip METAGER-18K: a ~100 MB upstream image was read whole into
     * memory and copied (Response, Cache::put's serialisation) past the
     * 256 MB memory_limit, a fatal error instead of a missing thumbnail.
     */
    public function testAnImageOverTheLimitIsNotFound(): void
    {
        $response = $this->get($this->proxied('/png?bytes=' . (Pictureproxy::MAX_IMAGE_BYTES + 1)));

        $response->assertNotFound();
    }

    public function testAnImageOverTheLimitWithoutContentLengthIsNotFound(): void
    {
        $response = $this->get($this->proxied('/png?length=0&bytes=' . (Pictureproxy::MAX_IMAGE_BYTES + 1)));

        $response->assertNotFound();
    }

    public function testAnImageOfExactlyTheLimitIsProxied(): void
    {
        $response = $this->get($this->proxied('/png?length=0&bytes=' . Pictureproxy::MAX_IMAGE_BYTES));

        $response->assertOk();
        $this->assertSame(Pictureproxy::MAX_IMAGE_BYTES, strlen($response->getContent()));
    }

    public function testAnOversizedImageIsNotCached(): void
    {
        $url = self::$origin . '/png?bytes=' . (Pictureproxy::MAX_IMAGE_BYTES + 1);
        $this->get(Pictureproxy::generateUrl($url))->assertNotFound();

        $this->assertFalse(Cache::has(md5($url)));
    }

    /**
     * GlitchTip METAGER-1A3: an upstream answered an AVIF labelled
     * image/jpeg. Imagick decoded it fine, and processJPEG() then asked it
     * to write the image back out in the format it was read in, which this
     * ImageMagick can decode but not encode: "Failed to get the image
     * contents", a 500 instead of an image. The proxy says image/jpeg, so
     * what it sends has to be a JPEG.
     */
    public function testAnAvifLabelledJpegIsConvertedToJpeg(): void
    {
        $response = $this->get($this->proxied('/avif-labelled-jpeg'));

        $response->assertOk();
        $this->assertSame('image/jpeg', $response->headers->get('Content-Type'));
        $this->assertSame('image/jpeg', (new \finfo(FILEINFO_MIME_TYPE))->buffer($response->getContent()));
    }

    public function testAnAvifLabelledJpegIsStillThumbnailed(): void
    {
        $response = $this->get($this->proxied('/avif-labelled-jpeg') . '&thumbnail_width=100');

        $response->assertOk();
        $this->assertSame(100, getimagesizefromstring($response->getContent())[0]);
    }

    /**
     * A JPEG Imagick cannot decode at all is the upstream's problem, not a
     * 500 of ours: it is served as received, the way every non-JPEG image
     * already is, and the browser makes of it what it can.
     */
    public function testAJpegImagickCannotReadIsServedUnprocessed(): void
    {
        $response = $this->get($this->proxied('/truncated-jpeg'));

        $response->assertOk();
        $this->assertSame(20, strlen($response->getContent()));
    }
}
