<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CacheGC extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:gc';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleans up every expired cache File';

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
        $cachedir = storage_path('framework/cache');

        $lockfile = $cachedir . "/cache.gc";

        if (file_exists($lockfile)) {
            return;
        } else {
            touch($lockfile);
        }

        try {
            foreach (new \DirectoryIterator($cachedir) as $fileInfo) {
                if ($fileInfo->isDot()) {
                    continue;
                }
                $file = $fileInfo->getPathname();
                $basename = basename($file);
                if (!is_dir($file) && $basename !== "cache.gc" && $basename !== ".gitignore") {
                    $fp = fopen($file, 'r');
                    $delete = false;
                    try {
                        $time = intval(fread($fp, 10));
                        if ($time < time()) {
                            $delete = true;
                        }
                    } finally {
                        fclose($fp);
                    }
                    if ($delete) {
                        unlink($file);
                    }
                } else if (is_dir($file)) {
                    // Delete Directory if empty
                    try {
                        rmdir($file);
                    } catch (\ErrorException $e) {

                    }
                }
            }
        } finally {
            unlink($lockfile);
        }

    }
}
