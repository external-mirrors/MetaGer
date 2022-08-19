<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ScheduleWorker extends Command
{
    private $should_exit = false;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:work-mg';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Starts the schedule worker with correct signal handling and graceful shutdown.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        pcntl_signal(SIGQUIT, array(&$this, "onExit"));
        $this->info("Starting Scheduler");
        $this->call('schedule:run');
        do {
            sleep(60);
            $this->call('schedule:run');
        } while (!$this->should_exit);
        return 0;
    }

    public function onExit()
    {
        $this->info("Stopping Scheduler on SIGQUIT");
        $this->should_exit = true;
    }
}
