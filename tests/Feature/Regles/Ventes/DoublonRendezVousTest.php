<?php

use App\Livewire\EditArgumentation;
use App\Models\RendezVous;
use App\Models\Source;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;

uses(LazilyRefreshDatabase::class);

it('refuses to create a second argumentation for the same client on the same date', function () {
    $rendezVous = RendezVous::factory()->create(['date' => '2026-10-15']);
    $user = User::factory()->directeur()->create();

    $this->actingAs($user);
    session(['selectedSecteur' => $rendezVous->secteur]);

    Livewire::test(EditArgumentation::class)
        ->set('client_id', $rendezVous->id_client)
        ->set('email', 'client@example.com')
        ->set('date', '2026-10-15')
        ->set('id_commercial', $user->id)
        ->set('id_source', Source::factory()->create()->id)
        ->set('complement', 'none')
        ->set('commentaire', 'Pas intéressé')
        ->call('save')
        ->assertHasNoErrors();

    expect(RendezVous::where('id_client', $rendezVous->id_client)->count())->toBe(1);
});

it('creates an argumentation for the same client on another date', function () {
    $rendezVous = RendezVous::factory()->create(['date' => '2026-10-15']);
    $user = User::factory()->directeur()->create();

    $this->actingAs($user);
    session(['selectedSecteur' => $rendezVous->secteur]);

    Livewire::test(EditArgumentation::class)
        ->set('client_id', $rendezVous->id_client)
        ->set('email', 'client@example.com')
        ->set('date', '2026-10-16')
        ->set('id_commercial', $user->id)
        ->set('id_source', Source::factory()->create()->id)
        ->set('complement', 'none')
        ->set('commentaire', 'Pas intéressé')
        ->call('save')
        ->assertHasNoErrors();

    expect(RendezVous::where('id_client', $rendezVous->id_client)->count())->toBe(2);
});
