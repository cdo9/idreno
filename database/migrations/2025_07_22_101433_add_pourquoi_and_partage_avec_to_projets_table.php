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
        Schema::table('projets', function (Blueprint $table) {
            $table->string('pourquoi')->nullable();
            $table->unsignedBigInteger('partage_avec')->nullable();
            $table->foreign('partage_avec')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projets', function (Blueprint $table) {
            $table->dropForeign(['partage_avec']);
            $table->dropColumn('pourquoi');
            $table->dropColumn('partage_avec');
        });
    }
};
