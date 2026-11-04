<?php

use App\Livewire\VentesCommissions\Ventes;
use App\Models\User;
use App\Models\Vente;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;

uses(LazilyRefreshDatabase::class);

it('forbids a commercial from changing a sale of another commercial', function (string $action, string $column) {
    $vente = Vente::factory()->create();

    $this->actingAs(User::factory()->create());

    Livewire::test(Ventes::class)
        ->call($action, $vente->id, ...($action === 'toggleVenteStatus' ? [true] : []));

    expect($vente->fresh()->only($column))->toBe($vente->only($column));
})->with([
    'sale status' => ['toggleVenteStatus', 'statut'],
    'order status' => ['toggleCommande', 'commande'],
    'commission status' => ['toggleCommission', 'commission'],
]);

it('lets the commercial of the sale and directeurs change its status', function (string $editor) {
    $vente = Vente::factory()->create();
    $users = [
        'commercial of the sale' => fn () => User::find($vente->id_commercial),
        'directeur' => fn () => User::factory()->directeur()->create(),
    ];

    $this->actingAs($users[$editor]());

    Livewire::test(Ventes::class)->call('toggleVenteStatus', $vente->id, true);

    expect($vente->fresh()->statut)->toBe(2);
})->with(['commercial of the sale', 'directeur']);

it('lets only admins remove a waiting reason from a sale', function (string $role, string $expectedAttente) {
    $vente = Vente::factory()->create(['attente' => 'Financement;Photos']);

    $this->actingAs(User::factory()->create(['role' => $role]));

    Livewire::test(Ventes::class)->call('handleAttente', $vente->id, 'Photos');

    expect($vente->fresh()->attente)->toBe($expectedAttente);
})->with([
    'admin' => ['admin', 'Financement'],
    'directeur' => ['directeur', 'Financement;Photos'],
]);
