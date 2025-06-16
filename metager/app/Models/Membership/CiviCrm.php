<?php

namespace App\Models\Membership;

use Arr;
use Cache;
use Exception;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Redis;
use DB;
use Carbon\Carbon;
use Log;


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

    public static function FIND_CONTACT(MembershipContact $contact): array|null
    {
        $params = [
            'select' => ['*', 'email_primary.email'],
            'where' => [['prefix_id:label', '=', $contact->title], ['first_name', '=', $contact->first_name], ['last_name', '=', $contact->last_name], ['email_primary.email', '=', $contact->email], ['contact_type', '=', 'Individual']],
            'limit' => 1,
        ];

        $response = self::API_POST("/Contact/get", $params);
        return Arr::get($response, "values.0");
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

    public static function CREATE_CONTACT(MembershipContact $contact)
    {
        $params = [
            'values' => ['contact_type' => 'Individual', 'prefix_id:label' => $contact->title, 'first_name' => $contact->first_name, 'last_name' => $contact->last_name],
            'chain' => ['name_me_0' => ['Email', 'create', ['values' => ['contact_id' => '$id', 'email' => $contact->email]]]],
        ];

        $response = self::API_POST("/Contact/create", $params);
        return Arr::get($response, "values.0");
    }

    public static function FIND_COMPANY(MembershipCompany $company): array|null
    {
        $params = [
            'select' => ['*', 'email_primary.email'],
            'where' => [['contact_type', '=', 'Organization'], ['organization_name', '=', $company->company], ['email_primary.email', '=', $company->email]],
            'limit' => 25,
        ];

        $response = self::API_POST("/Contact/get", $params);
        return Arr::get($response, "values.0");
    }

    public static function CREATE_COMPANY(MembershipCompany $company): array|null
    {
        $params = [
            'values' => ['organization_name' => $company->company, 'contact_type' => 'Organization'],
            'chain' => ['name_me_0' => ['Email', 'create', ['values' => ['contact_id' => '$id', 'email' => $company->email]]]],
        ];

        $response = self::API_POST("/Contact/create", $params);
        return Arr::get($response, "values.0");
    }

    /**
     * Summary of FIND_MEMBERSHIPS
     * @param string $contact_id
     * @param string $membership_id
     * @return MembershipApplication[]|null
     */
    public static function FIND_MEMBERSHIPS(string $contact_id = null, string $membership_id = null, string $mandate = null): array|null
    {
        $memberships = [];
        $params = [
            'select' => ['*', 'email.email', 'contact_id.prefix_id:label', 'contact_id.first_name', 'contact_id.last_name', 'contact_id.prefix_id:label', 'contact_id.organization_name', 'contact_id.addressee_display', 'membership_type_id.duration_unit', 'membership_type_id.duration_interval', 'Beitrag.Monatlicher_Mitgliedsbeitrag', 'Beitrag.Zahlungsweise:label', 'Beitrag.Zahlungsstatus:label', 'Beitrag.Locale', 'Beitrag.Zahlungsreferenz', 'Beitrag.Kontoinhaber', 'Beitrag.IBAN', 'Beitrag.BIC', 'Beitrag.PayPal_Vault', 'MetaGer_Key.Key'],
            'join' => [['Email AS email', 'LEFT', ['contact_id.email_primary', '=', 'email.id']]],
            'where' => [],
            'limit' => 25,
        ];
        if ($contact_id !== null) {
            $params["where"][] = ['contact_id', '=', $contact_id];
        } else if ($membership_id !== null) {
            $params["where"][] = ['id', '=', $membership_id];
        } else if ($mandate !== null) {
            $params["where"][] = ['Beitrag.Zahlungsreferenz', '=', $mandate];
        } else {
            return $memberships;
        }

        $response = self::API_POST("/Membership/get", $params);
        $response = Arr::get($response, "values", null);
        if ($response === null)
            return null;

        foreach ($response as $membership_entry) {
            $membership = new MembershipApplication;
            $membership->is_update = true;
            $membership->crm_contact = Arr::get($membership_entry, "contact_id");
            $membership->crm_membership = Arr::get($membership_entry, "id");
            $membership->amount = Arr::get($membership_entry, "Beitrag.Monatlicher_Mitgliedsbeitrag");
            $membership->key = Arr::get($membership_entry, 'MetaGer_Key.Key');
            $membership->locale = Arr::get($membership_entry, "Beitrag.Locale");
            if (Arr::get($membership_entry, 'contact_id.organization_name') !== null) {
                $company = new MembershipCompany;
                $company->company = Arr::get($membership_entry, 'contact_id.organization_name');
                $company->email = Arr::get($membership_entry, "email.email");
                $membership->company = $company;
            } else {
                $contact = new MembershipContact;
                $contact->title = Arr::get($membership_entry, "contact_id.prefix_id:label");
                $contact->first_name = Arr::get($membership_entry, 'contact_id.first_name');
                $contact->last_name = Arr::get($membership_entry, 'contact_id.last_name');
                $contact->email = Arr::get($membership_entry, "email.email");
                $membership->contact = $contact;
            }
            $membership->interval = "monthly";
            switch (Arr::get($membership_entry, 'membership_type_id.duration_unit')) {
                case "year":
                    $membership->interval = "annual";
                    break;
                case "month":
                    $membership->interval = match (Arr::get($membership_entry, 'membership_type_id.duration_interval')) {
                        1 => "monthly",
                        3 => "quarterly",
                        6 => "six-monthly",
                        default => "monthly"
                    };
                    break;
            }
            $membership->payment_method = match (Arr::get($membership_entry, 'Beitrag.Zahlungsweise:label')) {
                "Banküberweisung" => "banktransfer",
                "Lastschrift" => "directdebit",
                "PayPal" => "paypal",
                "Creditcard" => "card",
            };
            $membership->payment_reference = Arr::get($membership_entry, 'Beitrag.Zahlungsreferenz');
            if (in_array($membership->payment_method, ["paypal", "card"])) {
                $paypal = new MembershipPaymentPaypal;
                $paypal->vault_id = Arr::get($membership_entry, 'Beitrag.PayPal_Vault');
                $membership->paypal = $paypal;
            } elseif ($membership->payment_method === "directdebit") {
                $directdebit = new MembershipPaymentDirectdebit;
                $directdebit->accountholder = Arr::get($membership_entry, 'Beitrag.Kontoinhaber');
                $directdebit->iban = Arr::get($membership_entry, 'Beitrag.IBAN');
                $directdebit->bic = Arr::get($membership_entry, 'Beitrag.BIC');
                $membership->directdebit = $directdebit;
            }
            $memberships[] = $membership;
        }
        return $memberships;
    }

    public static function FIND_DUE_MEMBERSHIPS(array $ignore_references = [], array $ignore_vaults = []): array|null
    {
        $end_date = now()->addDays(14);
        $params = [
            'select' => ['id', 'Beitrag.PayPal_Vault'],
            'where' => [['Beitrag.PayPal_ID', '=', PayPal::GET_ID()], ['Beitrag.Zahlungsweise:label', 'IN', ['PayPal', 'Creditcard']], ['end_date', '<=', $end_date->format("Y-m-d")], ['Beitrag.PayPal_Vault', 'IS NOT NULL'], ['Beitrag.Zahlungsstatus:label', 'NOT IN', ['Ausgetreten', 'Verstorben']], ['Beitrag.Zahlungsreferenz', 'NOT IN', $ignore_references], ['Beitrag.PayPal_Vault', 'NOT IN', $ignore_vaults]],
            'limit' => 25,
            'chain' => ['payments' => ['Membership', 'nextPayments', ['membershipId' => '$id', 'count' => 1]]],
        ];
        $due_memberships = self::API_POST("/Membership/get", $params);
        if ($due_memberships === null)
            return null;
        $due_memberships = Arr::get($due_memberships, "values");
        return $due_memberships;
    }

    public static function FIND_MEMBERSHIP_APPLICATIONS()
    {
        $params = [
            'select' => ['*', 'contact_id.addressee_display', 'Beitrag.Monatlicher_Mitgliedsbeitrag', 'Beitrag.Zahlungsweise:label', 'Beitrag.Zahlungsstatus:label', 'Beitrag.Zahlungsreferenz', 'Beitrag.Kontoinhaber', 'Beitrag.IBAN', 'Beitrag.BIC', 'Beitrag.PayPal_Vault', 'MetaGer_Key.Key'],
            'where' => [['status_id', '=', 9]], // Status = Applied
            'orderBy' => ['join_date' => 'DESC'],
            'limit' => 25,
        ];

        $response = self::API_POST("/Membership/get", $params);
        return Arr::get($response, "values");
    }

    public static function ADD_MEMBERSHIP_PAYPAL_VAULT(string $membership_id, string $vault_id)
    {
        $params = [
            'values' => ['Beitrag.PayPal_Vault' => $vault_id, 'Beitrag.PayPal_ID' => PayPal::GET_ID()],
            'where' => [['id', '=', $membership_id]],
        ];
        $membership = Arr::get(self::FIND_MEMBERSHIPS(null, $membership_id), "0");
        if ($membership !== null) {
            $params["values"]['status_id'] = $membership["status_id"];
            $params["values"]['is_override'] = $membership["is_override"];
        }
        return self::API_POST("/Membership/update", $params);
    }

    public static function REMOVE_MEMBERSHIP_PAYPAL_VAULT(string $vault_id)
    {
        $params = [
            'values' => ['Beitrag.PayPal_Vault' => '', 'Beitrag.PayPal_ID' => '', 'Beitrag.Zahlungsweise' => 2],
            'where' => [['Beitrag.PayPal_Vault', '=', $vault_id]],
        ];
        return self::API_POST("/Membership/update", $params);
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

    public static function CREATE_MEMBERSHIP(MembershipApplication $application): array|null
    {
        $type_string = "";
        if ($application->contact !== null) {
            $type_string .= "person";
            if ($application->amount < 5) {
                $type_string .= ".reduced";
            } else {
                $type_string .= ".regular";
            }
        } else if ($application->company !== null) {
            $type_string .= "company";
            $type_string .= "." . $application->company->employees;
        } else {
            throw new Exception("Application is missing contact data.");
        }
        $type_string .= "." . $application->interval;

        $membership_type = Arr::get(self::MEMBERSHIP_TYPES, $type_string);

        $params = [
            'values' => [
                'contact_id' => $application->crm_contact,
                'membership_type_id' => $membership_type,
                'Beitrag.Monatlicher_Mitgliedsbeitrag' => $application->amount,
                'Beitrag.Zahlungsweise:label' => "Banküberweisung",
                'Beitrag.Zahlungsstatus:label' => 'Eingetreten',
                'Beitrag.Locale' => $application->locale,
                'Ver_ffentlichung.Eintrag_auf_SUMA_EV_Webseite' => FALSE,
                "MetaGer_Key.Key" => $application->key
            ],
        ];

        if ($application->payment_reference !== null) {
            $params["values"]['Beitrag.Zahlungsreferenz'] = $application->payment_reference;
        }

        $params["values"]["Beitrag.Zahlungsweise:label"] = match ($application->payment_method) {
            "banktransfer" => "Banküberweisung",
            "directdebit" => "Lastschrift",
            "paypal" => "PayPal",
            "card" => "Creditcard",
        };
        if ($application->payment_method === "directdebit") {
            $params["values"]["Beitrag.Kontoinhaber"] = $application->directdebit->accountholder;
            $params["values"]["Beitrag.IBAN"] = $application->directdebit->iban;
            $params["values"]["Beitrag.BIC"] = $application->directdebit->bic;
        } elseif ($application->payment_method === "paypal") {
            // ToDo complete paypal data
        }

        if ($application->reduction !== null && $application->reduction->expires_at !== null) {
            $params["values"]['Beitrag.Erm_igt_bis'] = $application->reduction->expires_at->format("Y-m-d");
        }

        $response = self::API_POST("/Membership/create", $params);
        $new_membership = Arr::get($response, "values.0");
        return $new_membership;
    }

    public static function UPDATE_MEMBERSHIP(MembershipApplication $application): array|null
    {
        $params = [
            'where' => [['id', '=', $application->crm_membership]],
            'values' => [],
        ];

        if ($application->amount !== null) {
            $params["values"]['Beitrag.Monatlicher_Mitgliedsbeitrag'] = $application->amount;
            $type_string = null;
            if ($application->contact !== null) {
                $type_string .= "person";
                if ($application->amount < 5) {
                    $type_string .= ".reduced";
                } else {
                    $type_string .= ".regular";
                }
            } else if ($application->company !== null) {
                $type_string .= "company";
                $type_string .= "." . $application->company->employees;
            }
            if ($type_string !== null) {
                $type_string .= "." . $application->interval;
                $params["values"]['membership_type_id'] = Arr::get(self::MEMBERSHIP_TYPES, $type_string);
            }
        }
        if ($application->payment_method !== null) {
            $params["values"]["Beitrag.Zahlungsweise:label"] = match ($application->payment_method) {
                "banktransfer" => "Banküberweisung",
                "directdebit" => "Lastschrift",
                "paypal" => "PayPal",
                "card" => "Creditcard",
            };
            if ($application->payment_method === "paypal" || $application->payment_method === "card") {
                if ($application->paypal === null || $application->paypal->vault_id === null)
                    return null;
                $params["values"]['Beitrag.PayPal_Vault'] = $application->paypal->vault_id;
                $params["values"]['Beitrag.PayPal_ID'] = PayPal::GET_ID();
                $params["values"]["Beitrag.Kontoinhaber"] = "";
                $params["values"]["Beitrag.IBAN"] = "";
                $params["values"]["Beitrag.BIC"] = "";
            } elseif ($application->payment_method === "directdebit") {
                if ($application->directdebit === null || $application->directdebit->iban === null)
                    return null;
                $params["values"]['Beitrag.PayPal_Vault'] = "";
                $params["values"]['Beitrag.PayPal_ID'] = "";
                $params["values"]["Beitrag.Kontoinhaber"] = $application->directdebit->accountholder;
                $params["values"]["Beitrag.IBAN"] = $application->directdebit->iban;
                $params["values"]["Beitrag.BIC"] = $application->directdebit->bic;
            } elseif ($application->payment_method === "banktransfer") {
                $params["values"]['Beitrag.Zahlungsweise:label'] = "Banküberweisung";
                $params["values"]["Beitrag.Kontoinhaber"] = "";
                $params["values"]["Beitrag.IBAN"] = "";
                $params["values"]["Beitrag.BIC"] = "";
                $params["values"]['Beitrag.PayPal_Vault'] = "";
                $params["values"]['Beitrag.PayPal_ID'] = "";
            }
        }
        if ($application->locale !== null) {
            $params["values"]["Beitrag.Locale"] = $application->locale;
        }
        if ($application->key !== null) {
            $params["values"]["MetaGer_Key.Key"] = $application->key;
        }
        if ($application->payment_reference !== null) {
            $params["values"]['Beitrag.Zahlungsreferenz'] = $application->payment_reference;
        }
        if ($application->reduction !== null && $application->reduction->expires_at !== null) {
            $params["values"]['Beitrag.Erm_igt_bis'] = $application->reduction->expires_at->format("Y-m-d");
        }
        if (!empty($params['values'])) {
            $response = self::API_POST("/Membership/update", $params);
            return $response;
        }
        return null;
    }

    /**
     * Creates a pending CiviCRM contribution for the incoming amount
     * If there already is a pending contribution we will make sure that the contribution amount
     * is not overpaid by $amount
     * @param int $membership_id CiviCRM membership id
     * @param float $amount what payment amount to account for
     * @return int|null
     */
    public static function CREATE_MEMBERSHIP_PAYPAL_CONTRIBUTION(int $membership_id, float $amount, Carbon $date = null): int|null
    {
        if ($date === null)
            $date = now();
        $membership = Arr::get(self::FIND_MEMBERSHIPS(membership_id: $membership_id), "0");
        if ($membership === null) {
            return null;
        }
        $payments = self::MEMBERSHIP_NEXT_PAYMENTS($membership_id, 1);
        if ($payments === null)
            return null;

        $contribution_id = Arr::get($payments, "0.contribution_id");
        if ($contribution_id === null) {
            // Create Contribution
            $params = [
                'values' => [
                    'financial_type_id' => 2,
                    'contribution_status_id' => 2,
                    'is_pay_later' => TRUE,
                    'payment_instrument_id' => 7,
                    'currency' => 'EUR',
                    'receive_date' => $date->format("Y-m-d H:i:s"),
                    'contact_id' => $membership->crm_contact,
                    'net_amount' => max($payments[0]["amount"], $amount),
                    'total_amount' => max($payments[0]["amount"], $amount),
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
            $contribution_id = intval($contribution_id);
            $params = [
                'values' => ['receive_date' => $date->format("Y-m-d H:i:s")],
                'where' => [['id', '=', $contribution_id]],
            ];
            if ($amount > Arr::get($payments, "0.amount")) {
                // Update total amount of contribution to account for overpayment
                // Fist fetch the current contribution
                $new_total = $amount - Arr::get($payments, "0.amount") + Arr::get($payments, "0.total_amount");
                $params["values"]["total_amount"] = $new_total;
                $params["values"]["net_amount"] = $new_total;
            }
            self::API_POST("/Contribution/update", $params);
        }

        return $contribution_id;
    }

    public static function CREATE_MEMBERSHIP_PAYPAL_PAYMENT(string $custom_id, float $amount, Carbon $date, string $transaction_id): array|null
    {
        $transaction_id = trim($transaction_id);
        // Verify that there is not already a transaction with this ID
        $params = [
            'where' => [['trxn_id', '=', $transaction_id]],
            'limit' => 25,
        ];
        $results = self::API_POST("/Payment/get", $params);
        if (!empty(Arr::get($results, "values", ["false"]))) {
            return $results;
        }

        $memberships = self::FIND_MEMBERSHIPS(mandate: $custom_id);
        if ($memberships === null)
            return null;
        $membership = Arr::get($memberships, "0");
        if ($membership !== null) {
            $contribution_id = self::CREATE_MEMBERSHIP_PAYPAL_CONTRIBUTION($membership->crm_membership, $amount);
            if ($contribution_id !== null) {
                $params = [
                    'notificationForPayment' => FALSE,
                    'notificationForCompleteOrder' => FALSE,
                    'disableActionsOnCompleteOrder' => true,
                    'values' => ['contribution_id' => $contribution_id, 'total_amount' => $amount, 'payment_instrument_id:name' => 'PayPal', 'trxn_date' => $date->format("Y-m-d H:i:s"), 'trxn_id' => $transaction_id],
                ];
                return self::API_POST("/Payment/create", $params);
            }
        } else {
            return [];
        }

        return null;
    }

    public static function HANDLE_PAYPAL_REFUND(array $capture): bool|null
    {
        // Make sure the value is negative
        Arr::set($capture, "amount.value", abs(Arr::get($capture, "amount.value")) * -1);
        return self::HANDLE_PAYPAL_CAPTURE($capture);
    }

    public static function HANDLE_PAYPAL_CAPTURE(array $capture): bool|null
    {
        $payment_reference = Arr::get($capture, "custom_id");
        if ($payment_reference === null)
            return false;
        $lock = Cache::lock("capture:$payment_reference", 10);
        try {
            $lock->block(5);
            if (Arr::get($capture, "status") !== "COMPLETED")
                return null;
            $currency = Arr::get($capture, "amount.currency_code");
            $amount = (float) Arr::Get($capture, "amount.value");
            $date = new Carbon(Arr::get($capture, "create_time"));
            $date->setTimezone("Europe/Berlin");
            if ($currency !== "EUR")
                return false;
            self::CREATE_MEMBERSHIP_PAYPAL_PAYMENT($payment_reference, $amount, $date, Arr::get($capture, "id"));
        } catch (LockTimeoutException $e) {
            return null;
        } finally {
            $lock->release();
        }
        return true;
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