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
        Schema::create('evaluations', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_argu');
            $table->foreignId('id_vendeur')->constrained('users');
            $table->foreignId('id_formateur')->constrained('users');            
            $table->string('action', 100)->comment('slug action');
            $table->string('fait_par', 1)->comment('V ou F');
            $table->integer('acquis')->comment('0 (non acquis), 1 (en cours), 2 (acquis)');
            $table->integer('secteur');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
