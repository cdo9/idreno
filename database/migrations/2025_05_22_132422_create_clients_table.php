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
        Schema::create('clients', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('nom');
            $table->string('prenom');
            $table->string('adresse');
            $table->string('cp', 10);
            $table->string('ville');
            $table->string('gps_lat')->nullable()->default('');
            $table->string('gps_lng')->nullable()->default('');
            $table->string('telephone', 16);
            $table->string('portable', 16);
            $table->string('mail');
            $table->integer('rentre_par');
            $table->integer('partage_avec')->default(0);
            $table->string('source')->nullable();
            $table->integer('id_doublon')->nullable();
            $table->date('rentre_le')->nullable();
            $table->text('debrief');
            $table->integer('type');
            $table->string('genre');
            $table->integer('id_source')->nullable();
            $table->string('type_habitation');
            $table->string('age');
            $table->string('profession');
            $table->string('profession2')->default('');
            $table->string('nb_foyer', 100)->nullable();
            $table->string('dernier_investissement');
            $table->string('mode_conso');
            $table->string('conso_chauffage')->default('');
            $table->string('credit_impot');
            $table->string('surface')->default('');
            $table->string('type_chauffage')->default('');
            $table->string('proprietaire')->default('');
            $table->string('duree_proprietaire')->default('');
            $table->date('date_relance')->nullable();
            $table->date('date_anniversaire')->nullable();
            $table->string('isolation', 100)->nullable();
            $table->string('conso_chauffage_qualite', 100)->nullable();
            $table->string('eau_chaude', 100)->nullable();
            $table->string('age_materiel', 100)->nullable();
            $table->integer('bloque')->default(0);
            $table->string('signe', 100)->nullable();
            $table->string('projet', 100)->nullable();
            $table->date('date_dernier_contact')->nullable();
            $table->string('dernier_contact', 500)->nullable();
            $table->integer('secteur');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
