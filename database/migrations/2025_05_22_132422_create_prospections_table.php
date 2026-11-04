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
        Schema::create('prospections', function (Blueprint $table) {
            $table->integer('id', true);
            $table->foreignId('id_commercial')->constrained('users');    
            $table->string('commune', 100);
            $table->string('zone', 10);
            $table->date('date');
            $table->string('type', 50);
            $table->boolean('termine')->nullable();
            $table->string('heure_debut', 50);
            $table->string('heure_fin', 50);
            $table->integer('secteur');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prospections');
    }
};
