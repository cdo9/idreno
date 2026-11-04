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
        Schema::create('objectif_annuel_commercial', function (Blueprint $table) {
            $table->integer('id', true);
            $table->foreignId('id_user')->constrained('users');
            $table->integer('year');
            $table->integer('value');
            $table->integer('secteur');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('objectif_annuel_commercial');
    }
};
