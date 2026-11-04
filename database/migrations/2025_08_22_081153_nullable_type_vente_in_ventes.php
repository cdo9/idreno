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
        DB::table('ventes')->where('type_vente', 'normal')->update(['type_vente' => 'Normale']);
        DB::table('ventes')->where('type_vente', 'commune')->update(['type_vente' => 'Commune']);

        Schema::table('ventes', function (Blueprint $table) {
            $table->enum('type_vente', ['Commune', 'Normale'])->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventes', function (Blueprint $table) {
            $table->string('type_vente', 40)->nullable(false)->change();
        });
    }
};
