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
            'eau_chaude' => DB::raw("CASE
                WHEN eau_chaude = '' THEN NULL
                ELSE eau_chaude END")
        ]);

        Schema::table('clients', function (Blueprint $table) {
            // Modifier le champ eau_chaude en enum avec les nouvelles valeurs
            $table->enum('eau_chaude', ['Cumulus', 'Chaudière', 'Autre'])
            ->default(null)
            ->nullable()
            ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('eau_chaude')->change();
        });
    }
};
