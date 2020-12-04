<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Log;
use Carbon;

class ServiceDesk extends Controller
{
    private $accessToken = null;
    private $apiUrl = "https://gitlab.metager.de/api/v4/projects";

    public function webhook(Request $request)
    {
        // Validate Token match
        $token = $request->header("X-Gitlab-Token");
        if ($token !== env("gitlab_webhook_token", "")) {
            Log::info("Webhook Action not taken. Token mismatch: " . $token);
            return;
        }
        $this->accessToken = env("gitlab_access_token", "");

        $event = json_decode($request->getContent(), true);

        if ($event["user"]["username"] === "support-bot" && $event["issue"]["author_id"] === 301 && $event["issue"]["state"] === "closed") {
            $closedAt = new Carbon($event["issue"]["closed_at"]);
            $createdAt = new Carbon($event["object_attributes"]["created_at"]);
            if ($createdAt->isAfter($closedAt)) {
                // Reopen the issues
                $getParameter = [
                    "state_event" => "reopen"
                ];
                $url = $this->apiUrl . "/2/issues/" . $event["issue"]["iid"] . "?" . http_build_query($getParameter);

                $response = file_get_contents($url, false, stream_context_create([
                    "http" => [
                        "method" => "PUT",
                        "header" => "Authorization: Bearer " . $this->accessToken . "\r\n"
                        ]
                ]));
                Log::info("reopened issue " . $event["issue"]["iid"]);
            }
        }

        return response("");
    }
}
