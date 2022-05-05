<?php

namespace App\Console\Commands;

use DB;
use Illuminate\Console\Command;

class WaitDB extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wait:db';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Waits until default DB connection is available. ';

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
        $starttime = microtime(true);

        while (microtime(true) - $starttime < 60) {
            try {
                $connection = DB::getPdo();
                $this->line("Connection to database successfull");
                return 0;
            } catch (\Exception $e) {
                $this->error($e->getMessage());
                sleep(1);
            }
        }
        return 1;
    }
}
