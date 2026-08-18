<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Drops the two affiliate tables.
 *
 * affiliate_clicks recorded clicks on adgoal/admitad affiliate links, so that
 * shops sending users to dead URLs could be found and blacklisted by hand;
 * affiliate_blacklist held the result of that judgement, which
 * load:affiliate-blacklist copied into Redis on every scheduler tick for the
 * search path to read.
 *
 * MetaGer no longer rewrites any result into an affiliate link, so neither has
 * a writer left: the redirect endpoint that filled the Redis click list is
 * gone, and so are both scheduled commands.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('affiliate_clicks');
        Schema::dropIfExists('affiliate_blacklist');
    }

    public function down(): void
    {
        Schema::create('affiliate_clicks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('hostname');
            $table->text('affillink');
            $table->text('link');
            $table->timestamp('created_at')->nullable(false)->useCurrent();
        });

        Schema::create('affiliate_blacklist', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('hostname');
            $table->boolean('blacklist')->default(true);
            $table->timestamp('created_at')->nullable(false)->useCurrent();
        });
    }
};
