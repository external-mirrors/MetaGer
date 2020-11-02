<?php 

namespace App\Models;

class Key{
    public $key;
    public $status;


    public function __construct($key, $status){
        $this->key = $key;
        $this->status = updateStatus();
    }

    public function getStatus(){
        return $this->status;
    }

    public function updateStatus(){
        
        try {
            $link = "https://key.metager3.de/" . urlencode($key) . "/request-permission/api-access";
            $result = json_decode(file_get_contents($link));
            if ($result->{'api-access'} == true) {
                $this->status = true;
                return true;
            } else {
                $this->status = false;
                return false;
            }
        } catch (\ErrorException $e) {
            return false;
        }
    }

    public function requestPermission(){

        $opts = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => $postdata,
            ),
        );

        $context = stream_context_create($opts);

        try {
            $link = "https://key.metager3.de/" . urlencode($key) . "/request-permission/api-access";
            $result = json_decode(file_get_contents($link, false, $context));
            if ($result->{'api-access'} == true) {
                return true;
            } else {
                $this->status = false;
                return false;
            }
        } catch (\ErrorException $e) {
            return false;
        }
    }
}