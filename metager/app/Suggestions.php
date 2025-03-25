<?php

namespace App;

use Composer\ClassMapGenerator\ClassMapGenerator;
use Exception;
use Illuminate\Support\Facades\Redis;

/**
 * Base class for all search suggestions implementations
 * by different providers
 */
abstract class Suggestions
{
    public const NAME = "";
    public const DISABLED = false;
    protected string $query;
    protected array $suggestions = [];
    /** Should the request be made as POST request. GET method is used otherwise */
    protected bool $api_method_post = false;
    protected int $api_success_response_code = 200;
    protected string $api_base;
    protected string $api_useragent = "MetaGer";

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

    public const COST = 0;


    abstract public function __construct(string $query);
    public static function fromProviderName(string $provider, string $query): Suggestions|null
    {
        foreach (self::GET_AVAILABLE_PROVIDERS(true) as $name => $provider_class) {
            if ($provider === $name) {
                return new $provider_class($query);
            }
        }
        return null;
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

        $hash = sha1($api_url . microtime(true));

        $mission = [
            "resulthash" => $hash,
            "url" => $api_url,
            "useragent" => $this->api_useragent,
            "cacheDuration" => 0,   // We'll cache seperately
            "headers" => $this->api_header,
            "name" => "Suggestions: " . self::NAME,
            "curlopts" => [
                CURLOPT_POST => $this->api_method_post,
            ]
        ];

        if ($this->api_method_post) {
            $mission["curlopts"][CURLOPT_POST] = $this->api_method_post;
            $mission["curlopts"][CURLOPT_POSTFIELDS] = $this->api_post_data;
        }

        $mission = json_encode($mission);
        Redis::rpush(\App\MetaGer::FETCHQUEUE_KEY, $mission);

        $results = Redis::brpop($hash, 10);
        if (!is_array($results))
            return;
        $results = $results[1];
        $results = json_decode($results, true);
        $info = $results["info"];
        $body = $results["body"];
        if ($info["http_code"] === $this->api_success_response_code) {
            try {
                return $this->parseResponse($body);
            } catch (Exception $e) {
                return [];
            }
        } else {
            return [];
        }
    }

    public static function GET_AVAILABLE_PROVIDERS(bool $include_disabled = false): array
    {
        $providers = [];
        $provider_path_map = ClassMapGenerator::createMap(app_path("Models/Suggestions"));
        foreach ($provider_path_map as $class => $path) {
            if (!defined("$class::NAME") || !defined("$class::DISABLED"))
                continue;
            if (!$include_disabled && $class::DISABLED === true)
                continue;
            $providers[$class::NAME] = $class;
        }
        return $providers;
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