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
        Schema::create('secteur_commercials', function (Blueprint $table) {
            $table->integer('id_secteur');            
            $table->foreignId('id_commercial')->constrained('users');    

            $table->primary(['id_secteur', 'id_commercial']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('secteur_commercials');
    }
};
