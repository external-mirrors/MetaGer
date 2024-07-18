<?php

namespace App\Models;

/**
 * Model to hold data which represents a tile as visible on the startpage
 */
class Tile implements \JsonSerializable
{
    public string $title;
    public string $image;
    public string $image_alt;
    public string $url;
    public string $classes = "";
    public string $image_classes = "";
    /**
     * 
     * @param string $title Title to show for the Tile
     * @param string $image URL to a image to show for the Tile
     * @param string $image_alt Alt Text to show for the image
     * @param string $url URL to link to when the user clicks on the Tile
     * @param string $classes Additional css classes to append for the tile
     * @param string $image_classes Additional css classes to append to the image of the tile
     */
    public function __construct(string $title, string $image, string $image_alt, string $url, string $classes = "", string $image_classes = "")
    {
        $this->title = $title;
        $this->image = $image;
        $this->image_alt = $image_alt;
        $this->url = $url;
        $this->classes = $classes;
        $this->image_classes = $image_classes;
    }

    public function jsonSerialize()
    {
        $json = get_object_vars($this);
        $json["html"] = view("parts.tile", ["tile" => $this])->render();
        return $json;
    }
}