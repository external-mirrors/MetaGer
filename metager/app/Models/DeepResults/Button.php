<?php

namespace App\Models\DeepResults;

class Button
{
    public $title, $link;
    function __construct($title, $link)
    {
        $this->title = $title;
        $this->link = $link;
    }
}