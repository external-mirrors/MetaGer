<?php

namespace App\Jobs;

use App\Localization;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ContactMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 30;

    /** @var string $to */
    private $to;
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
     * @param string $to
     * @param string $group
     * @param string $email
     * @param string $subject
     * @param string $message
     * @param array $message
     * @param string $contentType
     *
     * @return void
     */
    public function __construct($to, $group, $name, $email, $subject, $message, $attachments = [], $contentType = "text/html")
    {
        $this->to = $to;
        $this->group = $group;
        $this->name = $name;
        $this->email = $email;
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
        $context = stream_context_create([
            "http" => [
                "method" => "POST",
                "header" => [
                    "Content-Type: application/json",
                    "Authorization: Token " . config("metager.metager.ticketsystem.apikey")
                ],
                "content" => json_encode([
                    "title" => $this->subject,
                    "group" => $this->group,
                    "customer_id" => "guess:" . $this->email,
                    "preferences" => ["channel_id" => 3],
                    "article" => [
                        "type" => "email",
                        "sender" => "Customer",
                        "from" => sprintf('%s <%s>', $this->name, $this->email),
                        "reply_to" => $this->email,
                        "to" => $this->to,
                        "subject" => $this->subject,
                        "body" => $this->message,
                        "content_type" => $this->contentType,
                        "internal" => false,
                        "attachments" => $this->attachments
                    ]
                ])
            ]
        ]);
        $url = config("metager.metager.ticketsystem.url") . "/api/v1/tickets";
        file_get_contents($url, false, $context); // Will throw an error when statuscode is 4xx or 5xx
    }
}