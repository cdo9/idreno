<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modifie les colonnes en unsignedBigInteger
        Schema::table('clients', function (Blueprint $table) {
            $table->unsignedBigInteger('rentre_par')->nullable()->change();
            $table->unsignedBigInteger('partage_avec')->nullable()->change();
        });

        DB::table('clients')->where('partage_avec', 0)->update(['partage_avec' => null]);
        DB::table('clients')->where('rentre_par', 0)->update(['rentre_par' => null]);

        DB::table('clients')
            ->whereNotNull('rentre_par')
            ->whereNotIn('rentre_par', function ($query) {
                $query->select('id')->from('users');
            })
            ->update(['rentre_par' => null]);

        DB::table('clients')
            ->whereNotNull('partage_avec')
            ->whereNotIn('partage_avec', function ($query) {
                $query->select('id')->from('users');
            })
            ->update(['partage_avec' => null]);

        
        

        // Ajoute les index
        Schema::table('clients', function (Blueprint $table) {
            $table->index('rentre_par');
            $table->index('partage_avec');
        });

        // Ajoute les clés étrangères
        Schema::table('clients', function (Blueprint $table) {
            $table->foreign('rentre_par')->references('id')->on('users')->onDelete('set null');
            $table->foreign('partage_avec')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['rentre_par']);
            $table->dropForeign(['partage_avec']);
            $table->dropIndex(['rentre_par']);
            $table->dropIndex(['partage_avec']);
            $table->dropColumn(['rentre_par', 'partage_avec']);
        });
    }
};
