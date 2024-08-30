<?php

namespace App\Models\Logs;

use DB;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;

class LogsAbo
{
    const LOGS_ORDER_DAYS_BEFORE_NEXT = 28;
    public readonly string $interval;
    public readonly float $monthly_price;
    public readonly Carbon $created_at;
    public readonly Carbon $updated_at;
    public function __construct(string $email)
    {
        $abo = DB::table("logs_abo")->where("user_email", "=", $email)->first();
        if ($abo === null) {
            $this->interval = "never";
        } else {
            $this->interval = $abo->interval;
            $this->monthly_price = $abo->monthly_price;

            $this->created_at = Carbon::createFromFormat("Y-m-d H:i:s", $abo->created_at, "UTC");
            $this->updated_at = Carbon::createFromFormat("Y-m-d H:i:s", $abo->updated_at, "UTC");
        }
    }

    public function getLastInvoiceDate(): Carbon|null
    {
        if ($this->interval === "never")
            return null;

        $email = app(LogsAccountProvider::class)->client->contact->email;

        $latest_order = DB::table("logs_order")->where('user_email', $email)->whereNotNull("invoice_id")->orderBy("to", "desc")->first();
        if (is_null($latest_order)) {
            return null;
        } else {
            if (!is_null($latest_order->updated_at))
                return new Carbon($latest_order->updated_at);
            else
                return null;
        }
    }

    public function getNextInvoiceDate(): Carbon|null
    {
        if ($this->interval === "never") {
            return null;
        }
        $email = app(LogsAccountProvider::class)->client->contact->email;

        $latest_order_for_abo = DB::table("logs_abo", "la")
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
            ->where('la.user_email', '=', $email)
            ->first();

        $latest_order_date = new Carbon($latest_order_for_abo->latest_order_to, "UTC");
        $this_month = Carbon::now("UTC")->startOfMonth();
        $next_invoice = $latest_order_date->startOfMonth()->addMonth();
        if (is_null($latest_order_for_abo->latest_order_to) || $next_invoice->isBefore($this_month)) {
            $next_invoice = clone $this_month;
        }

        return $next_invoice;
    }

    public function getIntervalPrice()
    {
        $price = match ($this->interval) {
            "monthly" => $this->monthly_price,
            "quarterly" => $this->monthly_price * 3,
            "six-monthly" => $this->monthly_price * 6,
            "annual" => $this->monthly_price * 12,
        };
        $price *= app(\App\Models\Logs\LogsAccountProvider::class)->client->discount / 100;
        return $price;
    }

    public function update(string $interval)
    {
        $email = app(LogsAccountProvider::class)->client->contact->email;
        if (!in_array($interval, ["never", "monthly", "quarterly", "six-monthly", "annual"])) {
            throw new \InvalidArgumentException("Argument interval ($interval) is not a valid value.");
        }
        // Check if there already is a abo
        $abo = DB::table("logs_abo")->where("user_email", "=", $email)->first();
        if ($abo === null && $interval !== "never") {
            DB::table("logs_abo")->insert([
                "user_email" => $email,
                "interval" => $interval,
                "monthly_price" => config("metager.logs.monthly_cost"),
                "created_at" => now("UTC"),
                "updated_at" => now("UTC"),
            ]);
            $current_nda = DB::table("logs_nda")->where("user_email", $email)->first();
            if (is_null($current_nda)) {
                /*DB::table("logs_nda")->insert([
                    "user_email" => $email,
                    "nda" => file_get_contents(storage_path("app/public/logs_nda.pdf")),
                    "created_at" => now("UTC"),
                    "updated_at" => now("UTC"),
                ]);*/
            } else {
                /*DB::table("logs_nda")->where("user_email", $email)->update([
                    "nda" => file_get_contents(storage_path("app/public/logs_nda.pdf")),
                    "updated_at" => now("UTC"),
                ]);*/
            }

        } elseif ($interval === "never") {
            DB::table("logs_abo")->where("user_email", "=", $email)->delete();
        } else {
            DB::table("logs_abo")->where("user_email", "=", $email)->update([
                "interval" => $interval,
                "monthly_price" => config("metager.logs.monthly_cost"),
                "updated_at" => now("UTC"),
            ]);
            /*DB::table("logs_nda")->where("user_email", "=", $email)->update([
                "nda" => file_get_contents(storage_path("app/public/logs_nda.pdf")),
                "updated_at" => now("UTC"),
            ]);*/
        }
    }
}