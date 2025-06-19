<?php

namespace App\Console\Commands;

use App\Mail\Membership\ApplicationUnfinished;
use App\Models\Membership\MembershipApplication;
use Exception;
use Illuminate\Console\Command;
use Mail;

class MembershipNotifyUnfinished extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
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
        // Delete unfinished applications but send a notification to the user
        $unfinished = MembershipApplication::unfinishedUser()->where("updated_at", "<", now()->subHours(value: 6))->get();
        foreach ($unfinished as $unfinished_application) {
            try {
                $mail = new ApplicationUnfinished($unfinished_application);
                Mail::mailer("membership")->send($mail);
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
