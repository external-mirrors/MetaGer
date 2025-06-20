<?php

namespace App\Jobs;

use App\Localization;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MembershipMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 30;

    private string $to_name;
    private string $to_email;
    private string $from_name = "SUMA-EV";
    private string $from_email = "verein@metager.de";
    /** @var string $group */
    private $group;
    /** @var string $name */
    private $name;
    /** @var string $email */
    private $email;
    /** @var string $subject */
    private $subject;
    /** @var string $message */
    private $message;
    /** @var array $attachments */
    private $attachments = [];
    /** @var string $contentType */
    private $contentType;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $to_name, string $to_email, string $subject, string $message, array $attachments = [], string $contentType = "text/html")
    {
        $this->to_name = $to_name;
        $this->to_email = $to_email;
        $this->group = "Mitglieder";
        $this->subject = $subject;
        $this->message = $message;
        $this->attachments = $attachments;
        $this->contentType = $contentType;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $message = [
            "type" => "email",
            "sender" => "Agent",
            "from" => sprintf("%s <%s>", $this->from_name, $this->from_email),
            "to" => sprintf("%s <%s>", $this->to_name, $this->to_email),
            "subject" => $this->subject,
            "body" => $this->message,
            "content_type" => $this->contentType,
            "internal" => false,
            "attachments" => $this->attachments,
        ];

        $article = [
            "title" => $this->subject,
            "group" => $this->group,
            "customer_id" => sprintf("guess:%s", $this->to_email),
            "preferences" => ["channel_id" => 3],
            "article" => $message,
        ];

        $context = stream_context_create([
            "http" => [
                "method" => "POST",
                "header" => [
                    "Content-Type: application/json",
                    "Authorization: Token token=" . config("metager.metager.ticketsystem.apikey")
                ],
                "content" => json_encode($article),
            ],
        ]);
        $url = config("metager.metager.ticketsystem.url") . "/api/v1/tickets";
        file_get_contents($url, false, $context); // Will throw an error when statuscode is 4xx or 5xx
    }
}