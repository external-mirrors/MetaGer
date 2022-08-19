<?php

namespace App\Console\Commands;

use ErrorException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class FPMGracefulStop extends Command
{
    const REDIS_FPM_STOPPED_KEY = "fpm_stopped";
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fpm:graceful-stop';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will wait until there are no active fpm processes anymore and then return.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        do {
            $active_fpm_processes = $this->getActiveProcessCount();
            usleep(500 * 1000); // Check every half second
        } while ($active_fpm_processes === null || $active_fpm_processes > 1);

        $this->info("Only one FPM process left. Ready to stop fpm...");
        // The Request fetcher won't stop before FPM is not stopped with processing requests
        // because there could be last jobs flying in
        // This Redis Value will tell him that it's good to stop
        Redis::set(self::REDIS_FPM_STOPPED_KEY, "true");
        $this->info("Set Redis Key");
        return 0;
    }

    /**
     * Returns the number of active fpm processes
     * 
     * @return int|null Number of active fpm processes; Causes one active process by itself; Null if an error occured
     */
    private function getActiveProcessCount()
    {
        $url = route("fpm-status");

        $auth = \base64_encode(config("metager.metager.admin.user") . ":" . config("metager.metager.admin.password"));
        $context = \stream_context_create([
            "http" => [
                "header" => "Authorization: Basic $auth",
            ],
        ]);
        try {
            $fpm_info = \file_get_contents($url, false, $context);
        } catch (ErrorException $e) {
            // Webserver could not be reached. Probably already shut down so there are no active connections anyways
            return 0;
        }
        if ($fpm_info !== false) {
            $fpm_info = \json_decode($fpm_info);
        } else {
            return null;
        }

        if (!\is_object($fpm_info) || !\property_exists($fpm_info, "active-processes")) {
            return null;
        }

        return $fpm_info->{"active-processes"};
    }
}
