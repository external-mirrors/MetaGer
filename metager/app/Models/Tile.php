<?php

namespace App\Models;

use App\Http\Controllers\Pictureproxy;
use Exception;
use Request;

/**
 * Model to hold data which represents a tile as visible on the startpage
 */
class Tile implements \JsonSerializable
{
    public string $title;
    public string $image;
    public string $image_alt;
    public string $url;
    public string $target;
    public string $classes = "";
    public string $image_classes = "";
    public bool $advertisement = false;
    /**
     * 
     * @param string $title Title to show for the Tile
     * @param string $image URL to a image to show for the Tile
     * @param string $image_alt Alt Text to show for the image
     * @param string $url URL to link to when the user clicks on the Tile
     * @param string $classes Additional css classes to append for the tile
     * @param string $image_classes Additional css classes to append to the image of the tile
     */
    public function __construct(string $title, string $image, string $image_alt, string $url, string $target = "_SELF", string $classes = "", string $image_classes = "", bool $advertisement = false)
    {
        $this->title = $title;
        try {
            $host = parse_url($image, PHP_URL_HOST);
            if ($host !== null && Request::host() !== $host) {
                $image = Pictureproxy::generateUrl($image);
            }
        } catch (Exception $e) {
        }
        $this->image = $image;
        $this->image_alt = $image_alt;
        $this->url = $url;
        $this->target = $target;
        $this->classes = $classes;
        $this->image_classes = $image_classes;
        $this->advertisement = $advertisement;
    }

    public function jsonSerialize()
    {
        $json = get_object_vars($this);
        $json["html"] = view("parts.tile", ["tile" => $this])->render();
        return $json;
    }
}