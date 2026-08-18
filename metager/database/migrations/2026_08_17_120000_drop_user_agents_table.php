<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Drops the user_agents table.
 *
 * It backed a user-agent rotation scheme that stopped being wired up: the
 * middleware that filled it (UserAgentMaster) was registered as an alias but
 * attached to no route, so nothing pushed to the "useragents" Redis list and
 * nothing read a row back. The scheduled requests:useragents command kept
 * inserting and pruning an empty table every five minutes regardless.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('user_agents');
    }

    public function down(): void
    {
        Schema::create('user_agents', function (Blueprint $table) {
            $table->increments('id');
            $table->string('platform');
            $table->string('browser');
            $table->enum('device', ["desktop", "tablet", "mobile"]);
            $table->string('useragent', 300);
            $table->timestamps();
            $table->index(["platform", "browser", "device"], "useragent_random_select_idx");
        });
    }
};
