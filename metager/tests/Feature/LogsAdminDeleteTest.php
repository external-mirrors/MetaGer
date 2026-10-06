<?php

namespace Tests\Feature;

use App\Http\Controllers\LogsApiController;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LogsAdminDeleteTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app['router']->get('/admin/logs', function () {
            return 'ok';
        })->name('logs:admin');
    }

    #[Test]
    public function test_admin_delete_removes_related_log_rows_before_deleting_user(): void
    {
        $email = 'delete-me@example.com';
        DB::table('logs_user')->insert([
            'email' => $email,
            'discount' => 0,
            'created_at' => now('UTC'),
            'updated_at' => now('UTC'),
        ]);
        DB::table('logs_abo')->insert([
            'user_email' => $email,
            'interval' => 'monthly',
            'monthly_price' => 5.0,
            'created_at' => now('UTC'),
            'updated_at' => now('UTC'),
        ]);
        DB::table('logs_nda')->insert([
            'user_email' => $email,
            'nda' => 'test',
            'created_at' => now('UTC'),
            'updated_at' => now('UTC'),
        ]);
        DB::table('logs_order')->insert([
            'user_email' => $email,
            'from' => now('UTC'),
            'to' => now('UTC')->addMonth(),
            'price' => 10.0,
            'created_at' => now('UTC'),
            'updated_at' => now('UTC'),
        ]);
        // logs_access_key has no updated_at column (see its migration) — only
        // created_at and a nullable accessed_at.
        DB::table('logs_access_key')->insert([
            'user_email' => $email,
            'name' => 'api key',
            'key' => 'secret',
            'created_at' => now('UTC'),
        ]);

        $response = (new LogsApiController())->admin(new Request(['action' => 'delete', 'email' => $email]));

        $this->assertNotNull($response);
        $this->assertNull(DB::table('logs_user')->where('email', $email)->first());
        $this->assertSame(0, DB::table('logs_abo')->where('user_email', $email)->count());
        $this->assertSame(0, DB::table('logs_nda')->where('user_email', $email)->count());
        $this->assertSame(0, DB::table('logs_order')->where('user_email', $email)->count());
        $this->assertSame(0, DB::table('logs_access_key')->where('user_email', $email)->count());
    }
}
