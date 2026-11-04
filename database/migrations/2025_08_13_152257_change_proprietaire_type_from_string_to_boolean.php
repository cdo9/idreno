<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        DB::table('clients')->update([
            'proprietaire' => DB::raw("CASE
                WHEN proprietaire = 'oui' THEN '1'
                WHEN proprietaire = 'non' THEN '0'
                ELSE '0' END"),
        ]);

        Schema::table('clients', function (Blueprint $table) {
            // Changer le type de la colonne 'proprietaire' de string à boolean
            $table->boolean('proprietaire')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // Revertir le type de la colonne 'proprietaire' de boolean à string
            $table->string('proprietaire')->default('0')->change();
        });
    }
};
