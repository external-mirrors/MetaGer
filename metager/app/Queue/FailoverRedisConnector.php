<?php

namespace App\Queue;

use Illuminate\Queue\Connectors\RedisConnector;

/**
 * Builds App\Queue\FailoverRedisQueue instead of Illuminate's RedisQueue.
 *
 * Registered over the framework's own `redis` connector rather than under a new
 * driver name (App\Providers\AppServiceProvider). Naming a driver would mean
 * config/queue.php could opt out of the fix by accident — a copied connection
 * block saying `'driver' => 'redis'` would silently get the wedging version —
 * and there is no case in this application for wanting that one.
 *
 * The argument list is the parent's, forwarded unchanged; it exists only to
 * name a different class. Worth re-reading against
 * Illuminate\Queue\Connectors\RedisConnector after a framework upgrade, since a
 * new constructor argument would be dropped here silently.
 */
class FailoverRedisConnector extends RedisConnector
{
    /**
     * @param  array  $config
     * @return \Illuminate\Contracts\Queue\Queue
     */
    public function connect(array $config)
    {
        return new FailoverRedisQueue(
            $this->redis,
            $config['queue'],
            $config['connection'] ?? $this->connection,
            $config['retry_after'] ?? 60,
            $config['block_for'] ?? null,
            $config['after_commit'] ?? null,
            $config['migration_batch_size'] ?? -1
        );
    }
}
