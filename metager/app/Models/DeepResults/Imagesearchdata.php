<?php

namespace App\Models\DeepResults;

use App\Http\Controllers\Pictureproxy;

/**
 * Holds Metadata for Image search results
 */
class Imagesearchdata
{
    public $thumbnail, $thumbnail_width, $thumbnail_height, $image, $image_width, $image_height;
    public function __construct(string $thumbnail, int $thumbnail_width, int $thumbnail_height, string $image, int $image_width, int $image_height)
    {
        $this->thumbnail        = $thumbnail;
        $this->thumbnail_proxy  = Pictureproxy::generateUrl($thumbnail);
        $this->thumbnail_width  = $thumbnail_width;
        $this->thumbnail_height = $thumbnail_height;

        $this->image        = $image;
        $this->image_proxy  = Pictureproxy::generateUrl($image);
        $this->image_width  = $image_width;
        $this->image_height = $image_height;
    }
}