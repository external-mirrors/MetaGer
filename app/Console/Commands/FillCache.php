<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Cache;

class FillCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:fill';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * @return int
     */
    public function handle()
    {
        $writtenBytes = 0;
        $lastStatus = microtime(true);
        while (true) {
            $key = $this->getRandomString(rand(50, 100));
            $value = $this->getRandomString(rand(1024, 1024*1000));
            Cache::put($key, $value);
            $writtenBytes += mb_strlen(Cache::get($key));
            if(microtime(true) - $lastStatus > 1){
                echo "Stored " . $this->formatBytes($writtenBytes) . "." . PHP_EOL;
                $lastStatus = microtime(true);
            }
            usleep(5 * 1000);
        }
        return 0;
    }

    function getRandomString($n) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
      
        for ($i = 0; $i < $n; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }
      
        return $randomString;
    }

    function formatBytes($bytes, $precision = 2) { 
        $units = array('B', 'KiB', 'MiB', 'GiB', 'TiB'); 
    
        $bytes = max($bytes, 0); 
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
        $pow = min($pow, count($units) - 1); 
    
        // Uncomment one of the following alternatives
        // $bytes /= pow(1024, $pow);
        $bytes /= (1 << (10 * $pow)); 
    
        return round($bytes, $precision) . ' ' . $units[$pow]; 
    } 
}
