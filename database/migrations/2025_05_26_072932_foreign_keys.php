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

        Schema::table('clients', function (Blueprint $table) {
            $table->foreign('id_source')->references('id')->on('sources');
            $table->foreign('secteur')->references('id')->on('secteurs');
        });
        Schema::table('evaluations', function (Blueprint $table) {
            $table->foreign('id_argu')->references('id')->on('clients');
            $table->foreign('secteur')->references('id')->on('secteurs');
        });
        Schema::table('groupes', function (Blueprint $table) {
            $table->foreign('secteur')->references('id')->on('secteurs');
        });
        Schema::table('objectif_annuel_commercial', function (Blueprint $table) {
            $table->foreign('secteur')->references('id')->on('secteurs');
        });
        Schema::table('objectifs', function (Blueprint $table) {
            $table->foreign('secteur')->references('id')->on('secteurs');
        });
        Schema::table('parc_auto', function (Blueprint $table) {
            $table->foreign('secteur')->references('id')->on('secteurs');
        });
        Schema::table('parc_km', function (Blueprint $table) {
            $table->foreign('auto_id')->references('id')->on('parc_auto');
        });
        Schema::table('pose_details', function (Blueprint $table) {
            $table->foreign('id_pose')->references('id')->on('poses');
            $table->foreign('id_poseur')->references('id')->on('poseurs');
        });

        DB::table('poses')
            ->whereNotIn('id_vente', function ($query) {
                $query->select('id')->from('ventes');
            })
            ->delete();

        Schema::table('poses', function (Blueprint $table) {
            $table->foreign('id_client')->references('id')->on('clients');
            $table->foreign('id_vente')->references('id')->on('ventes');
            $table->foreign('secteur')->references('id')->on('secteurs');
        });
        Schema::table('projets', function (Blueprint $table) {
            $table->foreign('id_client')->references('id')->on('clients');
            $table->foreign('secteur')->references('id')->on('secteurs');
        });
        Schema::table('prospection_communes', function (Blueprint $table) {
            $table->foreign('secteur')->references('id')->on('secteurs');
        });
        Schema::table('prospections', function (Blueprint $table) {
            $table->foreign('secteur')->references('id')->on('secteurs');
        });
        Schema::table('rendez_vous', function (Blueprint $table) {
            $table->foreign('id_client')->references('id')->on('clients');
            $table->foreign('secteur')->references('id')->on('secteurs');
        });
        Schema::table('secteur_commercials', function (Blueprint $table) {
            $table->foreign('id_secteur')->references('id')->on('secteurs');
        });
        Schema::table('tracking', function (Blueprint $table) {
            $table->foreign('id_client')->references('id')->on('clients');
            $table->foreign('secteur')->references('id')->on('secteurs');
        });
        Schema::table('ventes', function (Blueprint $table) {
            $table->foreign('id_client')->references('id')->on('clients');
            $table->foreign('id_rdv')->references('id')->on('rendez_vous');
            $table->foreign('id_source')->references('id')->on('sources');
            $table->foreign('secteur')->references('id')->on('secteurs');
        });
        Schema::table('comments', function (Blueprint $table) {
            $table->foreign('client_id')->references('id')->on('clients');
            $table->foreign('user_id')->references('id')->on('users');
        });
        Schema::table('challenges', function (Blueprint $table) {
            $table->foreign('secteur')->references('id')->on('secteurs');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign('id_source');
            $table->dropForeign('secteur');
        });
        Schema::table('evaluations', function (Blueprint $table) {
            $table->dropForeign('id_vendeur');
            $table->dropForeign('id_formateur');
            $table->dropForeign('id_argu');
            $table->dropForeign('secteur');
        });
        Schema::table('groupes', function (Blueprint $table) {
            $table->dropForeign('id_responsable');
            $table->dropForeign('secteur');
        });
        Schema::table('objectif_annuel_commercial', function (Blueprint $table) {
            $table->dropForeign('id_user');
            $table->dropForeign('secteur');
        });
        Schema::table('objectifs', function (Blueprint $table) {
            $table->dropForeign('id_commercial');
            $table->dropForeign('secteur');
        });
        Schema::table('parc_auto', function (Blueprint $table) {
            $table->dropForeign('secteur');
        });
        Schema::table('parc_km', function (Blueprint $table) {
            $table->dropForeign('auto_id');
            $table->dropForeign('secteur');
        });
        Schema::table('pose_details', function (Blueprint $table) {
            $table->dropForeign('id_pose');
            $table->dropForeign('id_poseur');
        });
        Schema::table('poses', function (Blueprint $table) {
            $table->dropForeign('id_commercial_visite');
            $table->dropForeign('id_client');
            $table->dropForeign('id_vente');
            $table->dropForeign('secteur');
        });
        Schema::table('projets', function (Blueprint $table) {
            $table->dropForeign('id_client');
            $table->dropForeign('secteur');
        });
        Schema::table('prospection_communes', function (Blueprint $table) {
            $table->fdropForeignreign('secteur');
        });
        Schema::table('prospections', function (Blueprint $table) {
            $table->dropForeign('id_commercial');
            $table->dropForeign('secteur');
        });
        Schema::table('rendez_vous', function (Blueprint $table) {
            $table->dropForeign('id_commercial');
            $table->dropForeign('id_client');
            $table->dropForeign('secteur');
        });
        Schema::table('secteur_commercials', function (Blueprint $table) {
            $table->dropForeign('id_secteur');
            $table->dropForeign('id_commercial');
        });
        Schema::table('tracking', function (Blueprint $table) {
            $table->dropForeign('id_commercial');
            $table->dropForeign('id_client');
            $table->dropForeign('secteur');
        });
        Schema::table('ventes', function (Blueprint $table) {
            $table->dropForeign('id_commercial');
            $table->dropForeign('id_commercial_2');
            $table->dropForeign('id_commercial_3');
            $table->dropForeign('id_client');
            $table->dropForeign('id_rdv');
            $table->dropForeign('id_source');
            $table->dropForeign('secteur');
        });
        Schema::table('comments', function (Blueprint $table) {
            $table->dropForeign('client_id');
            $table->dropForeign('user_id');
        });
    }
};
