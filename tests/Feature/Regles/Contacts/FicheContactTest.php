<?php

/*
 * Règles propres à IdReno sur la fiche contact (absentes de Homiso) :
 * appartement, « accepte démarchage », doublon par adresse + appartement,
 * contrôle de distance GPS (alerte au-delà de 50 m, refus au-delà de 200 m
 * à la création). Voir COMPARAISON-HOMISO-IDRENO.md § 3.4.
 */

use App\Models\Alert;
use App\Models\Client;
use App\Models\Secteur;
use App\Models\Source;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $this->secteur = Secteur::factory()->create();
    $this->source = Source::factory()->create();
    $this->user = User::factory()->create();
    $this->actingAs($this->user)->withSession(['selectedSecteur' => $this->secteur->id]);
});

function nouveauContact(array $overrides = []): array
{
    return array_merge([
        'source' => test()->source->id,
        'nom' => 'Martin',
        'prenom' => 'Claire',
        'adresse' => '12 rue des Lilas',
        'code_postal' => '69140',
        'ville' => 'Rillieux-la-Pape',
        'telephone1' => '0600000000',
        'type' => 1,
        'type_habitation' => 'Maison Individuelle',
        'secteur_id' => test()->secteur->id,
        // Le formulaire envoie toujours la position du point et celle du commercial.
        'gps_lat' => '45.8000', 'gps_lng' => '4.9000',
        'user_lat' => '45.8000', 'user_lng' => '4.9000',
    ], $overrides);
}

it('requires the canvassing consent for an apartment', function () {
    $this->post(route('formulaire-contact'), nouveauContact(['type_habitation' => 'Appartement']))
        ->assertSessionHasErrors('accept_demarchage');

    expect(Client::count())->toBe(0);
});

it('does not require the canvassing consent for a house', function () {
    $this->post(route('formulaire-contact'), nouveauContact())
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect(Client::first()->accept_demarchage)->toBeFalse();
});

it('stores the apartment and the canvassing consent', function () {
    $this->post(route('formulaire-contact'), nouveauContact([
        'type_habitation' => 'Appartement',
        'appart' => 'Bât. B, 3e étage',
        'accept_demarchage' => '1',
    ]))->assertRedirect()->assertSessionHasNoErrors();

    $client = Client::first();

    expect($client->appart)->toBe('Bât. B, 3e étage')
        ->and($client->accept_demarchage)->toBeTrue();
});

it('makes the address optional', function () {
    $this->post(route('formulaire-contact'), nouveauContact(['adresse' => null]))
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect(Client::count())->toBe(1);
});

it('treats the same address with another apartment as a new contact', function () {
    $this->post(route('formulaire-contact'), nouveauContact(['appart' => 'Apt 1']));
    $this->post(route('formulaire-contact'), nouveauContact(['appart' => 'Apt 2']))
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect(Client::count())->toBe(2);
});

it('rejects the same address and apartment as a duplicate', function () {
    $this->post(route('formulaire-contact'), nouveauContact(['appart' => 'Apt 1']));
    $this->post(route('formulaire-contact'), nouveauContact(['appart' => 'Apt 1']))
        // Le message « Un client existe déjà à cette adresse. » est converti en
        // notification toastr par php-flasher : on vérifie le retour au formulaire.
        ->assertRedirect()
        ->assertSessionHasInput('appart', 'Apt 1');

    expect(Client::count())->toBe(1);
});

it('raises an alert when the commercial is more than 50 m from the mapped point', function () {
    // 0,0009° de latitude ≈ 100 m
    $this->post(route('formulaire-contact'), nouveauContact([
        'gps_lat' => '45.8000', 'gps_lng' => '4.9000',
        'user_lat' => '45.8009', 'user_lng' => '4.9000',
    ]))->assertRedirect()->assertSessionHasNoErrors();

    expect(Client::count())->toBe(1)
        ->and(Alert::where('id_commercial', $this->user->id)->count())->toBe(1);
});

it('raises no alert within 50 m', function () {
    // 0,0003° de latitude ≈ 33 m
    $this->post(route('formulaire-contact'), nouveauContact([
        'gps_lat' => '45.8000', 'gps_lng' => '4.9000',
        'user_lat' => '45.8003', 'user_lng' => '4.9000',
    ]))->assertRedirect()->assertSessionHasNoErrors();

    expect(Alert::count())->toBe(0);
});

it('refuses to create a contact more than 200 m away from the commercial', function () {
    // 0,0027° de latitude ≈ 300 m
    $this->post(route('formulaire-contact'), nouveauContact([
        'gps_lat' => '45.8000', 'gps_lng' => '4.9000',
        'user_lat' => '45.8027', 'user_lng' => '4.9000',
    ]))->assertSessionHasErrors('gps_lat');

    expect(Client::count())->toBe(0);
});
