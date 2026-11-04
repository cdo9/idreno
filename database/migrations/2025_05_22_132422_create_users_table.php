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
        Schema::table('users', function (Blueprint $table) {
            $table->string('nom', 512);
            $table->string('prenom', 512);
            $table->integer('objectif_ca');
            $table->integer('objectif_rdv');
            $table->integer('type');
            $table->integer('directeur')->nullable()->default(0);
            $table->integer('groupe');
            $table->integer('telepro')->default(0);
            $table->integer('prospecteur')->nullable()->default(0);
            $table->integer('junior')->nullable()->default(0);
            $table->string('role', 100)->nullable();
            $table->string('contrat', 100)->nullable();
            $table->integer('ordre_affichage')->default(0);
            $table->integer('active')->default(0)->comment('user\'s activation status');
            $table->string('activation_hash', 40)->nullable()->comment('user\'s email verification hash string');
            $table->char('password_reset_hash', 40)->nullable()->comment('user\'s password reset code');
            $table->bigInteger('password_reset_timestamp')->nullable()->comment('timestamp of the password reset request');
            $table->boolean('failed_logins')->default(false)->comment('user\'s failed login attemps');
            $table->integer('last_failed_login')->nullable()->comment('unix timestamp of last failed login attempt');
            $table->dateTime('registration_datetime')->nullable()->useCurrent();
            $table->string('registration_ip', 39)->default('0.0.0.0');
            $table->date('delete')->nullable();
            $table->date('popup_winner_loser')->nullable();
            $table->string('horaires')->nullable();
            $table->date('date_entree')->nullable();
            $table->string('lat', 20)->nullable();
            $table->string('lng', 20)->nullable();
            $table->dateTime('gps')->nullable();
            $table->integer('nouveau_vendeur')->nullable()->default(0);
            $table->integer('formateur')->nullable()->default(0);
            $table->date('date_entretien')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
