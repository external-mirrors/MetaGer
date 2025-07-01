<?php

namespace App\Models\Assistant;

use App\Models\Assistant\Assistant;
use Arr;
use GuzzleHttp\Psr7\Utils;
use Http;
use Illuminate\Support\Facades\Redis;
use Log;
use Symfony\Component\HttpFoundation\StreamedResponse;


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

    public function process(string $message, bool $sync = true): StreamedResponse|null
    {
        parent::process($message);
        $sync = true; // ToDo remove
        if ($sync) {
            $this->createSyncResponse();
        } else {
            return $this->createStreamingJsonResponse($message);
        }
        return null;
    }

    /**
     * Creates a synchronous response from AI 
     * model. Those requests are generally very slow.
     * 
     * @return void
     */
    private function createSyncResponse()
    {
        $request_data = $this->getRequestData();

        $response = Http::withHeaders([
            "Authorization" => "Bearer " . config("metager.assistant.openai.api_key"),
            "Content-Type" => "application/json"
        ])->post(self::API_BASE . "/v1/responses", $request_data);

        $body = $response->json();
        foreach (Arr::get($body, "output", []) as $output) {
            $this->messages[] = $this->parseOutput($output);
        }
    }

    /**
     * Parses the output from the OpenAI API response and returns a Message object.
     *
     * @param array $output The output data from the OpenAI API.
     * @return Message The parsed message object.
     */
    private function parseOutput(array $output): Message
    {
        $id = hash_hmac("sha256", Arr::get($output, "id", uniqid("msg_")), config("app.key"));
        $role = Arr::get($output, "role") === "assistant" ? MessageRole::Agent : MessageRole::User;
        $message = new Message($id, [], $role);
        switch (Arr::get($output, "type")) {
            case "message":
                foreach (Arr::get($output, "content") as $content) {
                    $this->parseContent($message, $content);
                }
                break;
        }
        return $message;
    }

    /**
     * Parses the content of a message and returns the appropriate MessageContent object.
     *
     * @param Message $message The message to which the content belongs.
     * @param array $content The content data to parse.
     * @return MessageContent The parsed content object.
     */
    private function parseContent(Message &$message, array $content): MessageContent|null
    {
        $content_type = Arr::get($content, "type");
        switch ($content_type) {
            case "output_text":
                $message->addContent(new MessageContentText(Arr::get($content, "text")));
            // Add more content types as needed
            default:
                Log::warning("Unknown content type: " . $content_type);
                return null;
        }
    }

    private function createStreamingJsonResponse(string $message): StreamedResponse
    {
        return response()->stream(function (): void {
            $request_data = $this->getRequestData();
            Arr::set($request_data, "stream", true);

            $response = Http::withOptions(["stream" => true])->withHeaders([
                "Authorization" => "Bearer " . config("metager.assistant.openai.api_key"),
                "Content-Type" => "application/json"
            ])->post(self::API_BASE . "/v1/responses", $request_data);

            $events = [];
            $event = null;
            $event_data = null;
            while (!$response->getBody()->eof()) {
                $line = Utils::readLine($response->getBody(), 4096);
                if (empty($line)) {
                    $event = null;
                    $event_data = null;
                }
                if (preg_match("/^event: (.*)/", $line, $matches)) {
                    $event = $matches[1];
                } elseif (preg_match("/^data: (.*)/", $line, $matches)) {
                    $event_data = json_decode($matches[1]);
                    if ($event_data === null) {
                        $event = null;
                        Log::error("Parse Openai Stream: cannot decode {$matches[1]}");
                    }
                    $events[] = [
                        "event" => $event,
                        "data" => $event_data,
                    ];
                    $event = null;
                    $event_data = null;
                }
            }

            ob_flush();
            flush();
        }, 200, ['X-Accel-Buffering' => 'no']);
    }

    private function getRequestData(): array
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
            foreach ($history_message->getContents() as $content) {
                if ($content instanceof MessageContentText) {
                    $request_data["input"][] = [
                        "role" => match ($history_message->role) {
                            MessageRole::Agent => "assistant",
                            MessageRole::User => "user"
                        },
                        "content" => $content->render()
                    ];
                }
            }
        }
        return $request_data;
    }
}