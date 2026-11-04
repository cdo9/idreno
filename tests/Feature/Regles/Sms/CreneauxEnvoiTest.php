<?php

/*
 * Comportement IdReno actuel : tous les envois planifiés sont commentés dans
 * DispatchPlannedSms, donc aucun SMS ne part, même aux créneaux de Homiso.
 * Voir COMPARAISON-HOMISO-IDRENO.md § 3.8. Est-ce volontaire ? Question § 6.4.
 */

use App\Models\Secteur;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(LazilyRefreshDatabase::class);

it('sends no planned SMS, even at the Homiso slots', function (string $date) {
    Http::preventStrayRequests();
    Http::fake(['www.spot-hit.fr/*' => Http::response(['resultat' => 1])]);
    Secteur::factory()->create();

    $this->artisan('sms:dispatch-planned', ['--date' => $date])->assertSuccessful();

    Http::assertNothingSent();
})->with([
    'business day at 8am' => ['2026-10-26 08:00'],
    'friday evening' => ['2026-10-23 20:00'],
    'last business day at 8pm' => ['2026-09-30 20:00'],
]);

it('sends no SMS outside the slots', function (string $date) {
    Http::preventStrayRequests();
    Http::fake(['www.spot-hit.fr/*' => Http::response(['resultat' => 1])]);
    Secteur::factory()->create();

    $this->artisan('sms:dispatch-planned', ['--date' => $date]);

    Http::assertNothingSent();
})->with([
    'saturday at 8am' => ['2026-10-31 08:00'],
    'business day afternoon' => ['2026-10-27 14:00'],
    'thursday evening' => ['2026-10-22 20:00'],
]);
