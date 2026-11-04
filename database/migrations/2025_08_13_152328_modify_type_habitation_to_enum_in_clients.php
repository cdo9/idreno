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
        Schema::table('clients', function (Blueprint $table) {
            // Modifier le champ type_habitation en enum avec les nouvelles valeurs
            $table->enum('type_habitation', ['', 'Maison Individuelle', 'Appartement'])
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
            $table->string('type_habitation')->nullable()->change();
        });
    }
};
