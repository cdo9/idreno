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

        DB::table('clients')
            ->where('genre', 'Monsieur et Madame')
            ->update(['genre' => 'Mr & Mme']);

        Schema::table('clients', function (Blueprint $table) {
            // Modifier le champ genre en enum avec les nouvelles valeurs
            
            $table->enum('genre', ['', 'Monsieur', 'Madame', 'Mr & Mme', 'SCI', 'Société'])
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
            // Revenir à un champ string simple
            $table->string('genre')->nullable()->change();
        });
    }
};
