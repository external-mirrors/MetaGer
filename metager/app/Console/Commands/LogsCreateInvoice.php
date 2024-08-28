<?php

namespace App\Console\Commands;

use App\Models\Logs\LogsAccountProvider;
use Illuminate\Console\Command;
use DB;
use InvoiceNinja\Sdk\InvoiceNinja;
use Carbon\Carbon;

class LogsCreateInvoice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:create-invoice';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates an Invoice for Orders that have no Invoice attached.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        setlocale(LC_TIME, "de_DE");
        // Fetch all orders without Invoice
        $orders = DB::table("logs_order")->whereNull("invoice_id")->get();
        if (sizeof($orders) === 0) {
            return 0;
        }
        $invoice_client = new InvoiceNinja(config("metager.invoiceninja.access_token"));
        $invoice_client->setUrl(config("metager.invoiceninja.url"));
        foreach ($orders as $order) {
            $email = $order->user_email;
            $logs_account = new LogsAccountProvider($email);

            $order_from = new Carbon($order->from, "UTC");
            $order_to = new Carbon($order->to, "UTC");

            $due_date = clone $order_from;
            $due_date->subDays(14);
            if ($due_date->isPast()) {
                $due_date = now("UTC")->addDays(14);
            }
            $notes = "MetaGer Logs API Access " . $order_from->format("F Y") . " - " . $order_to->format("F Y");


            $new_invoice = $invoice_client->invoices->create(
                [
                    'client_id' => $logs_account->client->id,
                    'date' => now("UTC")->format("Y-m-d"),
                    'discount' => round($order->price - ($order->price * ($order->discount / 100)), 2),
                    'due_date' => $due_date->format("Y-m-d"),
                    'tax_name1' => "Umsatzsteuer",
                    "tax_rate1" => 19,
                    'line_items' => [
                        [
                            'notes' => $notes,
                            "quantity" => 1,
                            "cost" => $order->price
                        ]
                    ]
                ],
                ['send_email' => 'true']
            );

            DB::table("logs_order")->where("id", $order->id)->update(["invoice_id" => $new_invoice["data"]["id"]]);
        }
    }
}
