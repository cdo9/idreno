<?php

use App\Livewire\VentesCommissions\Ventes;
use App\Models\RendezVous;
use App\Models\User;
use App\Models\Vente;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;

uses(LazilyRefreshDatabase::class);

it('activates the commission of every line of a sale as of today', function () {
    $this->travelTo('2026-10-29 10:00:00');
    $rendezVous = RendezVous::factory()->create();
    $ventes = Vente::factory()->count(2)->forRendezVous($rendezVous)->create();

    $this->actingAs(User::factory()->directeur()->create());

    Livewire::test(Ventes::class)->call('toggleCommission', $ventes->first()->id);

    foreach ($ventes->map->fresh() as $vente) {
        expect($vente->commission)->toBe(1)
            ->and($vente->date_commission->toDateString())->toBe('2026-10-29');
    }
});

it('deactivates the commission of every line of a sale', function () {
    $rendezVous = RendezVous::factory()->create();
    $ventes = Vente::factory()->count(2)->forRendezVous($rendezVous)->withCommission()->create();

    $this->actingAs(User::factory()->directeur()->create());

    Livewire::test(Ventes::class)->call('toggleCommission', $ventes->first()->id);

    foreach ($ventes->map->fresh() as $vente) {
        expect($vente->commission)->toBe(0)
            ->and($vente->date_commission)->toBeNull();
    }
});
