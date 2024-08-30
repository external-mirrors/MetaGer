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
            // Create Indices
            DB::statement("CREATE INDEX logs_partitioned_focus_idx ON logs_partitioned (focus)");
            DB::statement("CREATE INDEX logs_partitioned_locale_idx ON logs_partitioned (locale)");
            DB::statement("CREATE INDEX logs_partitioned_time_idx ON logs_partitioned (time)");
            DB::statement("CREATE INDEX logs_partitioned_time_locale_focus_idx ON logs_partitioned (time, locale, focus)");
            DB::statement("CREATE INDEX logs_partitioned_time_locale_idx ON logs_partitioned (time, locale)");

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
                $table->index("focus");
                $table->index("locale");
                $table->index("time");
                $table->index(["time", "locale", "focus"]);
                $table->index(["time", "locale"]);
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
