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
        Schema::create('ventes', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_rdv');
            $table->integer('id_client');
            $table->foreignId('id_commercial')->constrained('users');
            $table->foreignId('id_commercial_2')->nullable()->constrained('users');
            $table->foreignId('id_commercial_3')->nullable()->constrained('users');
            $table->integer('tarif_total_ht');
            $table->integer('ca_total_ht');
            $table->integer('repartition');
            $table->integer('tarif_ht');
            $table->integer('ca_ht');
            $table->integer('remise');
            $table->integer('montant');
            $table->integer('plus_value');
            $table->integer('prime_cee');
            $table->date('date');
            $table->integer('id_source')->nullable();
            $table->string('paiement', 512);
            $table->string('type_vente', 40);
            $table->integer('statut');
            $table->date('fin_financement')->nullable();
            $table->date('dateAdd')->nullable();
            $table->string('complement')->default('');
            $table->string('attente', 100);
            $table->string('attente_autre')->nullable();
            $table->integer('commande')->nullable();
            $table->date('date_commission')->nullable();
            $table->integer('commission')->nullable();
            $table->string('commentaire', 510)->nullable();
            $table->integer('secteur');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventes');
    }
};
