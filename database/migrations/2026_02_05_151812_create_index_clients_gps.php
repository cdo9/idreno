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
            $table->index(['gps_lat', 'gps_lng', 'secteur'], 'clients_gps_secteur_index');
            $table->index(['cp', 'ville', 'adresse'], 'clients_adr_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropIndex('clients_gps_secteur_index');
            $table->dropIndex('clients_adr_idx');
        });
    }
};
