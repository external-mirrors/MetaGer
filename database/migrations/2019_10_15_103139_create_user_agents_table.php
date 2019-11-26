<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserAgentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('useragents')->create('user_agents', function (Blueprint $table) {
            $table->increments('id');
            $table->string('platform');
            $table->string('browser');
            $table->enum('device', ["desktop", "tablet", "mobile"]);
            $table->string('useragent', 300);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('useragents')->dropIfExists('user_agents');
    }
}
