<?php

namespace App\Models\Quicktips;

class Quicktip
{
    public $type;
    public $title;
    public $link;
    public $gefVonTitle;
    public $gefVonLink;
    public $author;
    public $descr;
    public $details;
    /** @var string Order of display on the result page */
    public $order;

    # Erstellt ein neues Ergebnis
    public function __construct($type, $title, $link, $gefVonTitle, $gefVonLink, $author, $descr, $details)
    {
        $this->type = $type;
        $this->title = $title;
        $this->link = $link;
        $this->gefVonTitle = $gefVonTitle;
        $this->gefVonLink = $gefVonLink;
        $this->author = $author;
        $this->descr = $descr;
        $this->details = $details;

        switch ($this->type) {
            case "spruch":
                $this->order = 1;
                break;
            case "duckDuckGo-bang":
                $this->order = 2;
                break;
            case "wikipedia":
                $this->order = 3;
                break;
            case "dictCC":
                $this->order = 4;
                break;
            case "tip":
                $this->order = 5;
                break;
            case "ad":
                $this->order = 6;
                break;
            default:
                $this->order = 7;
        }
    }
}
