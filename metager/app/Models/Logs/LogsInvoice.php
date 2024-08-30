<?php

namespace App\Models\Logs;

use Carbon\Carbon;

class LogsInvoice
{
    const INVOICE_STATUS_PAID = "4";
    const INVOICE_STATUS_CANCELLED = "5";
    const INVOICE_STATUS_REVERSED = "6";
    const INVOICE_STATUS_PARTIAL = "3";
    const INVOICE_STATUS_SENT = "2";
    const INVOICE_STATUS_DRAFT = "1";
    const INVOICE_STATUS_PAST_DUE = "-1";
    const INVOICE_STATUS_UNPAID = "-2";
    const INVOICE_STATUS_VIEWED = "-3";
    public readonly string $status;
    public readonly Carbon $created_at;
    public readonly Carbon $updated_at;
    public readonly string $number;
    public readonly Carbon $date;
    public readonly Carbon $last_sent_date;
    public readonly Carbon $next_send_date;
    public readonly Carbon $due_date;
    public readonly string $invitation_link;
    public function __construct(string $invoice_id)
    {
        $invoice_client = LogsClient::getInvoiceNinjaClient();
        $invoice_raw = $invoice_client->invoices->get($invoice_id)["data"];
        $this->status = $invoice_raw["status_id"];
        $this->created_at = new Carbon($invoice_raw["created_at"], "UTC");
        $this->updated_at = new Carbon($invoice_raw["created_at"], "UTC");
        $this->number = $invoice_raw["number"];
        $this->date = new Carbon($invoice_raw["date"]);
        $this->last_sent_date = new Carbon($invoice_raw["last_sent_date"], "UTC");
        $this->next_send_date = new Carbon($invoice_raw["next_send_date"], "UTC");
        $this->due_date = new Carbon($invoice_raw["due_date"], "UTC");
        $this->invitation_link = $invoice_raw["invitations"][0]["link"];
    }
}