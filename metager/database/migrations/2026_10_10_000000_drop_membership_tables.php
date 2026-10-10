<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Drops the legacy membership form's tables.
 *
 * The form lived here until suma-crm took over applications; MetaGer now only
 * redirects to it. What was left behind is personal data nothing reads any
 * more: applications nobody finished, applications already pushed to suma-crm
 * (kept only so the success page could render), and PayPal order rows detached
 * from deleted applications for webhooks that now go to suma-payments.
 *
 * Children first: five tables reference membership_applications.
 */
return new class extends Migration
{
    private const CREATES = [
        '2025_05_27_170812_create_membership_applications_table.php',
        '2025_05_28_134846_create_membership_contacts_table.php',
        '2025_05_30_122159_create_membership_companies_table.php',
        '2025_05_30_161554_create_membership_payment_directdebits_table.php',
        '2025_06_03_113142_create_membership_payment_paypals_table.php',
        '2025_06_11_112142_create_membership_reductions_table.php',
        '2026_09_16_000000_add_pushed_to_crm_at_to_membership_applications_table.php',
    ];

    public function up(): void
    {
        Schema::dropIfExists('membership_reductions');
        Schema::dropIfExists('membership_payment_paypals');
        Schema::dropIfExists('membership_payment_directdebits');
        Schema::dropIfExists('membership_companies');
        Schema::dropIfExists('membership_contacts');
        Schema::dropIfExists('membership_applications');
    }

    /** The schema comes back, empty. The rows do not. */
    public function down(): void
    {
        foreach (self::CREATES as $migration) {
            (require __DIR__ . '/' . $migration)->up();
        }
    }
};
