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
        Schema::create('parc_km', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('auto_id');
            $table->date('date');
            $table->integer('km');

            $table->unique(['auto_id', 'date'], 'auto_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parc_km');
    }
};
