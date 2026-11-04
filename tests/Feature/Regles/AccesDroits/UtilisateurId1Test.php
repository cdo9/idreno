<?php

use App\Models\User;

/*
 * Règle « À confirmer » (.ai/rules/acces-droits.md) : comportement hérité, non validé par le client.
 */
it('gives user 1 access to admin pages whatever their role', function () {
    $user = User::factory()->make(['id' => 1, 'role' => 'commercial']);

    expect($user->canAccessPage('gestion-utilisateurs'))->toBeTrue();
});
