<?php

use App\Livewire\EditArgumentation;
use App\Livewire\EditContact;
use App\Models\Client;
use App\Models\RendezVous;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;

uses(LazilyRefreshDatabase::class);

it('forbids a commercial from editing a contact entered by someone else', function () {
    $client = Client::factory()->create();

    $this->actingAs(User::factory()->create());

    Livewire::test(EditContact::class, ['clientId' => $client->id])
        ->assertForbidden();
});

it('lets the author, the shared commercial and directeurs edit a contact', function (string $editor) {
    $sharedWith = User::factory()->create();
    $client = Client::factory()->sharedWith($sharedWith)->create();
    $users = [
        'author' => fn () => User::find($client->rentre_par),
        'shared' => fn () => $sharedWith,
        'directeur' => fn () => User::factory()->directeur()->create(),
    ];

    $this->actingAs($users[$editor]());

    Livewire::test(EditContact::class, ['clientId' => $client->id])
        ->assertOk();
})->with(['author', 'shared', 'directeur']);

it('forbids a commercial from editing the argumentation of a client entered by someone else', function () {
    $rendezVous = RendezVous::factory()->create();

    $this->actingAs(User::factory()->create());

    Livewire::test(EditArgumentation::class, ['rdvId' => $rendezVous->id])
        ->assertForbidden();
});

it('lets the client author edit the argumentation', function () {
    $rendezVous = RendezVous::factory()->create();

    $this->actingAs(User::find($rendezVous->client->rentre_par));

    Livewire::test(EditArgumentation::class, ['rdvId' => $rendezVous->id])
        ->assertOk();
});
