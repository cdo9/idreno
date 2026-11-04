<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tracking', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_client');
            $table->foreignId('id_commercial')->constrained('users');
            $table->dateTime('datetime');
            $table->string('gps_lat', 20);
            $table->string('gps_lng', 20);
            $table->string('user_lat', 20)->nullable();
            $table->string('user_lng', 20)->nullable();
            $table->integer('secteur');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracking');
    }
};
