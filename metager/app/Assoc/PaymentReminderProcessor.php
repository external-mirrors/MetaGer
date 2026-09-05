<?php

namespace App\Assoc;

use App\Mail\Assoc\PaymentReminder;
use App\Models\Assoc\Membership;
use Illuminate\Support\Carbon;
use Mail;

/**
 * Design decision 2 of the payment-ledger design pass
 * (docs/civicrm-replacement.md): reminders stay staged like legacy's
 * MembershipPaymentReminder/CiviCrm::FIND_*_REMINDER (2 weeks, 4 weeks),
 * but the trigger is the ledger balance's shortfall duration, not the
 * calendar — see Membership::oldestUnpaidChargeDueDate(). If the balance
 * clears at any point the escalation resets, same as legacy would have if
 * it ever looked at the amount (it didn't).
 *
 * Banktransfer-only, matching legacy's own confirmed scope
 * (FIND_FIRST_REMINDER() et al. all filter on
 * 'Beitrag.Zahlungsweise:label' = 'Banküberweisung'): a direct-debit
 * membership's failure mode is a Rücklastschrift, already handled by
 * BankStatementMatcher::confirmChargeback(), not a reminder email — nobody
 * needs nudging toward a payment method they're already on.
 *
 * Termination past the final stage is new: legacy's own "aborted" stage
 * only ever set a status label (Zahlungsstatus = "Unterbrochen"), never
 * touched standing. A membership actually being terminated for
 * non-payment must have been notified first — a membership with no
 * resolvable email address is skipped entirely (not just the send) rather
 * than silently escalating/terminating someone who was never told.
 */
class PaymentReminderProcessor
{
    private const FIRST_REMINDER_WEEKS = 2;

    private const SECOND_REMINDER_WEEKS = 4;

    private const TERMINATION_WEEKS = 6;

    /**
     * @return array{sent: array{first: int, second: int, terminated: int}, skipped_no_email: int}
     */
    public function process(): array
    {
        $sent = ["first" => 0, "second" => 0, "terminated" => 0];
        $skippedNoEmail = 0;

        $memberships = Membership::where("standing", "active")
            ->where("payment_method", "banktransfer")
            ->get();

        foreach ($memberships as $membership) {
            if ($membership->ledgerBalance() <= 0) {
                if ($membership->reminder_stage !== null) {
                    $membership->update(["reminder_stage" => null]);
                }

                continue;
            }

            $dueDate = $membership->oldestUnpaidChargeDueDate();
            $weeksOverdue = $dueDate->diffInWeeks(Carbon::today());
            $terminationDate = $dueDate->copy()->addWeeks(self::TERMINATION_WEEKS);

            [$email, $name] = $this->recipient($membership);
            if ($email === null) {
                $skippedNoEmail++;

                continue;
            }

            if ($weeksOverdue >= self::TERMINATION_WEEKS) {
                $this->send($membership, PaymentReminder::STAGE_TERMINATED, $dueDate, $terminationDate, $email, $name);
                $membership->update(["standing" => "terminated", "reminder_stage" => null]);
                $sent["terminated"]++;
            } elseif ($weeksOverdue >= self::SECOND_REMINDER_WEEKS && $membership->reminder_stage !== "second") {
                $this->send($membership, PaymentReminder::STAGE_SECOND, $dueDate, $terminationDate, $email, $name);
                $membership->update(["reminder_stage" => "second"]);
                $sent["second"]++;
            } elseif ($weeksOverdue >= self::FIRST_REMINDER_WEEKS && $membership->reminder_stage === null) {
                $this->send($membership, PaymentReminder::STAGE_FIRST, $dueDate, $terminationDate, $email, $name);
                $membership->update(["reminder_stage" => "first"]);
                $sent["first"]++;
            }
        }

        return ["sent" => $sent, "skipped_no_email" => $skippedNoEmail];
    }

    /**
     * A company payer has no email of its own — see Company's docblock —
     * so its contactPerson stands in, same fallback DebitCreator/
     * createForRecurContribution() already use for a display name.
     *
     * @return array{0: string|null, 1: string}
     */
    private function recipient(Membership $membership): array
    {
        $contact = $membership->contact;
        if ($contact !== null) {
            return [$contact->email, $contact->name()];
        }

        $contactPerson = $membership->company?->contactPerson;
        if ($contactPerson !== null) {
            return [$contactPerson->email, $membership->company->name];
        }

        return [null, ""];
    }

    private function send(Membership $membership, string $stage, Carbon $dueDate, Carbon $terminationDate, string $email, string $name): void
    {
        Mail::mailer("membership")->send(new PaymentReminder($membership, $stage, $dueDate, $terminationDate, $email, $name));
    }
}
