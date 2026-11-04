<?php

use App\Livewire\EditArgumentation;
use App\Models\Client;
use App\Models\RendezVous;
use App\Models\Secteur;
use App\Models\Source;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;

uses(LazilyRefreshDatabase::class);

it('refuses a sale whose shares between commercials do not add up to 100%', function () {
    $user = User::factory()->directeur()->create();
    $client = Client::factory()->create();

    $this->actingAs($user);
    session(['selectedSecteur' => Secteur::factory()->create()->id]);

    Livewire::test(EditArgumentation::class)
        ->set('client_id', $client->id)
        ->set('email', 'client@example.com')
        ->set('date_anniversaire', '1970-01-01')
        ->set('id_commercial', $user->id)
        ->set('partage_avec', User::factory()->create()->id)
        ->set('id_source', Source::factory()->create()->id)
        ->set('complement', 'none')
        ->set('vente_ok', 'ok')
        ->set('devis_total_ht', 20000)
        ->set('tarif_total_ht', 20000)
        ->set('repartition_1', 60)
        ->set('repartition_2', 30)
        ->call('save')
        ->assertHasErrors(['repartition_1', 'repartition_2', 'repartition_3']);

    expect(RendezVous::where('id_client', $client->id)->exists())->toBeFalse();
});

it('accepts a sale whose shares add up to 100%', function () {
    $user = User::factory()->directeur()->create();
    $client = Client::factory()->create();

    $this->actingAs($user);
    session(['selectedSecteur' => Secteur::factory()->create()->id]);

    Livewire::test(EditArgumentation::class)
        ->set('client_id', $client->id)
        ->set('email', 'client@example.com')
        ->set('date_anniversaire', '1970-01-01')
        ->set('id_commercial', $user->id)
        ->set('partage_avec', User::factory()->create()->id)
        ->set('id_source', Source::factory()->create()->id)
        ->set('complement', 'none')
        ->set('vente_ok', 'ok')
        ->set('devis_total_ht', 20000)
        ->set('tarif_total_ht', 20000)
        ->set('repartition_1', 60)
        ->set('repartition_2', 40)
        ->call('save')
        ->assertHasNoErrors();

    expect(RendezVous::where('id_client', $client->id)->exists())->toBeTrue();
});
