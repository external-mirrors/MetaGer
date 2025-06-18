<?php

namespace App\Console\Commands;

use App\Mail\Membership\ApplicationUnfinished;
use App\Mail\Membership\PaymentReminder;
use App\Models\Membership\CiviCrm;
use App\Models\Membership\MembershipApplication;
use Exception;
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
