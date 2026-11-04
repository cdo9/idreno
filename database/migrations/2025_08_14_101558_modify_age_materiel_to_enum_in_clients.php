<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        DB::table('clients')->update([
            'age_materiel' => DB::raw("CASE
                WHEN age_materiel = '' THEN NULL
                ELSE age_materiel END"),
        ]);

        Schema::table('clients', function (Blueprint $table) {
            $table->enum('age_materiel', ['0-5 ans', '5-10 ans', '10-15 ans', '15+'])
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
            $table->string('age_materiel')->change();
        });
    }
};
