<?php

namespace App\Models\Logs;
use InvoiceNinja\Sdk\InvoiceNinja;

class LogsClient
{
    public readonly LogsContact $contact;
    public readonly string $id;
    public readonly string $name;
    public readonly string $address1;
    public readonly string $postal_code;
    public readonly string $city;

    public function __construct(string $email)
    {
        $client = $this->getOrCreateClient($email);
        $this->id = $client["id"];
        $this->name = $client["name"];
        $this->address1 = $client["address1"];
        $this->postal_code = $client["postal_code"];
        $this->city = $client["city"];
        $this->contact = new LogsContact($client["contacts"], $email);
    }

    public function isDataComplete(): bool
    {
        return !empty($this->address1) && !empty($this->postal_code) && !empty($this->city) && $this->contact->isDataComplete();
    }

    public function updateData(string $name = null, string $address1 = null, string $postal_code = null, string $city = null, string $first_name = null, string $last_name = null)
    {
        $invoice_client = $this->getInvoiceNinjaClient();
        $invoice_client->clients->update($this->id, [
            "name" => $name,
            "address1" => $address1,
            "postal_code" => $postal_code,
            "city" => $city,
            "contacts" => $this->contact->updateData(first_name: $first_name, last_name: $last_name),
        ]);
    }

    private function getInvoiceNinjaClient()
    {
        $invoice_client = new InvoiceNinja(config("metager.invoiceninja.access_token"));
        $invoice_client->setUrl(config("metager.invoiceninja.url"));
        return $invoice_client;
    }

    private function getOrCreateClient(string $email)
    {
        // Check if there is already an Invoicing Account for this client
        $invoice_client = $this->getInvoiceNinjaClient();

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
            $invoice_client->clients->create([
                "group_settings_id" => config("metager.invoiceninja.logs_group_id"),
                "contacts" => [
                    [
                        'send_email' => true,
                        'email' => $email,
                    ]
                ]
            ]);
            return $this->getOrCreateClient($email);
        }
        return $client;
    }
}