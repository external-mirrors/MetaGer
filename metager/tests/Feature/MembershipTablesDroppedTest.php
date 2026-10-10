<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * The legacy membership form's tables are dropped, and the drop rolls back.
 *
 * Runs on a private in-memory database rather than the suite's, because the
 * suite never migrates: its sqlite file is whatever the development
 * entrypoint last left behind, which says nothing about a fresh deploy.
 */
class MembershipTablesDroppedTest extends TestCase
{
    private const TABLES = [
        "membership_applications",
        "membership_contacts",
        "membership_companies",
        "membership_payment_directdebits",
        "membership_payment_paypals",
        "membership_reductions",
    ];

    private const DROP = "2026_10_10_000000_drop_membership_tables.php";

    protected function setUp(): void
    {
        parent::setUp();

        config([
            "database.connections.membership_drop" => ["driver" => "sqlite", "database" => ":memory:", "foreign_key_constraints" => true],
            "database.default" => "membership_drop",
        ]);
        DB::purge("membership_drop");

        foreach (glob(database_path("migrations/*membership*.php")) as $migration) {
            if (basename($migration) !== self::DROP) {
                (require $migration)->up();
            }
        }
    }

    public function testEveryMembershipTableIsDropped(): void
    {
        DB::table("membership_applications")->insert(["id" => "0b6f1f4e-6f0e-4a8e-9d3c-2a1b5c7d9e0f", "locale" => "de-DE"]);
        DB::table("membership_contacts")->insert(["id" => "5e9c1a2b-4f6d-4c3e-9a71-2b8d0f4e6c15", "title" => "Frau", "application_id" => "0b6f1f4e-6f0e-4a8e-9d3c-2a1b5c7d9e0f", "first_name" => "Erika", "last_name" => "Mustermann", "email" => "erika@example.com"]);

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
        $this->assertTrue(Schema::hasColumn("membership_applications", "pushed_to_crm_at"));
    }

    private function migration(): object
    {
        return require database_path("migrations/" . self::DROP);
    }
}
