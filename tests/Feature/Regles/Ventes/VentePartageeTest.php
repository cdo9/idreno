<?php

use App\Livewire\VentesCommissions\Ventes;
use App\Models\RendezVous;
use App\Models\User;
use App\Models\Vente;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;

uses(LazilyRefreshDatabase::class);

it('confirms or cancels every line of a shared sale', function (bool $validate, int $expectedStatut) {
    $rendezVous = RendezVous::factory()->create();
    $ventes = Vente::factory()->count(2)->forRendezVous($rendezVous)->create(['repartition' => 50]);
    $otherVente = Vente::factory()->create();

    $this->actingAs(User::factory()->directeur()->create());

    Livewire::test(Ventes::class)->call('toggleVenteStatus', $ventes->first()->id, $validate);

    expect($ventes->map->fresh()->pluck('statut')->all())->toBe([$expectedStatut, $expectedStatut])
        ->and($otherVente->fresh()->statut)->toBe(1);
})->with([
    'confirmed' => [true, 2],
    'cancelled' => [false, 3],
]);

it('toggles the order of every line of a shared sale', function () {
    $rendezVous = RendezVous::factory()->create();
    $ventes = Vente::factory()->count(2)->forRendezVous($rendezVous)->create(['commande' => 0]);

    $this->actingAs(User::factory()->directeur()->create());

    Livewire::test(Ventes::class)->call('toggleCommande', $ventes->first()->id);

    expect($ventes->map->fresh()->pluck('commande')->all())->toBe([1, 1]);
});

it('removes a waiting reason from every line of a shared sale', function () {
    $rendezVous = RendezVous::factory()->create();
    $ventes = Vente::factory()->count(2)->forRendezVous($rendezVous)->create(['attente' => 'Financement;Photos']);

    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(Ventes::class)->call('handleAttente', $ventes->first()->id, 'Photos');

    expect($ventes->map->fresh()->pluck('attente')->all())->toBe(['Financement', 'Financement']);
});
