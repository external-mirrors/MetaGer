<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable("logs_partitioned")) {
            return;
        }
        if (config("database.default") === "pgsql") {
            DB::statement("CREATE TABLE logs_partitioned(
                time    timestamp not null,
                referer varchar(250),
                request_time numeric(5,2),
                focus   varchar(20),
                locale  varchar(5),
                query   text not null
            ) PARTITION BY RANGE (time)");
            // Default Partition
            DB::statement("CREATE TABLE logs_partitioned_default PARTITION OF logs_partitioned DEFAULT");
            // Create Partitions for this and the following year
            // ToDo
        } else {
            Schema::create('logs_partitioned', function (Blueprint $table) {
                if (config("database.default") === "sqlite") {
                    $table->integer("time");    // There is no "timestamp" type in sqlite
                } else {
                    $table->dateTime("time");
                }
                $table->string("referer", 250)->nullable();
                $table->float("request_time", 2)->nullable();
                $table->string("focus", 20)->nullable();
                $table->string("locale", 5)->nullable();
                $table->text("query");
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs_partitioned');
    }
};
