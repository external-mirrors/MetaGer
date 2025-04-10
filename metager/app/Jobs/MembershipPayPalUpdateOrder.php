<?php

namespace App\Jobs;

use App\Models\Membership\PayPal;
use Arr;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use DB;

/**
 * Updates Order status from PayPal Memberships
 * Extends Authorization if necessary
 */
class MembershipPayPalUpdateOrder implements ShouldQueue
{
    use Queueable;

    private $ids = null;
    private $order_ids = null;
    /**
     * Create a new job instance.
     */
    public function __construct(array $ids = null, array $order_ids = null)
    {
        $this->ids = $ids;
        $this->order_ids = $order_ids;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $query = DB::table("membership_paypal");

        if ($this->ids !== null) {
            $query = $query->whereIn("id", $this->ids);
        }
        if ($this->order_ids !== null) {
            $query = $query->whereIn("order_id", $this->order_ids);
        }

        $query->orderBy("id")->chunk(15, function ($membership_paypal) {
            foreach ($membership_paypal as $paypal_record) {
                if ($paypal_record->order_id !== null) {
                    $order = PayPal::GET_ORDER($paypal_record->order_id);
                    if ($order === null)
                        continue;
                    $membership_record = DB::table("membership")->where("paypal", "=", $paypal_record->id)->firstOrFail();
                    // Check if PaymentSource was vaulted
                    if (Arr::has($order, "payment_source")) {
                        $vault_status = Arr::get($order, "payment_source." . $membership_record->payment_method . ".attributes.vault.status");
                        if ($vault_status === "VAULTED") {
                            $vault_id = Arr::get($order, "payment_source." . $membership_record->payment_method . ".attributes.vault.id");
                            if ($vault_id !== $paypal_record->vault_id) {
                                $paypal_record->vault_id = $vault_id;
                                DB::table("membership_paypal")->where("id", "=", $membership_record->paypal)->update(["vault_id" => $paypal_record->vault_id]);
                            }
                        } elseif ($paypal_record->vault_id !== null) {
                            $paypal_record->vault_id = null;
                            DB::table("membership_paypal")->where("id", "=", $paypal_record->id)->update(["vault_id" => null]);
                        }
                    }
                    if (Arr::has($order, "purchase_units") && sizeof($order["purchase_units"]) === 1) {
                        $authorizations = Arr::get($order, "purchase_units.0.payments.authorizations");
                        if ($authorizations !== null && sizeof($authorizations) === 1) {
                            self::UPDATE_AUTHORIZATION($paypal_record, $authorizations[0]);
                        } elseif ($paypal_record->authorization_id !== null) {
                            $paypal_record->authorization_id = null;
                            DB::table("membership_paypal")->where("id", "=", $paypal_record->id)->update(["authorization_id" => null]);
                        }
                    }
                }

                if ($paypal_record->order_id === null && $paypal_record->authorization_id === null && $paypal_record->vault_id === null) {
                    DB::table("membership_paypal")->where("id", "=", $paypal_record->id)->delete();
                } else if ($paypal_record->authorization_id !== null && now()->isAfter($paypal_record->expires_at)) {
                    $new_authorization = PayPal::REAUTHORIZE_ORDER($paypal_record->authorization_id);
                    if ($new_authorization !== null) {
                        self::UPDATE_AUTHORIZATION($paypal_record, $new_authorization);
                    }
                }
            }
        });
    }

    public static function UPDATE_AUTHORIZATION(object &$paypal_record, $new_authorization)
    {
        $authorization_status = $new_authorization["status"];
        switch ($authorization_status) {
            case "CREATED":
            case "PARTIALLY_CAPTURED":
                if ($paypal_record->authorization_id !== $new_authorization["id"]) {
                    $paypal_record->authorization_id = $new_authorization["id"];
                    DB::table("membership_paypal")->where("id", "=", $paypal_record->id)->update(["authorization_id" => $paypal_record->authorization_id]);
                }
                break;
            case "PENDING":
                if ($paypal_record->authorization_id !== null) {
                    $paypal_record->authorization_id = null;
                    DB::table("membership_paypal")->where("id", "=", $paypal_record->id)->update(["authorization_id" => $paypal_record->authorization_id]);
                }
                break;
            case "CAPTURED":
            case "DENIED":
            case "VOIDED":
            default:
                if ($paypal_record->order_id !== null || $paypal_record->authorization_id !== null) {
                    $paypal_record->authorization_id = null;
                    $paypal_record->order_id = null;
                    DB::table("membership_paypal")->where("id", "=", $paypal_record->id)->update(["order_id" => $paypal_record->order_id, "authorization_id" => $paypal_record->authorization_id]);
                }
        }
    }
}
