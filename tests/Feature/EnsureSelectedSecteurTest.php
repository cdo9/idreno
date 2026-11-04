<?php

use App\Models\SecteurCommercial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

beforeEach(function () {
    Schema::disableForeignKeyConstraints();
    Route::middleware('web')->get('/_test/selected-secteur', fn () => (string) session('selectedSecteur'));
});

it('restores the selected secteur when the session lost it', function () {
    $user = (new User)->forceFill(['id' => 42]);
    SecteurCommercial::create(['id_secteur' => 5, 'id_commercial' => $user->id]);

    $this->actingAs($user)
        ->get('/_test/selected-secteur')
        ->assertSee('5');
});

it('keeps the secteur already selected in the session', function () {
    $user = (new User)->forceFill(['id' => 42]);
    SecteurCommercial::create(['id_secteur' => 5, 'id_commercial' => $user->id]);

    $this->actingAs($user)
        ->withSession(['selectedSecteur' => 7])
        ->get('/_test/selected-secteur')
        ->assertSee('7');
});
