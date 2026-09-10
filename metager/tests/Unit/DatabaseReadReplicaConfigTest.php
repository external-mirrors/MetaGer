<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Reads go to a replica, writes to the primary.
 *
 * CloudNativePG publishes `<cluster>-rw` (the current primary), `<cluster>-r`
 * (every Ready instance) and `<cluster>-ro` (the replicas). A planned
 * switchover — which is what draining the node a primary sits on triggers —
 * costs writes five to fifteen seconds while one instance is demoted and
 * another promoted. Reads never have to stop for it, because the replicas keep
 * serving; the app only misses that if it sends everything to `-rw`.
 *
 * `DB_READ_HOST` is the switch, and leaving it unset has to leave the
 * connection byte-for-byte what it was — that is the rollback path, and it is
 * what compose, the review environments and this suite run on.
 *
 * config/database.php reads env() directly, so the file is re-evaluated with
 * the environment set rather than read back through config(), which was
 * resolved at boot. Same approach as {@see RedisSentinelConfigTest}, and booted
 * for the same reason: the file calls database_path(). Unlike that one it has to
 * clear the variables as well as set them — see {@see forgetEnv()}.
 *
 * @see \App\Console\Commands\ScheduleWorker::releaseStickyReads()
 */
class DatabaseReadReplicaConfigTest extends TestCase
{
    private const KEYS = ['DB_HOST', 'DB_PORT', 'DB_READ_HOST', 'DB_READ_PORT', 'DB_STICKY'];

    /**
     * @var array<string, mixed>
     */
    private array $restore = [];

    protected function setUp(): void
    {
        parent::setUp();

        foreach (self::KEYS as $key) {
            $this->restore[$key] = [$_ENV[$key] ?? null, $_SERVER[$key] ?? null, getenv($key)];
            $this->forgetEnv($key);
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->restore as $key => [$env, $server, $putenv]) {
            $this->forgetEnv($key);

            if ($env !== null) {
                $_ENV[$key] = $env;
            }
            if ($server !== null) {
                $_SERVER[$key] = $server;
            }
            if ($putenv !== false) {
                putenv("{$key}={$putenv}");
            }
        }

        parent::tearDown();
    }

    /**
     * All three layers, not just putenv().
     *
     * Laravel reads env() through a repository whose adapters are $_SERVER,
     * $_ENV and getenv(), in that order, and Dotenv populates all three from
     * the .env file. `putenv('KEY')` clears only the last of them — so a test
     * that unsets a variable that way still reads whatever .env said, and the
     * CI test job runs against a copy of the production .env
     * (.gitlab/ci/integrationtest.yml). The moment DB_READ_HOST is set there,
     * {@see testWithoutAReadHostTheConnectionIsUnsplit} would be asserting the
     * opposite of what it says, in CI only.
     */
    private function forgetEnv(string $key): void
    {
        unset($_ENV[$key], $_SERVER[$key]);
        putenv($key);
    }

    private function setEnv(string $key, string $value): void
    {
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
        putenv("{$key}={$value}");
    }

    /**
     * @param array<string, string> $env
     * @return array<string, mixed>
     */
    private function pgsqlConfigWith(array $env): array
    {
        foreach ($env as $key => $value) {
            $this->setEnv($key, $value);
        }

        $config = require base_path('config/database.php');

        return $config['connections']['pgsql'];
    }

    public function testWithoutAReadHostTheConnectionIsUnsplit(): void
    {
        $pgsql = $this->pgsqlConfigWith(['DB_HOST' => 'cluster-rw']);

        $this->assertArrayNotHasKey('read', $pgsql);
        $this->assertArrayNotHasKey('write', $pgsql);
        $this->assertArrayNotHasKey('sticky', $pgsql);
        $this->assertSame('cluster-rw', $pgsql['host']);
    }

    public function testAReadHostSplitsTheConnection(): void
    {
        $pgsql = $this->pgsqlConfigWith([
            'DB_HOST' => 'cluster-rw',
            'DB_READ_HOST' => 'cluster-r',
        ]);

        $this->assertSame('cluster-r', $pgsql['read']['host']);
        $this->assertSame('cluster-rw', $pgsql['write']['host']);
    }

    /**
     * The two Services listen on the same port and nobody wants to say 5432
     * twice, so the read port follows DB_PORT unless it is given its own.
     */
    public function testTheReadPortFollowsTheWritePortUnlessOverridden(): void
    {
        $shared = $this->pgsqlConfigWith([
            'DB_HOST' => 'cluster-rw',
            'DB_PORT' => '5433',
            'DB_READ_HOST' => 'cluster-r',
        ]);

        $this->assertSame('5433', $shared['read']['port']);

        $split = $this->pgsqlConfigWith([
            'DB_READ_PORT' => '6432',
        ]);

        $this->assertSame('6432', $split['read']['port']);
        $this->assertSame('5433', $split['write']['port']);
    }

