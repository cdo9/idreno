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

/*
 * Règle « À confirmer » (.ai/rules/ventes-argumentations.md) : le code refuse un devis
 * supérieur au tarif, alors que le message parle d'un devis inférieur. Ce test fige le
 * comportement actuel du code en attendant la décision du client.
 */
it('refuses an argumentation whose quote exceeds the tarif', function (int $devis, bool $isSaved) {
    $user = User::factory()->directeur()->create();
    $client = Client::factory()->create();

    $this->actingAs($user);
    session(['selectedSecteur' => Secteur::factory()->create()->id]);

    Livewire::test(EditArgumentation::class)
        ->set('client_id', $client->id)
        ->set('email', 'client@example.com')
        ->set('id_commercial', $user->id)
        ->set('id_source', Source::factory()->create()->id)
        ->set('complement', 'none')
        ->set('commentaire', 'Trop cher')
        ->set('devis_total_ht', $devis)
        ->set('tarif_total_ht', 20000)
        ->call('save');

    expect(RendezVous::where('id_client', $client->id)->exists())->toBe($isSaved);
})->with([
    'quote above the tarif' => [25000, false],
    'quote below the tarif' => [15000, true],
]);
