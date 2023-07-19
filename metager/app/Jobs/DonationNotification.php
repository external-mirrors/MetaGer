<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DonationNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 30;

    /** @var float */
    private $amount;
    /** @var string */
    private $frequency;
    /** @var string */
    private $paymentMethod;
    /**
     * Create a new job instance.
     *
     * @param float $amount
     * @param string $frequency
     * @param string $paymentMethod
     *
     * @return void
     */
    public function __construct($amount, $frequency, $paymentMethod)
    {
        $this->amount = $amount;
        $this->frequency = $frequency;
        $this->paymentMethod = $paymentMethod;
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
                    "ticket_id" => config("metager.metager.ticketsystem.donation_ticket_id"),
                    "subject" => "Neue Spende",
                    "body" => "Betrag: {$this->amount}€\nInterval: {$this->frequency}\nZahlungsart: {$this->paymentMethod}",
                    "content_type" => "text/plain",
                    "type" => "web",
                    "internal" => false,
                    "sender" => "Agent",
                    "time_unit" => "15"
                ])
            ]
        ]);
        $url = config("metager.metager.ticketsystem.url") . "/api/v1/ticket_articles";
        file_get_contents($url, false, $context); // Will throw an error when statuscode is 4xx or 5xx
    }
}