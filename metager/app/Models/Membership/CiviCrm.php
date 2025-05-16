<?php

namespace App\Models\Membership;

use Arr;
use Illuminate\Support\Facades\Redis;
use DB;
use Carbon\Carbon;


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
            'where' => [['prefix_id:label', '=', $title], ['first_name', '=', $firstname], ['last_name', '=', $lastname], ['email_primary.email', '=', $email], ['contact_type', '=', 'Individual']],
            'limit' => 25,
        ];

        $response = self::API_POST("/Contact/get", $params);
        if ($response["count"] > 0) {
            return $response["values"][0];
        }

        return null;
    }

    public static function GET_CONTACT(int $contact_id)
    {
        $params = [
            'select' => ['*', 'email_primary.email'],
            'where' => [['id', '=', $contact_id]],
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

    public static function FIND_COMPANY(string $company_name, string $email)
    {
        $params = [
            'select' => ['*', 'email_primary.email'],
            'where' => [['contact_type', '=', 'Organization'], ['organization_name', '=', $company_name], ['email_primary.email', '=', $email]],
            'limit' => 25,
        ];

        $response = self::API_POST("/Contact/get", $params);
        if ($response["count"] > 0) {
            return $response["values"][0];
        }

        return null;
    }

    public static function CREATE_COMPANY(string $company_name, string $email)
    {
        $params = [
            'values' => ['organization_name' => $company_name, 'contact_type' => 'Organization'],
            'chain' => ['name_me_0' => ['Email', 'create', ['values' => ['contact_id' => '$id', 'email' => $email]]]],
        ];

        $response = self::API_POST("/Contact/create", $params);
        if ($response["count"] > 0) {
            return Arr::get($response, "values.0");
        }

        return null;
    }

    public static function FIND_MEMBERSHIPS(string $contact_id = null, string $membership_id = null)
    {
        if ($contact_id === null && $membership_id === null)
            return null;
        $params = [
            'select' => ['*', 'contact_id.addressee_display', 'Beitrag.Monatlicher_Mitgliedsbeitrag', 'Beitrag.Zahlungsweise:label', 'Beitrag.Zahlungsstatus:label', 'Beitrag.Zahlungsreferenz', 'Beitrag.Kontoinhaber', 'Beitrag.IBAN', 'Beitrag.BIC', 'Beitrag.PayPal_Vault', 'MetaGer_Key.Key'],
            'where' => [],
            'limit' => 25,
        ];
        if ($membership_id === null) {
            $params["where"][] = ['contact_id', '=', $contact_id];
        } else {
            $params["where"][] = ['id', '=', $membership_id];
        }

        $response = self::API_POST("/Membership/get", $params);
        return $response["values"];
    }

    public static function FIND_MEMBERSHIP_APPLICATIONS()
    {
        $params = [
            'select' => ['*', 'contact_id.addressee_display', 'Beitrag.Monatlicher_Mitgliedsbeitrag', 'Beitrag.Zahlungsweise:label', 'Beitrag.Zahlungsstatus:label', 'Beitrag.Zahlungsreferenz', 'Beitrag.Kontoinhaber', 'Beitrag.IBAN', 'Beitrag.BIC', 'Beitrag.PayPal_Vault', 'MetaGer_Key.Key'],
            'where' => [['status_id', '=', 9]], // Status = Applied
            'limit' => 25,
        ];

        $response = self::API_POST("/Membership/get", $params);
        return Arr::get($response, "values");
    }

    public static function ACCEPT_MEMBERSHIP_APPLICATION(string $membership_id)
    {
        $params = [
            'values' => ['is_override' => FALSE],
            'where' => [['id', '=', $membership_id]],
        ];
        return self::API_POST("/Membership/update", $params);
    }

    public static function DELETE_MEMBERSHIP_APPLICATION(string $membership_id)
    {
        $membership_entry = self::FIND_MEMBERSHIPS(null, $membership_id);
        $membership_entry = Arr::get($membership_entry, "0");
        if ($membership_entry === null)
            return;

        // Delete Membership
        $params = [
            'where' => [
                ['status_id', '=', 9], // Applied
                ['id', '=', $membership_id]
            ],
        ];
        self::API_POST("/Membership/delete", $params);

        // Check if contact has any contributions
        $params = [
            'where' => [['contact_id', '=', Arr::get($membership_entry, "contact_id")]],
            'limit' => 25,
        ];
        $contributions = Arr::get(self::API_POST("/Contribution/get", $params), "values", []);
        if (sizeof($contributions) > 0)
            return;

        // Delete the whole contact 
        $params = [
            'where' => [['id', '=', Arr::get($membership_entry, "contact_id")]],
            'useTrash' => FALSE,
        ];
        self::API_POST("/Contact/delete", $params);
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

    public static function MEMBERSHIP_NEXT_PAYMENTS(int $membership_id, int $count = 3)
    {
        $params = [
            'membershipId' => $membership_id,
            'count' => $count,
        ];

        $response = self::API_POST("/Membership/nextPayments", $params);
        $response = Arr::get($response, "values");
        foreach ($response as $index => $payment) {
            $due = Carbon::createFromFormat("Y-m-d", $payment["due_date"]);
            $response[$index]["due_date"] = $due;
        }
        return $response;
    }

    public static function CREATE_MEMBERSHIP(string $contact_id, array $membership)
    {
        $type_string = "";
        if (empty($membership["company"])) {
            $type_string .= "person";
            if ($membership["amount"] < 5) {
                $type_string .= ".reduced";
            } else {
                $type_string .= ".regular";
            }
        } else {
            $type_string .= "company";
            $type_string .= "." . $membership["employees"];
        }
        $type_string .= "." . $membership['interval'];

        $membership_type = Arr::get(self::MEMBERSHIP_TYPES, $type_string);
        $payment_type = match ($membership["payment-method"]) {
            "banktransfer" => "Banküberweisung",
            "directdebit" => "Lastschrift",
            "paypal" => "PayPal",
            "creditcard" => "Creditcard",
        };
        $params = [
            'values' => [
                'status_id:label' => 'Applied',
                'is_override' => TRUE,
                'start_date' => now()->format("Y-m-d"),
                'end_date' => now()->format("Y-m-d"),
                'contact_id' => $contact_id,
                'membership_type_id' => $membership_type,
                'Beitrag.Monatlicher_Mitgliedsbeitrag' => $membership["amount"],
                'Beitrag.Zahlungsweise:label' => $payment_type,
                'Beitrag.Zahlungsstatus:label' => 'Eingetreten',
                'Ver_ffentlichung.Eintrag_auf_SUMA_EV_Webseite' => FALSE,
            ],
        ];

        if ($membership["payment-method"] === "directdebit") {
            $params["values"]["Beitrag.Kontoinhaber"] = $membership["accountholder"];
            $params["values"]["Beitrag.IBAN"] = $membership["iban"];
        } elseif ($membership["payment-method"] === "paypal") {

        }

        if (!empty($membership["key"])) {
            $params["values"]["MetaGer_Key.Key"] = $membership["key"];
        }

        if (!empty($membership["reduced_until"])) {
            $params["values"]["Beitrag.Erm_igt_bis"] = $membership["reduced_until"];
        }

        $response = self::API_POST("/Membership/create", $params);
        $new_membership = Arr::get($response, "values.0");
        return $new_membership;
    }

    public static function CREATE_MEMBERSHIP_PAYPAL_CONTRIBUTION(int $membership_id): int|null
    {
        $membership = self::FIND_MEMBERSHIPS(null, $membership_id);
        if ($membership === null || sizeof($membership) === 0) {
            return null;
        } else {
            $membership = $membership[0];
        }
        $payments = self::MEMBERSHIP_NEXT_PAYMENTS($membership_id, 1);
        if ($payments === null)
            return null;

        $contribution_id = null;
        if ($payments[0]["contribution_id"] === null) {
            // Create Contribution
            $params = [
                'values' => [
                    'financial_type_id' => 2,
                    'contribution_status_id' => 2,
                    'is_pay_later' => TRUE,
                    'payment_instrument_id' => 7,
                    'currency' => 'EUR',
                    'receive_date' => $payments[0]["due_date"]->format("Y-m-d") . " 00:00:00",
                    'contact_id' => $membership["contact_id"],
                    'net_amount' => $payments[0]["amount"],
                    'total_amount' => $payments[0]["amount"],
                    'source' => ''
                ],
            ];
            $contribution = self::API_POST("/Contribution/create", $params);
            $contribution_id = $contribution["values"][0]["id"];
            self::API_POST_V3("MembershipPayment", "create", [
                "membership_id" => $membership_id,
                "contribution_id" => $contribution_id
            ]);
        } else {
            $contribution_id = intval($payments[0]["contribution_id"]);
        }

        return $contribution_id;
    }

    public static function CREATE_MEMBERSHIP_PAYPAL_PAYMENT(int $contribution_id, float $amount, Carbon $date, string $transaction_id): array|null
    {
        $transaction_id = trim($transaction_id);
        // Verify that there is not already a transaction with this ID
        $params = [
            'where' => [['trxn_id', '=', $transaction_id]],
            'limit' => 25,
        ];
        $results = self::API_POST("/Payment/get", $params);
        if (!empty(Arr::get($results, "values", ["false"]))) {
            return Arr::get($results, "values.0");
        }

        $params = [
            'notificationForPayment' => FALSE,
            'disableActionsOnCompleteOrder' => TRUE,
            'values' => ['contribution_id' => $contribution_id, 'total_amount' => $amount, 'payment_instrument_id:name' => 'PayPal', 'trxn_date' => $date->format("Y-m-d H:i:s"), 'trxn_id' => $transaction_id],
        ];
        return self::API_POST("/Payment/create", $params);
    }

    private static function API_POST_V3(string $entity, string $action, array $json)
    {
        $resulthash = md5("civicrm:api" . microtime(true));

        $url = str_replace("/verein/ajax/api4", "/wp-content/plugins/civicrm/civicrm/extern/rest.php", config("metager.metager.civicrm.url"));

        $data = [
            "entity" => $entity,
            "action" => $action,
            //"json" => json_encode($json),
            "api_key" => config("metager.metager.civicrm.apikey"),
            "key" => config("metager.metager.civicrm.sitekey")
        ];
        $data = array_merge($data, $json);
        $test = http_build_query($data);

        $mission = [
            "resulthash" => $resulthash,
            "url" => $url,
            "useragent" => "MetaGer",
            "cacheDuration" => 0,   // We'll cache seperately
            "headers" => [
                "Content-Type" => 'application/x-www-form-urlencoded',
                //"X-Civi-Auth" => "Bearer " . config("metager.metager.civicrm.apikey"),
            ],
            "name" => "CiviCRM",
            "curlopts" => [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query($data)
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
        //if ($body["is_error"] === 1)
        //    return null;
        return $body;
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