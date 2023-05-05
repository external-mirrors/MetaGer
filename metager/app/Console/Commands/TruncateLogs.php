<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TruncateLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:truncate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Truncates Logs that should only be kept for a day';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $log_files = [
            \storage_path("logs/metager/bv_fail.csv"),
            \storage_path("logs/metager/captcha_show.csv"),
            \storage_path("logs/metager/captcha_solve.csv"),
            \storage_path("logs/metager/csp_fail.csv"),
            \storage_path("logs/metager/yahoo_fail.csv"),
        ];
        foreach ($log_files as $log_file) {
            if (\file_exists($log_file) && \is_writable($log_file)) {
                $fp = fopen($log_file, "r+");
                try {
                    ftruncate($fp, 0);
                } finally {
                    fclose($fp);
                }
            }
        }

        return 0;
    }
}