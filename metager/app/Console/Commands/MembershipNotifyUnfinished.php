<?php

namespace App\Console\Commands;

use App\Mail\Membership\ApplicationUnfinished;
use App\Models\Membership\MembershipApplication;
use Cache;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\RateLimiter;
use Mail;

class MembershipNotifyUnfinished extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    /**
     * The address in an application is whatever the submitter typed, and nobody
     * has proven it is theirs. This mail is therefore the one way the form can
     * be used to make us write to a stranger, and what a mail bomber spends.
     * Real traffic is 2-3 unfinished applications a day, so the budgets sit far
     * above it and far below what hurts our sender reputation.
     *
     * An application over budget is still deleted; only the reminder is
     * skipped. The form itself stays open for everyone.
     */
    public const MAX_PER_HOUR = 10;
    public const MAX_PER_DAY = 30;
    /** One reminder per address in this many days, however often it is entered. */
    public const ADDRESS_QUIET_DAYS = 7;
    public const HOURLY_LIMITER_KEY = "membership:unfinished-mail:hour";
    public const DAILY_LIMITER_KEY = "membership:unfinished-mail:day";

    protected $signature = 'membership:notify-unfinished';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notifies users of unfinished membership applications once to allow finishing the application';

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
        $lock = Cache::lock("console:commands:membership:notify-unfinished:lock", 60 * 5);
        if ($lock->get()) {
            // Delete unfinished applications but send a notification to the user
            $unfinished = MembershipApplication::unfinishedUser()->where("updated_at", "<", now()->subHours(value: 6))->get();
            foreach ($unfinished as $unfinished_application) {
                try {
                    if ($this->mayNotify($unfinished_application)) {
                        $mail = new ApplicationUnfinished($unfinished_application);
                        Mail::mailer("membership")->send($mail);
                    }
                } catch (Exception $ignored) {
                }
                $unfinished_application->delete();
            }

            // Delete unfinished update requests
            $unfinished = MembershipApplication::unfinishedUpdateRequestsUser()->where("updated_at", "<", now()->subHours(value: 6))->get();
            foreach ($unfinished as $unfinished_application) {
                $unfinished_application->delete();
            }
        }
    }

    /**
     * Whether this application's address may get a reminder now, and if so,
     * uses up the budget for it.
     *
     * The address is checked first: a repeat of one victim's address must not
     * eat into the budget real applicants share.
     */
    private function mayNotify(MembershipApplication $application): bool
    {
        $address = $application->contact?->email ?? $application->company?->email;
        if ($address === null) {
            return false;
        }

        $address_key = "membership:unfinished-mail:address:" . sha1(mb_strtolower(trim($address)));
        if (Cache::has($address_key)) {
            return false;
        }
        if (
            RateLimiter::tooManyAttempts(self::HOURLY_LIMITER_KEY, self::MAX_PER_HOUR)
            || RateLimiter::tooManyAttempts(self::DAILY_LIMITER_KEY, self::MAX_PER_DAY)
        ) {
            return false;
        }

        // Counted before sending: a failing mail server must not turn into an
        // unbounded retry of the same batch.
        RateLimiter::hit(self::HOURLY_LIMITER_KEY, 3600);
        RateLimiter::hit(self::DAILY_LIMITER_KEY, 86400);
        Cache::put($address_key, true, now()->addDays(self::ADDRESS_QUIET_DAYS));
        return true;
    }
}
