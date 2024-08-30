<?php

namespace App\Models\Logs;

class LogsContact
{
    public readonly string $first_name;
    public readonly string $last_name;
    public readonly string $email;
    private readonly int $client_index;
    public readonly array $raw_contacts_data;

    public function __construct(array $contacts, $email)
    {
        foreach ($contacts as $index => $contact) {
            if ($contact["email"] === $email) {
                $this->client_index = $index;
            }
        }
        $this->email = $contacts[$this->client_index]["email"];
        $this->first_name = $contacts[$this->client_index]["first_name"];
        $this->last_name = $contacts[$this->client_index]["last_name"];

        $this->raw_contacts_data = $contacts;
    }

    public function isDataComplete(): bool
    {
        return !empty($this->email) && !empty($this->first_name) && !empty($this->last_name);
    }

    public function updateData(string $first_name, string $last_name)
    {
        $raw_contacts = $this->raw_contacts_data;
        $raw_contacts[$this->client_index]["first_name"] = $first_name;
        $raw_contacts[$this->client_index]["last_name"] = $last_name;
        return $raw_contacts;
    }
}