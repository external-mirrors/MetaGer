<?php

namespace App\Models\Assistant;

use App\Models\Assistant\Assistant;
use Arr;
use Illuminate\Support\Facades\Redis;


class Openai extends Assistant
{
    const API_BASE = "https://api.openai.com";

    public function __construct()
    {
        $this->capabilities[] = AssistantCapability::CHAT;
        $this->capabilities[] = AssistantCapability::SEARCH;

        $this->available_models = [
            "gpt-4.1" => [
                "cost" => new OpenaiCost(per_input_token: 2 / 1000000, per_cached_input_token: 0.5 / 1000000, per_output_token: 8 / 1000000),
            ],
            "gpt-3.5-turbo" => [
                "cost" => new OpenaiCost(per_input_token: 0.5 / 1000000, per_cached_input_token: null, per_output_token: 1.5 / 1000000),
            ]
        ];
        $this->selected_model = "gpt-4.1";
    }

    public function process(string $message)
    {
        parent::process($message);
        $this->createResponse($message);
    }

    private function createResponse($message)
    {
        $request_data = [
            "model" => $this->selected_model,
            "store" => false,
            "stream" => false,
            "tools" => [["type" => "web_search_preview"]],
            "tool_choice" => "auto",
            "input" => [

            ]
        ];

        foreach ($this->getMessages() as $history_message) {
            $request_data["input"][] = [
                "role" => match ($history_message->type) {
                    MessageType::Agent => "assistant",
                    MessageType::User => "user"
                },
                "content" => $history_message->message
            ];
        }

        $resulthash = sha1("assistant:openai" . json_encode($request_data));
        $mission = [
            "resulthash" => $resulthash,
            "url" => self::API_BASE . "/v1/responses",
            "useragent" => "MetaGer",
            "cacheDuration" => 60 * 60,
            "headers" => [
                "Authorization" => "Bearer " . config("metager.assistant.openai.api_key"),
                "Content-Type" => "application/json"
            ],
            "name" => "PayPal",
            "curlopts" => [
                CURLOPT_TIMEOUT => 60,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($request_data)
            ]
        ];

        $mission = json_encode($mission);
        Redis::rpush(\App\MetaGer::FETCHQUEUE_KEY, $mission);
        $results = Redis::brpop($resulthash, 10);
        if (!is_array($results))
            return;
        $results = json_decode($results[1], true);
        if (!in_array($results["info"]["http_code"], [200])) {
            $this->messages[] = new Message(__('assistant.response.error'), MessageType::Agent);
            return;
        }

        $body = json_decode($results["body"], true);
        foreach (Arr::get($body, "output", []) as $output) {
            $role = Arr::get($output, "role") === "assistant" ? MessageType::Agent : MessageType::User;
            switch (Arr::get($output, "type")) {
                case "message":
                    foreach (Arr::get($output, "content") as $content) {
                        switch (Arr::get($content, "type")) {
                            case "output_text":
                                $this->messages[] = new Message(Arr::get($content, "text"), $role);
                                break;
                        }
                    }
                    break;
            }

        }
    }
}