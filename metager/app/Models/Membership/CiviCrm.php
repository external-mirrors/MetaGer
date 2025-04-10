<?php

namespace App\Models\Membership;

use Arr;
use Illuminate\Support\Facades\Redis;
use DB;


class CiviCrm
{
    public const MEMBERSHIP_TYPES = [
        "person" => [
            "reduced" => [
                "monthly" => 20,
                "quarterly" => 19,
                "six-monthly" => 18,
                "annual" => 17
            ],
            "regular" => [
                "monthly" => 4,
                "quarterly" => 3,
                "six-monthly" => 2,
                "annual" => 1
            ],
        ],
        "company" => [
            "1-19" => [
                "monthly" => 8,
                "quarterly" => 7,
                "six-monthly" => 6,
                "annual" => 5
            ],
            "20-199" => [
                "monthly" => 12,
                "quarterly" => 11,
                "six-monthly" => 10,
                "annual" => 9
            ],
            ">200" => [
                "monthly" => 16,
                "quarterly" => 15,
                "six-monthly" => 14,
                "annual" => 13
            ],
        ]
    ];

    public static function FIND_CONTACT(string $title, string $firstname, string $lastname, string $email)
    {
        $params = [
            'select' => ['*', 'email_primary.email'],
            'where' => [['prefix_id:label', '=', 'Herr'], ['first_name', '=', 'Max'], ['last_name', '=', 'Mustermann'], ['email_primary.email', '=', 'dominik@hebeler.club'], ['contact_type', '=', 'Individual']],
            'limit' => 25,
        ];

        $response = self::API_POST("/Contact/get", $params);
        if ($response["count"] > 0) {
            return $response["values"][0];
        }

        return null;
    }

    public static function CREATE_CONTACT(string $title, string $firstname, string $lastname, string $email)
    {
        $params = [
            'values' => ['contact_type' => 'Individual', 'prefix_id:label' => $title, 'first_name' => $firstname, 'last_name' => $lastname],
            'chain' => ['name_me_0' => ['Email', 'create', ['values' => ['contact_id' => '$id', 'email' => $email]]]],
        ];

        $response = self::API_POST("/Contact/create", $params);
        if ($response["count"] > 0) {
            return $response["values"][0];
        }

        return null;
    }

    public static function FIND_COMPANY(string $company_name)
    {
        $params = [
            'select' => ['*', 'email_primary.email'],
            'where' => [['contact_type', '=', 'Organization'], ['organization_name', '=', $company_name]],
            'limit' => 25,
        ];

        $response = self::API_POST("/Contact/get", $params);
        if ($response["count"] > 0) {
            return $response["values"][0];
        }

        return null;
    }

    public static function FIND_MEMBERSHIPS(string $contact_id)
    {
        $params = [
            'where' => [['contact_id', '=', $contact_id]],
            'limit' => 25,
        ];

        $response = self::API_POST("/Membership/get", $params);
        return $response["values"];
    }

    public static function GET_MEMBERSHIP_COUNT()
    {
        $params = [
            'select' => ['row_count'],
            'where' => [['membership_type_id.is_active', '=', TRUE], ['status_id.is_current_member', '=', TRUE]],
            'limit' => 25,
        ];

        $response = self::API_POST("/Membership/get", $params);
        return Arr::get($response, "count", 0);
    }

    public static function CREATE_MEMBERSHIP(string $contact_id, object $membership_entry)
    {
        $type_string = "";
        if ($membership_entry->company === null) {
            $type_string .= "person";
            if ($membership_entry->amount < 5) {
                $type_string .= ".reduced";
            } else {
                $type_string .= ".regular";
            }
        } else {
            $type_string .= "company";
            $type_string .= "." . $membership_entry->employees;
        }
        $type_string .= ".{$membership_entry->interval}";

        $membership_type = Arr::get(self::MEMBERSHIP_TYPES, $type_string);
        $payment_type = match ($membership_entry->payment_method) {
            "banktransfer" => "Banküberweisung",
            "directdebit" => "Lastschrift",
            "paypal" => "PayPal",
            "creditcard" => "PayPal",
        };
        $params = [
            'values' => [
                'contact_id' => $contact_id,
                'membership_type_id' => $membership_type,
                'Beitrag.Monatlicher_Mitgliedsbeitrag' => $membership_entry->amount,
                'Beitrag.Zahlungsweise:label' => $payment_type,
                'Beitrag.Zahlungsstatus:label' => 'Eingetreten',
                'Ver_ffentlichung.Eintrag_auf_SUMA_EV_Webseite' => FALSE,
            ],
        ];

        if (!empty($membership_entry->key)) {
            $params["values"]["MetaGer_Key.Key"] = $membership_entry->key;
        }

        if ($membership_entry->payment_method === "directdebit") {
            $directdebit_entry = DB::table("membership_directdebit")->where("id", "=", $membership_entry->directdebit)->first();
            if ($directdebit_entry === null)
                return null;
            $params["values"]['Beitrag.Kontoinhaber'] = $directdebit_entry->name;
            $params["values"]['Beitrag.IBAN'] = $directdebit_entry->iban;
            $params["values"]['Beitrag.BIC'] = $directdebit_entry->bic;
        }

        $response = self::API_POST("/Membership/create", $params);
        $new_membership = Arr::get($response, "values.0");
        return $new_membership;
    }


    private static function API_POST(string $method, array $params)
    {
        $resulthash = md5("civicrm:api" . microtime(true));

        $mission = [
            "resulthash" => $resulthash,
            "url" => config("metager.metager.civicrm.url") . $method,
            "useragent" => "MetaGer",
            "cacheDuration" => 0,   // We'll cache seperately
            "headers" => [
                "Content-Type" => 'application/x-www-form-urlencoded',
                "X-Civi-Auth" => "Bearer " . config("metager.metager.civicrm.apikey"),
            ],
            "name" => "CiviCRM",
            "curlopts" => [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query(['params' => json_encode($params)])
            ]
        ];
        $mission = json_encode($mission);
        Redis::rpush(\App\MetaGer::FETCHQUEUE_KEY, $mission);
        $results = Redis::brpop($resulthash, 10);
        if (!is_array($results))
            return null;
        $results = json_decode($results[1], true);
        if ($results["info"]["http_code"] !== 200) {
            return null;
        }
        $body = json_decode($results["body"], true);
        return $body;
    }
}