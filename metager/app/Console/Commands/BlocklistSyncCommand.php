<?php

namespace App\Console\Commands;

use App\Search\BlocklistSync;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Fetch MetaGer's list from the blocklist service: App\Search\BlocklistSync.
 * Scheduled every minute (routes/console.php), and run at once when the
 * service sends its change notice (BlocklistChangedController).
 */
class BlocklistSyncCommand extends Command
{
    protected $signature = "blocklist:sync";

    protected $description = "Fetch MetaGer's blocklist from the blocklist service if it changed";

    public function handle(BlocklistSync $sync): int
    {
        [$status, $message] = $sync->sync();

        if ($status === BlocklistSync::FAILED) {
            Log::warning($message);
            $this->error($message);

            return self::FAILURE;
        }

        $this->info($message);

        return self::SUCCESS;
    }
}
