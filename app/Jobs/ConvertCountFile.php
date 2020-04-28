<?php

namespace App\Jobs;

use Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;

class ConvertCountFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $files;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($files)
    {
        $this->files = $files;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $result = [
            "insgesamt" => [
                "all" => 0,
            ],
        ];
        $fh = false;
        $fullRound = false;
        $error = false;
        try {
            $fh = fopen($this->files["logFile"], "r");
            $currentLogTime = Carbon::now()->hour(0)->minute(0)->second(0)->addMinutes(5);
            while ($fh !== false && ($line = fgets($fh)) !== false) {
                $logTime = [];
                $interface = "";
                // i.e. [Wed Apr 17 00:00:01] ref=https://metager.de/ time=0.51 serv=web interface=de
                if (preg_match('/(\d{2}:\d{2}:\d{2}).*?\sinterface=(\S+)/', $line, $matches)) {
                    // Create Date Object
                    $logTime = $matches[1];
                    $interface = $matches[2];
                } else {
                    continue;
                }
                $thatTime = \DateTime::createFromFormat('H:i:s', $logTime);
                $thatTime->sub(new \DateInterval("PT" . ($thatTime->format('i') % 5) . "M"));

                if(empty($result["time"][$thatTime->format('H:i')])){
                    $result["time"][$thatTime->format('H:i')] = [
                        "insgesamt" => [
                            "all" => 0,
                        ],
                    ];
                }
                if(empty($result["time"][$thatTime->format('H:i')]["all"])){
                    $result["time"][$thatTime->format('H:i')]["all"] = 1;
                }else{
                    $result["time"][$thatTime->format('H:i')]["all"]++;
                }
                if (!empty($interface)) {
                    if(empty($result["time"][$thatTime->format('H:i')][$interface])){
                        $result["time"][$thatTime->format('H:i')][$interface] = 1;
                    }else{
                        $result["time"][$thatTime->format('H:i')][$interface]++;
                    }
                }
                // Update the total statistics
                if (empty($result["insgesamt"]["all"])) {
                    $result["insgesamt"]["all"] = 1;
                } else {
                    $result["insgesamt"]["all"]++;
                }
                if (!empty($interface)) {
                    if (empty($result["insgesamt"][$interface])) {
                        $result["insgesamt"][$interface] = 1;
                    } else {
                        $result["insgesamt"][$interface]++;
                    }
                }
            }
        } catch (\ErrorException $e) {
            $error = true;
        } finally {
            if ($fh !== false) {
                fclose($fh);
            }

            if (!$error) {
                $oldUmask = umask(0);
                // Write the result to a File
                if (!file_exists($this->files["countPath"])) {
                    mkdir($this->files["countPath"], 0777, true);
                }
                file_put_contents($this->files["countFile"], json_encode($result, JSON_PRETTY_PRINT));
                umask($oldUmask);
            }

            Redis::del(md5($this->files["countFile"]));
        }
    }
}