    /**
     * Sticky is what stops a page rendering from a replica that has not caught
     * up with a write the same request just made. On by default, and the env
     * var is there to turn it off deliberately, not to have to remember it.
     */
    public function testStickyIsOnByDefaultAndCanBeTurnedOff(): void
    {
        $on = $this->pgsqlConfigWith([
            'DB_HOST' => 'cluster-rw',
            'DB_READ_HOST' => 'cluster-r',
        ]);

        $this->assertTrue($on['sticky']);

        $off = $this->pgsqlConfigWith(['DB_STICKY' => 'false']);

        $this->assertFalse($off['sticky']);
    }

    /**
     * The connect timeout is not lost to the split.
     *
     * `read` and `write` are merged over the rest of the connection config
     * (ConnectionFactory::mergeReadWriteConfig), so anything they do not name
     * survives — but the pgsql block's PDO::ATTR_TIMEOUT is load-bearing
     * enough to have a test of its own ({@see DatabaseConfigWiringTest}), and
     * a split that quietly dropped it from one of the two connections would
     * reintroduce the hang on whichever half was unlucky.
     */
    public function testTheSplitKeepsTheConnectTimeoutOnBothHalves(): void
    {
        $pgsql = $this->pgsqlConfigWith([
            'DB_HOST' => 'cluster-rw',
            'DB_READ_HOST' => 'cluster-r',
        ]);

        $this->assertArrayHasKey(\PDO::ATTR_TIMEOUT, $pgsql['options']);
        $this->assertArrayNotHasKey('options', $pgsql['read']);
        $this->assertArrayNotHasKey('options', $pgsql['write']);
    }

    /**
     * And the shape actually splits.
     *
     * Everything above asserts the config file's output; this asserts what
     * Laravel does with it, because the contract being relied on is entirely
     * the framework's — that selects take the `read` half and writes the
     * `write` half — and it is exercised in production the first time anyone
     * deploys the change, not before.
     *
     * Driven on sqlite with two files standing in for the two Services. There
     * is no Postgres in this suite and no replica if there were, and the
     * routing under test is the connection's, not the driver's.
     */
    public function testLaravelSendsSelectsToTheReadHalfAndWritesToTheWriteHalf(): void
    {
        [$read, $write] = $this->twoDatabases();

        $this->assertSame('replica', DB::connection('split')->table('t')->value('v'));

        DB::connection('split')->table('t')->insert(['v' => 'written']);

        $this->assertSame(
            ['primary', 'written'],
            $this->rowsIn($write),
            'the insert did not land on the write half'
        );
        $this->assertSame(
            ['replica'],
            $this->rowsIn($read),
            'the insert landed on the read half'
        );
    }

    /**
     * And having written, the same connection stops reading the replica —
     * which is the whole of what `sticky` buys, and the reason replication lag
     * is not visible inside a request.
     */
    public function testAConnectionThatHasWrittenReadsFromTheWriteHalf(): void
    {
        $this->twoDatabases();

        $this->assertSame('replica', DB::connection('split')->table('t')->value('v'));

        DB::connection('split')->table('t')->insert(['v' => 'written']);

        $this->assertSame(
            ['primary', 'written'],
            DB::connection('split')->table('t')->pluck('v')->all(),
            'a request read a replica after writing to the primary'
        );
    }

    /**
     * Two sqlite files, one row each, wired up as the read and write halves of
     * one connection. The rows differ so that every assertion above can name
     * which half answered rather than counting.
     *
     * @return array{0: string, 1: string}
     */
    private function twoDatabases(): array
    {
        $read = tempnam(sys_get_temp_dir(), 'mg-replica-');
        $write = tempnam(sys_get_temp_dir(), 'mg-primary-');

        config([
            'database.connections.split' => [
                'driver' => 'sqlite',
                'database' => $write,
                'prefix' => '',
                'foreign_key_constraints' => false,
                'read' => ['database' => $read],
                'write' => ['database' => $write],
                'sticky' => true,
            ],
        ]);

        foreach (['replica' => $read, 'primary' => $write] as $row => $file) {
            $pdo = new \PDO('sqlite:' . $file);
            $pdo->exec('create table t (v text)');
            $pdo->exec("insert into t (v) values ('{$row}')");
        }

        DB::purge('split');

        $this->beforeApplicationDestroyed(function () use ($read, $write) {
            DB::purge('split');
            @unlink($read);
            @unlink($write);
        });

        return [$read, $write];
    }

    /**
     * @return list<string>
     */
    private function rowsIn(string $file): array
    {
        return (new \PDO('sqlite:' . $file))
            ->query('select v from t order by rowid')
            ->fetchAll(\PDO::FETCH_COLUMN);
    }
}
