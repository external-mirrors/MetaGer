<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A finished, non-update, reduction-resolved application now moves into
     * suma-crm's own admin review queue (docs/civicrm-replacement.md,
     * "Membership application review moves to suma-crm") rather than
     * waiting on this app's adminAccept()/adminDeny(). It can't simply be
     * deleted at that point, though: `MembershipController::success()`
     * still resolves it locally by id to render the key/QR/bookmark
     * confirmation the applicant is redirected to immediately afterward
     * (`finishedUser()`, a different scope than the admin-review one this
     * marker affects). This column lets `adminIndex()` stop offering a
     * pushed application for review — `MembershipController
     * ::maybePushToSumaCrm()` sets it instead of deleting the row — while
     * leaving it in place for `success()` and, later, `abortApplication()`.
     */
    public function up(): void
    {
        Schema::table("membership_applications", function (Blueprint $table) {
            $table->timestamp("pushed_to_crm_at")->nullable();
        });
    }

    public function down(): void
    {
        Schema::table("membership_applications", function (Blueprint $table) {
            $table->dropColumn("pushed_to_crm_at");
        });
    }
};
