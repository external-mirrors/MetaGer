<?php 

namespace App\Models;

class Key{
    public $key;
    public $status; # valid key = true, invalid key = false, unidentified key = null


    public function __construct($key, $status = null){
        $this->key = $key;
        $this->status = $status;
    }

    # always returns true or false
    public function getStatus() {
        if($this->key !== '' && $this->status === null) {
            $this->updateStatus();
        }
        if($this->status === null || $this->status === false) {
            return false;
        } else {
            return true;
        }
    }

    
    public function updateStatus() {
        
        try {
            $link = "https://key.metager3.de/" . urlencode($this->key) . "/request-permission/api-access";
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

    public function requestPermission() {

        $postdata = http_build_query(array(
            'dummy' => 0,
        ));
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