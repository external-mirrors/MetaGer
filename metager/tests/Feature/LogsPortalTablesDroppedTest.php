<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * The Logs API customer portal's tables are dropped, and the drop rolls back.
 *
 * The portal itself is gone (see LogsPortalRemovedTest); these held its
 * accounts, signed NDAs, subscriptions, orders and hashed API keys — personal
 * data nothing reads any more.
 *
 * `logs_partitioned`, the search query log, shares the prefix and not the
 * feature, so the glob below names the five create migrations exactly rather
 * than matching on "logs".
 *
 * Runs on a private in-memory database for the reason MembershipTablesDroppedTest
 * gives: the suite's own sqlite file is never migrated by the suite.
 */
class LogsPortalTablesDroppedTest extends TestCase
{
    private const TABLES = [
        "logs_user",
        "logs_abo",
        "logs_nda",
        "logs_order",
        "logs_access_key",
    ];

    private const DROP = "2026_11_05_000000_drop_logs_portal_tables.php";

    protected function setUp(): void
    {
        parent::setUp();

        config([
            "database.connections.logs_portal_drop" => ["driver" => "sqlite", "database" => ":memory:", "foreign_key_constraints" => true],
            "database.default" => "logs_portal_drop",
        ]);
        DB::purge("logs_portal_drop");

        foreach (self::TABLES as $table) {
            foreach (glob(database_path("migrations/*_create_{$table}.php")) as $migration) {
                (require $migration)->up();
            }
        }
    }

    public function testEveryPortalTableIsDropped(): void
    {
        $this->assertTrue(Schema::hasTable("logs_access_key"), "The create migrations did not run, so the drop proves nothing.");

        DB::table("logs_user")->insert(["email" => "researcher@example.com"]);
        DB::table("logs_nda")->insert(["user_email" => "researcher@example.com", "nda" => "%PDF"]);
        DB::table("logs_access_key")->insert(["user_email" => "researcher@example.com", "name" => "k", "key" => "hash"]);

        $this->migration()->up();

        foreach (self::TABLES as $table) {
            $this->assertFalse(Schema::hasTable($table), "$table should be gone");
        }
    }

    public function testTheDropRollsBack(): void
    {
        $migration = $this->migration();
        $migration->up();
        $migration->down();

        foreach (self::TABLES as $table) {
            $this->assertTrue(Schema::hasTable($table), "$table should be back");
        }
        $this->assertTrue(Schema::hasColumn("logs_order", "invoice_id"));
    }

    private function migration(): object
    {
        return require database_path("migrations/" . self::DROP);
    }
}
