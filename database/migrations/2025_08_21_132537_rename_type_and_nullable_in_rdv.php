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

        Schema::table('rendez_vous', function(Blueprint $table){
            $table->string('type')->nullable()->change();
        });

        DB::table('rendez_vous')->where('type', 0)->update(['type' => null]);

        DB::table('rendez_vous')
            ->whereNotNull('type')
            ->whereNotIn('type', function ($query) {
                $query->select('id')->from('users');
            })
            ->update(['type' => null]);
        
        Schema::table('rendez_vous', function (Blueprint $table) {
            $table->renameColumn('type', 'partage_avec');
            $table->unsignedBigInteger('partage_avec')->nullable()->change();
            $table->foreign('partage_avec')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rendez_vous', function (Blueprint $table) {
            $table->dropForeign(['partage_avec']);
        });
    }
};
