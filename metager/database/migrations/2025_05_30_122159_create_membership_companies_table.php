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
        Schema::create('membership_companies', function (Blueprint $table) {
            $table->uuid('id')->primary(true);
            $table->string("company")->nullable(false);
            $table->enum("employees", ["1-19", "20-199", ">200"])->nullable(false);
            $table->string("email")->nullable(false);
            $table->uuid("application_id")->unique()->references("id")->on("membership_applications");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_companies');
    }
};
