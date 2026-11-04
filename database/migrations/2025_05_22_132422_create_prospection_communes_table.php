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
        Schema::create('prospection_communes', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('nom', 100);
            $table->string('zone', 100);
            $table->date('date')->default('2000-01-01');
            $table->string('commerciaux', 200)->nullable();
            $table->integer('secteur');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prospection_communes');
    }
};
