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

        Schema::table('clients', function (Blueprint $table) {
            // Modifier le champ type_chauffage en enum avec les nouvelles valeurs
            $table->string('type_chauffage')->nullable()->change();
        });

        DB::table('clients')->update([
            'type_chauffage' => DB::raw("CASE
                WHEN type_chauffage = '' THEN NULL
                ELSE type_chauffage END")
        ]);

        Schema::table('clients', function (Blueprint $table) {
            // Modifier le champ type_chauffage en enum avec les nouvelles valeurs
            $table->enum('type_chauffage', ['Électrique', 'Fioul', 'Gaz', 'Poêle', 'Autre'])
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
            $table->string('type_chauffage')->change();
        });
    }
};
