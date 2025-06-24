<?php

namespace App\Console\Commands;

use App\Mail\Membership\MembershipAdminApplicationNotification;
use App\Models\Membership\MembershipApplication;
use Illuminate\Console\Command;
use Mail;

class MembershipAdminNotifyApplications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'membership:notify-admin {subject? : (optional) Custom subject for the Notification}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notifies admins about pending membership applications and updates';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Delete unfinished applications but send a notification to the user
        $finished = MembershipApplication::finishedAdmin()->orderBy("updated_at", "desc")->get();
        $updates = MembershipApplication::updateRequestsAdmin()->orderBy("updated_at", "desc")->get();
        $reductions = MembershipApplication::reductionRequests()->orderBy("updated_at", "desc")->get();

        if (sizeof($finished) === 0 && sizeof($updates) === 0 && sizeof($reductions) === 0)
            return;

        $subject = $this->argument("subject");
        if (empty($subject)) {
            $subject = "[SUMA-EV] Unbearbeitete Mitgliedsanträge";
        }

        if (!empty(config("metager.metager.membership.notification_address"))) {
            $mail = new MembershipAdminApplicationNotification($finished, $updates, $reductions, $subject);
            Mail::mailer("membership")->send($mail);
        }
    }
}
