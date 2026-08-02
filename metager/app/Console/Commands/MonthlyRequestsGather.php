<?php

namespace App\Console\Commands;

use App\Models\Configuration\SearchEngineRegistry;
use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class MonthlyRequestsGather extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'requests:gather';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends the gathered monthly requests from the redis to the central Database';

    private $values = [];

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
        $sumas = app(SearchEngineRegistry::class);

        $this->gatherLogs($sumas);

        foreach ($this->values as $name => $value) {
            $entry = DB::table('monthlyrequests')->where(['name' => $name])->first();
            $newCount = $value;
            if ($entry === null) {
                DB::table('monthlyrequests')->insert(['name' => $name, 'count' => $newCount]);
            } else {
                $newCount = $value + $entry->count;
                DB::table('monthlyrequests')->where(['name' => $name])->update(['count' => $newCount]);
            }
        }

        DB::disconnect('mysql');
    }

    private function gatherLogs($sumas)
    {
        foreach ($sumas->sumas as $sumaName => $suma) {
            if (!empty($suma->{"monthly-requests"})) {
                $monthlyLimit = $suma->{"monthly-requests"};
                $currentValue = Redis::get('monthlyRequests:' . $sumaName);
                Redis::del('monthlyRequests:' . $sumaName);
                if (empty($currentValue)) {
                    $currentValue = 0;
                }
                if (empty($this->values[$sumaName]) && $currentValue > 0) {
                    $this->values[$sumaName] = intval($currentValue);
                }
            }
        }
    }
}
