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
        Schema::create('parc_auto', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('immatriculation', 100);
            $table->string('modele', 100);
            $table->string('conducteur', 100);
            $table->date('date_attribution');
            $table->integer('secteur');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parc_auto');
    }
};
