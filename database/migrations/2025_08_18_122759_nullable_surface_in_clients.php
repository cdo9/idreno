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

        DB::table('clients')->where('surface', '')->update(['surface' => 0]);

        DB::table('clients')->update([
            'surface' => DB::raw("REGEXP_REPLACE(surface, '[^0-9]', '')"),
        ]);

        Schema::table('clients', function (Blueprint $table) {
            $table->integer('surface')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('surface')->nullable(false)->change();
        });
    }
};
