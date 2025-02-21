<?php

namespace App;

use App\Models\Suggestions\Serper;
use Exception;

/**
 * Base class for all search suggestions implementations
 * by different providers
 */
abstract class Suggestions
{
    public const NAME = "";
    protected string $query;
    protected array $suggestions = [];
    /** Should the request be made as POST request. GET method is used otherwise */
    protected bool $api_method_post = false;
    protected int $api_success_response_code = 200;
    protected string $api_base;
    /**
     * Only used if $api_method_post == true
     * Defines the Post data to send
     * @var string
     */
    protected string $api_post_data;
    /**
     * Only used if $api_method_post == false
     * Defines the GET-Parameters to attach to the URL
     * @var array
     */
    protected array $api_get_parameters = [];
    protected array $api_header = [];

    public $cost = 0;


    abstract public function __construct(string $query);
    public static function fromProviderName(string $provider, string $query): Suggestions|null
    {
        switch ($provider) {
            case "serper":
                return new Serper($query);
            default:
                return null;
        }
    }

    /**
     * Parses the server response and returns an array of suggestions
     * @return array
     */
    abstract protected function parseResponse(string $response): void;

    public function fetch()
    {
        $api_url = $this->api_base;
        if ($this->api_method_post === false && sizeof($this->api_get_parameters) > 0) {
            $api_url .= "?" . http_build_query($this->api_get_parameters);
        }
        $ch = curl_init($api_url);
        curl_setopt_array($ch, [
            CURLOPT_USERAGENT => "MetaGer",
            CURLOPT_HTTPHEADER => $this->api_header,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 3,
            CURLOPT_POST => $this->api_method_post,
        ]);
        if ($this->api_method_post) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->api_post_data);
        }

        $response = curl_exec($ch);

        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) === $this->api_success_response_code) {
            try {
                return $this->parseResponse($response);
            } catch (Exception $e) {
                return [];
            }
        } else {
            return [];
        }
    }

    public function toJSON(): array
    {
        $result = [];
        $result[0] = $this->query;
        $result[1] = $this->suggestions;
        $result[2] = [];
        foreach ($result[1] as $suggestion) {
            $result[3][] = route("resultpage", ["eingabe" => $suggestion]);
        }
        return $result;
    }
}