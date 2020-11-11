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
        $token = $request->header("X-Gitlab-Token HTTP");
        if ($token !== env("gitlab_webhook_token", "")) {
            Log::info("Webhook Action not taken. Token mismatch: " . $token);
            return;
        }
        $this->accessToken = env("gitlab_access_token", "");
        $issues = [];

        $getParameter = [
            "confidential" => "true",
            "author_username" => "support-bot",
            "state" => "closed"
        ];
        // Gather all relevant issues
        // Issue:
        // - closed
        // - from support-bot
        // - confidential
        // - updated after it was closed
        $currentUrl = $this->apiUrl . "/2/issues?" . http_build_query($getParameter);
        while (true) {
            if ($currentUrl === null) {
                break;
            }
            $response = file_get_contents($currentUrl, false, stream_context_create([
                "http" => [
                    "header" => "Authorization: Bearer " . $this->accessToken . "\r\n"
                    ]
            ]));

            $currentUrl = null;
            $response = \json_decode($response, true);
            if ($response === null) {
                break;
            }

            foreach ($response as $issue) {
                $updatedAt = new Carbon($issue["updated_at"]);
                $closedAt = new Carbon($issue["closed_at"]);
                if ($updatedAt->isAfter($closedAt)) {
                    $issues[] = $issue;
                }
            }

            // Check if there is a next page
            foreach ($http_response_header as $header) {
                if (stripos($header, "Link") === 0) {
                    if (preg_match("/<([^>]+)>; rel=\"next\"/", $header, $matches) && !empty($matches[1])) {
                        $currentUrl = $matches[1];
                        break;
                    }
                    $matches = null;
                }
            }
        }

        if (sizeof($issues) === 0) {
            Log::info("Webhook: No closed and updated issues found");
        }

        foreach ($issues as $issue) {
            // Reopen the issues
            $getParameter = [
                "state_event" => "reopen"
            ];
            $url = $this->apiUrl . "/2/issues/" . $issue["iid"] . "?" . http_build_query($getParameter);

            $response = file_get_contents($url, false, stream_context_create([
                "http" => [
                    "method" => "PUT",
                    "header" => "Authorization: Bearer " . $this->accessToken . "\r\n"
                    ]
            ]));
            Log::info("reopened issue " . $issue["iid"]);
        }

        return response("");
    }
}
