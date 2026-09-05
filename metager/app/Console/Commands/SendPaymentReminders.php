<?php

namespace App\Console\Commands;

use App\Assoc\PaymentReminderProcessor;
use Illuminate\Console\Command;

class SendPaymentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assoc:send-payment-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Sends the balance-driven payment-shortfall reminders (banktransfer memberships only) and terminates a membership past the final stage. See PaymentReminderProcessor.";

    public function handle(PaymentReminderProcessor $processor): int
    {
        $result = $processor->process();

        $this->line("Erste Erinnerung versendet: {$result['sent']['first']}");
        $this->line("Zweite Erinnerung versendet: {$result['sent']['second']}");
        $this->line("Mitgliedschaften gekündigt: {$result['sent']['terminated']}");
        $this->line("Übersprungen (keine E-Mail-Adresse): {$result['skipped_no_email']}");

        return self::SUCCESS;
    }
}
