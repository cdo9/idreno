<?php

/*
 * Règles propres à IdReno sur les statistiques (différentes de Homiso) :
 * - les clients partagés (partage_avec) comptent pour le commercial ;
 * - le mois est découpé en semaines calendaires (lundi → dimanche), alors que
 *   Homiso utilise des blocs fixes 1-7, 8-14, 15-21, 22-28, 29-fin.
 * Voir COMPARAISON-HOMISO-IDRENO.md § 3.6.
 */

use App\Models\Client;
use App\Models\User;
use App\Services\TableauChiffreService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(LazilyRefreshDatabase::class);

function projetConfirme(Client $client, string $date): void
{
    DB::table('projets')->insert([
        'id_client' => $client->id,
        'priorite' => 1,
        'projet' => 'PAC',
        'source' => '1',
        'estimation' => 0,
        'date_ajout' => $date,
        'compte_rendu' => '',
        'date_realisation' => $date,
        'heure' => '10:00',
        'quantite1' => '',
        'quantite2' => '',
        'quantite3' => '',
        'secteur' => $client->secteur,
        'rdv_annule' => 0,
        'date_confirmation' => $date,
    ]);
}

it('counts the appointments of the clients entered by or shared with the commercial', function () {
    $commercial = User::factory()->create();
    projetConfirme(Client::factory()->create(['rentre_par' => $commercial->id]), '2026-10-05');
    projetConfirme(Client::factory()->sharedWith($commercial)->create(), '2026-10-06');
    projetConfirme(Client::factory()->create(), '2026-10-07');

    expect(TableauChiffreService::calculerNombreRdv($commercial->id, '2026-10-01', '2026-10-31'))->toBe(2);
});

it('splits the month into calendar weeks', function (int $month, array $expectedWeeks) {
    expect(TableauChiffreService::getSemainesDefinitions(2026, $month))->toBe($expectedWeeks);
})->with([
    'october 2026, starts on a thursday' => [10, [[1, 4], [5, 11], [12, 18], [19, 25], [26, 31]]],
    'february 2026, starts on a sunday' => [2, [[1, 1], [2, 8], [9, 15], [16, 22], [23, 28]]],
]);

it('can produce 6 weeks, while the ranking only shows 5 columns', function () {
    // Point d'attention : en novembre 2026, le 30 tombe dans une 6e semaine
    // que le classement (S1 à S5) n'affiche pas.
    expect(TableauChiffreService::getSemainesDefinitions(2026, 11))
        ->toHaveCount(6)
        ->and(TableauChiffreService::getSemainesDefinitions(2026, 11)[5])->toBe([30, 30]);
});
