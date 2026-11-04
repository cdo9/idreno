<?php

use App\Http\Middleware\CheckBlocagesAccess;
use App\Livewire\Blocages;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    Route::middleware(['web', 'auth', 'blocages'])->group(function () {
        Route::get('/_test/page', fn () => 'page')->name('_test.page');
        Route::get('/_test/nouveau-contact', fn () => 'nouveau-contact')->name('nouveau-contact');
    });
});

/**
 * Le calcul d'un blocage (écarts, chantiers…) est une autre règle : ici on le force.
 */
function simulateBlocage(bool $isBlocked): void
{
    $blocages = Mockery::mock(Blocages::class)->makePartial();
    $blocages->shouldReceive('hasBlocageQuelconque')->andReturn($isBlocked);

    $middleware = new CheckBlocagesAccess;
    (new ReflectionProperty(CheckBlocagesAccess::class, 'blocages'))->setValue($middleware, $blocages);

    app()->instance(CheckBlocagesAccess::class, $middleware);
}

it('redirects a blocked user to the catch-up page', function () {
    simulateBlocage(true);

    $this->actingAs(User::factory()->create())
        ->get('/_test/page')
        ->assertRedirectToRoute('rattrapage');
});

it('keeps the allowed pages accessible to a blocked user', function () {
    simulateBlocage(true);

    $this->actingAs(User::factory()->create())
        ->get('/_test/nouveau-contact')
        ->assertOk();
});

it('lets a blocked user released today through', function () {
    simulateBlocage(true);

    $this->actingAs(User::factory()->releasedToday()->create())
        ->get('/_test/page')
        ->assertOk();
});

it('blocks again a user released on a previous day', function () {
    simulateBlocage(true);

    $this->actingAs(User::factory()->create(['released' => today()->subDay()]))
        ->get('/_test/page')
        ->assertRedirectToRoute('rattrapage');
});

it('never blocks an admin', function () {
    simulateBlocage(true);

    $this->actingAs(User::factory()->admin()->create())
        ->get('/_test/page')
        ->assertOk();
});

it('lets a user without blocage through', function () {
    simulateBlocage(false);

    $this->actingAs(User::factory()->create())
        ->get('/_test/page')
        ->assertOk();
});
