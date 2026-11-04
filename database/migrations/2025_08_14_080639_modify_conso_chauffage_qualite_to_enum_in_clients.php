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

        DB::table('clients')->update([
            'conso_chauffage_qualite' => DB::raw("CASE
                WHEN conso_chauffage_qualite = '' THEN NULL
                ELSE conso_chauffage_qualite END")
        ]);

         Schema::table('clients', function (Blueprint $table) {
            // Modifier le champ conso_chauffage_qualite en enum avec les nouvelles valeurs
            $table->enum('conso_chauffage_qualite', ['Basse', 'Moyenne', 'Élevée'])
                    ->nullable()
                    ->default(null)
                    ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // Revenir à un champ string simple
            $table->string('conso_chauffage_qualite')->nullable()->default(null)->change();
        });
    }
};
