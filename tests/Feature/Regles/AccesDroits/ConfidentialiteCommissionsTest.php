<?php

use App\Models\User;
use App\Services\CommissionService;

it('shows every commission to admins and directeurs', function (string $role) {
    $user = User::factory()->make(['id' => 2, 'role' => $role]);

    expect(CommissionService::shouldMaskCommission($user, 99))->toBeFalse();
})->with(['admin', 'directeur']);

it('shows a commercial their own commission', function () {
    $user = User::factory()->make(['id' => 2]);

    expect(CommissionService::shouldMaskCommission($user, 2))->toBeFalse();
});

it('masks the commission of other commercials', function () {
    $user = User::factory()->make(['id' => 2]);

    expect(CommissionService::shouldMaskCommission($user, 99))->toBeTrue();
});
