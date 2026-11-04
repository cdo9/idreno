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
        Schema::create('rendez_vous', function (Blueprint $table) {
            $table->integer('id', true);
            $table->foreignId('id_commercial')->nullable()->constrained('users');    
            $table->integer('id_client')->index('id_client');
            $table->string('resultat');
            $table->string('commentaire', 510);
            $table->string('type');
            $table->date('date')->nullable();
            $table->date('date_relance')->nullable();
            $table->integer('relance_confirme')->default(0);
            $table->string('source', 100);
            $table->integer('tarif_total_ht');
            $table->integer('devis_total_ht');
            $table->text('produits')->nullable();
            $table->integer('secteur');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rendez_vous');
    }
};
