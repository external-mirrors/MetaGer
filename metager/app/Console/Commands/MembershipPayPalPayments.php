<?php

namespace App\Console\Commands;

use App\Localization;
use App\Mail\Membership\MembershipAdminPaymentFailed;
use App\Mail\Membership\PaymentMethodFailed;
use App\Models\Membership\CiviCrm;
use App\Models\Membership\MembershipApplication;
use App\Models\Membership\MembershipPaymentPaypal;
use App\Models\Membership\PayPal;
use Arr;
use Cache;
use Carbon;
use Illuminate\Console\Command;
use LaravelLocalization;
use Mail;

class MembershipPayPalPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'membership:paypal-payments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates Payments for all due PayPal Memberships';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $ignore_vault = [];
        $ignore_reference = [];

        MembershipPaymentPaypal::whereNull("application_id")->where("updated_at", "<", now()->subDays(3))->delete();

        foreach (MembershipPaymentPaypal::whereNotNull("vault_id")->orWhereNotNull("application_id")->get() as $paypal) {
            if ($paypal->vault_id !== null) {
                $ignore_vault[] = $paypal->vault_id;
            }
            if ($paypal->application !== null && $paypal->application->payment_reference !== null) {
                $ignore_reference[] = $paypal->application->payment_reference;
            }
        }

        $due_memberships = CiviCrm::FIND_DUE_MEMBERSHIPS($ignore_reference, $ignore_vault);
        foreach ($due_memberships as $membership) {
            $next_payment = Arr::get($membership, "payments.0");
            if ($next_payment === null)
                continue;
            $membership = Arr::get(CiviCrm::FIND_MEMBERSHIPS(membership_id: $membership["id"]), "0");
            LaravelLocalization::setLocale($membership->locale);
            if ($membership === null)
                continue;
            $paypal_payment = MembershipPaymentPaypal::create(["vault_id" => $membership->paypal->vault_id]);
            $paypal_order = PayPal::CREATE_ORDER($membership);
            $status_code = Arr::get($paypal_order, "status_code");
            $paypal_order = Arr::get($paypal_order, "response_body");
            if (!in_array($status_code, [200, 201])) {
                // There was an error
                if ($status_code === 403) {
                    // We are not authorized to create the order
                    // Most likely our vault was invalidated i.e. by the user
                    $this->disablePaymentMethod($membership);
                    continue;
                }
            }
            if ($paypal_order !== null) {
                // Validate PayPal Order
                $old_order = $paypal_order;
                $paypal_order = PayPal::VALIDATE_ORDER(Arr::get($paypal_order, "id"), $paypal_order);
                if (is_string($paypal_order)) {
                    $this->disablePaymentMethod($membership);
                    $notification = new MembershipAdminPaymentFailed($membership, $old_order);
                    Mail::mailer("membership")->send($notification);
                    continue;
                }
                $paypal_payment->order_id = Arr::get($paypal_order, "id");
                $paypal_payment->save();
            }
            // We'll only process one purchase unit since we do not create orders with more than that
            $captures = Arr::get($paypal_order, "purchase_units.0.payments.captures", []);
            foreach ($captures as $capture) {
                CiviCrm::HANDLE_PAYPAL_CAPTURE($capture);
            }
        }
    }

    private function disablePaymentMethod(MembershipApplication $membership): bool
    {
        if (CiviCrm::REMOVE_MEMBERSHIP_PAYPAL_VAULT($membership->paypal->vault_id) !== null) {
            $notification = new PaymentMethodFailed($membership);
            Mail::mailer("membership")->send($notification);
            return true;
        } else {
            return false;
        }
    }
}
