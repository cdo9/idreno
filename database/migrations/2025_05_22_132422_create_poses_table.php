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
        Schema::create('poses', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_vente');
            $table->integer('id_client')->index('id_client');
            $table->string('produit');
            $table->string('produit2');
            $table->string('produit3');
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->integer('visite_chantier')->nullable()->default(0);
            $table->integer('avis_client')->nullable()->default(0);
            $table->integer('indicateur')->default(1);
            $table->foreignId('id_commercial_visite')->nullable()->constrained('users');
            $table->integer('avis_facebook')->nullable()->default(0);
            $table->integer('secteur');

            $table->index(['id_client'], 'id_client_2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('poses');
    }
};
