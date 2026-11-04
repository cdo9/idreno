<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        DB::statement("
            CREATE OR REPLACE VIEW view_clients_fullname AS
            SELECT
                id,
                nom,
                prenom,
                CONCAT(TRIM(nom), ' ', TRIM(prenom)) AS fullname,
                adresse,
                ville,
                mail,
                cp,
                telephone1,
                telephone2,
                rentre_par,
                partage_avec,
                type_habitation
            FROM clients
        ");
    }

    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS view_clients_fullname');
    }
};
