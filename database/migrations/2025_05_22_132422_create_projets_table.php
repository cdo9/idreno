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
        Schema::create('projets', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_client')->index('id_client');
            $table->integer('priorite');
            $table->string('projet', 510);
            $table->string('projet2')->default('');
            $table->string('projet3')->default('');
            $table->string('source', 100);
            $table->integer('estimation');
            $table->date('date_ajout');
            $table->date('date')->nullable();
            $table->integer('rdv_confirme')->default(0);
            $table->date('date_confirmation')->nullable();
            $table->integer('rdv_annule')->default(0);
            $table->string('compte_rendu', 100);
            $table->date('date_realisation');
            $table->string('raison')->default('');
            $table->string('heure');
            $table->string('couple_present')->default('');
            $table->string('temps_disponible')->default('');
            $table->string('quantite1', 10);
            $table->string('quantite2', 10);
            $table->string('quantite3', 10);
            $table->integer('secteur');

            $table->index(['id_client'], 'id_client_2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projets');
    }
};
