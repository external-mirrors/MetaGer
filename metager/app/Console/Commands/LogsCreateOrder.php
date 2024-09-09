<?php

namespace App\Console\Commands;

use App\Models\Logs\LogsAbo;
use Carbon;
use Illuminate\Console\Command;
use DB;
use Illuminate\Database\Query\JoinClause;

class LogsCreateOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:create-order';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Goes through all abos and creates due orders for any.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Fetch all outstanding Orders from the database
        /**
         * SELECT la.user_email, la."interval", la.monthly_price, lo.id as "latest_order_id", lo."from" as "latest_order_from", lo."to" as "latest_order_to" 
         * FROM logs_abo la 
         * LEFT JOIN logs_order lo ON (la.user_email = lo.user_email)
         * LEFT OUTER JOIN logs_order lo2 ON 
         * (la.user_email = lo2.user_email 
         *  AND (lo."to" < lo2."to"
         *  OR (lo."to" = lo2."to" AND lo.id < lo2.id)))
         * WHERE lo2.user_email IS NULL
         *  AND latest_order_to <= $target_date;
         */
        $all_before = Carbon::createMidnightDate(null, null, null, "UTC");
        $all_before->addDays(LogsAbo::LOGS_ORDER_DAYS_BEFORE_NEXT);

        $abos = DB::table("logs_abo", "la")
            ->select(['la.user_email', 'la.interval', 'la.monthly_price', 'lo.id AS latest_order_id', 'lo.from AS latest_order_from', 'lo.to AS latest_order_to'])
            ->leftJoin('logs_order as lo', 'la.user_email', "=", "lo.user_email")
            ->leftJoin('logs_order as lo2', function (JoinClause $join) {
                $join->on('la.user_email', '=', 'lo2.user_email')
                    ->on(function ($join) {
                        $join->on('lo.to', '<', 'lo2.to')
                            ->orOn(function ($join) {
                                $join->on('lo.to', '=', 'lo2.to')
                                    ->on('lo.id', '<', 'lo2.id');
                            });
                    });
            })
            ->whereNull('lo2.user_email')
            ->where('lo.to', '<=', $all_before->format("Y-m-d"))
            ->orWhereNull("lo.to")
            ->get();


        foreach ($abos as $abo) {
            $this_month = Carbon::now("UTC")->startOfMonth();

            $next_order_from = new Carbon($abo->latest_order_to, "UTC");
            $next_order_from->startOfMonth()->addMonths(1);

            if (is_null($abo->latest_order_to) || $next_order_from->isBefore($this_month)) {
                $next_order_from = clone $this_month;
            }
            $months_to_add = match ($abo->interval) {
                "monthly" => 1,
                "quarterly" => 3,
                "six-monthly" => 6,
                "annual" => 12
            };
            $next_order_to = clone $next_order_from;
            $next_order_to->addMonths($months_to_add - 1)->endOfMonth();

            $discount = DB::table("logs_user")->where("email", $abo->user_email)->first()->discount;

            DB::table("logs_order")->insert([
                "user_email" => $abo->user_email,
                "from" => $next_order_from,
                "to" => $next_order_to,
                "price" => $abo->monthly_price * $months_to_add,
                "discount" => $discount,
                "created_at" => now("UTC"),
                "updated_at" => now("UTC")
            ]);
        }
        return 0;
    }
}
