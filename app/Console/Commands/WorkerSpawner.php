<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class WorkerSpawner extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'worker:spawner';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command makes sure that enough worker processes are spawned';

    protected $shouldRun = true;
    protected $processes = [];

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
        pcntl_async_signals(true);
        pcntl_signal(SIGINT, [$this, "sig_handler"]);
        pcntl_signal(SIGTERM, [$this, "sig_handler"]);
        pcntl_signal(SIGHUP, [$this, "sig_handler"]);

        try {
            $counter = 0;
            while ($this->shouldRun) {
                $counter++;
                $counter = $counter % 10;
                $length = Redis::llen("queues:default");
                if ($length > 0) {
                    while (true) {
                        usleep(50 * 1000);
                        if (Redis::llen("queues:default") !== $length) {
                            $length = Redis::llen("queues:default");
                        } else {
                            break;
                        }
                    }
                    $jobs = Redis::lrange("queues:default", 0, -1);
                    $length = sizeof($jobs) + 5;
                    $ids = $this->getJobIds($jobs);
                    for ($i = 0; $i <= $length; $i++) {
                        $this->processes[] = $this->spawnWorker();
                    }
                    while (sizeof($ids) > 0) {
                        $jobs = Redis::lrange("queues:default", 0, -1);
                        $newIds = $this->getJobIds($jobs);
                        foreach ($ids as $index => $id) {
                            foreach ($newIds as $newId) {
                                if ($id === $newId) {
                                    continue 2;
                                }
                            }
                            unset($ids[$index]);
                            break;
                        }
                    }
                } else {
                    usleep(100 * 1000); // Sleep for 100ms
                }
                if ($counter === 0) {
                    $newProcs = [];
                    foreach ($this->processes as $process) {
                        $infos = proc_get_status($process["process"]);
                        if (!$infos["running"]) {
                            fclose($process["pipes"][1]);
                            proc_close($process["process"]);
                        } else {
                            $newProcs[] = $process;
                        }
                    }
                    $this->processes = $newProcs;
                }
            }
        } finally {
            foreach ($this->processes as $process) {
                fclose($process["pipes"][1]);
                proc_close($process["process"]);
            }
        }
    }

    private function getJobIds($jobs)
    {
        $result = [];
        foreach ($jobs as $job) {
            $result[] = json_decode($job, true)["id"];
        }
        return $result;
    }

    private function sig_handler($sig)
    {
        $this->shouldRun = false;
        echo ("Terminating Process\n");
    }

    private function spawnWorker()
    {
        $descriptorspec = array(
            0 => array("pipe", "r"), // STDIN ist eine Pipe, von der das Child liest
            1 => array("pipe", "w"), // STDOUT ist eine Pipe, in die das Child schreibt
            2 => array("file", "/tmp/worker-error.txt", "a"), // STDERR ist eine Datei,
            // in die geschrieben wird
        );
        $cwd = getcwd();
        $env = array();

        $process = proc_open('php artisan queue:work --stop-when-empty --sleep=1', $descriptorspec, $pipes, $cwd, $env);
        if (is_resource($process)) {
            fclose($pipes[0]);
            \stream_set_blocking($pipes[1], 0);
            return [
                "process" => $process,
                "pipes" => $pipes,
                "working" => false,
            ];
        }

    }
}
