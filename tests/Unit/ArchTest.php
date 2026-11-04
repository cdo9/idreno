<?php

arch('All files on App\Livewire\Traits directory are traits.')
    ->expect('App\Livewire\Traits')
    ->toBeTraits();

arch('All files on App\Models directory are classes and extend default Eloquent Model.')
    ->expect('App\Models')
    ->toBeClasses()
    ->toExtend('Illuminate\Database\Eloquent\Model');

arch('All files on App\Enums directory are enums.')
    ->expect('App\Enums')
    ->toBeEnums();

arch('All files on App\Http\Controllers directory use Controller suffix and extend the base Controller.')
    ->expect('App\Http\Controllers')
    ->toHaveSuffix('Controller')
    ->toExtend('App\Http\Controllers\Controller')
    ->ignoring('App\Http\Controllers\Controller');

arch('All files on App\Http\Middleware directory are classes and define handle.')
    ->expect('App\Http\Middleware')
    ->toBeClasses()
    ->toHaveMethod('handle');

arch('All files on App\Services directory are classes and use Service suffix.')
    ->expect('App\Services')
    ->toBeClasses()
    ->toHaveSuffix('Service')
    ->ignoring('App\Services\StatsContactsServices');

arch('All files on App\Console\Commands directory extend the console Command.')
    ->expect('App\Console\Commands')
    ->toExtend('Illuminate\Console\Command');

arch('All files on App\Livewire directory are Livewire components.')
    ->expect('App\Livewire')
    ->toExtend('Livewire\Component')
    ->ignoring('App\Livewire\Traits');

arch('Only App\Http\Controllers classes may use Controller suffix.')
    ->expect('App')
    ->not->toHaveSuffix('Controller')
    ->ignoring('App\Http\Controllers');

// Bugs connus IdReno : dd() actifs dans ces 3 fichiers (voir
// COMPARAISON-HOMISO-IDRENO.md § 5). Retirer chaque fichier de la liste
// dès que son dd() est corrigé.
arch('No debug helpers are left in app code.')
    ->expect(['dd', 'dump', 'ray'])
    ->not->toBeUsed()
    ->ignoring([
        'App\Providers\AppServiceProvider',
        'App\Livewire\VentesCommissions\Commissions',
        'App\Livewire\VentesCommissions\Ventes',
        'App\Console\Commands\DispatchPlannedSms',
    ]);
