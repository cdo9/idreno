<?php

/*
 * Chez IdReno, aucune règle « pas d'envoi en août » n'existe dans le code :
 * ce test passe uniquement parce que tous les envois sont désactivés
 * (voir CreneauxEnvoiTest). Si les envois sont réactivés, il faudra décider
 * de la règle d'août pour IdReno. Voir COMPARAISON-HOMISO-IDRENO.md § 3.8.
 */

use App\Models\Secteur;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(LazilyRefreshDatabase::class);

it('sends no planned SMS in August', function (string $date) {
    Http::preventStrayRequests();
    Http::fake(['www.spot-hit.fr/*' => Http::response(['resultat' => 1])]);
    Secteur::factory()->create();

    $this->artisan('sms:dispatch-planned', ['--date' => $date]);

    Http::assertNothingSent();
})->with([
    'business day at 8am' => ['2026-08-03 08:00'],
    'friday evening' => ['2026-08-07 20:00'],
    'last business day at 8pm' => ['2026-08-31 20:00'],
]);
