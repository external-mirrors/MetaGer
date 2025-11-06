<?php

namespace App\Console\Commands;

use App\Mail\Membership\PaymentMethodFailed;
use App\Mail\Membership\PaymentReminder;
use App\Mail\Membership\ReductionReminder;
use App\Models\Membership\CiviCrm;
use Cache;
use Illuminate\Console\Command;
use Mail;

class MembershipPaymentReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'membership:payment-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notifies users of outstanding payments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        /**
         * Since Laravels Schedule::onOneServer failed multiple times now
         * We'll implement a manual atomic lock here.
         * It doesn't need to be released since the job doesn't run more often than every 5 minutes
         */
        $lock = Cache::lock("console:commands:membership:payment-reminder:lock", 60 * 5);
        if ($lock->get()) {
            $chargebacks = CiviCrm::FIND_CHARGEBACKS();
            if (sizeof($chargebacks) > 0) {
                CiviCrm::MEMBERSHIP_RENEW(true);
            }
            foreach ($chargebacks as $chargeback) {
                $mail = new PaymentMethodFailed($chargeback);
                if (Mail::mailer("membership")->send($mail)) {
                    CiviCrm::UPDATE_MEMBERSHIP_RAW($chargeback, ['Beitrag.Zahlungsweise:label' => 'Banküberweisung', 'Beitrag.IBAN' => '', 'Beitrag.BIC' => '', 'Beitrag.Kontoinhaber' => '', 'Beitrag.PayPal_Vault' => '', 'Beitrag.PayPal_ID' => '']);
                }
            }

            $reductions = CiviCrm::FIND_REDUCTION_REMINDER();
            foreach ($reductions as $reduction) {
                $mail = new ReductionReminder($reduction);
                if (Mail::mailer("membership")->send($mail)) {
                    $reduction->reduction = null;
                    $reduction->amount = 5;
                    CiviCrm::UPDATE_MEMBERSHIP($reduction, ['Beitrag.Erm_igt_bis']);
                }
            }

            $first_reminders = CiviCrm::FIND_FIRST_REMINDER();
            foreach ($first_reminders as $first_reminder) {
                $mail = new PaymentReminder($first_reminder, PaymentReminder::REMINDER_STAGE_FIRST);
                if (Mail::mailer("membership")->send($mail)) {
                    CiviCrm::UPDATE_MEMBERSHIP_RAW($first_reminder, ['Beitrag.Zahlungsstatus:label' => 'Erste Zahlungserinnerung']);
                }
            }

            $second_reminders = CiviCrm::FIND_SECOND_REMINDER();
            foreach ($second_reminders as $second_reminder) {
                $mail = new PaymentReminder($second_reminder, PaymentReminder::REMINDER_STAGE_SECOND);
                if (Mail::mailer("membership")->send($mail)) {
                    CiviCrm::UPDATE_MEMBERSHIP_RAW($second_reminder, ['Beitrag.Zahlungsstatus:label' => 'Zweite Zahlungserinnerung']);
                }
            }

            $final_reminders = CiviCrm::FIND_ABORTED_REMINDER();
            foreach ($final_reminders as $final_reminder) {
                $mail = new PaymentReminder($final_reminder, PaymentReminder::REMINDER_STAGE_ABORTED);
                if (Mail::mailer("membership")->send($mail)) {
                    CiviCrm::UPDATE_MEMBERSHIP_RAW($final_reminder, ['Beitrag.Zahlungsstatus:label' => 'Unterbrochen']);
                }
            }
        }
    }
}
