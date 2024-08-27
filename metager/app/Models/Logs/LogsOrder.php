<?php

namespace App\Models\Logs;
use Carbon;
use InvoiceNinja\Sdk\Exceptions\ApiException;

class LogsOrder
{
    public readonly string $id;
    public readonly Carbon $from;
    public readonly Carbon $to;
    public readonly float $price;
    public readonly LogsInvoice|null $invoice;



    public function __construct($order)
    {
        $this->id = $order->id;
        $this->from = new Carbon($order->from, "UTC");
        $this->to = new Carbon($order->to, "UTC");
        $this->price = $order->price;

        if (!is_null($order->invoice_id)) {
            try {
                $this->invoice = new LogsInvoice($order->invoice_id);
            } catch (ApiException $e) {
                // Invoice probably does not exist anymore; Could be another error aswell however
                $this->invoice = null;
            }
        }
    }
}