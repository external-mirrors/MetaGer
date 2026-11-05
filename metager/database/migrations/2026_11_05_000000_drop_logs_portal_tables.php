<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Drops the Logs API customer portal's tables.
 *
 * The portal — accounts for researchers buying access to the search query log,
 * billed through InvoiceNinja — never had customers and has been removed. What
 * was left behind is personal data nothing reads: account e-mail addresses,
 * signed NDAs, subscriptions, orders and hashed API keys.
 *
 * `logs_partitioned`, the search query log itself, is a different feature and
 * stays.
 *
 * Children first: four tables reference logs_user.
 */
return new class extends Migration
{
    private const CREATES = [
        '2024_08_20_103005_create_logs_user.php',
        '2024_08_23_095904_create_logs_abo.php',
        '2024_08_23_115033_create_logs_nda.php',
        '2024_08_23_122531_create_logs_order.php',
        '2024_08_27_132141_create_logs_access_key.php',
    ];

    public function up(): void
    {
        Schema::dropIfExists('logs_access_key');
        Schema::dropIfExists('logs_order');
        Schema::dropIfExists('logs_nda');
        Schema::dropIfExists('logs_abo');
        Schema::dropIfExists('logs_user');
    }

    /** The schema comes back, empty. The rows do not. */
    public function down(): void
    {
        foreach (self::CREATES as $migration) {
            (require __DIR__ . '/' . $migration)->up();
        }
    }
};
