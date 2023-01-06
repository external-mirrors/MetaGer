<?php

namespace App\Console\Commands;

use App\Http\Middleware\BrowserVerification;
use App\QueryLogger;
use Illuminate\Console\Command;

class AppendLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:gather';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieves all Log Entries from Redis and writes them to file';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->handleMGLogs();
    }

    private function handleMGLogs()
    {
        QueryLogger::flushLogs();
        BrowserVerification::FLUSH_LOGS();
    }
}