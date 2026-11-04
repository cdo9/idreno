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
            // Modifier le champ nb en enum avec les nouvelles valeurs
            $table->enum('nb_foyer', ['0', '1', '2', '3+'])
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
            // Revert the changes made to the nb_foyer column
            $table->string('nb_foyer')->default('0')->change();
        });
    }
};
