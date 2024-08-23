<?php

namespace App\Models\Logs;

use DB;
use Carbon\Carbon;

class LogsAbo
{
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
        return null;
    }

    public function getNextInvoiceDate(): Carbon|null
    {
        if ($this->interval === "never") {
            return null;
        }
        $email = app(LogsAccountProvider::class)->client->contact->email;
        // Check if there is an order for this month
        $order_db = DB::table("logs_order")->where("user_email", $email)->orderBy("to", "desc")->first();
        if (!is_null($order_db)) {
            $next_invoice = Carbon::createFromFormat("Y-m-d H:i:s", $order_db->to, "UTC");
            $next_invoice->addSeconds(1);
            return $next_invoice;
        } else {
            // There is no order for this month - next invoice will be now
            return now("UTC");
        }
    }

    public function getIntervalPrice()
    {
        return match ($this->interval) {
            "monthly" => $this->monthly_price,
            "quarterly" => $this->monthly_price * 3,
            "six-monthly" => $this->monthly_price * 6,
            "annual" => $this->monthly_price * 12,
        };
    }

    public function update(string $interval)
    {
        $email = app(LogsAccountProvider::class)->client->contact->email;
        if (!in_array($interval, ["monthly", "quarterly", "six-monthly", "annual"])) {
            throw new \InvalidArgumentException("Argument interval ($interval) is not a valid value.");
        }
        // Check if there already is a abo
        $abo = DB::table("logs_abo")->where("user_email", "=", $email)->first();
        if ($abo === null) {
            DB::table("logs_abo")->insert([
                "user_email" => $email,
                "interval" => $interval,
                "monthly_price" => config("metager.logs.monthly_cost"),
                "created_at" => now("UTC"),
                "updated_at" => now("UTC"),
            ]);
            $current_nda = DB::table("logs_nda")->where("user_email", $email)->first();
            if (is_null($current_nda)) {
                DB::table("logs_nda")->insert([
                    "user_email" => $email,
                    "nda" => file_get_contents(storage_path("app/logs_nda.pdf")),
                    "created_at" => now("UTC"),
                    "updated_at" => now("UTC"),
                ]);
            } else {
                DB::table("logs_nda")->where("user_email", $email)->update([
                    "nda" => file_get_contents(storage_path("app/logs_nda.pdf")),
                    "updated_at" => now("UTC"),
                ]);
            }

        } elseif ($interval === "never") {
            DB::table("logs_abo")->where("user_email", "=", $email)->delete();
        } else {
            DB::table("logs_abo")->where("user_email", "=", $email)->update([
                "interval" => $interval,
                "monthly_price" => config("metager.logs.monthly_cost"),
                "updated_at" => now("UTC"),
            ]);
            DB::table("logs_nda")->where("user_email", "=", $email)->update([
                "nda" => file_get_contents(storage_path("app/logs_nda.pdf")),
                "updated_at" => now("UTC"),
            ]);
        }
    }
}