<?php

namespace App\Models\Assistant;

use App\Models\Assistant\Assistant;
use Arr;
use Cache;
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

        $cache_key = "openai_response:" . md5(json_encode($request_data));
        $body = Cache::get($cache_key);

        if ($body === null) {
            $response = Http::withHeaders([
                "Authorization" => "Bearer " . config("metager.assistant.openai.api_key"),
                "Content-Type" => "application/json"
            ])->post(self::API_BASE . "/v1/responses", $request_data);
            $body = $response->json();
            Cache::put($cache_key, $body, now()->addHours(6)); // Cache for 6 hours
        }

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
        $id = Message::CREATE_ID(Arr::get($output, "id"));
        $role = Arr::get($output, "role") === "assistant" ? MessageRole::Agent : MessageRole::User;
        $type = Arr::get($output, "type");
        if ($type === "web_search_call") {
            $role = MessageRole::Agent; // Web search calls are always from the agent
        }
        $message = new Message($id, [], $role);
        switch ($type) {
            case "message":
                foreach (Arr::get($output, "content") as $content) {
                    $this->parseContent($message, $content);
                }
                break;
            case "web_search_call":
                $message->addContent(new MessageContentWebsearch(Arr::get($output, "action.query", "")));
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

            // Render the initial user message
            echo json_encode([
                "event" => "message.added",
                "message_id" => $this->messages[count($this->messages) - 1]->id,
                "message_data_html" => $this->messages[count($this->messages) - 1]->render()
            ]) . PHP_EOL;
            ob_flush();
            flush();

            $typing_message = new Message(
                Message::CREATE_ID(),
                [new MessageContentTyping()],
                MessageRole::Agent
            );
            $this->messages[] = $typing_message;
            // Render the initial user message
            echo json_encode([
                "event" => "message.added",
                "message_id" => $this->messages[count($this->messages) - 1]->id,
                "message_data_html" => $this->messages[count($this->messages) - 1]->render()
            ]) . PHP_EOL;
            ob_flush();
            flush();



            $request_data = $this->getRequestData();
            Arr::set($request_data, "stream", true);
            $cache_key = "openai_response:" . md5(json_encode($request_data));
            $body = Cache::get($cache_key);
            if ($body === null) {
                $response = Http::withOptions(["stream" => true])->withHeaders([
                    "Authorization" => "Bearer " . config("metager.assistant.openai.api_key"),
                    "Content-Type" => "application/json"
                ])->post(self::API_BASE . "/v1/responses", $request_data);
                $stream = $response->getBody();
                $body = "";
            } else {
                $stream = Utils::streamFor($body);
            }
            if ($stream->isSeekable())
                $stream->rewind();

            $event = null;
            $event_data = null;
            $last_run = now();
            while (!$stream->eof()) {
                usleep(max(0, 10000 - now()->diffInMicroseconds($last_run, true))); // Sleep for 10ms if last run was less than 10ms ago
                $last_run = now();
                $line = Utils::readLine($stream, 4096);
                $body .= $line . PHP_EOL;
                if (empty($line)) {
                    $event = null;
                    $event_data = null;
                }
                if (preg_match("/^event: (.*)/", $line, $matches)) {
                    $event = $matches[1];
                } elseif (preg_match("/^data: (.*)/", $line, $matches)) {
                    $event_data = json_decode($matches[1], true);
                    if ($event_data === null) {
                        $event = null;
                        Log::error("Parse Openai Stream: cannot decode {$matches[1]}");
                    }

                    // Remove typing animation
                    if ($typing_message !== null) {
                        foreach ($this->messages as $index => $message) {
                            if ($message->id === $typing_message->id) {
                                unset($this->messages[$index]);
                                // Re-index the array to maintain sequential IDs
                                $this->messages = array_values($this->messages);
                                echo json_encode([
                                    "event" => "message.removed",
                                    "message_id" => $message->id,
                                ]) . PHP_EOL;
                                ob_flush();
                                flush();
                                $typing_message = null;
                                break;
                            }
                        }
                    }

                    switch ($event) {
                        case "response.output_item.added":
                            $this->messages[] = $this->parseOutput(Arr::get($event_data, "item", []));
                            echo json_encode([
                                "event" => "message.added",
                                "message_id" => $this->messages[count($this->messages) - 1]->id,
                                "message_data_html" => $this->messages[count($this->messages) - 1]->render()
                            ]) . PHP_EOL;
                            break;
                        case "response.output_item.done":
                            $id = Message::CREATE_ID(Arr::get($event_data, "item.id"));
                            foreach ($this->messages as $index => $message) {
                                // Find the message with the matching ID
                                if ($message->id === $id) {
                                    $this->messages[$index] = $this->parseOutput(Arr::get($event_data, "item", []));
                                    break;
                                }
                            }
                            echo json_encode([
                                "event" => "message.updated",
                                "message_id" => $id,
                                "message_data_html" => $this->messages[count($this->messages) - 1]->render()
                            ]) . PHP_EOL;
                            break;
                        case "response.content_part.added":
                            $id = Message::CREATE_ID(Arr::get($event_data, "item_id"));
                            foreach ($this->messages as $message) {
                                // Find the message with the matching ID
                                if ($message->id === $id) {
                                    $this->parseContent($message, Arr::get($event_data, "part", []));
                                    echo json_encode([
                                        "event" => "message.content.added",
                                        "message_id" => $id,
                                        "message_data_html" => $message->render()
                                    ]) . PHP_EOL;
                                    break;
                                }
                            }
                            break;
                        case "response.output_text.delta":
                            foreach ($this->messages as $message) {
                                $id = Message::CREATE_ID(Arr::get($event_data, "item_id"));
                                if ($message->id === $id) {
                                    $content_index = Arr::get($event_data, "content_index", 0);
                                    $message->appendTextContent($content_index, Arr::get($event_data, "delta", ""));
                                    echo json_encode([
                                        "event" => "message.content.updated",
                                        "message_id" => $id,
                                        "message_data_html" => $message->render()
                                    ]) . PHP_EOL;
                                    break;
                                }
                            }
                            break;
                        default:
                            Log::warning("Unhandled event type: {$event}");
                            $event = null;
                            $event_data = null;
                    }

                    $event = null;
                    $event_data = null;
                    ob_flush();
                    flush();
                }
            }
            if (isset($response) && $response->getStatusCode() === 200) {
                Cache::put($cache_key, $body, now()->addHours(6)); // Cache for 6 hours
            }
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