<?php

namespace App\Jobs;

use DateTime;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;
use PHP_IBAN\IBAN;

class CreateDirectDebit implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 600;

    /** @var string */
    private $fullname;
    /** @var IBAN */
    private $iban;
    /** @var float */
    private $amount;
    /** @var string */
    private $interval;

    /**
     * Create a new job instance.
     *
     * @param string $fullname
     * @param IBAN $iban
     * @param float $amount
     * @param string $interval
     *
     * @return void
     */
    public function __construct($fullname, $iban, $amount, $interval)
    {
        $this->fullname = $fullname;
        $this->iban = $iban;
        $this->amount = $amount;
        $this->interval = $interval;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $params = [
                "fullname" => $this->fullname,
                "iban" => $this->iban->MachineFormat(),
                "amount" => (string) $this->amount,
                "interval" => $this->interval
            ];
            $request = stream_context_create([
                "http" => [
                    "method" => "POST",
                    "header" => [
                        "Content-Type: application/x-www-form-urlencoded",
                        "Useragent: MetaGer: Donation Controller",
                        "Referer: https://metager.de",
                        "X-Requested-With: XMLHttpRequest",
                        "X-Civi-Auth: Bearer " . config("metager.metager.civicrm.apikey")
                    ],
                    "content" => http_build_query(['params' => json_encode($params)])
                ]
            ]);

            $response = json_decode(file_get_contents(config("metager.metager.civicrm.url") . "/Debit/metaGerDonation", false, $request));

            if (property_exists($response, "error_code")) {
                $errorstring = json_encode($response);
                LOG::error($errorstring);
                throw new Exception($errorstring);
            }
        } catch (Exception $e) {
            $this->release(600);
        }
    }

    /**
     * Determine the time at which the job should timeout.
     *
     * @return \DateTime
     */
    public function retryUntil()
    {
        return now()->addDays(1);
    }
}