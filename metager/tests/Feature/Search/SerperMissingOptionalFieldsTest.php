<?php

namespace Tests\Feature\Search;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\FakesSearchEngines;
use Tests\TestCase;

/**
 * `serper_news` and `serper_shopping` copied `serper_web`'s parser, and copied
 * its bug along with it: each reads one "description" field off every result
 * unconditionally -- `snippet` for news, `delivery` for shopping -- unlike
 * every other optional field on the same object, which is guarded with
 * `property_exists()`. See SerperMissingSnippetTest for `serper_web` itself
 * and the production log entries this class of bug produced.
 *
 * `serper_images` doesn't have a description field, but it has the same shape
 * of bug one level down: it built an `Imagesearchdata` from six response
 * properties -- `thumbnailUrl`, `thumbnailWidth`, `thumbnailHeight`,
 * `imageUrl`, `imageWidth`, `imageHeight` -- with none of them guarded, and
 * that struct's constructor is strictly typed (`string`, `int`), so a missing
 * one throws the same way a missing `snippet` did.
 *
 * Missing the property, not just an empty value, is what reproduces the bug:
 * accessing an absent property on `stdClass` is what PHP turns into a
 * catchable error. An empty string would parse fine either way.
 */
class SerperMissingOptionalFieldsTest extends TestCase
{
    use FakesSearchEngines;

    protected function tearDown(): void
    {
        $this->forgetSearchUserClaims();
        parent::tearDown();
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: string, 3: string}>
     *         fokus, engine, response property, the field the parser used to read unconditionally
     */
    public static function descriptionFieldEndpoints(): array
    {
        return [
            "news" => ["nachrichten", "serper_news", "news", "snippet"],
            "shopping" => ["produkte", "serper_shopping", "shopping", "delivery"],
        ];
    }

    #[DataProvider("descriptionFieldEndpoints")]
    public function testAResultMissingItsDescriptionFieldDoesNotDropTheWholePage(string $fokus, string $engine, string $property, string $field): void
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses([
            $engine => json_encode([
                $property => [
                    ["title" => "Ohne $field", "link" => "https://serper-example.net/ohne-$field"],
                    ["title" => "Mit $field", "link" => "https://serper-example.net/mit-$field", $field => "vorhanden"],
                ],
            ]),
        ]);

        $response = $this->get("/meta/meta.ger3?eingabe=kaffee&focus=$fokus&out=json");
        $response->assertOk();

        $links = array_column($response->json("results"), "link");

        $this->assertContains(
            "https://serper-example.net/ohne-$field",
            $links,
            "The $engine result missing `$field` should still appear, with an empty description, not vanish."
        );
        $this->assertContains(
            "https://serper-example.net/mit-$field",
            $links,
            "A well-formed $engine result parsed after the broken one was lost along with it."
        );
    }

    public function testAnImageResultMissingADimensionDoesNotDropTheWholePage(): void
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses([
            "serper_images" => json_encode([
                "images" => [
                    [
                        "title" => "Ohne thumbnailWidth",
                        "link" => "https://serper-example.net/ohne-thumbnailwidth",
                        "thumbnailUrl" => "https://serper-example.net/thumb.jpg",
                        "thumbnailHeight" => 100,
                        "imageUrl" => "https://serper-example.net/full.jpg",
                        "imageWidth" => 800,
                        "imageHeight" => 600,
                    ],
                    [
                        "title" => "Vollstaendiges Bild",
                        "link" => "https://serper-example.net/vollstaendig",
                        "thumbnailUrl" => "https://serper-example.net/thumb2.jpg",
                        "thumbnailWidth" => 100,
                        "thumbnailHeight" => 100,
                        "imageUrl" => "https://serper-example.net/full2.jpg",
                        "imageWidth" => 800,
                        "imageHeight" => 600,
                    ],
                ],
            ]),
        ]);

        $response = $this->get("/meta/meta.ger3?eingabe=kaffee&focus=bilder&out=json");
        $response->assertOk();

        $links = array_column($response->json("results"), "link");

        $this->assertContains(
            "https://serper-example.net/ohne-thumbnailwidth",
            $links,
            "The image result missing `thumbnailWidth` should still appear, not vanish."
        );
        $this->assertContains(
            "https://serper-example.net/vollstaendig",
            $links,
            "A well-formed image result parsed after the broken one was lost along with it."
        );
    }
}
