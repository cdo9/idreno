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
        if (!Schema::hasTable('relances')) {
            Schema::create('relances', function (Blueprint $table) {
                $table->id();
                $table->integer('client_id');
                $table->unsignedBigInteger('commercial_id');
                $table->boolean('relance')->default(false);

                $table->foreign('client_id')->references('id')->on('clients');
                $table->foreign('commercial_id')->references('id')->on('users');
                $table->timestamps();
                $table->unique(['client_id', 'commercial_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('relances');
    }
}; 