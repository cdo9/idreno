<?php

use App\Models\User;

it('grants a role to the users the hierarchy allows', function (string $role, array $attributes) {
    $user = User::factory()->make($attributes);

    expect($user->hasRole($role))->toBeTrue();
})->with([
    'admin is directeur' => ['directeur', ['role' => 'admin']],
    'admin is commercial' => ['commercial', ['role' => 'admin']],
    'directeur is commercial' => ['commercial', ['role' => 'directeur']],
    'directeur flag is directeur' => ['directeur', ['role' => 'commercial', 'directeur' => 1]],
    'resp-prosp is telepro' => ['telepro', ['role' => 'resp-prosp']],
    'telepro flag is telepro' => ['telepro', ['role' => 'commercial', 'telepro' => 1]],
]);

it('refuses a role to the users the hierarchy does not allow', function (string $role, array $attributes) {
    $user = User::factory()->make($attributes);

    expect($user->hasRole($role))->toBeFalse();
})->with([
    'directeur is not admin' => ['admin', ['role' => 'directeur']],
    'commercial is not directeur' => ['directeur', ['role' => 'commercial']],
    'telepro is not commercial' => ['commercial', ['role' => 'telepro']],
    'admin is not telepro' => ['telepro', ['role' => 'admin']],
    'administratif is not commercial' => ['commercial', ['role' => 'administratif']],
]);
