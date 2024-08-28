<?php

namespace App\Models\Logs;
use InvoiceNinja\Sdk\InvoiceNinja;
use DB;

class LogsClient
{
    public readonly LogsContact $contact;
    public readonly string $id;
    public readonly string $email;
    public readonly string $name;
    public readonly string $address1;
    public readonly string $postal_code;
    public readonly string $city;
    public readonly int $discount;
    /** @var LogsOrder[] $invoices */
    public readonly array $orders;
    /** @var LogsAccessKey[] $access_keys */
    public readonly array $access_keys;

    public function __construct(string $email)
    {
        $client = $this->getOrCreateClient($email);
        $this->id = $client["id"];
        $this->email = $email;
        $this->contact_id = $client["assigned_user_id"];
        $this->name = $client["name"];
        $this->address1 = $client["address1"];
        $this->postal_code = $client["postal_code"];
        $this->city = $client["city"];
        $this->discount = DB::table("logs_user")->where("email", $email)->first()->discount;
        $this->contact = new LogsContact($client["contacts"], $email);
        $this->orders = $this->fetchOrders(10);
        $this->access_keys = $this->fetchAccessKeys();
    }

    public function isDataComplete(): bool
    {
        return !empty($this->address1) && !empty($this->postal_code) && !empty($this->city) && $this->contact->isDataComplete();
    }

    public function updateData(string $name = null, string $address1 = null, string $postal_code = null, string $city = null, string $first_name = null, string $last_name = null)
    {
        $invoice_client = self::getInvoiceNinjaClient();
        $invoice_client->clients->update($this->id, [
            "name" => $name,
            "address1" => $address1,
            "postal_code" => $postal_code,
            "city" => $city,
            "contacts" => $this->contact->updateData(first_name: $first_name, last_name: $last_name),
        ]);
    }

    /**
     * Summary of fetchInvoices
     * @param int $count
     * @return LogsOrder[]
     */
    private function fetchOrders(int $count): array
    {
        $orders = [];
        $order_data = DB::table("logs_order")->where("user_email", $this->email)->orderBy("created_at", "desc")->get();
        foreach ($order_data as $order) {
            $orders[] = new LogsOrder($order);
        }
        return $orders;
    }

    /**
     * Fetches Access Keys for the current user
     * @return LogsAccessKey[]
     */
    private function fetchAccessKeys(): array
    {
        $access_keys = [];
        $access_key_data = DB::table("logs_access_key")->where("user_email", $this->email)->orderBy("accessed_at", "DESC")->orderBy("created_at", "DESC")->get();
        foreach ($access_key_data as $access_key) {
            $access_keys[] = new LogsAccessKey($access_key);
        }
        return $access_keys;
    }

    public static function getInvoiceNinjaClient()
    {
        $invoice_client = new InvoiceNinja(config("metager.invoiceninja.access_token"));
        $invoice_client->setUrl(config("metager.invoiceninja.url"));
        return $invoice_client;
    }

    private function getOrCreateClient(string $email)
    {
        // Check if there is already an Invoicing Account for this client
        $invoice_client = self::getInvoiceNinjaClient();

        $client = null;
        $clients = $invoice_client->clients->all([
            "email" => $email
        ]);
        foreach ($clients["data"] as $tmp_client) {
            if ($tmp_client["group_settings_id"] === config("metager.invoiceninja.logs_group_id")) {
                $client = $tmp_client;
            }
        }

        if (is_null($client)) {
            $new_client = $invoice_client->clients->create([
                "group_settings_id" => config("metager.invoiceninja.logs_group_id"),
                "contacts" => [
                    [
                        'send_email' => true,
                        "is_primary" => true,
                        'email' => $email,
                    ]
                ]
            ]);
            return $this->getOrCreateClient($email);
        }

        return $client;
    }
}